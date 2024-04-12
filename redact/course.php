<?php

use format_udehauthoring\utils;

require_once('../../../../config.php');

global $DB, $PAGE, $OUTPUT, $USER, $URL, $ME, $COURSE;

echo '<!doctype html>';

$PAGE->requires->css('/course/format/udehauthoring/authoring_tool.css');

$courseid = optional_param('course_id', 0, PARAM_INT); // This is required.

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('format/udehauthoring:redact', $context);

$isfrontpage = ($course->id == SITEID);

if ($isfrontpage) {
    throw new \moodle_exception('errorcantredactfrontpage', 'format_udehauthoring');
    exit;
}

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/course/format/udehauthoring/redact/course.php', ['id' => $courseid]);
$PAGE->set_title("$course->shortname: ".get_string('redactcourseshort', 'format_udehauthoring'));
$PAGE->set_heading($course->fullname);

$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'init', array($COURSE->lang));

$courseplan = \format_udehauthoring\model\course_plan::instance_by_courseid($courseid, $context);

$form = new \format_udehauthoring\form\redact_course(
    null,
    array(
        'id' => $courseplan ? $courseplan->id : null,
        'courseid' => $courseplan ? $courseplan->courseid : null,
        'coursetitle' => $courseplan ? $courseplan->title : ''),
    'post',
    '',
    ['class' => 'udeh-form',
        'id' => 'udeh-form']);

if ($courseplan) {
    $val = $courseplan->to_form_data($context, false);
    $form->set_data($val);
    $PAGE->requires->js_call_amd('format_udehauthoring/helper', 'fillForm', array(json_encode(['courseId' => $courseid])));
} else {
    $courseplan = \format_udehauthoring\model\course_plan::base_instance($courseid, $context);
    $form->set_data($courseplan->to_form_data($context, true));
}

if ($data = $form->get_data()) {
    $courseplan = \format_udehauthoring\model\course_plan::instance_by_form_data($data, $_POST);
    $issaved = $courseplan->save($context, $_POST['anchor']);
    if ($issaved != '') {
        redirect(new moodle_url('/course/format/udehauthoring/redact/course.php', ['course_id' => $courseid], $_POST['anchor']),
            $issaved, null,
            \core\output\notification::NOTIFY_ERROR);
        exit;
    }
    utils::refreshPreview($course->id);
    redirect(new moodle_url('/course/format/udehauthoring/redact/course.php', ['course_id' => $courseid], $_POST['anchor']),
        get_string('coursesaved', 'format_udehauthoring'), null,
        \core\output\notification::NOTIFY_SUCCESS);
    exit;
}

echo $OUTPUT->header();

$PAGE->requires->js_call_amd('format_udehauthoring/notificationHelper', 'initNotification');

echo \format_udehauthoring\utils::breadCrumb($courseplan);

$PAGE->requires->js_call_amd('format_udehauthoring/mainNavigation', 'init');
echo \format_udehauthoring\utils::mainMenu($courseplan, substr($ME, strrpos($ME, '/') + 1));

echo \format_udehauthoring\titlesUtils::buildTitlesModal($courseplan->id);
echo \format_udehauthoring\modalUtils::buildWarningModal();

$previewurls = [];
$previewurls['displayable-form-informations-container'] =
    utils::getPreviewUrlFromName($courseid, 'displayable-form-informations-container');
$previewurls['displayable-form-additional-information-container']  =
    utils::getPreviewUrlFromName($courseid, 'displayable-form-additional-information-container');
$previewurls['displayable-form-objectives-container']   =
    utils::getPreviewUrlFromName($courseid, 'displayable-form-objectives-container');
$previewurls['displayable-form-sections-container']     =
    utils::getPreviewUrlFromName($courseid, 'displayable-form-sections-container');
$previewurls['displayable-form-evaluations-container']  =
    utils::getPreviewUrlFromName($courseid, 'displayable-form-evaluations-container');

echo \format_udehauthoring\utils::navBar($courseplan->courseid, $previewurls);
$PAGE->requires->js_call_amd('format_udehauthoring/mainNavBar', 'initNavBar');

echo \format_udehauthoring\utils::mainProgress($courseplan);
$PAGE->requires->js_call_amd('format_udehauthoring/mainProgress', 'init');

$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'exportCourse', array($courseplan->courseid));
$PAGE->requires->js_call_amd(
    'format_udehauthoring/helper',
    'publishCoursePlan',
    array(array($courseplan->id, $courseplan->courseid)));
$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'initSaveWarningModal');

$form->display();

echo \html_writer::end_tag('div');

echo $OUTPUT->footer();
