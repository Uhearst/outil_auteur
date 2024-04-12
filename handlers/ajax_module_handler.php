<?php
require_once('../../../../config.php');
require_once($CFG->dirroot.'/course/format/udehauthoring/classes/model/course_plan.php');
require_once($CFG->dirroot.'/lib/editor/atto/lib.php');

global $DB;

if (isset($_POST)) {
    $id = filter_input(INPUT_POST, 'courseId', FILTER_SANITIZE_NUMBER_INT); // $_POST["courseId"];
    $context = context_course::instance($id, MUST_EXIST);
    $record = $DB->get_record('udehauthoring_course', ['courseid' => $id], 'id');
    $section_plan = \format_udehauthoring\model\section_plan::instance_all_by_course_plan_id($record->id, $context);
    echo json_encode(array('success' => 1, 'data' => $section_plan));
} else {
    echo json_encode(array('success' => 0));
}