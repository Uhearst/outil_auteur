<?php
require_once('../../../../config.php');
require_once($CFG->dirroot.'/course/format/udehauthoring/classes/model/course_plan.php');
require_once($CFG->dirroot.'/lib/editor/atto/lib.php');

global $DB;

if (isset($_POST)) {
    $id = $_POST["courseId"];
    $context = context_course::instance($id, MUST_EXIST);
    $course_plan = \format_udehauthoring\model\course_plan::instance_by_courseid($id, $context);

    if (!$course_plan) {
        echo json_encode(array('success' => 0));
        exit;
    }

    // official publish
    $publish_target = new \format_udehauthoring\publish\target\official();
    $publish_structure = new \format_udehauthoring\publish\structure($course_plan, $publish_target);
    $publish_content = new \format_udehauthoring\publish\content($course_plan, $publish_target);

    if (isset($_POST['flush']) && 'flush' === $_POST['flush'] && has_capability('format/udehauthoring:flushpublish', \context_course::instance($id))) {
        $publish_structure->flush_publish();
        $publish_content->publish();

        $preview_target = new \format_udehauthoring\publish\target\preview();
        $preview_structure = new \format_udehauthoring\publish\structure($course_plan, $preview_target);
        $preview_content = new \format_udehauthoring\publish\content($course_plan, $preview_target);
        $preview_structure->flush_publish();
        $preview_content->publish();
    } else {
        $publish_structure->publish();
        $publish_content->publish();
    }

    echo json_encode(array('success' => 1));
    exit;
}

echo json_encode(array('success' => 0));
exit;