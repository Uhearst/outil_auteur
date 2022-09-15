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

$PAGE->requires->js_call_amd('format_udehauthoring/utils', 'formatEditorAndFileManager');

$form->set_data($subquestionplan->to_form_data($context));

$PAGE->requires->js_call_amd('format_udehauthoring/alertModal', 'handleModal');

if ($form->no_submit_button_pressed()) {
    $data = $form->get_submitted_data();
    $subquestionplan = \format_udehauthoring\model\subquestion_plan::instance_by_form_data($data);
    $subquestionplan->save($context);
    utils::refreshPreview($course->id);

    if(property_exists($data, 'tool_group')) {
        foreach ($data->tool_group as $key=>$tool) {
            if (array_key_exists('generate_tool', $tool) && property_exists($data, 'exploration_id')) {
                $url = \format_udehauthoring\model\explorationtool_plan::buildToolUrl(
                    $tool['exploration_tool'],
                    $courseplan->courseid,
                    $data->exploration_id[$key],
                    $data->id);
                redirect(new moodle_url($url));
                exit;
            }
        }
    }
}



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

$PAGE->requires->js_call_amd('format_udehauthoring/mainNavigation', 'init');

$toolList = [];
foreach ($subquestionplan->explorations as $exploration) {
    if($exploration->toolcmid !== null) {
        $info = get_fast_modinfo($course);
        if ($exploration->toolcmid !== "" && $exploration->toolcmid !== 0 && $info->cms[$exploration->toolcmid]) {
            $toolList[] = $info->cms[$exploration->toolcmid]->name;
        } else {
            $toolList[] = '';
        }

    } else {
        $toolList[] = '';
    }
}

$PAGE->requires->js_call_amd('format_udehauthoring/phpHelper', 'init',
    array(json_encode([
        'type' => 1,
        'courseId' => $courseplan->courseid,
        'sectionId' => $sectionplan->id,
        'subQuestionId' => $subquestionid,
        'toolList' => $toolList])));
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
if ($jj === count($sectionplan->subquestions)) {
    print_error('subquestionmissing');
}
$subquestionindex = $jj;

$previewurl = utils::getPreviewUrl($course->id, $sectionindex, $subquestionindex);
echo \format_udehauthoring\utils::navBar($courseplan->title, $courseplan->courseid, $previewurl);

$PAGE->requires->js_call_amd('format_udehauthoring/mainNavBar', 'initNavBar');

echo \format_udehauthoring\utils::mainProgress($courseplan);
$PAGE->requires->js_call_amd('format_udehauthoring/mainProgress', 'init');

$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'exportCourse', array($courseplan->courseid));
$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'publishCoursePlan', array(array($courseplan->id, $courseplan->courseid)));

$form->display();

echo \html_writer::end_tag('div');

echo $OUTPUT->footer();