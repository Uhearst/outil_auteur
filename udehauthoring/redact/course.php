<?php

use format_udehauthoring\utils;

require_once('../../../../config.php');

global $DB, $PAGE, $OUTPUT, $USER, $URL;

$PAGE->requires->js(new \moodle_url('https://cdn.tiny.cloud/1/wllc5n0q7z2e5cw8iph2p6dqbkicp2l2jwffh61dg2j0y4gk/tinymce/5/tinymce.min.js'), true);
$PAGE->requires->css('/course/format/udehauthoring/authoring_tool.css');

$courseid = optional_param('course_id', 0, PARAM_INT); // This are required.

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('format/udehauthoring:redact', $context);

$isfrontpage = ($course->id == SITEID);

if ($isfrontpage) {
    print_error('errorcantredactfrontpage', 'format_udehauthoring');
    exit;
}

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/course/format/udehauthoring/redact/course.php', ['id' => $courseid]);
$PAGE->set_title("$course->shortname: ".get_string('redactcourseshort', 'format_udehauthoring'));
$PAGE->set_heading($course->fullname);

$filemanageropts = array('subdirs' => 0, 'maxbytes' => '0', 'maxfiles' => 1, 'context' => $context);

$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'init');

$courseplan = \format_udehauthoring\model\course_plan::instance_by_courseid($courseid, $context);

$form = new \format_udehauthoring\form\redact_course(
    null,
    array('courseid' => $courseplan ? $courseplan->id : null,
        'coursetitle' => $courseplan ? $courseplan->title : ''),
    'post',
    '',
    ['class' => 'udeh-form',
        'id' => 'udeh-form']);

if($courseplan) {
    $form->set_data($courseplan->to_form_data($context, false));
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

$PAGE->requires->js_call_amd('format_udehauthoring/notificationHelper', 'init');

echo \format_udehauthoring\utils::breadCrumb($courseplan->title);

$PAGE->requires->js_call_amd('format_udehauthoring/mainNavigation', 'init');
echo \format_udehauthoring\utils::mainMenu($courseplan, substr($ME, strrpos($ME, '/') + 1));

$previewurl = (new \moodle_url('/course/view.php', ['id' => $courseid, 'preview' => 1]))->out(false);
echo \format_udehauthoring\utils::navBar($courseplan->title, $courseplan->courseid, $previewurl);
$PAGE->requires->js_call_amd('format_udehauthoring/mainNavBar', 'initNavBar');

echo \format_udehauthoring\utils::mainProgress($courseplan);
$PAGE->requires->js_call_amd('format_udehauthoring/mainProgress', 'init');

$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'exportCourse', array($courseplan->courseid));
$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'publishCoursePlan', array(array($courseplan->id, $courseplan->courseid)));

$form->display();

echo \html_writer::end_tag('div');

echo $OUTPUT->footer();
