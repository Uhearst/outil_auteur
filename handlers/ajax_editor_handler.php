<?php

global $DB, $PAGE, $CFG;

require_once('../../../../config.php');
require_once($CFG->dirroot.'/course/format/udehauthoring/classes/model/course_plan.php');

if (isset($_POST)) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $elementId = filter_input(INPUT_POST, 'elementId');
    $text = filter_input(INPUT_POST, 'text');
    $context = context_course::instance($id, MUST_EXIST);
    $PAGE->set_url('/course/format/udehauthoring/redact/course.php', ['id' => $id]);
    $course_plan = \format_udehauthoring\model\course_plan::instance_by_courseid($id, $context);

    $course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

    require_login($course);

    $editor = editors_get_preferred_editor(FORMAT_HTML);
    $class = get_class($editor);
    if($class === 'editor_tiny\editor') {
        $editor = new \format_udehauthoring\customEditor();
    }
    $editor->set_text($text);
    $editoroptions = format_udehauthoring_get_editor_options($context);
    $editoroptions['areamaxbytes'] = -1;
    $editoroptions['return_types'] = 15;
    $editoroptions['enable_filemanagement'] = true;
    $editoroptions['removeorphaneddrafts'] = false;
    $editoroptions['autosave'] = false;
    $editoroptions['trusted'] = false;

    $maxfiles     = $editoroptions['maxfiles'];
    $ctx     = $editoroptions['context'];
    // security - never ever allow guest/not logged in user to upload anything
    if (isguestuser() or !isloggedin()) {
        $maxfiles = 0;
    }

    $_values = array('text'=>null, 'format'=>null, 'itemid'=>null);
    $draftitemid = $_values['itemid'];
    // get filepicker info
    //
    $fpoptions = array();
    if ($maxfiles != 0 ) {
        if (empty($draftitemid)) {
            // no existing area info provided - let's use fresh new draft area
            require_once("$CFG->libdir/filelib.php");
            $_values['itemid'] = file_get_unused_draft_itemid();
            $draftitemid = $_values['itemid'];
        }
        require_once("$CFG->dirroot/repository/lib.php");
        $args = new stdClass();
        // need these three to filter repositories list
        $args->accepted_types = array('web_image');
        $args->return_types = $editoroptions['return_types'];
        $args->context = $ctx;
        $args->env = 'filepicker';
        // advimage plugin
        $image_options = initialise_filepicker($args);
        $image_options->context = $ctx;
        $image_options->client_id = uniqid();
        $image_options->maxbytes = $editoroptions['maxbytes'];
        $image_options->areamaxbytes = $editoroptions['areamaxbytes'];
        $image_options->env = 'editor';
        $image_options->itemid = $draftitemid;

        // moodlemedia plugin
        $args->accepted_types = array('video', 'audio');
        $media_options = initialise_filepicker($args);
        $media_options->context = $ctx;
        $media_options->client_id = uniqid();
        $media_options->maxbytes  = $editoroptions['maxbytes'];
        $media_options->areamaxbytes  = $editoroptions['areamaxbytes'];
        $media_options->env = 'editor';
        $media_options->itemid = $draftitemid;

        // advlink plugin
        $args->accepted_types = '*';
        $link_options = initialise_filepicker($args);
        $link_options->context = $ctx;
        $link_options->client_id = uniqid();
        $link_options->maxbytes  = $editoroptions['maxbytes'];
        $link_options->areamaxbytes  = $editoroptions['areamaxbytes'];
        $link_options->env = 'editor';
        $link_options->itemid = $draftitemid;

        $args->accepted_types = array('.vtt');
        $subtitle_options = initialise_filepicker($args);
        $subtitle_options->context = $ctx;
        $subtitle_options->client_id = uniqid();
        $subtitle_options->maxbytes  = $editoroptions['maxbytes'];
        $subtitle_options->areamaxbytes  = $editoroptions['areamaxbytes'];
        $subtitle_options->env = 'editor';
        $subtitle_options->itemid = $draftitemid;

        if (has_capability('moodle/h5p:deploy', $ctx)) {
            // Only set H5P Plugin settings if the user can deploy new H5P content.
            // H5P plugin.
            $args->accepted_types = array('.h5p');
            $h5poptions = initialise_filepicker($args);
            $h5poptions->context = $ctx;
            $h5poptions->client_id = uniqid();
            $h5poptions->maxbytes  = $editoroptions['maxbytes'];
            $h5poptions->areamaxbytes  = $editoroptions['areamaxbytes'];
            $h5poptions->env = 'editor';
            $h5poptions->itemid = $draftitemid;
            $fpoptions['h5p'] = $h5poptions;
        }

        $fpoptions['image'] = $image_options;
        $fpoptions['media'] = $media_options;
        $fpoptions['link'] = $link_options;
        $fpoptions['subtitle'] = $subtitle_options;
    }

    $editorParams = $editor->use_custom_editor($elementId, $editoroptions, $fpoptions);

    echo json_encode(array(
        'success' => 1,
        'data' => array(
            'elementId' => $editorParams['elementId'],
            'editorDefaultConfig' => $editorParams['defaultConfig'],
            'config' => $editorParams['config'])
        )
    );
} else {
    echo json_encode(array('success' => 0));
}