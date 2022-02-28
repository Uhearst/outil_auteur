<?php


use format_udehauthoring\utils;

require_once('../../../../config.php');

global $DB, $OUTPUT, $PAGE;

$PAGE->requires->css('/course/format/udehauthoring/authoring_tool.css');

$courseid = optional_param('course_id', 0, PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);
$courseplan = \format_udehauthoring\model\course_plan::instance_by_courseid($courseid, $context);
$gloablevaluationplans = \format_udehauthoring\model\evaluation_plan::instance_all_global_by_course_plan_id($courseplan->id);


require_login($course);
require_capability('format/udehauthoring:redact', $context);

$isfrontpage = ($course->id == SITEID);

if ($isfrontpage) {
    print_error('errorcantredactfrontpage', 'format_udehauthoring');
    exit;
}

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/course/format/udehauthoring/redact/globalevaluation.php', ['course_id' => $courseid]);
$PAGE->set_title("$course->shortname: " . get_string('redactglobalevaluationshort', 'format_udehauthoring'));
$PAGE->set_heading($course->fullname);

$filemanageropts = array('subdirs' => 0, 'maxbytes' => '0', 'maxfiles' => 1, 'context' => $context);
$form = new \format_udehauthoring\form\redact_global_evaluation(null, array(
    'section' => $gloablevaluationplans,
    'globalevaluation_count' => $gloablevaluationplans ? count($gloablevaluationplans) : 0,
    'coursetitle' => $courseplan->title),
    'post',
    '',
    ['class' => 'udeh-form',
        'id' => 'udeh-form']);

$form->set_data(\format_udehauthoring\model\evaluation_plan::to_form_data_global($context, $gloablevaluationplans, $courseid));

if ($data = $form->get_data()) {
    $evaluationplans = \format_udehauthoring\model\evaluation_plan::instance_by_form_data_global($data);
    foreach ($evaluationplans as $evaluationplan) {
        $evaluationplan->save($context, false);
    }
    utils::refreshPreview($course->id);
    redirect(new moodle_url('/course/format/udehauthoring/redact/globalevaluation.php', ['course_id' => $courseid]),
        get_string('coursesaved', 'format_udehauthoring'), null,
        \core\output\notification::NOTIFY_SUCCESS);
    exit;
}

echo $OUTPUT->header();

echo \format_udehauthoring\utils::breadCrumb($courseplan->title);

$PAGE->requires->js_call_amd('format_udehauthoring/mainNavigation', 'init');

echo \format_udehauthoring\utils::mainMenu($courseplan, substr($ME, strrpos($ME, '/') + 1));

$previewurl = (new \moodle_url('/course/view.php', ['id' => $course->id, 'preview' => 1]))->out(false);
echo \format_udehauthoring\utils::navBar($courseplan->title, $courseplan->courseid, $previewurl);
$PAGE->requires->js_call_amd('format_udehauthoring/mainNavBar', 'initNavBar');

echo \format_udehauthoring\utils::mainProgress($courseplan);
$PAGE->requires->js_call_amd('format_udehauthoring/mainProgress', 'init');
$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'exportCourse', array($courseplan->courseid));
$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'publishCoursePlan', array(array($courseplan->id, $courseplan->courseid)));

$form->display();
$PAGE->requires->js_call_amd('format_udehauthoring/phpHelper', 'initGlobalEvaluations',
    array(json_encode(array_map(function($globalevaluation) { return $globalevaluation->title; } , $gloablevaluationplans))));

echo \html_writer::end_tag('div');

echo $OUTPUT->footer();