<?php

namespace format_udehauthoring\publish;

use format_udehauthoring\model\evaluation_plan;
use format_udehauthoring\model\evaluationtool_plan;
use format_udehauthoring\model\exploration_plan;
use format_udehauthoring\model\explorationtool_plan;
use format_udehauthoring\publish\content\syllabus;
use format_udehauthoring\utils;

require_once($CFG->libdir . '/gradelib.php');
require_once("{$CFG->libdir}/grade/grade_category.php");
require_once($CFG->dirroot.'/grade/edit/tree/lib.php');

class content
{

    private $course_plan;
    private $course;
    private $syllabus_content;
    private $target;

    public function __construct(\format_udehauthoring\model\course_plan $course_plan, target $target) {
        global $DB;
        $this->course_plan = $course_plan;
        $this->target = $target;
        $this->course = $DB->get_record('course', ['id' => $this->course_plan->courseid]);
        $this->syllabus_content = new syllabus($course_plan);
    }

    public function publish() {
        $modinfo = get_fast_modinfo($this->course, -1);

        $cms = [];
        foreach ($modinfo->cms as $cm) {
            if (!empty($cm->idnumber)) {
                $cms[$cm->idnumber] = $cm;
            }
        }

        $this->publish_course_info();
        $this->publish_course_sections($cms);
        $this->publish_syllabus_pages($cms);
        $this->publish_section_pages($cms);
        $this->publish_subquestion_pages($cms);
        $this->publish_evaluation_pages($cms);

        // organize grade items

        $modinfo = get_fast_modinfo($this->course, -1);

        $cms = [];
        foreach ($modinfo->cms as $cm) {
            if (!empty($cm->idnumber)) {
                $cms[$cm->idnumber] = $cm;
            }
        }

        // Check if category already exists
        $grade_category = \grade_category::fetch([
            'fullname' => get_string('titlegradecategoryignore', 'format_udehauthoring'),
            'courseid' => $this->course->id
        ]);

        // Create category
        if (!$grade_category) {
            $grade_category = new \grade_category(['courseid' => $this->course->id], false);
            $grade_category->apply_default_settings();
            $grade_category->apply_forced_settings();
        }

        \grade_edit_tree::update_gradecategory($grade_category, (object)[
            'fullname' => get_string('titlegradecategoryignore', 'format_udehauthoring'),
            'aggregation' => GRADE_AGGREGATE_SUM,
            'aggregateonlygraded' => 1,
            'aggregateoutcomes' => 0,
            'droplow' => 0,
            'grade_item_itemname' => '',
            'grade_item_iteminfo' => '',
            'grade_item_idnumber' => '',
            'grade_item_gradetype' => GRADE_TYPE_VALUE,
            'grade_item_grademax' => 100,
            'grade_item_grademin' => 0,
            'grade_item_gradepass' => '0',
            'grade_item_display' => '0',
            'grade_item_decimals' => '-1',
            'grade_item_hiddenuntil' => 0,
            'grade_item_locktime' => 0,
            'grade_item_weightoverride' => "1",
            'grade_item_aggregationcoef2' => "0",
        ]);

        $grade_category->set_hidden(1, true);

        // move all preview and tool grade items to the category
        global $DB;
        $last_sortorder = $DB->get_field_select('grade_items', 'MAX(sortorder)', "courseid = ?", array($this->course->id));
        $grade_category->move_after_sortorder($last_sortorder);
        if ($this->target instanceof target\preview) {
            foreach($cms as $idnumber => $cm) {
                if (!$this->target->unpack_cmidnumber($idnumber)) {
                    continue;
                }

                $gi = new \grade_item([
                        'itemmodule' => $cm->modname,
                        'iteminstance' => $cm->instance
                ]);

                if ($gi->id) {
                    $gi->categoryid = $grade_category->id;
                    $gi->update();
                }
            }

            foreach ($this->course_plan->evaluations as $evaluation) {
                $tool = evaluationtool_plan::instance_by_audehevaluationid($evaluation->id);
                if ($tool) {
                    $this->organize_grade_item($grade_category->id, $tool->tooltype, $tool->toolid);
                }
            }

            foreach ($this->course_plan->sections as $section) {
                foreach ($section->subquestions as $subquestion) {
                    foreach ($subquestion->explorations as $exploration) {
                        $tool = explorationtool_plan::instance_by_audehexplorationid($exploration->id);
                        if ($tool) {
                            $this->organize_grade_item($grade_category->id, $tool->tooltype, $tool->toolid);
                        }
                    }
                }
            }
        }
    }

    private function organize_grade_item($categoryid, $modname, $instanceid, $hidden=1) {
        $gi = new \grade_item([
            'itemmodule' => $modname,
            'iteminstance' => $instanceid
        ]);

        if ($gi->id) {
            $gi->categoryid = $categoryid;
            $gi->set_hidden($hidden);
            $gi->update();
        }
    }

    /**
     * HTML rending for listing modules in the course main page. Used by the syllabus as well.
     *
     * @param $vignettefilehtml
     * @param $title
     * @param $description
     * @param $url
     * @return string
     * @throws \coding_exception
     */
    private function render_module_preview($vignettefilehtml, $title, $description, $url) {
        $strexplore = get_string('explore', 'format_udehauthoring');

        return "<div class='udeha-course-section-vignette'>{$vignettefilehtml}</div>" .
            "<div class='udeha-course-section-name'>{$title}</div>" .
            "<div class='udeha-course-section-description'>{$description}</div>" .
            "<div class='udeha-course-section-description-explore'><a class='btn btn-primary' href='{$url}'>$strexplore</a></div>";
    }

    private function render_subquestion_preview($index, $subindex, $text, $modurl) {
        $strindex = '';
        if (!is_null($index)) {
            $strindex = $index;
            if (!is_null($subindex)) {
                $strindex .= ".{$subindex}";
            }
        }
        return <<<EOD
                <tr>
                    <td class="udeha-subquestion-index">
                       {$strindex}
                    </td>
                    <td class="udeha-subquestion-text">
                        {$text}
                    </td>
                    <td class="udeha-subquestion-link">
                         <a href="{$modurl}"><i class='icon fa fa-angle-right'></i></a>
                    </td>
                </tr>
        EOD;
    }

    private function maxtimemodified(...$objs) {
        $timemodifieds = array_map(function($obj) { return $obj->timemodified;}, $objs);

        if (1 < count($timemodifieds)) {
            return max(...$timemodifieds);
        } else if(1 === count($timemodifieds)) {
            return reset($timemodifieds);
        }

        return 0;
    }

    public function publish_course_info() {
        if (!$this->target->rewrite_courseinfo()) {
            return;
        }

        global $DB;
        $course = $DB->get_record('course', ['id' => $this->course_plan->courseid], '*', MUST_EXIST);
        $context = \context_course::instance($course->id);

        $course->fullname  = clean_param($this->course_plan->title, PARAM_TEXT);
        $course->shortname = substr( clean_param(str_replace(' ', '', $this->course_plan->code), PARAM_TEXT), 0, 254 );
        if (empty($course->shortname)) {
            $target = new target\official();
            $course->shortname = $target->make_cmidnumber($course->id);
        }

        utils::copyToFilearea(
            $context->id, 'format_udehauthoring', 'courseintroduction', 0,
            $context->id, 'course', 'summary', 0
        );

        if ($this->course_plan->isembed) {
            $introductionfilehtml = $this->course_plan->embed;
        } else {
            $introductionfilehtml = utils::renderFileAreaHTML($context->id, 'format_udehauthoring', 'courseintroduction', 0);
            if (!empty($introductionfilehtml)) {
                $introductionfilehtml = reset($introductionfilehtml);
            } else {
                $introductionfilehtml = '';
            }
        }

        $course->summary = <<<EOD
            <div class='udeha-course-question'>{$this->course_plan->question}</div>
            <div id="udeha-course-introduction" class="collapse">{$introductionfilehtml}</div>
        EOD;

        $DB->update_record('course', $course);

    }

    public function publish_course_sections($cms) {
        global $DB, $CFG;
        $context_course = \context_course::instance($this->course_plan->courseid);

        $course_section_records = $this->target->get_existing_sections($this->course_plan->courseid);

        // syllabus section
        $course_section_record = $course_section_records[0];

        $vignettehaschanges = utils::copyToFilearea(
            $context_course->id, 'format_udehauthoring', 'coursevignette', 0,
            $context_course->id, 'course', 'section', $course_section_record->id
        );

        if ($vignettehaschanges || $course_section_record->summary === structure::$CONTENT_PLACEHOLDER || empty($course_section_record->summary)) {
            $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0);
            $section_page = $cms[$cmidnumber];
            $page_url = $section_page->url;

            $vignettefilehtml = utils::renderFileAreaHTML($context_course->id, 'format_udehauthoring', 'coursevignette', 0);

            if (empty($vignettefilehtml)) {
                $vignettefilehtml = "<img src='{$CFG->wwwroot}/course/format/udehauthoring/assets/img-front/vignette-defaut-intro.png'>";
            } else {
                $vignettefilehtml = reset($vignettefilehtml);
            }

            $course_section_record->summary = $this->render_module_preview(
                $vignettefilehtml,
                get_string('moduleintroduction', 'format_udehauthoring'),
                get_string('courseplan', 'format_udehauthoring'),
                $page_url
            );
            $course_section_record->timemodified = time();
            $DB->update_record('course_sections', $course_section_record, TRUE);
        }

        // course sections
        foreach ($this->course_plan->sections as $ii => $section) {
            $sectionindex = $ii + 1;
            $course_section_record = $course_section_records[$sectionindex];

            $vignettehaschanges = utils::copyToFilearea(
                $context_course->id, 'format_udehauthoring', 'sectionvignette', $section->id,
                $context_course->id, 'course', 'section', $course_section_record->id
            );

            if (!$vignettehaschanges && $section->timemodified < $course_section_record->timemodified && $course_section_record->summary !== structure::$CONTENT_PLACEHOLDER) {
                continue;
            }

            $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $sectionindex);
            $section_page = $cms[$cmidnumber];
            $page_url = $section_page->url;
            $vignettefilehtml = utils::renderFileAreaHTML($context_course->id, 'format_udehauthoring', 'sectionvignette', $section->id);

            if (empty($vignettefilehtml)) {
                $image_index = $ii % 3;
                $vignettefilehtml = "<img src='{$CFG->wwwroot}/course/format/udehauthoring/assets/img-front/vignette-defaut-{$image_index}.png'>";
            } else {
                $vignettefilehtml = reset($vignettefilehtml);
            }

            $course_section_record->summary = $this->render_module_preview(
                $vignettefilehtml,
                "Module " . $sectionindex . " : " . strip_tags($section->title, '<strong><em><sup><sub>'),
                $section->description,
                $page_url
            );
            $course_section_record->timemodified = time();

            $DB->update_record('course_sections', $course_section_record, TRUE);
        }
    }

    private function update_page($cminfo, $name, $content) {
        global $DB;

        $record = $DB->get_record('page', array('id'=> $cminfo->instance), '*', MUST_EXIST);
        $record->name = substr($name, 0, 254);

        $record->page = [
            'itemid' => 0,
            'text' => $content,
            'format' => $record->contentformat
        ];
        $record->introeditor = [
            'itemid' => 0,
            'text' => $record->intro,
            'format' => $record->introformat
        ];
        $displayoptions = unserialize($record->displayoptions);
        $record->printheading = $displayoptions['printheading'];
        $record->printintro = $displayoptions['printintro'];
        $record->printlastmodified = $displayoptions['printlastmodified'];

        // common required options - not related to the activity type
        $record->course = $this->course_plan->courseid;
        $record->coursemodule = $cminfo->id;
        $record->modulename = 'page';
        $record->groupmode = $cminfo->groupmode;
        $record->groupingid = $cminfo->groupingid;
        $record->visible = $cminfo->visible;
        $record->visibleoncoursepage = $cminfo->visibleoncoursepage;

        update_module($record);
    }

    private function publish_syllabus_pages($cms) {
        $syllabusparts = structure::get_syllabus_sections($this->course_plan);
        $alllearningobjectives = array_reduce($this->course_plan->teachingobjectives, function($all, $teachingobjective) {
            return array_merge($all, $teachingobjective->learningobjectives);
        }, []);
        $maxtimemodified = $this->maxtimemodified(
            $this->course_plan,
            ...$this->course_plan->units,
            ...$this->course_plan->teachingobjectives,
            ...$alllearningobjectives,
            ...$this->course_plan->sections,
            ...$this->course_plan->evaluations);


        $this->publish_syllabus_toc($cms, $syllabusparts, $maxtimemodified);
        $this->publish_syllabus_pdf($cms, $syllabusparts, $maxtimemodified);

        foreach($syllabusparts as $index => $syllabuspart) {
            switch ($syllabuspart) {
                case 'presentation':
                    $this->publish_syllabus_presentation($cms, $index, $maxtimemodified);
                    break;
                case 'description':
                    $this->publish_syllabus_desc($cms, $index, $maxtimemodified);
                    break;
                case 'teachingobjectives':
                    $this->publish_syllabus_objectives($cms, $index, $maxtimemodified);
                    break;
                case 'problematic':
                    $this->publish_syllabus_problematic($cms, $index, $maxtimemodified);
                    break;
                case 'place':
                    $this->publish_syllabus_place($cms, $index, $maxtimemodified);
                    break;
                case 'method':
                    $this->publish_syllabus_method($cms, $index, $maxtimemodified);
                    break;
                case 'sections':
                    $this->publish_syllabus_modules($cms, $index, $maxtimemodified);
                    break;
                case 'evaluations':
                    $this->publish_syllabus_evaluations($cms, $index, $maxtimemodified);
                    break;
                case 'annex':
                    $this->publish_syllabus_extra($cms, $index, $maxtimemodified);
                    break;
            }
        }
    }

    private function publish_syllabus_toc($cms, $syllabusparts, $maxtimemodified) {
        global $DB;
        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        if ($page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $str_download = get_string('downloadcourseplan', 'format_udehauthoring');

        $parts_html = '';
        foreach($syllabusparts as $ii => $partname) {
            $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, $ii);
            $label = get_string("syllabustitle_{$partname}", 'format_udehauthoring');
            $parts_html .= $this->render_subquestion_preview(0, $ii + 1, $label, $cms[$cmidnumber]->url);
        }

        $syllabusfilename = $this->syllabus_content->get_pdf_filename();
        $content = <<<EOD
        <table class='udeha-subquestions'>{$parts_html}</table>
        <div><a class="btn btn-primary" href="@@PLUGINFILE@@/{$syllabusfilename}?forcedownload=1">{$str_download}</a></div>
        EOD;

        $this->update_page($cminfo, get_string('courseplan', 'format_udehauthoring'), $content);
    }

    private function publish_syllabus_pdf($cms, $syllabusparts, $maxtimemodified) {
        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0);
        $cminfo = $cms[$cmidnumber];

        $fs = get_file_storage();
        $context_module = \context_module::instance($cminfo->id);

        $file = $fs->get_file($context_module->id, 'mod_page', 'content', 0, '/', $this->syllabus_content->get_pdf_filename());

        if ($file) {
            $publishtimemodified = $file->get_timemodified();
        } else {
            $publishtimemodified = 0;
        }

        if ($publishtimemodified > $maxtimemodified) {
            return;
        }

        if ($file) {
            $file->delete();
        }

        $fs->create_file_from_string([
            'contextid' => $context_module->id,
            'component' => 'mod_page',
            'filearea' => 'content',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => $this->syllabus_content->get_pdf_filename()
        ], $this->syllabus_content->get_pdf_content());
    }

    private function publish_syllabus_presentation($cms, $index, $maxtimemodified) {
        global $DB;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, $index);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        if ($page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('presentation', 'format_udehauthoring'),
            $this->syllabus_content->get_presentation_content());
    }


    private function publish_syllabus_desc($cms, $index, $maxtimemodified) {
        global $DB;
        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, $index);

        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        if ($page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('coursedescription', 'format_udehauthoring'),
            $this->syllabus_content->get_desc_content());
    }

    private function publish_syllabus_objectives($cms, $index, $maxtimemodified) {
        global $DB;
        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, $index);

        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);


        if ($page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('teachingobjectives', 'format_udehauthoring'),
            $this->syllabus_content->get_objectives_content());
    }

    private function publish_syllabus_problematic($cms, $index, $maxtimemodified) {
        global $DB;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, $index);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        if ($page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('syllabustitle_problematic', 'format_udehauthoring'),
            $this->syllabus_content->get_problematic_content());
    }

    private function publish_syllabus_place($cms, $index, $maxtimemodified) {
        global $DB;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, $index);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        if ($page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('placeprog', 'format_udehauthoring'),
            $this->syllabus_content->get_place_content());
    }

    private function publish_syllabus_method($cms, $index, $maxtimemodified) {
        global $DB;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, $index);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        if ($page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('syllabustitle_method', 'format_udehauthoring'),
            $this->syllabus_content->get_method_content());
    }

    private function publish_syllabus_modules($cms, $index, $maxtimemodified) {
        global $DB;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, $index);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        if ($page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('modulescontent', 'format_udehauthoring'),
            $this->syllabus_content->get_modules_content());
    }

    private function publish_syllabus_evaluations($cms, $index, $maxtimemodified) {
        global $DB;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, $index);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        if ($page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('evaluations', 'format_udehauthoring'),
            $this->syllabus_content->get_evaluations_content());
    }

    private function publish_syllabus_extra($cms, $index, $maxtimemodified) {
        global $DB;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, $index);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        if ($page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('extrainfo', 'format_udehauthoring'),
            $this->syllabus_content->get_extra_content());
    }

    private function publish_section_pages($cms) {
        global $DB;

        foreach ($this->course_plan->sections as $ii => $section) {
            $sectionindex = $ii + 1;
            $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $sectionindex);
            $cminfo = $cms[$cmidnumber];
            $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

            $context_course = \context_course::instance($this->course_plan->courseid);
            $context_module = \context_module::instance($cminfo->id);
            $fileschanged = utils::copyToFilearea(
                $context_course->id, 'format_udehauthoring', 'sectionintroduction', $section->id,
                $context_module->id, 'mod_page', 'content', 0
            );

            if ($evaluation = evaluation_plan::instance_by_section_plan_id($section->id)) {
                $maxtimemodified = $this->maxtimemodified($section, $evaluation, ...$section->subquestions);
            } else {
                $maxtimemodified = $this->maxtimemodified($section, ...$section->subquestions);
            }



            if (!$fileschanged && $page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
                continue;
            }

            // PAGE CONTENT

            // intro media
            if ($section->isembed) {
                $introductionfilehtml = $section->embed;
            } else {
                $introductionfilehtml = utils::renderFileAreaHTML($context_course->id, 'format_udehauthoring', 'sectionintroduction', $section->id);
                if (!empty($introductionfilehtml)) {
                    $introductionfilehtml = reset($introductionfilehtml);
                } else {
                    $introductionfilehtml = '';
                }
            }

            $introductionfilehtml = "<div class='udeha-section-intro-media'>{$introductionfilehtml}</div>";

            //evaluations

            $evaluations_html = '';
            foreach($this->course_plan->evaluations as $jj => $evaluation) {
                if ($section->id === $evaluation->audehsectionid) {
                    $evaltitle = strip_tags($evaluation->title, '<strong><em><sup><sub>');

                    $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $jj, false, true);
                    $evalcminfo = $cms[$cmidnumber];

                    $evalurl = new \moodle_url('/mod/' . $evalcminfo->modname . '/view.php', ['id' => $evalcminfo->id]);

                    $evaluations_html .= "<h3>{$evaltitle}</h3>
                        <div class='udeha-evaluation-description'>{$evaluation->description}</div>
                        <div class='udeha-evaluation-link'><a class='btn btn-primary' href='{$evalurl}'>" . get_string('evalanswer', 'format_udehauthoring') . "</a></div>";
                }
            }
            if (empty($evaluations_html)) {
                $evaluations_html = get_string('noeval', 'format_udehauthoring');
            }
            $evaluations_html = "<div class='udeha-section-evaluations'>{$evaluations_html}</div>";

            $titlesubquestion = get_string('titlesubquestion', 'format_udehauthoring', $sectionindex);

            $str_show = '<i class="icon fa fa-bars"></i>' . get_string('btnsubquestionsshow', 'format_udehauthoring');
            $str_hide = '<i class="icon fa fa-times"></i>' . get_string('btnsubquestionshide', 'format_udehauthoring');
            $sectionelements = <<<EOD
                <hr class="udeha-separator udeha-subquestions-separator">
                <div class="udeha-section-elements">
                    <div class="udeha-section-subquestion">
                        <h3>{$titlesubquestion}</h3>
                        <div class="udeha-question-text">{$section->question}</div>
                        <div class="udeha-subquestions-actions">
                            <a class="collapsed btn-subquestions btn btn-secondary" data-toggle="collapse" href="#udeha-list-subquestions" aria-expanded="false">
                                    <span class="label-show">{$str_show}</span>
                                    <span class="label-hide">{$str_hide}</span>
                            </a>
                        </div>
                    </div>
                    <div class="udeha-section-evaluation">
                        {$evaluations_html}
                    </div>
                </div>
            EOD;

            $subquestions_html = '';
            foreach ($section->subquestions as $subindex => $subquestion) {
                $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $sectionindex, $subindex);
                $subcminfo = $cms[$cmidnumber];
                $subquestions_html .= self::render_subquestion_preview($sectionindex, $subindex+1, $subquestion->title, $subcminfo->url);
            }
            $subquestions_html = "<table class='udeha-subquestions'>{$subquestions_html}</table>";

            $content = $section->introductiontext .
                $introductionfilehtml .
                $sectionelements .
                "<div id='udeha-list-subquestions' class='collapse'>" .
                "<h3>" . get_string('titlesubquestions', 'format_udehauthoring') . "</h3>" .
                $subquestions_html .
                "</div>";

            // END PAGE CONTENT

            $this->update_page($cminfo, $section->title, $content);
        }

    }

    private function publish_subquestion_pages($cms) {
        global $DB, $CFG;

        foreach ($this->course_plan->sections as $ii => $section) {
            $sectionindex = $ii + 1;
            foreach ($section->subquestions as $subindex => $subquestion) {

                // files first
                $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $sectionindex,  $subindex);
                $cminfo = $cms[$cmidnumber];

                $fileareas = [];

                $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

                $context_module = \context_module::instance($cminfo->id);
                $context_course = \context_course::instance($this->course_plan->courseid);

                $fileareas[] = (object)[
                    'folder' => 'subquestionvignette',
                    'contextid' => $context_course->id,
                    'component' => 'format_udehauthoring',
                    'filearea' => 'subquestionvignette',
                    'itemid' => $subquestion->id
                ];

                foreach($subquestion->resources as $resource) {
                    $fileareas[] = (object)[
                        'folder' => 'resourcevignette' . $resource->id,
                        'contextid' => $context_course->id,
                        'component' => 'format_udehauthoring',
                        'filearea' => 'resourcevignette',
                        'itemid' => $resource->id
                    ];
                }

                $haschanged = utils::copyToFileareaMultiple($fileareas, $context_module->id, 'mod_page', 'content', 0);

                $fileshtml = utils::renderFileAreaHTML($context_module->id, 'mod_page', 'content', 0);

                $exp_tools = [];
                foreach ($subquestion->explorations as $exploration) {
                    $exp_tool = explorationtool_plan::instance_by_audehexplorationid($exploration->id);
                    if ($exp_tool) {
                        $exp_tools[] = $exp_tool;
                    }
                }

                $maxtimemodified = $this->maxtimemodified(
                        $subquestion,
                        ...$subquestion->explorations,
                        ...$exp_tools,
                        ...$subquestion->resources);

                if (!$haschanged && $page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
                    continue;
                }

                // explorations

                $explorationsync_html = '';
                $explorationasync_html = '';
                foreach ($subquestion->explorations as $exploration) {

                    switch($exploration->grouping) {
                        case 0:
                            $groupingicon = "<i class='icon fa fa-user'></i>";
                            break;
                        case 1:
                            $groupingicon = "<i class='icon fa fa-user-plus'></i>";
                            break;
                        case 2:
                            $groupingicon = "<i class='icon fa fa-comments'></i>";
                            break;
                        default:
                            $groupingicon = '';
                            break;
                    }

                    // exploration tool duplication

                    $toollink = '';
                    if ($exploration->toolcmid) {
                        list($course, $cmsource) = get_course_and_cm_from_cmid($exploration->toolcmid);

                        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $sectionindex, $subindex, false, 0);

                        // only regenerate if modifications were done
                        if (isset($cms[$cmidnumber])) {
                            $sourceinstance_timemodified    = $DB->get_field($cmsource->modname, 'timemodified', ['id' => $cmsource->instance]);
                            $publishedinstance_timemodified = $DB->get_field($cmsource->modname, 'timemodified', ['id' => $cms[$cmidnumber]->instance]);

                            if ($publishedinstance_timemodified < $sourceinstance_timemodified) {
                                course_delete_module($cms[$cmidnumber]->id);
                                unset($cms[$cmidnumber]);
                            }
                        }

                        if (!isset($cms[$cmidnumber])) {
                            $cms[$cmidnumber] = duplicate_module($course, $cmsource);

                            $modinfo = get_fast_modinfo($this->course, -1);
                            $section = $modinfo->get_section_info($cminfo->sectionnum);
                            moveto_module($cms[$cmidnumber], $section);

                            $DB->set_field($cmsource->modname, 'name', $cmsource->name, ['id' => $cms[$cmidnumber]->instance]);
                            $DB->set_field('course_modules', 'idnumber', $cmidnumber, ['id' => $cms[$cmidnumber]->id]);

                            $graderoot = \grade_category::fetch_course_category($this->course->id);
                            $this->organize_grade_item($graderoot->id, $cms[$cmidnumber]->modname, $cms[$cmidnumber]->instance, 0);
                        }

                        $toolurl = new \moodle_url('/mod/' . $cms[$cmidnumber]->modname . '/view.php', ['id' => $cms[$cmidnumber]->id]);
                        $toollink = "<a class='btn btn-primary' href='$toolurl'>" . $cmsource->get_name() . "</a>";
                    }


                    ob_start(); ?>

                    <tr class='udeha-exploration'>
                        <td class='udeha-exploration-icon'>
                            <?php echo $groupingicon; ?>
                        </td>
                        <td class='udeha-exploration-details'>

                            <table>
                                <tr>
                                    <th><?php print_string('titleexplorationtype', 'format_udehauthoring'); ?></th>
                                    <td>
                                        <?php if($exploration->activitytype == count(exploration_plan::activity_type_list()) - 1): ?>
                                            <?php echo $exploration->activityfreetype; ?>
                                        <?php else: ?>
                                            <?php echo exploration_plan::get_activity_type_from_index($exploration->activitytype); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if (!empty($duration = strip_tags($exploration->length))): ?>
                                    <tr>
                                        <th><?php print_string('titleexplorationlength', 'format_udehauthoring'); ?></th>
                                        <td><?php echo $duration; ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($instructions = trim($exploration->instructions))): ?>
                                <tr>
                                    <th><?php print_string('titleexplorationinstructions', 'format_udehauthoring'); ?></th>
                                    <td><?php echo $instructions; ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th><?php print_string('titleexplorationlocation', 'format_udehauthoring'); ?></th>
                                    <td><?php echo exploration_plan::get_location_from_index($exploration->location); ?></td>
                                </tr>
                                <tr>
                                    <th><?php print_string('titleexplorationgrouping', 'format_udehauthoring'); ?></th>
                                    <td><?php echo exploration_plan::get_grouping_from_index($exploration->grouping); ?></td>
                                </tr>
                            </table>

                            <?php echo $toollink; ?>
                        </td>
                    </tr>

                    <?php $html = ob_get_clean();

                    if (0 == $exploration->temporality) {
                        $explorationsync_html .= $html;
                    } else if (1 == $exploration->temporality) {
                        $explorationasync_html .= $html;
                    }
                }

                if (!empty($explorationsync_html)) {
                    $str_title = get_string('titleexplorationssync', 'format_udehauthoring');
                    $explorationsync_html = <<<EOD
                        <h3>{$str_title}</h3>
                        <table class='udeha-explorations'>{$explorationsync_html}</table>
                    EOD;
                }

                if (!empty($explorationasync_html)) {
                    $str_title = get_string('titleexplorationsasync', 'format_udehauthoring');
                    $explorationasync_html = <<<EOD
                        <h3>{$str_title}</h3>
                        <table class='udeha-explorations'>{$explorationasync_html}</table>
                    EOD;
                }

                // Ressources

                $resources_html = '';
                foreach($subquestion->resources as $ii => $resource) {

                    $vignettefilehtml = array_filter($fileshtml, function($filepath) use ($resource) {
                        return 0 === strpos($filepath, "/resourcevignette{$resource->id}/");
                    }, ARRAY_FILTER_USE_KEY);

                    if (empty($vignettefilehtml)) {
                        $vignetteindex = $ii % 4;
                        $vignettehtml = "<img src='{$CFG->wwwroot}/course/format/udehauthoring/assets/img-front/vignette-ressource-defaut-{$vignetteindex}.png'>";
                    } else {
                        $vignettehtml = reset($vignettefilehtml);
                    }

                    $resources_html .= <<<EOD
                        <tr class="udeha-resource">
                            <td class="udeha-resource-vignette">{$vignettehtml}</td>
                            <td class="udeha-resource-link"><a href="{$resource->link}">{$resource->title}</a></td>
                        </tr>
                    EOD;
                }

                $str_titleresources = get_string('titleresources', 'format_udehauthoring');
                $resources_html = <<<EOD
                    <h3>{$str_titleresources}</h3>
                    <table class='udeha-resources'>{$resources_html}</table>
                EOD;

                $vignettefilehtml = array_filter($fileshtml, function($filepath) {
                    return 0 === strpos($filepath, '/subquestionvignette/');
                }, ARRAY_FILTER_USE_KEY);

                $vignettehtml = empty($vignettefilehtml) ?
                    '' :
                    reset($vignettefilehtml) ;

                $vignettehtml = "<div class='udeha-subquestion-vignette'>{$vignettehtml}</div>";

                $str_titlesubquestion = get_string('titlesubquestionenonce', 'format_udehauthoring', (object)[
                    'title' => strip_tags($subquestion->title, '<strong><em><sup><sub>'),
                    'index' => $ii+1,
                    'subindex' => $subindex+1
                ]);
                $enoncehtml = "<div class='udeha-subquestion-enonce'>
                    {$subquestion->enonce}
                    </div>";

                $explorations_html = $explorationsync_html . $explorationasync_html;
                if (empty($explorations_html)) {
                    $explorations_html = get_string('noactivities', 'format_udehauthoring');
                }

                $content = $vignettehtml .
                    $enoncehtml .
                    '<hr class="udeha-separator udeha-explorations-separator">' .
                    $explorations_html .
                    '<hr class="udeha-separator udeha-resources-separator">' .
                    $resources_html;

                $this->update_page($cminfo, $str_titlesubquestion, $content);
            }
        }
    }

    private function publish_evaluation_pages($cms) {
        global $DB;

        // evaluations page
        $toolidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, false, false, true);
        $cminfo = $cms[$toolidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);
        $maxtimemodified = $this->maxtimemodified($this->course_plan, ...$this->course_plan->evaluations);

        if ($page->timemodified < $maxtimemodified || $page->content === structure::$CONTENT_PLACEHOLDER) {
            $parts_html = '';
            foreach ($this->course_plan->evaluations as $ii => $evaluation) {
                $toolidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $ii, false, true);
                $evaltitle = strip_tags($evaluation->title, '<strong><em><sup><sub>');
                $parts_html .= $this->render_subquestion_preview($ii + 1, null, $evaltitle, $cms[$toolidnumber]->url);
            }
            if (empty($parts_html)) {
                $parts_html = get_string('noeval', 'format_udehauthoring');
            }

            $content = "<table class='udeha-subquestions'>{$parts_html}</table>";

            $this->update_page($cminfo, get_string('titleevaluations', 'format_udehauthoring'), $content);
        }

        $context_course = \context_course::instance($this->course_plan->courseid);

        // evaluation pages
        foreach ($this->course_plan->evaluations as $ii => $evaluation) {
            $toolidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $ii, false, true);
            $cminfo = $cms[$toolidnumber];
            $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);
            $context_module = \context_module::instance($cminfo->id);

            $fileareas = [];
            $fileareas[] = (object)[
                'folder' => 'evaluationintro',
                'contextid' => $context_course->id,
                'component' => 'format_udehauthoring',
                'filearea' => 'evaluationintroduction',
                'itemid' => $evaluation->id
            ];

            $fileareas[] = (object)[
                'folder' => 'evaluationfiles',
                'contextid' => $context_course->id,
                'component' => 'format_udehauthoring',
                'filearea' => 'evaluationfiles',
                'itemid' => $evaluation->id
            ];

            $fileschanged = utils::copyToFileareaMultiple($fileareas, $context_module->id, 'mod_page', 'content', 0);
            $fileshtml = utils::renderFileAreaHTML($context_module->id, 'mod_page', 'content', 0);

            // PAGE CONTENT

            // intro media
            if ($evaluation->isembed) {
                $introductionfilehtml = $evaluation->embed;
            } else {
                $introductionfilehtml = array_filter($fileshtml, function($filepath) {
                    return 0 === strpos($filepath, "/evaluationintro/");
                }, ARRAY_FILTER_USE_KEY);

                if (!empty($introductionfilehtml)) {
                    $introductionfilehtml = reset($introductionfilehtml);
                } else {
                    $introductionfilehtml = '';
                }
            }

            // tool publish
            $toolidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $ii, false, true, 0);
            $sourceinstance_timemodified = 0;
            if ($evaluation->toolcmid) {
                list($course, $cmsource) = get_course_and_cm_from_cmid($evaluation->toolcmid);

                // only regenerate if modifications were done
                if (isset($cms[$toolidnumber])) {
                    $sourceinstance_timemodified    = $DB->get_field($cmsource->modname, 'timemodified', ['id' => $cmsource->instance]);
                    $publishedinstance_timemodified = $DB->get_field($cmsource->modname, 'timemodified', ['id' => $cms[$toolidnumber]->instance]);

                    if ($publishedinstance_timemodified < $sourceinstance_timemodified) {
                        course_delete_module($cms[$toolidnumber]->id);
                        unset($cms[$toolidnumber]);
                    }
                }

                if (!isset($cms[$toolidnumber])) {
                    $cms[$toolidnumber] = duplicate_module($course, $cmsource);

                    $modinfo = get_fast_modinfo($this->course, -1);
                    $section = $modinfo->get_section_info($cminfo->sectionnum);
                    moveto_module($cms[$toolidnumber], $section);

                    $DB->set_field($cmsource->modname, 'name', $cmsource->name, ['id' => $cms[$toolidnumber]->instance]);
                    $DB->set_field('course_modules', 'idnumber', $toolidnumber, ['id' => $cms[$toolidnumber]->id]);

                    $graderoot = \grade_category::fetch_course_category($this->course->id);
                    $this->organize_grade_item($graderoot->id, $cms[$toolidnumber]->modname, $cms[$toolidnumber]->instance, 0);
                }

                $toolurl = new \moodle_url('/mod/' . $cms[$toolidnumber]->modname . '/view.php', ['id' => $cms[$toolidnumber]->id]);
                $toollink = "<p class='udeha-evaluation-tool'><a class='btn btn-primary' href='$toolurl'>" . $cmsource->get_name() . "</a></p>";
            } else {
                $toollink = '';
                if (isset($cms[$toolidnumber])) {
                    course_delete_module($cms[$toolidnumber]->id);
                    unset($cms[$toolidnumber]);
                    $sourceinstance_timemodified = time();
                }
            }

            if (isset($cms[$toolidnumber])) {
                $sourceinstance_timemodified = $DB->get_field($cmsource->modname, 'timemodified', ['id' => $cmsource->instance]);
            }

            if ($fileschanged || $page->timemodified < $evaluation->timemodified || $page->content === structure::$CONTENT_PLACEHOLDER ||
                ($evaluation->toolcmid && $page->timemodified < $sourceinstance_timemodified)) {
                $evaltitle = strip_tags($evaluation->title, '<strong><em><sup><sub>');

                ob_start(); ?>
                <div class='udeha-evaluation-top'>
                    <div class="udeha-evaluation-shortdesc">
                        <?php echo $evaluation->description; ?>
                        <table class="udeha-evaluation-table">
                            <tr>
                                <th><?php print_string('evaluationweight', 'format_udehauthoring'); ?></th>
                                <td>
                                    <?php echo $evaluation->weight; ?>%
                                </td>
                            </tr>
                            <tr>
                                <th><?php print_string('learningobjectivestarget', 'format_udehauthoring'); ?></th>
                                <td>
                                    <ul>
                                        <?php foreach($evaluation->learningobjectiveids as $obj): ?>
                                                <li><?php echo $obj->get_objective_name(); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class='udeha-evaluation-intro-media'><?php echo $introductionfilehtml; ?></div>
                <hr class="udeha-separator udeha-subquestions-separator">
                <div class="udeha-evaluation-description-full">
                    <?php echo $evaluation->descriptionfull; ?>
                </div>
                <?php

                // loop files
                $fs = get_file_storage();
                $files = $fs->get_area_files($context_module->id, 'mod_page', 'content', 0);

                $files = array_filter($files, function($file) {
                    return $file->get_filename() !== '.' && '/evaluationfiles/' === $file->get_filepath();
                });

                if (!empty($files)): ?>

                    <h3><?php print_string('files'); ?></h3>
                    <ul class="evaluation-files-list"><?php
                        global $OUTPUT;
                        foreach ($files as $file) {

                            $icon = $OUTPUT->pix_icon(file_file_icon($file),
                                $file->get_filename(),
                                'moodle',
                                array('class' => 'icon'));

                            echo \html_writer::tag('li',
                                \html_writer::link(
                                    "@@PLUGINFILE@@" . $file->get_filepath() . $file->get_filename() . "?forcedownload=1",
                                    $icon . ' ' . $file->get_filename()
                                )
                            );
                        }

                    ?></ul><?php

                endif;

                ?>
                <div class="udeha-evaluation-elements">
                    <?php if(!empty($evaluation->instructions)): ?>
                    <div class="udeha-evaluation-instructions">
                        <h3><?php print_string('evaluationinstructions', 'format_udehauthoring'); ?></h3>
                        <?php echo $evaluation->instructions; ?>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($evaluation->criteria)): ?>
                    <div class="udeha-evaluation-criteria">
                        <h3><?php print_string('evaluationcriteria', 'format_udehauthoring'); ?></h3>
                        <?php echo $evaluation->criteria; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($toollink)): ?>
                    <?php echo $toollink; ?>
                <?php endif; ?>

                <?php $evalcontent = ob_get_clean();

                $this->update_page($cminfo, $evaltitle, $evalcontent);
            }
        }

    }

}