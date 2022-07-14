<?php

use format_udehauthoring\menu_node;

require_once($CFG->dirroot. '/course/format/lib.php');

class format_udehauthoring extends format_base {

    public function uses_sections() {
        return true;
    }

    /**
     * Add a authoring link to the left menu of the course
     *
     * @param global_navigation $navigation
     * @param navigation_node $node
     * @return array|void
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function extend_course_navigation($navigation, navigation_node $node) {
        if (has_capability('format/udehauthoring:redact', \context_course::instance($this->courseid))) {
            $edit = navigation_node::create(
                get_string('redactcourse', 'format_udehauthoring'),
                new \moodle_url('/course/format/udehauthoring/redact/course.php', ['course_id' => $this->courseid], 'displayable-form-informations-container'),
                navigation_node::TYPE_SETTING,
                get_string('redactcourseshort', 'format_udehauthoring'),
                'authoringudehedit',
                new pix_icon('i/edit', ''));
            $node->add_node($edit, 'participants');
        }
    }

    /**
     * Prepares necessary data for rendering the header menu
     *
     * @return format_udehauthoring_menuinfo
     * @throws moodle_exception
     */
    public function course_content_header() {
        global $cm, $course;

        if ($cm) {
            $target = \format_udehauthoring\publish\target::get_target_by_cm($cm);
        } else {
            if (optional_param('preview', 0, PARAM_INT)) {
                $target = new \format_udehauthoring\publish\target\preview();
            } else {
                $target = new \format_udehauthoring\publish\target\official();
            }
        }

        if (!$course) {
            return new format_udehauthoring_menuinfo($target, null);
        }

        $root = menu_node::build_tree(get_fast_modinfo($course), $target);

        if (is_null($cm) || empty($cm->idnumber)) {
            $current = $root;
        } else {
            $data = $target->unpack_cmidnumber($cm->idnumber);
            if ($data) {
                $current = $root->find($data);
            } else {
                $current = $root;
            }

        }

        return new format_udehauthoring_menuinfo($target, $current);
    }

    /**
     * Default Section name. If this method is not defined, course deletion and backup provokes an error.
     *
     * @param $section
     * @return Display|string
     */
    public function get_section_name($section) {
        return \format_udehauthoring\publish\structure::$CONTENT_PLACEHOLDER;
    }
}

/**
 * Carries necessary data for rendering the header menu
 */
class format_udehauthoring_menuinfo implements renderable {
    public $target;
    public $currentmenunode;
    public function __construct(\format_udehauthoring\publish\target $target, ?menu_node $current) {
        $this->target = $target;
        $this->currentmenunode = $current;
    }
}




/**
 * Serve the files from the MYPLUGIN file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function format_udehauthoring_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'courseintroduction') {
        return false;
    }

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, true, $cm);

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    if (!has_capability('format/udehauthoring:redact', $context)) {
        return false;
    }

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'format_udehauthoring', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
