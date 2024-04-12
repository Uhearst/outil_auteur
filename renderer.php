<?php

use format_udehauthoring\menu_node;
use format_udehauthoring\publish\content\syllabus;
use format_udehauthoring\utils;
use format_udehauthoring\model\course_plan;

/**
 * Renderer for the course’s main page, as well as extra headers and footers for all
 * pages of the course.
 *
 * Used for course navigation.
 */
class format_udehauthoring_renderer extends plugin_renderer_base {

    /**
     * Rendering the body of the course’s main page
     *
     * @param $course
     * @return string
     * @throws coding_exception
     * @throws dml_exception
     */
    public function display($course) {

        $ispreview = optional_param('preview', 0, PARAM_INT);

        if($ispreview) {
            return $this->display_preview();
        } else {
            return $this->display_official();
        }
    }

    private function display_official() {
        global $DB;

        $target = new \format_udehauthoring\publish\target\official();

        $courseid = required_param('id', PARAM_INT);

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $context = \context_course::instance($course->id);
        $coursesections = $target->get_existing_sections($courseid);
        array_pop($coursesections); // Evaluations section not displayed

        $formatted_coursefullname = format_text($course->fullname);
        $formatted_coursesummary = format_text(file_rewrite_pluginfile_urls(
            $course->summary,
            'pluginfile.php',
            $context->id,
            'course',
            'summary',
            null
        ), FORMAT_MOODLE, ['allowid' => true, 'trusted' => true, 'noclean' => true]);
        $str_show = '<i class="icon fa fa-play"></i>' . get_string('showcourseintro', 'format_udehauthoring');
        $str_hide = '<i class="icon fa fa-times"></i>' . get_string('hidecourseintro', 'format_udehauthoring');
        // #udeha-course-introduction refers to a media rendered during export in the course summary
        $content = <<<EOD
                    <div class='container udeha-course-page'>
                      <div class='row'>
                        <div class='col-12 udeha-course-course'>
                            <a class="collapsed btn-course-intro btn" data-toggle="collapse" href="#udeha-course-introduction" aria-expanded="false">
                                <span class="label-show">{$str_show}</span>
                                <span class="label-hide">{$str_hide}</span>
                            </a>
                            <h2 class='udeha-course-fullname'>{$formatted_coursefullname}</h2>
                            <div class='udeha-course-summary'>{$formatted_coursesummary}</div>
                        </div>
                      </div>
                      <hr class="udeha-separator udeha-sections-separator">
        EOD;

        $content .= '<div class="row">';

        foreach ($coursesections as $section) {

            $formatted_coursesectionsummary = format_text(file_rewrite_pluginfile_urls(
                $section->summary,
                'pluginfile.php',
                $context->id,
                'course',
                'section',
                $section->id
            ));

            if(strlen($formatted_coursesectionsummary) > 40) {
                $content .= "
                    <div class='col-4 px-2 udeha-course-section'>
                        {$formatted_coursesectionsummary}
                    </div>
                ";
            }

        }
        $content .= '</div></div>';

        return $content;
    }

    private function display_preview() {
        global $DB;

        $courseid = required_param('id', PARAM_INT);

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $context = \context_course::instance($course->id);
        $course_plan = \format_udehauthoring\model\course_plan::instance_by_courseid($courseid, $context);
        $coursesections = $DB->get_records('course_sections', [
            'course' => $course->id,
            'visible' => 0
        ]);
        array_pop($coursesections); // Evaluations section not displayed
        $udehcourse = course_plan::instance_by_courseid($courseid, null);

        // If module is not set as visible, remove from $coursesections
        foreach($udehcourse->sections as $key => $value) {
            if(intval($value->isvisiblepreview) === 0) {
                foreach($coursesections as $key2 => $section) {

                    if(strip_tags($value->title) !== '' && $value->description !== '' &&
                        strpos($section->summary, $value->description) &&
                        strpos($section->summary, strip_tags($value->title)) && strpos($section->summary, $value->description)) {
                        unset($coursesections[$key2]);
                    }
                }
            }
        }

        $formatted_coursefullname = format_text($course_plan->title);

        if ($course_plan->isembed) {
            $introductionfilehtml = $course_plan->embed;
        } else {
            $introductionfilehtml = utils::renderFileAreaHTML($context->id, 'format_udehauthoring', 'courseintroduction', 0);
            if (!empty($introductionfilehtml)) {
                $introductionfilehtml = reset($introductionfilehtml);
            } else {
                $introductionfilehtml = '';
            }
        }

        $summary = <<<EOD
            <div class='udeha-course-question'>{$course_plan->question}</div>
            <div id="udeha-course-introduction" class="collapse">{$introductionfilehtml}</div>
        EOD;

        $formatted_coursesummary = format_text(file_rewrite_pluginfile_urls(
            $summary,
            'pluginfile.php',
            $context->id,
            'format_udehauthoring',
            'courseintroduction',
            0
        ), FORMAT_MOODLE, ['allowid' => true, 'trusted' => true, 'noclean' => true]);
        $str_show = '<i class="icon fa fa-play"></i>' . get_string('showcourseintro', 'format_udehauthoring');
        $str_hide = '<i class="icon fa fa-times"></i>' . get_string('hidecourseintro', 'format_udehauthoring');
        // #udeha-course-introduction refers to a media rendered during export in the course summary
        $content = <<<EOD
                    <div class='container udeha-course-page'>
                      <div class='row'>
                        <div class='col-12 udeha-course-course'>
                            <a class="collapsed btn-course-intro btn btn-secondary" data-toggle="collapse" href="#udeha-course-introduction" aria-expanded="false">
                                <span class="label-show">{$str_show}</span>
                                <span class="label-hide">{$str_hide}</span>
                            </a>
                            <h2 class='udeha-course-fullname'>{$formatted_coursefullname}</h2>
                            <div class='udeha-course-summary'>{$formatted_coursesummary}</div>
                        </div>
                      </div>
                      <hr class="udeha-separator udeha-sections-separator">
        EOD;

        $content .= '<div class="row">';

        foreach ($coursesections as $section) {

            $formatted_coursesectionsummary = format_text(file_rewrite_pluginfile_urls(
                $section->summary,
                'pluginfile.php',
                $context->id,
                'course',
                'section',
                $section->id
            ));

            if(strlen($formatted_coursesectionsummary) > 40) {
                $content .= "
                    <div class='col-4 px-2 udeha-course-section'>
                        {$formatted_coursesectionsummary}
                    </div>
                ";
            }

        }
        $content .= '</div></div>';

        return $content;
    }

    /**
     * Top Menu Helper
     * @param int $displaymode
     * @param bool $curonly
     * @param bool $cur
     * @param string|number $idx
     * @param string $tmplabel
     * @return string|empty
     */
    function topmenuhelper($displaymode,$curonly, $cur, $idx, $tmplabel) {
        $label = '';
        if ($displaymode === 1) {
            if($curonly) {
                if ($cur) {
                    $label = $tmplabel;
                }
            }else {
                $label = $tmplabel;
            }
        } elseif ($displaymode === 2) {
            if($curonly) {
                if ($cur) {
                    $label = $idx . ' - ' . $tmplabel;
                }
            }else {
                $label = $idx . ' - ' . $tmplabel;
            }
        }
        return $label;
    }

    /**
     * Renders course top menu navigation
     *
     * @param format_udehauthoring_menuinfo $menuinfo
     * @param int $displaymode Menu display mode 0=index(default), 1=text, 2=index+text
     * @param bool $curonly current page only
     * @return string
     * @throws coding_exception
     */
    public function render_format_udehauthoring_menuinfo(\format_udehauthoring_menuinfo $menuinfo, int $displaymode=2, bool $curonly = true) {
        global $DB;

        $menuhtml = '';
        $node = $menuinfo->currentmenunode;

        if (is_null($node) || !isset($node->courseid)) {
           return '';
        }

        $context = \context_course::instance($node->courseid);
        $courseplan = \format_udehauthoring\model\course_plan::instance_by_courseid($node->courseid, $context);

        if(!$courseplan) {
            return '';
        }

        $titles = $DB->get_record('udehauthoring_title', ['audehcourseid' => $courseplan->id]);

        $syllabusSections = \format_udehauthoring\publish\structure::get_syllabus_sections($courseplan);

        if ($menuinfo->target instanceof \format_udehauthoring\publish\target\preview) {

            switch ($node->type()) {
                case menu_node::$TYPE_SYLLABUSPART:
                    if($node->subquestionindex) {
                        $idNumber = "P".$courseplan->courseid."M0T".$node->subquestionindex;
                        $cm = $DB->get_record_sql("Select instance from {course_modules} where idnumber like '".$idNumber."'");
                        $pageName = $DB->get_record_sql("Select name from {page} where id = '".$cm->instance."'")->name;
                        $sectionLabel = $DB->get_record(
                            'udehauthoring_title',
                            ['audehcourseid' => $courseplan->id]
                        )->module;
                        switch (true) {
                            case ($pageName === "Objectifs d’enseignement" || $pageName === "Teaching objectives"):
                                $editurl = new \moodle_url(
                                    '/course/format/udehauthoring/redact/course.php',
                                    ['course_id' => $node->courseid],
                                    'displayable-form-objectives-container'
                                );
                                break;
                            case ($pageName === $sectionLabel . "s content" || $pageName === "Contenu des " . strtolower($sectionLabel) . "s du cours"):
                                $editurl = new \moodle_url(
                                    '/course/format/udehauthoring/redact/course.php',
                                    ['course_id' => $node->courseid],
                                    'displayable-form-sections-container'
                                );
                                break;
                            case ($pageName === "Evaluations" || $pageName === "Évaluation des apprentissages"):
                                $editurl = new \moodle_url(
                                    '/course/format/udehauthoring/redact/course.php',
                                    ['course_id' => $node->courseid],
                                    'displayable-form-evaluations-container'
                                );
                                break;
                            case ($pageName === "Extra information" || $pageName === "Informations supplémentaires"):
                                $editurl = new \moodle_url(
                                    '/course/format/udehauthoring/redact/course.php',
                                    ['course_id' => $node->courseid],
                                    'displayable-form-additional-information-container'
                                );
                                break;
                            default:
                                $editurl = new \moodle_url(
                                    '/course/format/udehauthoring/redact/course.php',
                                    ['course_id' => $node->courseid],
                                    'displayable-form-informations-container'
                                );
                                break;
                        }
                    } else {
                        $editurl = new \moodle_url(
                            '/course/format/udehauthoring/redact/course.php',
                            ['course_id' => $node->courseid],
                            'displayable-form-informations-container'
                        );
                    }

                    break;
                case menu_node::$TYPE_MODULE:
                    $sectionid = $courseplan->sections[$node->moduleindex - 1]->id;
                    $editurl = new \moodle_url('/course/format/udehauthoring/redact/section.php', ['id' => $sectionid]);
                    break;
                case menu_node::$TYPE_SUBQUESTION:
                case menu_node::$TYPE_EXPTOOL:
                    $subquestionid = $courseplan->sections[$node->moduleindex - 1]->subquestions[$node->subquestionindex]->id;
                    $editurl = new \moodle_url('/course/format/udehauthoring/redact/subquestion.php', ['id' => $subquestionid]);
                    break;
                case menu_node::$TYPE_EVALUATIONSLIST:
                    $editurl = new \moodle_url('/course/format/udehauthoring/redact/course.php', ['course_id' => $node->courseid], 'displayable-form-informations-container');
                    break;
                case menu_node::$TYPE_EVALUATION:
                case menu_node::$TYPE_EVALTOOL:
                    $evaluation = $courseplan->evaluations[$node->moduleindex];
                    $evaluationid = $courseplan->evaluations[$node->moduleindex]->id;
                    if ($evaluation->audehsectionids) {
                        $editurl = new \moodle_url('/course/format/udehauthoring/redact/evaluation.php', ['id' => $evaluationid]);
                    } else {
                        $editurl = new \moodle_url('/course/format/udehauthoring/redact/globalevaluation.php', ['course_id' => $node->courseid]);
                    }

                    break;

                default:
                    $editurl = new \moodle_url('/course/format/udehauthoring/redact/course.php', ['course_id' => $node->courseid], 'displayable-form-informations-container');
                    break;
            }

            //$editurl = new \moodle_url('/course/format/udehauthoring/redact/course.php', ['course_id' => $node->courseid], 'displayable-form-informations-container');

            $menuhtml .= "<div class='alert alert-warning'>" . get_string('warningpreview', 'format_udehauthoring', $editurl->out(false)) . "</div>";
        }

        switch($node->type()) {
            case menu_node::$TYPE_ROOT:
                break;

            case menu_node::$TYPE_SYLLABUS:
            case menu_node::$TYPE_MODULE:
            case menu_node::$TYPE_EVALUATIONSLIST:
                $str_backhome = get_string('menubackhome', 'format_udehauthoring');
                $menuhtml .= "<div class='udeha-course-nav-back'><a href='{$node->parent->url}'><i class='icon fa fa-angle-left'></i>{$str_backhome}</a></div>";
                $menuhtml .= "<ul>";

                foreach($node->siblings() as $sibling) {
                    $classattr = $sibling === $node ?
                        " class='active'" :
                        "";

                    switch ($sibling->type()) {
                        case menu_node::$TYPE_SYLLABUS:
                            $label = get_string('menuintro', 'format_udehauthoring');
                            break;
                        case menu_node::$TYPE_MODULE:
                            if (10 > count($node->siblings()) || $sibling === $node) {
                                $label = $titles->module . ' ' . $sibling->moduleindex;
                            } else {
                                $label = $sibling->moduleindex;
                            }
                            if ($displaymode) {
                                if(isset($courseplan->sections[$sibling->moduleindex-1])) {
                                    $modulename = $courseplan->sections[$sibling->moduleindex-1]->title;
                                    $tmplabel = preg_replace('/\s*alt="[^"]*"/i', '', $modulename);
                                    $tmplabel = syllabus::cleanEditorContentForCoursePlan($tmplabel);
                                    $tmplabel = content_to_text($tmplabel,false);
                                    $tmplabel = $this->topmenuhelper($displaymode, $curonly, ($sibling === $node), ($sibling->moduleindex), $tmplabel);
                                    if(!empty($tmplabel)) {
                                        $label =$tmplabel;
                                    }
                                }else{
                                    // Error if data was deleted, need to publish the course
                                }
                            }
                            break;
                       case menu_node::$TYPE_EVALUATIONSLIST:
                           $label = get_string('menuevaluations', 'format_udehauthoring');
                            break;
                    }

                    if ($sibling->type() != menu_node::$TYPE_EVALUATIONSLIST || 0 < count($sibling->children)) {
                        // $context = \context_course::instance($node->courseid);
                        // $courseplan = \format_udehauthoring\model\course_plan::instance_by_courseid($node->courseid, $context);

                        if($sibling->type() != menu_node::$TYPE_MODULE ||
                            ($menuinfo->target instanceof \format_udehauthoring\publish\target\preview && intval($courseplan->sections[$sibling->moduleindex - 1]->isvisiblepreview) === 1) ||
                            ($menuinfo->target instanceof \format_udehauthoring\publish\target\official && intval($courseplan->sections[$sibling->moduleindex - 1]->isvisible) === 1)) {
                            $menuhtml .= "<li{$classattr}><a href='{$sibling->url}'>{$label}</a></li>";
                        }
                    }
                }
                $menuhtml .= "</ul>";
                break;

            case menu_node::$TYPE_SYLLABUSPART:
            case menu_node::$TYPE_SUBQUESTION:
            case menu_node::$TYPE_EVALUATION:
                $moduleindex = $node->parent->moduleindex;

                if (menu_node::$TYPE_SYLLABUS === $node->parent->type()) {
                    $parentlabel = get_string('menubackintro', 'format_udehauthoring');
                } else if(menu_node::$TYPE_MODULE === $node->parent->type()) {
                    $plabel =  $moduleindex;
                    if ($displaymode) {
                        $modulename = $courseplan->sections[$moduleindex - 1]->title;
                        $tmplabel = preg_replace('/\s*alt="[^"]*"/i', '', $modulename);
                        $tmplabel = syllabus::cleanEditorContentForCoursePlan($tmplabel);
                        $tmplabel = content_to_text($tmplabel,false);
                        if ($displaymode == 1) {
                            $plabel = ' - ' . $tmplabel;
                        } elseif ($displaymode == 2) {
                            $plabel .= ' - ' . $tmplabel;
                        }
                    }
                    $parentlabel = get_string('menubackmodule', 'format_udehauthoring', $plabel);
                } else if(menu_node::$TYPE_EVALUATIONSLIST === $node->parent->type()) {
                    $parentlabel = get_string('menubackevals', 'format_udehauthoring');
                }

                $menuhtml .= "<div class='udeha-course-nav-back'><a href='{$node->parent->url}'><i class='icon fa fa-angle-left'></i>{$parentlabel}</a></div>";
                $menuhtml .= "<ul>";
                foreach($node->siblings() as $sibling) {
                    $classattr = $sibling === $node ?
                        " class='active'" :
                        "";
                    if (menu_node::$TYPE_EVALUATION === $node->type()) {
                        if (8 > count($node->siblings()) || $sibling === $node) {
                            $label = get_string('menuevaluation', 'format_udehauthoring', $sibling->moduleindex + 1);
                        } else {
                            $label = $sibling->moduleindex + 1;
                        }
                        if ($displaymode) {
                            if(isset($courseplan->evaluations[$sibling->moduleindex])) {
                                $evalname = $courseplan->evaluations[$sibling->moduleindex]->title;
                                $tmplabel = preg_replace('/\s*alt="[^"]*"/i', '', $evalname);
                                $tmplabel = syllabus::cleanEditorContentForCoursePlan($tmplabel);
                                $tmplabel = content_to_text($tmplabel,false);
                                $tmplabel = $this->topmenuhelper($displaymode, $curonly, ($sibling === $node), ($sibling->moduleindex + 1), $tmplabel);
                                if(!empty($tmplabel)) {
                                    $label =$tmplabel;
                                }
                            } else {
                                // Error if data was deleted, need to publish the course
                            }
                        }
                    } else {
                        $subquestionindex = $sibling->subquestionindex + 1;
                        $label = "{$moduleindex}.{$subquestionindex}";
                        if($displaymode) {
                            $tmplabel = '';
                            if (menu_node::$TYPE_SUBQUESTION === $node->type()) {
                                $subquestionname = $courseplan->sections[$moduleindex-1]->subquestions[$subquestionindex-1]->title;
                                $tmplabel = preg_replace('/\s*alt="[^"]*"/i', '', $subquestionname);
                                $tmplabel = syllabus::cleanEditorContentForCoursePlan($tmplabel);
                                $tmplabel = content_to_text($tmplabel,false);
                            } elseif (menu_node::$TYPE_SYLLABUSPART === $node->type()) {
                                if (isset($syllabusSections[$sibling->subquestionindex])) {
                                    $tmpstr = $syllabusSections[$sibling->subquestionindex];
                                    if ($tmpstr === 'sections') {
                                        $sectionLabel = $DB->get_record(
                                            'udehauthoring_title',
                                            ['audehcourseid' => $courseplan->id]
                                        )->module;
                                        $tmplabel = get_string(
                                            "syllabustitle_{$tmpstr}",
                                            'format_udehauthoring',
                                            strtolower($sectionLabel)
                                        );
                                    } else {
                                        $tmplabel = get_string("syllabustitle_{$tmpstr}", 'format_udehauthoring');
                                    }
                                }
                            }
                            if(!empty($tmplabel)) {
                                $tmplabel = $this->topmenuhelper($displaymode, $curonly, ($sibling === $node), $label, $tmplabel);
                                if(!empty($tmplabel)) {
                                    $label =$tmplabel;
                                }
                            }
                        }
                    }

                    $menuhtml .= "<li{$classattr}><a href='{$sibling->url}'>{$label}</a></li>";
                }
                $menuhtml .= "</ul>";
                break;

            case menu_node::$TYPE_EXPTOOL:
                $parentlabel = get_string('menubacksubquestion', 'format_udehauthoring', $node->parent->moduleindex . '.' . ($node->parent->subquestionindex + 1));
                $menuhtml .= "<div class='udeha-course-nav-back'><a href='{$node->parent->url}'><i class='icon fa fa-angle-left'></i>{$parentlabel}</a></div>";
                break;

            case menu_node::$TYPE_EVALTOOL:
                $parentlabel = get_string('menubackeval', 'format_udehauthoring', ($node->parent->moduleindex + 1));
                $menuhtml .= "<div class='udeha-course-nav-back'><a href='{$node->parent->url}'><i class='icon fa fa-angle-left'></i>{$parentlabel}</a></div>";
                break;
        }

        return "<nav class='udeha-course-nav'>{$menuhtml}</nav>";
    }
}