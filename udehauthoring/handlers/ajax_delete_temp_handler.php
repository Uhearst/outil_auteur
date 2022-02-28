<?php

require_once('../../../../config.php');

if (isset($_POST)) {
    $filename = $_POST["fileName"];
    $folder_path = $CFG->dirroot.'/course/format/udehauthoring/generation/temp/';
    $fullpath = $folder_path . $filename;

    // Use unlink() function to delete a file
    if (!unlink($fullpath)) {
        echo json_encode(array('success' => 'File can\'t be deleted'));
    }
    else {
        echo json_encode(array('success' => 'File got deleted'));
    }
} else {
    echo json_encode(array('success' => 0));
}