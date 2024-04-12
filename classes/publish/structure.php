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

    private $course_plan;
    private $modpage_id;
    private $course;
    private $target;

    public static function get_syllabus_sections(course_plan $course_plan) {
        $sections = [ 'presentation' ];

        if (!empty($course_plan->description)) {
            $sections[] = 'description';
        }

        if (!empty($course_plan->teachingobjectives)) {
            $sections[] = 'teachingobjectives';
        }

        if (!empty($course_plan->problematic)) {
            $sections[] = 'problematic';
        }

        if (!empty($course_plan->place)) {
            $sections[] = 'place';
        }

        if (!empty($course_plan->method)) {
            $sections[] = 'method';
        }

        if (!empty($course_plan->sections)) {
            $sections[] = 'sections';
        }

        if (!empty($course_plan->evaluations)) {
            $sections[] = 'evaluations';
        }

        if (!empty($course_plan->additionalinformation)) {
            $sections[] = 'additionalinformation';
        }

        return $sections;
    }

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
        $sections = $this->publish_course_sections();

        $modinfo = get_fast_modinfo($this->course, -1);
        $cms = [];
        foreach ($modinfo->cms as $cm) {
            if (!empty($cm->idnumber)) {
                $cms[$cm->idnumber] = $cm;
            }
        }

        $this->publish_syllabus_pages($cms, $sections);
        $this->publish_section_pages($cms, $sections);
        $this->publish_subquestion_pages($cms, $sections);
        $this->publish_evaluation_pages($cms, $sections);
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

        $modinfo = get_fast_modinfo($this->course, -1);
        foreach ($modinfo->cms as $cm) {
            if (!empty($cm->idnumber) && false !== $this->target->unpack_cmidnumber($cm->idnumber)) {
                course_delete_module($cm->id);
            }
        }

        $course_section_records = array_reverse( $this->target->get_existing_sections($this->course_plan->courseid) );
        foreach($course_section_records as $course_section_record) {
            if (0 == $course_section_record->section) {
                // Cannot delete base section. Let’s at least empty it
                $fs = get_file_storage();
                $fs->delete_area_files($context->id, 'course', 'summary');
                $course_section_record->summary = self::$CONTENT_PLACEHOLDER;
                $DB->update_record('course_sections', $course_section_record);
            } else {
                course_delete_section($this->course_plan->courseid, $course_section_record);
            }
        }

        get_fast_modinfo($this->course, -1, true);
        $this->publish();
    }

    private function publish_course_sections() {
        global $DB;

        if ($this->target instanceof target\official) {
            // Publish module visibility
            $context = \context_course::instance($this->course_plan->courseid, MUST_EXIST);
            foreach($this->course_plan->sections as $key => $section) {
                $section->isvisible = $section->isvisiblepreview;
                $section->updateVisibility($section->id, $section->isvisiblepreview);
            }
        }

        $sections_offset = $this->target->get_sections_offset($this->course_plan->courseid);
        $existing_sections = $this->target->get_existing_sections($this->course_plan->courseid);
        $nb_existing_sections = count($existing_sections);
        $nb_required_sections = count($this->course_plan->sections) + 2; // modules sections + syllabus section + evaluations section

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

            $obsolete_records = array_slice(
                $existing_sections,
                $nb_required_sections - 1, // leave the last section alone (evaluations)
                $nb_existing_sections - $nb_required_sections);

            foreach(array_reverse($obsolete_records) as $course_section_record) {
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

    private function publish_syllabus_pages($cms, $sections) {

        $sectionposition = $sections[0]->section;

        $syllabus_sections = self::get_syllabus_sections($this->course_plan);

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0);
        if (!isset($cms[$cmidnumber])) {
            $this->add_page($sectionposition, $cmidnumber);
        }

        $existing_cmidnumbers = $this->target->filter_cmidnumbers(array_keys($cms), $this->course_plan->courseid, 0, '([1-9]|\d{,2})');

        for ($index = 0; $index < count($syllabus_sections); ++$index) {
            $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, $index);
            if (!isset($cms[$cmidnumber])) {
                $this->add_page($sectionposition, $cmidnumber);
            } else {
                $existing_cmidnumbers = array_diff($existing_cmidnumbers, [$cmidnumber]);
            }
        }

        foreach ($existing_cmidnumbers as $obsolete_cmidnumber) {
            course_delete_module($cms[$obsolete_cmidnumber]->id);
        }
    }

    private function publish_section_pages($cms, $sections) {
        global $DB;

        $existing_cmidnumbers = $this->target->filter_cmidnumbers(array_keys($cms), $this->course_plan->courseid, '([1-9]|\d{,2})');

        foreach ($this->course_plan->sections as $ii => $section) {
            $sectionindex = $ii + 1;
            $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $sectionindex);
            if (!isset($cms[$cmidnumber])) {
                if ($section->title !== '' && $section->title !== null) {
                    $this->add_page($sections[$sectionindex]->section, $cmidnumber);
                }
            } else {
                $existing_cmidnumbers = array_diff($existing_cmidnumbers, [$cmidnumber]);
                if ($cms[$cmidnumber]->section != $sections[$sectionindex]->id) {
                    $modinfo = get_fast_modinfo($this->course, -1);
                    $section = $modinfo->get_section_info($sections[$sectionindex]->section);
                    moveto_module($cms[$cmidnumber], $section);
                }
            }
        }

        foreach ($existing_cmidnumbers as $obsolete_cmidnumber) {
            course_delete_module($cms[$obsolete_cmidnumber]->id);
        }
    }

    private function publish_evaluation_pages($cms, $sections) {
        global $DB;
        $sectionindex =
            count($this->course_plan->sections) + 1;

        // evaluations page
        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, false, false, true);
        if (!isset($cms[$cmidnumber])) {
            $this->add_page($sections[$sectionindex]->section, $cmidnumber);
        } else {
            if ($cms[$cmidnumber]->section != $sections[$sectionindex]->id) {
                $modinfo = get_fast_modinfo($this->course, -1);
                $section = $modinfo->get_section_info($sections[$sectionindex]->section);
                moveto_module($cms[$cmidnumber], $section);
            }
        }

        // evaluation pages
        $existing_cmidnumbers = $this->target->filter_cmidnumbers(array_keys($cms), $this->course_plan->courseid, '([1-9]|\d{,2})', false, true);

        foreach ($this->course_plan->evaluations as $ii => $evaluation) {
            $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $ii, false, true);
            if (!isset($cms[$cmidnumber])) {
                $this->add_page($sections[$sectionindex]->section, $cmidnumber);
            } else {
                $existing_cmidnumbers = array_diff($existing_cmidnumbers, [$cmidnumber]);
                if ($cms[$cmidnumber]->section != $sections[$sectionindex]->id) {
                    $modinfo = get_fast_modinfo($this->course, -1);
                    $section = $modinfo->get_section_info($sections[$sectionindex]->section);
                    moveto_module($cms[$cmidnumber], $section);
                }
            }
        }

        foreach ($existing_cmidnumbers as $obsolete_cmidnumber) {
            course_delete_module($cms[$obsolete_cmidnumber]->id);
        }
    }

    private function publish_subquestion_pages($cms, $sections) {
        $existing_cmidnumbers = $this->target->filter_cmidnumbers(array_keys($cms), $this->course_plan->courseid, '([1-9]|\d{,2})', '\d+');

        foreach ($this->course_plan->sections as $ii => $section) {
            $sectionindex = $ii + 1;
            foreach ($section->subquestions as $jj => $subquestion) {
                $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $sectionindex, $jj);

                if (!isset($cms[$cmidnumber])) {
                    $this->add_page($sections[$sectionindex]->section, $cmidnumber);
                } else {
                    $existing_cmidnumbers = array_diff($existing_cmidnumbers, [$cmidnumber]);
                }
            }
        }

        foreach ($existing_cmidnumbers as $obsolete_cmidnumber) {
            course_delete_module($cms[$obsolete_cmidnumber]->id);
        }
    }
}