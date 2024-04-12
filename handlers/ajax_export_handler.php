<?php

use format_udehauthoring\utils;

require_once('../../../../config.php');
require_once($CFG->dirroot.'/course/format/udehauthoring/classes/model/course_plan.php');
require_once($CFG->dirroot.'/lib/editor/atto/lib.php');

global $DB;

if (isset($_POST)) {
    $courseId = filter_input(INPUT_POST, 'courseId', FILTER_SANITIZE_NUMBER_INT); // $_POST["courseId"];
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT); // $_POST["id"];
    $context = context_course::instance($courseId, MUST_EXIST);
    $course_plan = \format_udehauthoring\model\course_plan::instance_by_courseid($courseId, $context);

    $course = $DB->get_record('course', ['id' => $courseId], '*', MUST_EXIST);

    require_login($course);
    require_capability('format/udehauthoring:redact', $context);

    if (!$course_plan) {
        echo json_encode(array(
            'error' => 1,
            'msg' => get_string('notificationerrormissingcourseplan', 'format_udehauthoring')
        ));
        exit;
    }

    if ($course_plan->sections) {
        foreach ($course_plan->sections as $section) {
            if (property_exists($section, 'title') && empty($section->title)) {
                echo json_encode(array(
                    'error' => 1,
                    'msg' => get_string('notificationerrormissingsectionsname', 'format_udehauthoring')
                ));
                exit;
            }
        }
    }

    // official publish
    $publish_target = new \format_udehauthoring\publish\target\official();
    $publish_structure = new \format_udehauthoring\publish\structure($course_plan, $publish_target);
    $publish_content = new \format_udehauthoring\publish\content($course_plan, $publish_target);

    $publish_structure->flush_publish();
    $publish_content->publish();

    // preview publish
    $preview_target = new \format_udehauthoring\publish\target\preview();
    $preview_structure = new \format_udehauthoring\publish\structure($course_plan, $preview_target);
    $preview_content = new \format_udehauthoring\publish\content($course_plan, $preview_target);

    $preview_structure->flush_publish();
    $preview_content->publish();


    try {
        if (str_contains($_SERVER['HTTP_REFERER'], 'redact/course.php')) {
            $previewurls = [];
            $previewurls['displayable-form-informations-container'] =
                utils::getPreviewUrlFromName($courseId, 'displayable-form-informations-container');
            $previewurls['displayable-form-additional-information-container']  =
                utils::getPreviewUrlFromName($courseId, 'displayable-form-additional-information-container');
            $previewurls['displayable-form-objectives-container']   =
                utils::getPreviewUrlFromName($courseId, 'displayable-form-objectives-container');
            $previewurls['displayable-form-sections-container']     =
                utils::getPreviewUrlFromName($courseId, 'displayable-form-sections-container');
            $previewurls['displayable-form-evaluations-container']  =
                utils::getPreviewUrlFromName($courseId, 'displayable-form-evaluations-container');
            echo json_encode(array('success' => 1, 'data' => array('previewurls' => $previewurls)));
            exit;
        } elseif (str_contains($_SERVER['HTTP_REFERER'], 'redact/section.php')) {
            // find section index
            $ii = 0;
            while ($course_plan->sections[$ii]->id != $id) {
                ++$ii;
            }
            if ($ii === count($course_plan->sections)) {
                throw new \moodle_exception('sectionmissing');
            }

            if(count($course_plan->sections) > 0) {
                if(count($course_plan->sections) === 1) {
                    $sectionindex = 0;
                } else {
                    $sectionindex = $ii + 1;
                }
            } else { $sectionindex = false; }

            $previewurl = utils::getPreviewUrl($course->id, $sectionindex);
            echo json_encode(array('success' => 1, 'data' => array('previewurl' => $previewurl)));
            exit;
        } elseif (str_contains($_SERVER['HTTP_REFERER'], 'redact/subquestion.php')) {
            $subquestionplan = \format_udehauthoring\model\subquestion_plan::instance_by_id($id);
            $sectionplan = \format_udehauthoring\model\section_plan::instance_by_id($subquestionplan->audehsectionid);
            // find section index
            $ii = 0;
            while ($course_plan->sections[$ii]->id != $sectionplan->id) {
                ++$ii;
            }
            if ($ii === count($course_plan->sections)) {
                throw new \moodle_exception('sectionmissing');
            }
            $sectionindex = $ii + 1;

            // find subquestion index
            $jj = 0;
            while ($sectionplan->subquestions[$jj]->id != $subquestionplan->id) {
                ++$jj;
            }
            if ($jj === count($sectionplan->subquestions)) {
                throw new \moodle_exception('subquestionmissing');
            }
            $subquestionindex = $jj;

            $previewurl = utils::getPreviewUrl($course->id, $sectionindex, $subquestionindex);
            echo json_encode(array('success' => 1, 'data' => array('previewurl' => $previewurl)));
            exit;
        } elseif (str_contains($_SERVER['HTTP_REFERER'], 'redact/evaluation.php')) {
            // find evaluation index
            $ii = 0;
            while ($course_plan->evaluations[$ii]->id != $id) {
                ++$ii;
            }
            if ($ii === count($course_plan->evaluations)) {
                throw new \moodle_exception('sectionmissing');
            }
            $evaluationindex = $ii;

            $previewurl = utils::getPreviewUrl($course->id, $evaluationindex, null, true);
            echo json_encode(array('success' => 1, 'data' => array('previewurl' => $previewurl)));
            exit;
        } elseif (str_contains($_SERVER['HTTP_REFERER'], 'redact/globalevaluation.php')) {
            $previewurl = utils::getPreviewUrl($course->id, null, null, true);
            echo json_encode(array('success' => 1, 'data' => array('previewurl' => $previewurl)));
            exit;
        }
        $previewurl = '';
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    }


}

echo json_encode(array('success' => 0));
exit;