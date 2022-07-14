<?php

use format_udehauthoring\utils;

require_once('../../../../config.php');
require_once('../classes/form/redact_evaluation.php');

global $DB, $PAGE, $OUTPUT, $ME;

$PAGE->requires->css('/course/format/udehauthoring/authoring_tool.css');

$evaluationid = optional_param('id', 0, PARAM_INT); // This are required.
$evaluationplan = \format_udehauthoring\model\evaluation_plan::instance_by_id($evaluationid);
$sectionplan = \format_udehauthoring\model\section_plan::instance_by_id($evaluationplan->audehsectionid);
$courseplan =  \format_udehauthoring\model\course_plan::instance_by_id($sectionplan->audehcourseid);
$course = $DB->get_record('course', ['id' => $courseplan->courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('format/udehauthoring:redact', $context);

$isfrontpage = ($course->id == SITEID);

if ($isfrontpage) {
    print_error('errorcantredactfrontpage', 'format_udehauthoring');
    exit;
}

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/course/format/udehauthoring/redact/evaluation.php', ['id' => $evaluationid]);
$PAGE->set_title("$course->shortname: ".get_string('redactevaluationshort', 'format_udehauthoring'));
$PAGE->set_heading($course->fullname);

$filemanageropts = array('subdirs' => 0, 'maxbytes' => '0', 'maxfiles' => 1, 'context' => $context);

$form = new \format_udehauthoring\form\redact_evaluation(
    null,
    array('coursetitle' => $courseplan->title,
        'evaluation' => $evaluationplan,
        'teachingobectives' => \format_udehauthoring\model\teachingobjective_plan::instance_all_by_course_plan_id($courseplan->id),
        'courseid' => $courseplan->courseid),
    'post',
    '',
    ['class' => 'udeh-form',
        'id' => 'udeh-form']
);
$PAGE->requires->js_call_amd('format_udehauthoring/alertModal', 'handleModal');
$PAGE->requires->js_call_amd('format_udehauthoring/utils', 'formatEditorAndFileManager');

$form->set_data($evaluationplan->to_form_data($context));

if ($form->no_submit_button_pressed()) {
    $data = $form->get_submitted_data();
    $evaluationplan = \format_udehauthoring\model\evaluation_plan::instance_by_form_data($data);
    $evaluationplan->save($context, true);
    utils::refreshPreview($course->id);

    if(property_exists($data, 'generate_tool') && property_exists($data, 'id')) {
        $url = \format_udehauthoring\model\evaluationtool_plan::buildToolUrl(
            $data->evaluation_tool,
            $courseplan->courseid,
            $data->id,
            false);
    redirect(new moodle_url($url));
    exit;
    }
}

if ($data = $form->get_data()) {
    $evaluationplan = \format_udehauthoring\model\evaluation_plan::instance_by_form_data($data);
    $evaluationplan->save($context, true);
    utils::refreshPreview($course->id);
    redirect(new moodle_url('/course/format/udehauthoring/redact/evaluation.php', ['id' => $evaluationplan->id]),
        get_string('coursesaved', 'format_udehauthoring'), null,
        \core\output\notification::NOTIFY_SUCCESS);
    exit;
}

echo $OUTPUT->header();

$PAGE->requires->js_call_amd('format_udehauthoring/notificationHelper', 'initNotification');

echo \format_udehauthoring\utils::breadCrumb($courseplan);

$PAGE->requires->js_call_amd('format_udehauthoring/mainNavigation', 'init');
if($evaluationplan->toolcmid !== null) {
    $info = get_fast_modinfo($course);
    if ($info->cms[$evaluationplan->toolcmid]) {
        $PAGE->requires->js_call_amd('format_udehauthoring/evaluationHelper', 'initPhpEvaluationValidation', array($info->cms[$evaluationplan->toolcmid]->name));
    } else {
        $PAGE->requires->js_call_amd('format_udehauthoring/evaluationHelper', 'initPhpEvaluationValidation');
    }

} else {
    $PAGE->requires->js_call_amd('format_udehauthoring/evaluationHelper', 'initPhpEvaluationValidation');
}

echo \format_udehauthoring\utils::mainMenu($courseplan, substr($ME, strrpos($ME, '/') + 1));

// find evaluation index
$ii = 0;
while ($courseplan->evaluations[$ii]->id != $evaluationid) {
    ++$ii;
}
if ($ii === count($courseplan->evaluations)) {
    print_error('sectionmissing');
}
$evaluationindex = $ii;

$previewurl = utils::getPreviewUrl($course->id, $evaluationindex, null, true);
echo \format_udehauthoring\utils::navBar($courseplan->title, $courseplan->courseid, $previewurl);
$PAGE->requires->js_call_amd('format_udehauthoring/mainNavBar', 'initNavBar');

echo \format_udehauthoring\utils::mainProgress($courseplan);
$PAGE->requires->js_call_amd('format_udehauthoring/mainProgress', 'init');
$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'exportCourse', array($courseplan->courseid));
$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'publishCoursePlan', array(array($courseplan->id, $courseplan->courseid)));

$form->display();

echo \html_writer::end_tag('div');

echo $OUTPUT->footer();