<?php


namespace format_udehauthoring\model;


use format_udehauthoring\utils;

class evaluationobjective_plan
{
    public $id = null;
    public $audehcourseid = null;
    public $audehevaluationid = null;
    public $audehlearningobjectiveid = null;
    public $timemodified = null;

    /**
     * Returns an array of all evaluationobjective_plan included in the evaluation_plan with the specified ID.
     *
     * @param $audehcourseid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_course_plan_id($audehcourseid) {
        global $DB;

        $records = $DB->get_records('udehauthoring_evaluation_obj', ['audehcourseid' => $audehcourseid]);

        $evaluationobjplans = [];

        foreach ($records as $record) {
            $evaluationobjplan = new self();
            $evaluationobjplan->id = $record->id;
            $evaluationobjplan->audehcourseid = $record->audehcourseid;
            $evaluationobjplan->audehevaluationid = $record->audehevaluationid;
            $evaluationobjplan->audehlearningobjectiveid = $record->audehlearningobjectiveid;
            $evaluationobjplan->timemodified = $record->timemodified;

            $evaluationobjplans[] = $evaluationobjplan;
        }

        return $evaluationobjplans;

    }

    /**
     * Returns an array of all evaluationobjective_plan included in the evaluation_plan with the specified ID.
     *
     * @param $audehevaluationid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_evaluation_plan_id($audehevaluationid) {
        global $DB;

        $records = $DB->get_records('udehauthoring_evaluation_obj', ['audehevaluationid' => $audehevaluationid]);

        $evaluationobjplans = [];

        foreach ($records as $record) {
            $evaluationobjplan = new self();
            $evaluationobjplan->id = $record->id;
            $evaluationobjplan->audehcourseid = $record->audehcourseid;
            $evaluationobjplan->audehevaluationid = $record->audehevaluationid;
            $evaluationobjplan->audehlearningobjectiveid = $record->audehlearningobjectiveid;
            $evaluationobjplan->timemodified = $record->timemodified;

            $evaluationobjplans[] = $evaluationobjplan;
        }

        return $evaluationobjplans;

    }

    public function instance_exists() {
        global $DB;

        $record = $DB->get_record('udehauthoring_evaluation_obj', ['audehevaluationid' => $this->audehevaluationid, 'audehlearningobjectiveid' => $this ->audehlearningobjectiveid]);

        if($record) {
            return true;
        } else {
            return false;
        }
    }

    public function get_instance_id() {
        global $DB;

        $record = $DB->get_record('udehauthoring_evaluation_obj', ['audehevaluationid' => $this->audehevaluationid, 'audehlearningobjectiveid' => $this ->audehlearningobjectiveid]);

        if (!$record) {
            return false;
        }

        $this->id = $record->id;
    }

    public function get_objective_name() {
        global $DB;

        return $DB->get_field('udehauthoring_learning_obj', 'learningobjective', [
            'id' => $this->audehlearningobjectiveid
        ]);
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
            utils::db_update_if_changes('udehauthoring_evaluation_obj', $record);
        } else {
            $record->timemodified = time();
            $DB->insert_record_raw('udehauthoring_evaluation_obj', $record, false);
        }

    }

    public function delete() {
        global $DB;

        return $DB->delete_records('udehauthoring_evaluation_obj', ['audehevaluationid' => $this->audehevaluationid, 'audehlearningobjectiveid' => $this->audehlearningobjectiveid]);
    }
}