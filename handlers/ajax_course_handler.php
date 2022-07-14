<?php
require_once('../../../../config.php');
require_once($CFG->dirroot.'/course/format/udehauthoring/classes/model/course_plan.php');
require_once($CFG->dirroot.'/lib/editor/atto/lib.php');


if (isset($_POST)) {
        $id = $_POST["courseId"];
        $context = context_course::instance($id, MUST_EXIST);
        $course_plan = \format_udehauthoring\model\course_plan::instance_by_courseid($id, $context);
        echo json_encode(array('success' => 1, 'data' => $course_plan));
    } else {
        echo json_encode(array('success' => 0));
    }