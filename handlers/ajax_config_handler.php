<?php

require_once('../../../../config.php');
global $DB;

$cid = filter_input(INPUT_POST, 'courseId', FILTER_SANITIZE_NUMBER_INT);

if (isset($_POST)) {
    $record = $DB->get_record('udehauthoring_title', ['audehcourseid' => $cid]);

    if (!$record) {
        $record = new \stdClass();
        $record->audehcourseid = $cid;
    }

    if(isset($_POST['module'])) {
        if(strlen(filter_input(INPUT_POST, 'module', FILTER_SANITIZE_STRING)) === 0) {
            $record->module = 'Module';
        } else {
            $record->module = filter_input(INPUT_POST, 'module', FILTER_SANITIZE_STRING);
        }
    }

    if(isset($_POST['question'])) {
        if(strlen(filter_input(INPUT_POST, 'question', FILTER_SANITIZE_STRING)) === 0) {
            $record->question = 'Question de réflexion';
        } else {
            $record->question = filter_input(INPUT_POST, 'question', FILTER_SANITIZE_STRING);
        }
    }

    if(isset($_POST['questionExplore'])) {
        if(strlen(filter_input(INPUT_POST, 'questionExplore', FILTER_SANITIZE_STRING)) === 0) {
            $record->question_explore = 'Explorer les sous-questions';
        } else {
            $record->question_explore = filter_input(INPUT_POST, 'questionExplore', FILTER_SANITIZE_STRING);
        }
    }

    if(isset($_POST['questionHide'])) {
        if(strlen(filter_input(INPUT_POST, 'questionHide', FILTER_SANITIZE_STRING)) === 0) {
            $record->question_hide = 'Cacher les sous-questions';
        } else {
            $record->question_hide = filter_input(INPUT_POST, 'questionHide', FILTER_SANITIZE_STRING);
        }
    }

    if(isset($_POST['questionSub'])) {
        if(strlen(filter_input(INPUT_POST, 'questionSub', FILTER_SANITIZE_STRING)) === 0) {
            $record->question_sub = 'Sous-questions';
        } else {
            $record->question_sub = filter_input(INPUT_POST, 'questionSub', FILTER_SANITIZE_STRING);
        }
    }

    $record->timemodified = time();

    if(isset($record->id)) {
        try {
            $DB->update_record('udehauthoring_title', $record);
        } catch(Exception $e) {
            echo json_encode(array('success' => 0, 'message' => $e->getMessage()));
        }
    } else {
        try {
            $DB->insert_record('udehauthoring_title', $record);
        } catch(Exception $e) {
            echo json_encode(array('success' => 0, 'message' => $e->getMessage()));
        }
    }


    echo json_encode(array('success' => 1));
} else {
    echo json_encode(array('success' => 0));
}