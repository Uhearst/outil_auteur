<?php
require_once('../../../../config.php');
require_once($CFG->dirroot.'/course/format/udehauthoring/classes/model/course_plan.php');
require_once($CFG->dirroot.'/lib/editor/atto/lib.php');

global $DB;

if (!isset($_GET) || !isset($_GET["courseId"])) {
    exit;
}

$id = $_GET["courseId"];
$context = context_course::instance($id, MUST_EXIST);
$course_plan = \format_udehauthoring\model\course_plan::instance_by_courseid($id, $context);
$syllabus = new \format_udehauthoring\publish\content\syllabus($course_plan);

if (isset($_GET['html'])) {
    echo $syllabus->get_html_content();
    exit;
}



header("Content-type:application/pdf");
header("Content-Disposition:attachment;filename=" . $syllabus->get_pdf_filename());
echo $syllabus->get_pdf_content();
exit;