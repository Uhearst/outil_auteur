<?php

use format_udehauthoring\utils;

require_once('../../../../config.php');

global $DB, $PAGE, $OUTPUT, $ME;

$PAGE->requires->css('/course/format/udehauthoring/authoring_tool.css');

$subquestionid = optional_param('id', 0, PARAM_INT); // This are required.
$subquestionplan = \format_udehauthoring\model\subquestion_plan::instance_by_id($subquestionid);
$sectionplan = \format_udehauthoring\model\section_plan::instance_by_id($subquestionplan->audehsectionid);
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
$PAGE->set_url('/course/format/udehauthoring/redact/subquestion.php', ['id' => $subquestionid]);
$PAGE->set_title("$course->shortname: ".get_string('redactsubquestionshort', 'format_udehauthoring'));
$PAGE->set_heading($course->fullname);

$filemanageropts = array('subdirs' => 0, 'maxbytes' => '0', 'maxfiles' => 1, 'context' => $context);
$form = new \format_udehauthoring\form\redact_subquestion(null, array(
    'section' => $sectionplan,
    'subquestion' => $subquestionplan,
    'explorationcount' => !$subquestionplan || $subquestionplan->explorations == null ? 0 : count($subquestionplan->explorations),
    'resourcecount' => !$subquestionplan || $subquestionplan->resources == null ? 0 : count($subquestionplan->resources),
    'coursetitle' => $courseplan->title,
    'courseid' => $courseplan->courseid),
    'post',
    '',
    ['class' => 'udeh-form',
        'id' => 'udeh-form']);

$form->set_data($subquestionplan->to_form_data($context));

if ($data = $form->get_data()) {
    $subquestionplan = \format_udehauthoring\model\subquestion_plan::instance_by_form_data($data);
    $subquestionplan->save($context);
    utils::refreshPreview($course->id);
    redirect(new moodle_url('/course/format/udehauthoring/redact/subquestion.php', ['id' => $subquestionplan->id]),
        get_string('coursesaved', 'format_udehauthoring'), null,
        \core\output\notification::NOTIFY_SUCCESS);
    exit;
}

echo $OUTPUT->header();

$PAGE->requires->js_call_amd('format_udehauthoring/notificationHelper', 'initNotification');

echo \format_udehauthoring\utils::breadCrumb($courseplan);

$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'exportCourse', array($courseplan->courseid));
$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'publishCoursePlan', array(array($courseplan->id, $courseplan->courseid)));
$PAGE->requires->js_call_amd('format_udehauthoring/mainNavigation', 'init');
$PAGE->requires->js_call_amd('format_udehauthoring/phpHelper', 'init',
    array(json_encode([
        'type' => 1,
        'courseId' => $courseplan->courseid,
        'sectionId' => $sectionplan->id,
        'subQuestionId' => $subquestionid])));
echo \format_udehauthoring\utils::mainMenu($courseplan, substr($ME, strrpos($ME, '/') + 1));

// find section index
$ii = 0;
while ($courseplan->sections[$ii]->id != $sectionplan->id) {
    ++$ii;
}
if ($ii === count($courseplan->sections)) {
    print_error('sectionmissing');
}
$sectionindex = $ii + 1;

// find subquestion index
$jj = 0;
while ($sectionplan->subquestions[$jj]->id != $subquestionplan->id) {
    ++$jj;
}
if ($ii === count($sectionplan->subquestions)) {
    print_error('subquestionmissing');
}
$subquestionindex = $jj;

// build cmidnumber
$target = new \format_udehauthoring\publish\target\preview();
$cmidnumber = $target->make_cmidnumber($course->id, $sectionindex, $subquestionindex);

// find cm
$cmid = $DB->get_field('course_modules', 'id', ['idnumber' => $cmidnumber], MUST_EXIST);

// make url
$previewurl = (new \moodle_url('/mod/page/view.php', ['id' => $cmid]))->out(false);
echo \format_udehauthoring\utils::navBar($courseplan->title, $courseplan->courseid, $previewurl);

$PAGE->requires->js_call_amd('format_udehauthoring/mainNavBar', 'initNavBar');

echo \format_udehauthoring\utils::mainProgress($courseplan);
$PAGE->requires->js_call_amd('format_udehauthoring/mainProgress', 'init');

$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'exportCourse', array($courseplan->courseid));
$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'publishCoursePlan', array(array($courseplan->id, $courseplan->courseid)));

$form->display();

echo \html_writer::end_tag('div');

echo $OUTPUT->footer();