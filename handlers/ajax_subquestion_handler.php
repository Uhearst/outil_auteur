<?php
require_once('../../../../config.php');
require_once($CFG->dirroot.'/course/format/udehauthoring/classes/model/course_plan.php');
require_once($CFG->dirroot.'/course/format/udehauthoring/classes/model/section_plan.php');
require_once($CFG->dirroot.'/lib/editor/atto/lib.php');

global $DB;

if (isset($_POST)) {
    $id = $_POST["sectionId"];
    $courseid = $_POST["courseId"];
    $index = null;
    $context = context_course::instance($courseid, MUST_EXIST);
    $record = \format_udehauthoring\model\section_plan::instance_by_id($id, $context);
    $subquestion_plans = \format_udehauthoring\model\subquestion_plan::instance_all_by_section_plan_id($record->id, $context);
    $section_plans = \format_udehauthoring\model\section_plan::instance_all_by_course_plan_id($record->audehcourseid, $context);
    foreach ($section_plans as $i=>$section_plan) {
        if($record->id === $section_plan->id) {
            $index = $i;
        }
    }
    echo json_encode(array('success' => 1, 'data' => $subquestion_plans, 'sectionIndex' => $index));
} else {
    echo json_encode(array('success' => 0));
}