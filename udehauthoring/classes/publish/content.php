<?php

namespace format_udehauthoring\publish;

use format_udehauthoring\model\exploration_plan;
use format_udehauthoring\publish\content\syllabus;
use format_udehauthoring\utils;

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
            $cms[$cm->idnumber] = $cm;
        }

        $this->publish_course_info();
        $this->publish_course_sections($cms);
        $this->publish_syllabus_pages($cms);
        $this->publish_section_pages($cms);
        $this->publish_subquestion_pages($cms);
        $this->publish_evaluation_pages($cms);
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

        $introductionfilehtml = utils::renderFileAreaHTML($context->id, 'format_udehauthoring', 'courseintroduction', 0);
        $introductionfilehtml = empty($introductionfilehtml) ?
            '' :
            reset($introductionfilehtml);

        $course->summary = <<<EOD
            <div class='udeha-course-question'>{$this->course_plan->question}</div>
            <div id="udeha-course-introduction" class="collapse">{$introductionfilehtml}</div>
        EOD;

        $DB->update_record('course', $course);

    }

    public function publish_course_sections($cms) {
        global $DB;
        $context_course = \context_course::instance($this->course_plan->courseid);

        $course_section_records = $this->target->get_existing_sections($this->course_plan->courseid);

        // syllabus section
        $course_section_record = $course_section_records[0];
        if ($course_section_record->summary === structure::$CONTENT_PLACEHOLDER || empty($course_section_record->summary)) {
            $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0);
            $section_page = $cms[$cmidnumber];
            $page_url = $section_page->url;
            $course_section_record->summary = $this->render_module_preview(
                "<img src='../course/format/udehauthoring/assets/img-front/vignette-defaut-intro.png'>",
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

            utils::copyToFilearea(
                $context_course->id, 'format_udehauthoring', 'sectionvignette', $section->id,
                $context_course->id, 'course', 'section', $course_section_record->id
            );

            if ($section->timemodified < $course_section_record->timemodified && $course_section_record->summary !== structure::$CONTENT_PLACEHOLDER) {
                continue;
            }

            $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $sectionindex);
            $section_page = $cms[$cmidnumber];
            $page_url = $section_page->url;
            $vignettefilehtml = utils::renderFileAreaHTML($context_course->id, 'format_udehauthoring', 'sectionvignette', $section->id);

            if (empty($vignettefilehtml)) {
                $image_index = $ii % 3;
                $vignettefilehtml = "<img src='../course/format/udehauthoring/assets/img-front/vignette-defaut-{$image_index}.png'>";
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
        $this->publish_syllabus_toc($cms);
        $this->publish_syllabus_pdf($cms);
        $this->publish_syllabus_presentation($cms);
        $this->publish_syllabus_desc($cms);
        $this->publish_syllabus_objectives($cms);
        $this->publish_syllabus_place($cms);
        $this->publish_syllabus_modules($cms);
        $this->publish_syllabus_evaluations($cms);
        $this->publish_syllabus_extra($cms);
    }

    private function publish_syllabus_toc($cms) {
        global $DB;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0);
        $cminfo = $cms[$cmidnumber];
        $content = $DB->get_field('page', 'content', ['id' => $cminfo->instance], MUST_EXIST);

        // no need to update this page once it has been created
        if (\format_udehauthoring\publish\structure::$CONTENT_PLACEHOLDER !== $content) {
            return;
        }

        $str_download = get_string('downloadcourseplan', 'format_udehauthoring');

        $syllabusparts = [
            'presentation',
            'coursedescription',
            'teachingobjectives',
            'placeprog',
            'modulescontent',
            'evaluations',
            'extrainfo'
        ];

        $parts_html = '';
        foreach($syllabusparts as $ii => $partname) {
            $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, $ii);
            $label = get_string($partname, 'format_udehauthoring');
            $parts_html .= $this->render_subquestion_preview(0, $ii + 1, $label, $cms[$cmidnumber]->url);
        }

        $content = <<<EOD
        <table class='udeha-subquestions'>{$parts_html}</table>
        <div><a class="btn btn-primary" href="@@PLUGINFILE@@/plan.pdf?forcedownload=1">{$str_download}</a></div>
        EOD;

        $this->update_page($cminfo, get_string('courseplan', 'format_udehauthoring'), $content);
    }

    private function publish_syllabus_pdf($cms) {
        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0);
        $cminfo = $cms[$cmidnumber];

        $fs = get_file_storage();
        $context_module = \context_module::instance($cminfo->id);

        $file = $fs->get_file($context_module->id, 'mod_page', 'content', 0, '/', 'plan.pdf');

        if ($file) {
            $publishtimemodified = $file->get_timemodified();
        } else {
            $publishtimemodified = 0;
        }

        $alllearningobjectives = array_reduce($this->course_plan->teachingobjectives, function($all, $teachingobjective) {
            return array_merge($all, $teachingobjective->learningobjectives);
        }, []);
        $maxtimemodified = $this->maxtimemodified(
            $this->course_plan,
            ...$this->course_plan->teachingobjectives,
            ...$alllearningobjectives,
            ...$this->course_plan->sections,
            ...$this->course_plan->evaluations);

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
            'filename' => 'plan.pdf'
        ], $this->syllabus_content->get_pdf_content());
    }

    private function publish_syllabus_presentation($cms) {
        global $DB;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, 0);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        if ($page->timemodified > $this->course_plan->timemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('presentation', 'format_udehauthoring'),
            $this->syllabus_content->get_presentation_content());
    }


    private function publish_syllabus_desc($cms) {
        global $DB;
        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, 1);

        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        if ($page->timemodified > $this->course_plan->timemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('coursedescription', 'format_udehauthoring'),
            $this->syllabus_content->get_desc_content());
    }

    private function publish_syllabus_objectives($cms) {
        global $DB;
        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, 2);

        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        $alllearningobjectives = array_reduce($this->course_plan->teachingobjectives, function($all, $teachingobjective) {
            return array_merge($all, $teachingobjective->learningobjectives);
        }, []);
        $maxtimemodified = $this->maxtimemodified(
            $this->course_plan,
            ...$this->course_plan->teachingobjectives,
            ...$alllearningobjectives);

        if ($page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('teachingobjectives', 'format_udehauthoring'),
            $this->syllabus_content->get_objectives_content());
    }

    private function publish_syllabus_place($cms) {
        global $DB;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, 3);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        if ($page->timemodified > $this->course_plan->timemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('placeprog', 'format_udehauthoring'),
            $this->syllabus_content->get_place_content());
    }

    private function publish_syllabus_modules($cms) {
        global $DB;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, 4);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        $maxtimemodified = $this->maxtimemodified(...$this->course_plan->sections);

        if ($page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('modulescontent', 'format_udehauthoring'),
            $this->syllabus_content->get_modules_content());
    }

    private function publish_syllabus_evaluations($cms) {
        global $DB;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, 5);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);
        $maxtimemodified = $this->maxtimemodified(...$this->course_plan->evaluations);

        if ($page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
            return;
        }

        $this->update_page(
            $cminfo,
            get_string('evaluations', 'format_udehauthoring'),
            $this->syllabus_content->get_evaluations_content());
    }

    private function publish_syllabus_extra($cms) {
        global $DB;

        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, 0, 6);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);

        if ($page->timemodified > $this->course_plan->timemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
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

            $maxtimemodified = $this->maxtimemodified($section, ...$section->subquestions);

            if (!$fileschanged && $page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
                continue;
            }

            // PAGE CONTENT

            // intro media

            $introductionfilehtml = utils::renderFileAreaHTML($context_course->id, 'format_udehauthoring', 'sectionintroduction', $section->id);
            $introductionfilehtml = empty($introductionfilehtml) ? '' : reset($introductionfilehtml);
            $introductionfilehtml = "<div class='udeha-section-intro-media'>{$introductionfilehtml}</div>";

            //evaluations

            $evaluations_html = '';
            foreach($this->course_plan->evaluations as $evaluation) {
                if ($section->id === $evaluation->audehsectionid) {
                    $evaltitle = strip_tags($evaluation->title, '<strong><em><sup><sub>');
                    $evaluations_html .= "<h3>{$evaltitle}</h3><div class='udeha-evaluation-description'>{$evaluation->description}</div>";
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
        global $DB;

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

                // do html now

                $maxtimemodified = $this->maxtimemodified($subquestion, ...$subquestion->explorations, ...$subquestion->resources);

                if (!$haschanged && $page->timemodified > $maxtimemodified && $page->content !== structure::$CONTENT_PLACEHOLDER) {
                    continue;
                }

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

                    $activitytype_html =
                        "<div class='udeha-exploration-activitytype'>" .
                            exploration_plan::getActivityTypeFromIndex($exploration->activitytype);
                        "</div>" ;

                    $length_html = $exploration->length ?
                        "<div class='udeha-exploration-length'>" .
                            get_string('titleexplorationlength', 'format_udehauthoring') .
                            strip_tags($exploration->length) .
                        "</div>" :
                        "";

                    $html = <<<EOD
                        <tr class='udeha-exploration'>
                            <td class='udeha-exploration-icon'>
                                {$groupingicon}
                            </td>
                            <td class='udeha-exploration-details'>
                                {$activitytype_html}
                                {$length_html}
                                <div class="udeha-exploration-instructions">{$exploration->instructions}</div>
                            </td>
                        </tr>
                    EOD;

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

                $resources_html = '';
                foreach($subquestion->resources as $resource) {

                    $vignettefilehtml = array_filter($fileshtml, function($filepath) use ($resource) {
                        return 0 === strpos($filepath, "/resourcevignette{$resource->id}/");
                    }, ARRAY_FILTER_USE_KEY);

                    $vignettehtml = empty($vignettefilehtml) ?
                        '' :
                        reset($vignettefilehtml);

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

                $str_titlesubquestion = get_string('titlesubquestionenonce', 'format_udehauthoring', (object)['index' => $ii+1, 'subindex' => $subindex+1]);
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
        $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, false, false, true);
        $cminfo = $cms[$cmidnumber];
        $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);
        $maxtimemodified = $this->maxtimemodified(...$this->course_plan->evaluations);

        if ($page->timemodified < $maxtimemodified || $page->content === structure::$CONTENT_PLACEHOLDER) {
            $parts_html = '';
            foreach ($this->course_plan->evaluations as $ii => $evaluation) {
                $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $ii, false, true);
                $evaltitle = strip_tags($evaluation->title, '<strong><em><sup><sub>');
                $parts_html .= $this->render_subquestion_preview($ii + 1, null, $evaltitle, $cms[$cmidnumber]->url);
            }
            if (empty($parts_html)) {
                $parts_html = get_string('noeval', 'format_udehauthoring');
            }

            $content = "<table class='udeha-subquestions'>{$parts_html}</table>";

            $this->update_page($cminfo, get_string('titleevaluations', 'format_udehauthoring'), $content);
        }

        // evaluation pages
        foreach ($this->course_plan->evaluations as $ii => $evaluation) {
            $cmidnumber = $this->target->make_cmidnumber($this->course_plan->courseid, $ii, false, true);
            $cminfo = $cms[$cmidnumber];
            $page = $DB->get_record('page', ['id' => $cminfo->instance], 'content, timemodified', MUST_EXIST);
            if ($page->timemodified < $evaluation->timemodified || $page->content === structure::$CONTENT_PLACEHOLDER) {
                $evaltitle = strip_tags($evaluation->title, '<strong><em><sup><sub>');
                $this->update_page($cminfo, $evaltitle, $evaluation->description);
            }
        }

    }

}