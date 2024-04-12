<?php

use format_udehauthoring\utils;

require_once('../../../../config.php');

global $DB, $OUTPUT, $PAGE, $ME;

echo '<!doctype html>';

$PAGE->requires->css('/course/format/udehauthoring/authoring_tool.css');

$sectionid = optional_param('id', 0, PARAM_INT); // This is required.
$sectionplan = \format_udehauthoring\model\section_plan::instance_by_id($sectionid);
$courseplan =  \format_udehauthoring\model\course_plan::instance_by_id($sectionplan->audehcourseid);
$evaluationplan =  \format_udehauthoring\model\evaluation_plan::instance_by_section_plan_id($sectionplan->id);
$course = $DB->get_record('course', ['id' => $courseplan->courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('format/udehauthoring:redact', $context);

$isfrontpage = ($course->id == SITEID);

if ($isfrontpage) {
    throw new \moodle_exception('errorcantredactfrontpage', 'format_udehauthoring');
    exit;
}

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/course/format/udehauthoring/redact/section.php', ['id' => $sectionid]);
$PAGE->set_title("$course->shortname: ".get_string('redactsectionshort', 'format_udehauthoring'));
$PAGE->set_heading($course->fullname);

$form = new \format_udehauthoring\form\redact_section(
    null,
    array(
        'section' => $sectionplan,
        'evaluation' => $evaluationplan,
        'subquestioncount' => !$sectionplan || $sectionplan->subquestions == null ? 0 : count($sectionplan->subquestions),
        'coursetitle' => $courseplan->title,
        'courseid' => $courseplan->courseid
    ),
    'post',
    '',
    ['class' => 'udeh-form',
        'id' => 'udeh-form']
);

$form->set_data($sectionplan->to_form_data($context));

if ($data = $form->get_data()) {
    $sectionplan = \format_udehauthoring\model\section_plan::instance_by_form_data($data);
    $sectionplan->save($context);
    utils::refreshPreview($course->id);
    redirect(new moodle_url('/course/format/udehauthoring/redact/section.php', ['id' => $sectionplan->id]),
        get_string('coursesaved', 'format_udehauthoring'), null,
        \core\output\notification::NOTIFY_SUCCESS);
    exit;
}

echo $OUTPUT->header();

$PAGE->requires->js_call_amd('format_udehauthoring/notificationHelper', 'initNotification');

echo \format_udehauthoring\utils::breadCrumb($courseplan);

$PAGE->requires->js_call_amd('format_udehauthoring/mainNavigation', 'init');
echo \format_udehauthoring\utils::mainMenu($courseplan, substr($ME, strrpos($ME, '/') + 1));

$PAGE->requires->js_call_amd('format_udehauthoring/phpHelper', 'init',
    array(json_encode([
        'type' => 0,
        'courseId' => $courseplan->courseid,
        'sectionId' => $sectionid])));

echo \format_udehauthoring\titlesUtils::buildTitlesModal($courseplan->id);
echo \format_udehauthoring\modalUtils::buildWarningModal();

// find section index
$ii = 0;
while ($courseplan->sections[$ii]->id != $sectionplan->id) {
    ++$ii;
}
if ($ii === count($courseplan->sections)) {
    throw new \moodle_exception('sectionmissing');
}

if(count($courseplan->sections) > 0) {
    if(count($courseplan->sections) === 1) {
        $sectionindex = 0;
    } else {
        $sectionindex = $ii + 1;
    }
} else { $sectionindex = false; }

$previewurl = utils::getPreviewUrl($course->id, $sectionindex);
echo \format_udehauthoring\utils::navBar($courseplan->courseid, $previewurl);

$PAGE->requires->js_call_amd('format_udehauthoring/mainNavBar', 'initNavBar');

echo \format_udehauthoring\utils::mainProgress($courseplan);
$PAGE->requires->js_call_amd('format_udehauthoring/mainProgress', 'init');

$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'exportCourse', array($courseplan->courseid));
$PAGE->requires->js_call_amd(
    'format_udehauthoring/helper',
    'publishCoursePlan',
    array(array($courseplan->id, $courseplan->courseid))
);
$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'initSaveWarningModal');

$form->display();

echo \html_writer::end_tag('div');

echo $OUTPUT->footer();