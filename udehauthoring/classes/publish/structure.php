<?php

namespace format_udehauthoring\publish;

use format_udehauthoring\model\course_plan;
use format_udehauthoring\utils;

/**
 * Publishes all course sections and activities according to the content input in the editor.
 * Makes sure that
 *   - all sections exists
 *   - all activities exists
 *   - all activities are in the right section and in the right order
 *   - all course modules have an ID Number, to be able to find them back and update them
 *   - any obsolete activity or section is destroy
 *
 * Does NOT actually fill the course sections and activities with content.
 */
class structure
{
    public static $CONTENT_PLACEHOLDER = 'NIL';
    private static $NB_SYLLABUS_PAGES = 5;

    private $course_plan;
    private $modpage_id;
    private $course;
    private $target;

    public function __construct(course_plan $course_plan, target $target) {
        global $DB;
        $this->course_plan = $course_plan;
        $this->target = $target;
        $this->course = $DB->get_record('course', ['id' => $this->course_plan->courseid]);
        $this->modpage_id = $DB->get_field('modules', 'id', ['name' => 'page'], MUST_EXIST);
    }

    /**
     * Incrementally publish course structure (sections and activities)
     *
     * @throws \moodle_exception
     */
    public function publish() {
        $modinfo = get_fast_modinfo($this->course, -1);

        $cms = [];
        foreach ($modinfo->cms as $cm) {
            $cms[$cm->idnumber] = $cm;
        }

        $sections = $this->publish_course_sections();
        $section_sequences = array_fill(0, count($sections), []);
        $this->publish_syllabus_pages($cms, $sections, $section_sequences);
        $this->publish_section_pages($cms, $sections, $section_sequences);
        $this->publish_subquestion_pages($cms, $sections, $section_sequences);
        $this->publish_course_section_sequences($sections, $section_sequences);
    }

    /**
     * Publish cleanly the course structure, destroying first all previous sections and activities
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function flush_publish() {
        global $DB;
        $context = \context_course::instance($this->course_plan->courseid);

        $course_section_records = array_reverse( $this->target->get_existing_sections($this->course_plan->courseid) );
        foreach($course_section_records as $course_section_record) {
            if (0 == $course_section_record->section) {
                $fs = get_file_storage();
                $fs->delete_area_files($context->id, 'course', 'summary');

                // Cannot delete base section. Let’s at least empty it
                foreach (preg_split('/,/', $course_section_record->sequence, -1, PREG_SPLIT_NO_EMPTY) as $cmid) {
                    course_delete_module($cmid);
                }
                $course_section_record->summary = self::$CONTENT_PLACEHOLDER;
                $DB->update_record('course_sections', $course_section_record);
            } else {
                course_delete_section($this->course_plan->courseid, $course_section_record);
            }
        }

        $this->publish();
    }

    private function publish_course_sections() {
        global $DB;

        $sections_offset = $this->target->get_sections_offset($this->course_plan->courseid);
        $existing_sections = $this->target->get_existing_sections($this->course_plan->courseid);
        $nb_existing_sections = count($existing_sections);
        $nb_required_sections = count($this->course_plan->sections) + 1; // +1 for syllabus section

        if ($nb_existing_sections < $nb_required_sections) {
            // create missing sections

            for ($sectionindex = $nb_existing_sections + $sections_offset; $sectionindex < $nb_required_sections + $sections_offset; ++$sectionindex) {
                $record = course_create_section($this->course_plan->courseid, $sectionindex);
                $record->summary = self::$CONTENT_PLACEHOLDER;
                $record->visible = $this->target->sections_visible();
                $DB->update_record('course_sections', $record);
            }

        } else if ($nb_existing_sections > $nb_required_sections) {
            // remove extra sections

            $obsolete_records = array_slice($existing_sections, $nb_required_sections);

            foreach($obsolete_records as $course_section_record) {
                course_delete_section($this->course_plan->courseid, $course_section_record);
            }
        }

        return $this->target->get_existing_sections($this->course_plan->courseid);
    }

    private function add_page($sectionposition, $cmidnumber) {
        return \add_moduleinfo((object)[
            'modulename' => 'page',
            'section' => $sectionposition,
            'module' => $this->modpage_id,
            'visible' => $this->target->sections_visible(),
            'display' => RESOURCELIB_DISPLAY_OPEN,
            'printheading' => true,
            'printintro' => false,
            'printlastmodified' => false,
            'cmidnumber' => $cmidnumber,
            'name' => $cmidnumber,
            'content' => self::$CONTENT_PLACEHOLDER
        ], $this->course);
    }

    private function publish_syllabus_pages($cms, $sections, &$section_sequences) {

        $sectionposition = $sections[0]->section;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0);
        if (!isset($cms[$cmidnumber])) {
            $modinfo = $this->add_page($sectionposition, $cmidnumber);
            $cmid = $modinfo->coursemodule;
        } else {
            $cmid = $cms[$cmidnumber]->id;
        }

        $section_sequences[0][] = $cmid;

        for ($index = 0; $index < self::$NB_SYLLABUS_PAGES; ++$index) {
            $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, $index);
            if (!isset($cms[$cmidnumber])) {
                $modinfo = $this->add_page($sectionposition, $cmidnumber);
                $cmid = $modinfo->coursemodule;
            } else {
                $cmid = $cms[$cmidnumber]->id;
            }

            $section_sequences[0][] = $cmid;
        }
    }

    private function publish_section_pages($cms, $sections, &$section_sequences) {
        global $DB;

        $existing_cmidnumbers = $this->target->filter_cmidnumbers(array_keys($cms), $this->course_plan->courseid, '([1-9]|\d{,2})');

        foreach ($this->course_plan->sections as $ii => $section) {
            $sectionindex = $ii + 1;
            $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $sectionindex);
            if (!isset($cms[$cmidnumber])) {
                $modinfo = $this->add_page($sections[$sectionindex]->section, $cmidnumber);
                $cmid = $modinfo->coursemodule;
            } else {
                $existing_cmidnumbers = array_diff($existing_cmidnumbers, [$cmidnumber]);
                $cmid = $cms[$cmidnumber]->id;
                if ($cms[$cmidnumber]->section != $sections[$sectionindex]->id) {
                    $cmrecord = $DB->get_record('course_modules', ['id' => $cmid]);
                    $cmrecord->section = $sections[$sectionindex]->id;
                    $DB->update_record('course_modules', $cmrecord);
                }
            }

            $section_sequences[$sectionindex][] = $cmid;
        }

        // evaluations page
        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, false, false, true);
        if (!isset($cms[$cmidnumber])) {
            $modinfo = $this->add_page($sections[0]->section, $cmidnumber);
            $cmid = $modinfo->coursemodule;
        } else {
            $existing_cmidnumbers = array_diff($existing_cmidnumbers, [$cmidnumber]);
            $cmid = $cms[$cmidnumber]->id;
        }
        $section_sequences[0][] = $cmid;

        foreach ($existing_cmidnumbers as $obsolete_cmidnumber) {
            course_delete_module($cms[$obsolete_cmidnumber]->id);
        }
    }

    private function publish_subquestion_pages($cms, $sections, &$section_sequences) {
        $existing_cmidnumbers = $this->target->filter_cmidnumbers(array_keys($cms), $this->course_plan->courseid, '([1-9]|\d{,2})', '\d+');

        foreach ($this->course_plan->sections as $ii => $section) {
            $sectionindex = $ii + 1;
            foreach ($section->subquestions as $jj => $subquestion) {
                $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $sectionindex, $jj);

                if (!isset($cms[$cmidnumber])) {
                    $modinfo = $this->add_page($sections[$sectionindex]->section, $cmidnumber);
                    $cmid = $modinfo->coursemodule;
                } else {
                    $existing_cmidnumbers = array_diff($existing_cmidnumbers, [$cmidnumber]);
                    $cmid = $cms[$cmidnumber]->id;
                }

                $section_sequences[$sectionindex][] = $cmid;
            }
        }

        foreach ($existing_cmidnumbers as $obsolete_cmidnumber) {
            course_delete_module($cms[$obsolete_cmidnumber]->id);
        }
    }

    private function publish_course_section_sequences($sections, $section_sequences) {
        global $DB;
        foreach ($section_sequences as $ii => $sequence) {
            $sections[$ii]->sequence = implode(',', $sequence);
            $DB->update_record('course_sections', $sections[$ii]);
        }
    }
}