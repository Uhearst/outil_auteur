<?php


use format_udehauthoring\utils;

require_once('../../../../config.php');

global $DB, $OUTPUT, $PAGE, $ME;

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
    'coursetitle' => $courseplan->title,
    'courseid' => $courseplan->courseid),
    'post',
    '',
    ['class' => 'udeh-form',
        'id' => 'udeh-form']);

$form->set_data(\format_udehauthoring\model\evaluation_plan::to_form_data_global($context, $gloablevaluationplans, $courseid));

$PAGE->requires->js_call_amd('format_udehauthoring/alertModal', 'handleModal');

if ($form->no_submit_button_pressed()) {
    $data = $form->get_submitted_data();
    $evaluationplans = \format_udehauthoring\model\evaluation_plan::instance_by_form_data_global($data);
    foreach ($evaluationplans as $evaluationplan) {
        $evaluationplan->save($context, true);
    }
    utils::refreshPreview($course->id);

    if(property_exists($data, 'tool_group')) {
        foreach ($data->tool_group as $key=>$tool) {
            if (array_key_exists('generate_tool', $tool) && property_exists($data, 'evaluation_id')) {
                $url = \format_udehauthoring\model\evaluationtool_plan::buildToolUrl(
                    $tool['evaluation_tool'],
                    $courseplan->courseid,
                    $data->evaluation_id[$key],
                    true);
                redirect(new moodle_url($url));
                exit;
            }
        }
    }
}

if ($data = $form->get_data()) {
    $evaluationplans = \format_udehauthoring\model\evaluation_plan::instance_by_form_data_global($data);
    foreach ($evaluationplans as $evaluationplan) {
        $evaluationplan->save($context, true);
    }
    utils::refreshPreview($course->id);
    redirect(new moodle_url('/course/format/udehauthoring/redact/globalevaluation.php', ['course_id' => $courseid]),
        get_string('coursesaved', 'format_udehauthoring'), null,
        \core\output\notification::NOTIFY_SUCCESS);
    exit;
}

echo $OUTPUT->header();

$PAGE->requires->js_call_amd('format_udehauthoring/notificationHelper', 'initNotification');

echo \format_udehauthoring\utils::breadCrumb($courseplan);

$PAGE->requires->js_call_amd('format_udehauthoring/mainNavigation', 'init');

echo \format_udehauthoring\utils::mainMenu($courseplan, substr($ME, strrpos($ME, '/') + 1));

$previewurl = utils::getPreviewUrl($course->id, null, null, true);
echo \format_udehauthoring\utils::navBar($courseplan->title, $courseplan->courseid, $previewurl);
$PAGE->requires->js_call_amd('format_udehauthoring/mainNavBar', 'initNavBar');

echo \format_udehauthoring\utils::mainProgress($courseplan);
$PAGE->requires->js_call_amd('format_udehauthoring/mainProgress', 'init');
$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'exportCourse', array($courseplan->courseid));
$PAGE->requires->js_call_amd('format_udehauthoring/helper', 'publishCoursePlan', array(array($courseplan->id, $courseplan->courseid)));

$form->display();
$associatedModules = [];

$toolList = [];
$counter = 0;
$teachingobjs = \format_udehauthoring\model\teachingobjective_plan::instance_all_by_course_plan_id($courseplan->id);
if ($teachingobjs !== []) {
    foreach ($gloablevaluationplans as $eval) {
        $learningobjs = [];
        foreach($teachingobjs as $key => $teachingobj) {
            foreach($teachingobj->learningobjectives as $innerkey => $learningobj) {
                    if(in_array($learningobj->id, array_column($eval->learningobjectiveids, 'audehlearningobjectiveid'))) {
                        $learningobjs[] =
                            '<span class="mr-2">' . ($key + 1) . '.' . ($innerkey + 1) . ' - ' .'</span>' . $learningobj->learningobjective;
                        $counter = $counter + 1;
                }
            }
        }
        $eval->associatedobjtext = $learningobjs;

        if($eval->toolcmid !== null) {
            $info = get_fast_modinfo($course);
            if ($info->cms[$eval->toolcmid]) {
                $toolList[] = $info->cms[$eval->toolcmid]->name;
            } else {
                $toolList[] = '';
            }

        } else {
            $toolList[] = '';
        }
    }
}



$formattedInput = \format_udehauthoring\utils::formatGlobalEvalDataForJs($gloablevaluationplans, $toolList);

$PAGE->requires->js_call_amd('format_udehauthoring/globalEvaluationHelper', 'initGlobalEvaluations',
    array(json_encode($formattedInput)));

echo \html_writer::end_tag('div');

echo $OUTPUT->footer();