<?php

use format_udehauthoring\menu_node;
use format_udehauthoring\utils;

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

        $courseid = required_param('id', PARAM_INT);

        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $context = \context_course::instance($course->id);
        $coursesections = $DB->get_records('course_sections', [
            'course' => $course->id,
            'visible' => 1
        ]);

        $formatted_coursefullname = format_text($course->fullname);
        $formatted_coursesummary = format_text(file_rewrite_pluginfile_urls(
            $course->summary,
            'pluginfile.php',
            $context->id,
            'course',
            'summary',
            null
        ), FORMAT_MOODLE, ['allowid' => TRUE]);
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

            $content .= "
                <div class='col-4 px-2 udeha-course-section'>
                    {$formatted_coursesectionsummary}
                </div>
            ";

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

        $formatted_coursefullname = format_text($course_plan->title);
        $introductionfilehtml = utils::renderFileAreaHTML($context->id, 'format_udehauthoring', 'courseintroduction', 0);
        $introductionfilehtml = empty($introductionfilehtml) ? '' :
            $introductionfilehtml[0];

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
        ), FORMAT_MOODLE, ['allowid' => TRUE]);
        $str_show = get_string('showcourseintro', 'format_udehauthoring');
        $str_hide = get_string('hidecourseintro', 'format_udehauthoring');
        // #udeha-course-introduction refers to a media rendered during export in the course summary
        $content = <<<EOD
                    <div class='container udeha-course-page'>
                      <div class='row'>
                        <div class='col-12 udeha-course-course'>
                            <a class="collapsed btn-course-intro btn btn-secondary" data-toggle="collapse" href="#udeha-course-introduction" aria-expanded="false">
                                <span class="btn-course-intro-show">{$str_show}</span>
                                <span class="btn-course-intro-hide">{$str_hide}</span>
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

            $content .= "
                <div class='col-4 px-2 udeha-course-section'>
                    {$formatted_coursesectionsummary}
                </div>
            ";

        }
        $content .= '</div></div>';

        return $content;
    }

    /**
     * Renders course top menu navigation
     *
     * @param format_udehauthoring_menuinfo $menuinfo
     * @return string
     * @throws coding_exception
     */
    public function render_format_udehauthoring_menuinfo(\format_udehauthoring_menuinfo $menuinfo) {
        $menuhtml = '';
        $node = $menuinfo->currentmenunode;

        if (is_null($node)) {
           return '';
        }

        if ($menuinfo->target instanceof \format_udehauthoring\publish\target\preview) {
            $context = \context_course::instance($node->courseid);
            $courseplan = \format_udehauthoring\model\course_plan::instance_by_courseid($node->courseid, $context);
            if (is_null($node->moduleindex) || 0 === $node->moduleindex) {
                // url to course redact
                $editurl = new \moodle_url('/course/format/udehauthoring/redact/course.php', ['course_id' => $node->courseid]);
            } else if (is_null($node->subquestionindex)) {
                // url to section redact
                $sectionid = $courseplan->sections[$node->moduleindex - 1]->id;
                $editurl = new \moodle_url('/course/format/udehauthoring/redact/section.php', ['id' => $sectionid]);
            } else {
                // url to subquestion redact
                $subquestionid = $courseplan->sections[$node->moduleindex - 1]->subquestions[$node->subquestionindex]->id;
                $editurl = new \moodle_url('/course/format/udehauthoring/redact/subquestion.php', ['id' => $subquestionid]);
            }

            $menuhtml .= "<div class='alert alert-warning'>" . get_string('warningpreview', 'format_udehauthoring', $editurl->out(false)) . "</div>";
        }

        switch($node->type()) {
            case menu_node::$TYPE_ROOT:
                break;

            case menu_node::$TYPE_SYLLABUS:
            case menu_node::$TYPE_MODULE:
            case menu_node::$TYPE_EVALUATIONS:
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
                            $label = get_string('menumodule', 'format_udehauthoring', $sibling->moduleindex);
                            break;
                       case menu_node::$TYPE_EVALUATIONS:
                           $label = get_string('menuevaluations', 'format_udehauthoring');
                            break;
                    }

                    $menuhtml .= "<li{$classattr}><a href='{$sibling->url}'>{$label}</a></li>";
                }
                $menuhtml .= "</ul>";
                break;

            case menu_node::$TYPE_SYLLABUSPART:
            case menu_node::$TYPE_SUBQUESTION:
                $moduleindex = $node->parent->moduleindex;
                $parentlabel = menu_node::$TYPE_SYLLABUS === $node->parent->type() ?
                    get_string('menubackintro', 'format_udehauthoring') :
                    get_string('menubackmodule', 'format_udehauthoring', $node->parent->moduleindex);

                $menuhtml .= "<div class='udeha-course-nav-back'><a href='{$node->parent->url}'><i class='icon fa fa-angle-left'></i>{$parentlabel}</a></div>";
                $menuhtml .= "<ul>";
                foreach($node->siblings() as $sibling) {
                    $classattr = $sibling === $node ?
                        " class='active'" :
                        "";
                    $subquestionindex = $sibling->subquestionindex + 1;
                    $label = "{$moduleindex}.{$subquestionindex}";
                    $menuhtml .= "<li{$classattr}><a href='{$sibling->url}'>{$label}</a></li>";
                }
                $menuhtml .= "</ul>";
                break;
        }

        return "<nav class='udeha-course-nav'>{$menuhtml}</nav>";
    }
}