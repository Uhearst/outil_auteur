<?php
require_once('../../../../config.php');
require_once($CFG->dirroot.'/course/format/udehauthoring/classes/model/course_plan.php');
require_once($CFG->dirroot.'/lib/editor/atto/lib.php');


if (isset($_POST)) {
    $id = filter_input(INPUT_POST, 'courseId', FILTER_SANITIZE_NUMBER_INT); // $_POST["courseId"];
    $context = context_course::instance($id, MUST_EXIST);
    $course_plan = \format_udehauthoring\model\course_plan::instance_by_courseid($id, $context);

    $subArrays = [];
    foreach ($course_plan as $key => $val) {
        if (is_array($val) && !str_contains($key, '_editor')) {
            $subArrays[$key] = $val;
        }
    }

    preparePluginFileUrls($subArrays, $context);

    echo json_encode(array('success' => 1, 'data' => $course_plan));
} else {
    echo json_encode(array('success' => 0));
}

function preparePluginFileUrls($arr, $context) {
    foreach($arr as $key => $_) {
        if ((is_array($_) || is_object($_)) && !str_contains($key, '_editor')) {
            preparePluginFileUrls($_, $context);
        } elseif ((is_array($_) || is_object($_)) && str_contains($key, '_editor')) {
            continue;
        } else {
            if ($_ !== null && str_contains($_, 'PLUGINFILE')) {
                $fileArea = getFileAreaForField(get_class($arr), $key, $arr->id);
                $arr->{$key} = file_rewrite_pluginfile_urls(
                    $_,
                    'pluginfile.php',
                    $context->id,
                    'format_udehauthoring',
                    $fileArea,
                    0
                );
                unset($arr->{$key.'format'});
                unset($arr->{$key.'trust'});
                unset($arr->{$key.'_editor'});
            }
        }
    }
}

function getFileAreaForField($class, $key, $id) {
    $fileArea = '';
    switch (true) {
        case str_contains($class, 'additionalinformation_plan'):
            $fileArea = 'course_additional_info_content_' . $id;
            break;
        case str_contains($class, 'section_plan'):
            $fileArea = 'course_section_' . $key . '_' . $id;
            break;
        case str_contains($class, 'evaluation_plan'):
            $fileArea = 'course_evaluation_' . $key . '_' . $id;
            break;
        case str_contains($class, 'teachingobjective_plan'):
            $fileArea = 'course_teachingobjective_' . $id;
            break;
        case str_contains($class, 'learningobjective_plan'):
            $fileArea = 'course_learningobjective_' . $id;
            break;
    }
    return $fileArea;
}