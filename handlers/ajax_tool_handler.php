<?php

global $CFG;
require_once('../../../../config.php');
require_once($CFG->dirroot.'/course/format/udehauthoring/classes/model/explorationtool_plan.php');
require_once($CFG->dirroot.'/course/format/udehauthoring/classes/model/evaluationtool_plan.php');
require_once($CFG->dirroot.'/course/lib.php');

if (isset($_POST)) {
    $cmid = $_POST["cmid"];
    $id = $_POST["id"];
    $type = $_POST["type"];
    // find course id
    list($course, $cm) = get_course_and_cm_from_cmid($cmid);
    course_delete_module($cmid, true);
    $tool = null;
    if(intval($type) === 1) {
        $tool = \format_udehauthoring\model\explorationtool_plan::instance_by_audehexplorationid($id);
    } else {
        $tool = \format_udehauthoring\model\evaluationtool_plan::instance_by_audehevaluationid($id);
    }
    $tool->delete();

    \format_udehauthoring\utils::refreshPreview($course->id);

    echo json_encode(array('success' => 1));
} else {
    echo json_encode(array('success' => 0));
}