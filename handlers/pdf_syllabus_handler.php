<?php
require_once('../../../../config.php');
require_once($CFG->dirroot.'/course/format/udehauthoring/classes/model/course_plan.php');
require_once($CFG->dirroot.'/lib/editor/atto/lib.php');

global $DB;

if (!isset($_GET) || !isset($_GET["courseId"])) {
    exit;
}

$id = filter_input(INPUT_GET, 'courseId', FILTER_SANITIZE_NUMBER_INT); //$_GET["courseId"];
$context = context_course::instance($id, MUST_EXIST);
$course_plan = \format_udehauthoring\model\course_plan::instance_by_courseid($id, $context);

foreach($course_plan->sections as $key => $value) {
    if(intval($value->isvisible) === 0) {
        array_splice($course_plan->sections, $key, 1);
    }
}

$syllabus = new \format_udehauthoring\publish\content\syllabus($course_plan);

if (isset($_GET['html'])) {
    echo $syllabus->get_html_content();
    exit;
}



header("Content-type:application/pdf");
header("Content-Disposition:attachment;filename=" . $syllabus->get_pdf_filename());
echo $syllabus->get_pdf_content();
exit;