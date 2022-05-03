<?php


namespace format_udehauthoring\model;
use format_udehauthoring\utils;

global $CFG, $DB;

require_once $CFG->dirroot.  '/lib/resourcelib.php';

class unit_plan {

    public $id = null;
    public $audehcourseid = null;
    public $audehunitid = null;
    public $timemodified = null;

    /**
     * Returns an array of all unit_plan of the current course with the specified ID.
     *
     * @param $audehcourseid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_course_plan_id($audehcourseid) {
        global $DB;

        $records = $DB->get_records('udehauthoring_unit', ['audehcourseid' => $audehcourseid]);

        $units = [];

        foreach ($records as $record) {
            $unitplan = new self();
            $unitplan->id = $record->id;
            $unitplan->audehcourseid = $record->audehcourseid;
            $unitplan->audehunitid = $record->audehunitid;
            $unitplan->timemodified = $record->timemodified;

            $units[] = $unitplan;
        }

        return $units;

    }

    /**
     * Returns an an instance of unit_plan with the specified ID.
     *
     * @param $id
     * @return unit_plan
     * @throws \dml_exception
     */
    public static function instance_by_config_id($id) {
        global $DB;

        $record = $DB->get_record('udehauthoring_unit', ['audehunitid' => $id]);

        $unitplan = new self();
        foreach($unitplan as $key => $_) {
            $unitplan->$key = $record->$key;
        }

        return $unitplan;

    }

    public function save() {
        global $DB;

        $record = new \stdClass();
        foreach ($this as $key => $value) {
            if (!is_null($value)) {
                $record->$key = $value;
            }
        }

        if (isset($record->id)) {
            utils::db_update_if_changes('udehauthoring_unit', $record);
        } else {
            $record->timemodified = time();
            $DB->insert_record_raw('udehauthoring_unit', $record, false);
        }

    }

    public function delete() {
        global $DB;

        return $DB->delete_records('udehauthoring_unit', ['id' => $this->id]);
    }

}