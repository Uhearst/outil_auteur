<?php
require_once('../../../../config.php');
require_once($CFG->dirroot.'/course/format/udehauthoring/classes/model/learningobjective_plan.php');
require_once($CFG->dirroot.'/lib/editor/atto/lib.php');

global $DB;

if (isset($_POST)) {
    $id = $_POST["courseId"];
    $context = context_course::instance($id, MUST_EXIST);
    $record = $DB->get_record('udehauthoring_course', ['courseid' => $id], 'id');
    $learning_plans = \format_udehauthoring\model\learningobjective_plan::instance_all_by_audeh_course_id($record->id, $context);
    echo json_encode(array('success' => 1, 'data' => $learning_plans));
} else {
    echo json_encode(array('success' => 0));
}