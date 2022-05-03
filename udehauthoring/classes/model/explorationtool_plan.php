<?php


namespace format_udehauthoring\model;

use format_udehauthoring\utils;

class explorationtool_plan
{

    public $id = null;
    public $courseid = null;
    public $audehexplorationid = null;
    public $toolid = null;
    public $tooltype = null;
    public $timemodified = null;

    /**
     * Instantiate an object by querying the database with the exploration_plan ID. An error is raised if no such subquestion
     * plan exists.
     * @param $audehexplorationid
     * @return explorationtool_plan
     * @throws \dml_exception
     */
    public static function instance_by_audehexplorationid($audehexplorationid) {
        global $DB;

        $record = $DB->get_record('udehauthoring_exp_tool', ['audehexplorationid' => $audehexplorationid]);

        if($record) {
            $explorationtool_plan = new self();
            foreach($explorationtool_plan as $key => $_) {
                $explorationtool_plan->$key = $record->$key;
            }

            return $explorationtool_plan;
        }
        return null;

    }

    /**
     * Instantiate an object by querying the database with the exploration_plan ID. An error is raised if no such subquestion
     * plan exists.
     * @param $audehexplorationid
     * @return Int
     * @throws \dml_exception
     */
    public static function get_related_cmid($audehexplorationid) {
        global $DB;

        $toolrecord = $DB->get_record('udehauthoring_exp_tool', ['audehexplorationid' => $audehexplorationid]);

        if($toolrecord) {
            $moduletypeid = $DB->get_record('modules', ['name' => $toolrecord->tooltype]);
            $cmid = $DB->get_record('course_modules', ['course' => $toolrecord->courseid,
                "module" => $moduletypeid->id,
                "instance" => $toolrecord->toolid]);
            return $cmid->id;
        }
        return null;

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
            utils::db_update_if_changes('udehauthoring_exp_tool', $record);
        } else {
            $record->timemodified = time();
            $this->id = $DB->insert_record('udehauthoring_exp_tool', $record);
        }
    }

}