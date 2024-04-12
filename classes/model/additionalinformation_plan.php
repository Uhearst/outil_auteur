<?php

namespace format_udehauthoring\model;

use format_udehauthoring\utils;

global $CFG;

require_once $CFG->dirroot.  '/course/modlib.php';
require_once $CFG->dirroot.  '/course/lib.php';
require_once $CFG->dirroot.  '/lib/resourcelib.php';

class additionalinformation_plan
{

    public $id = null;
    public $audehcourseid = null;
    public $title = null;
    public $content = null;
    public $contentformat = null;

    /**
     * Instantiate an object by querying the database with the additionalinformation_plan plan ID. An error is raised if no such section
     * plan exists.
     * @param $id
     * @return additionalinformation_plan
     * @throws \dml_exception
     */
    public static function instance_by_id($id) {
        global $DB;

        $record = $DB->get_record('udehauthoring_add_info', ['id' => $id], '*', MUST_EXIST);

        $additionalinformationplan = new self();
        foreach ($additionalinformationplan as $key => $_) {
            if ($key !== 'contentformat') { $additionalinformationplan->$key = $record->$key; }
        }
        return $additionalinformationplan;
    }

    /**
     * Returns an array of all additionalinformation_plan of the current course with the specified ID.
     *
     * @param $audehcourseid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_course_plan_id($audehcourseid, $context = null) {
        global $DB;

        $records = $DB->get_records('udehauthoring_add_info', ['audehcourseid' => $audehcourseid]);

        $addInfos = [];

        foreach ($records as $record) {
            $additionalinformationplan = new self();
            $additionalinformationplan->id = $record->id;
            $additionalinformationplan->audehcourseid = $record->audehcourseid;
            $additionalinformationplan->title = $record->title;
            $additionalinformationplan->content = $record->content;

            $options = format_udehauthoring_get_editor_options($context);

            $additionalinformationplan = file_prepare_standard_editor(
                $additionalinformationplan,
                'content',
                $options,
                $context,
                'format_udehauthoring',
                'course_additional_info_content_' . $additionalinformationplan->id,
                0
            );

            $addInfos[] = $additionalinformationplan;
        }

        return $addInfos;

    }

    public function save($context) {
        global $DB;

        $record = new \stdClass();

        if (!isset($this->audehcourseid)) {
            return;
        }

        $record->audehcourseid =  $this->audehcourseid;

        if ($this->id) { $record->id = $this->id; }
        if ($this->title) { $record->title = $this->title; }
        if (!isset($record->id)) {
            $record->timemodified = time();
            $this->id = $DB->insert_record('udehauthoring_add_info', $record);
            $record->id = $this->id;
        }

        if (!empty($this->content_editor)) {
            $record = utils::prepareEditorContent($this, $record, $context, 'content', 'course_additional_info_');
        }

        if (isset($record->title)) {
            utils::db_update_if_changes('udehauthoring_add_info', $record);
        } else {
            $this->delete();
        }
    }

    public function delete() {
        global $DB;

        utils::db_bump_timechanged('udehauthoring_course', $this->audehcourseid);

        // bump all following siblings
        $following_siblings = $DB->get_records_sql(
            " SELECT id 
                  FROM {udehauthoring_add_info}
                  WHERE audehcourseid = ?
                  AND id > ?",
            [ $this->audehcourseid, $this->id ]
        );

        foreach ($following_siblings as $following_sibling) {
            utils::db_bump_timechanged('udehauthoring_add_info', $following_sibling->id);
        }

        $courseId = $DB->get_record('udehauthoring_course', ['id' => $this->audehcourseid])->courseid;
        $context = \context_course::instance($courseId);
        utils::deleteAssociatedAutoSavesAndFiles($context, 'id_add_info_content_' . $this->id);

        return $DB->delete_records('udehauthoring_add_info', ['id' => $this->id]);
    }
}