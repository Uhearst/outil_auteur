<?php


namespace format_udehauthoring\model;


use format_udehauthoring\utils;

class teachingobjective_plan
{
    public $id = null;
    public $audehcourseid = null;
    public $teachingobjective = null;
    public $learningobjectives = null;
    public $timemodified = null;

    /**
     * Returns an array of all teachingobjective_plans included in the course_plan with the specified ID.
     *
     * @param $audehcourseid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_course_plan_id($audehcourseid) {
        global $DB;

        $records = $DB->get_records('udehauthoring_teaching_obj', ['audehcourseid' => $audehcourseid]);

        $teachingobjectiveplans = [];

        foreach ($records as $record) {
            $teachingobjective = new self();
            $teachingobjective->id = $record->id;
            $teachingobjective->audehcourseid = $record->audehcourseid;
            $teachingobjective->teachingobjective = $record->teachingobjective;
            $teachingobjective->learningobjectives = learningobjective_plan::instance_all_by_teaching_objective_plan_id($teachingobjective->id);
            $teachingobjective->timemodified = $record->timemodified;

            $teachingobjectiveplans[] = $teachingobjective;
        }

        return $teachingobjectiveplans;
    }

    /**
     * Instantiate an object by querying the database with the section plan ID. An error is raised if no such section
     * plan exists.
     * @param $id
     * @return teachingobjective_plan
     * @throws \dml_exception
     */
    public static function instance_by_id($id) {
        global $DB;

        $record = $DB->get_record('udehauthoring_teaching_obj', ['id' => $id], '*', MUST_EXIST);

        $teachingobjectiveplan = new self();
        foreach($teachingobjectiveplan as $key => $_) {
            if('learningobjectives' === $key) {
                $teachingobjectiveplan->$key = learningobjective_plan::instance_all_by_teaching_objective_plan_id($teachingobjectiveplan->id);
            }  else {
                $teachingobjectiveplan->$key = $record->$key;
            }
        }
        return $teachingobjectiveplan;
    }

    public function save() {
        global $DB;
        $record = new \stdClass();
        $record->audehcourseid = $this->audehcourseid;
        if($this->id) $record->id = $this->id;
        if($this->teachingobjective) $record->teachingobjective = $this->teachingobjective;

        if (isset($record->id)) {
            if (isset($record->teachingobjective)) {
                utils::db_update_if_changes('udehauthoring_teaching_obj', $record);
            } else {
                $this->delete();
            }
        } else if (isset($record->teachingobjective)) {
            $record->timemodified = time();
            $this->id = $DB->insert_record('udehauthoring_teaching_obj', $record);
            foreach ($this->learningobjectives as $learningobjective) {
                $learningobjective->audehteachingobjectiveid = $this->id;
            }
        }

        // save teaching objectives
        $input_learnings_id = [];
        $learning_record_ids = $DB->get_records('udehauthoring_learning_obj', ['audehteachingobjectiveid' => $this->id], '', 'id');
        foreach ($this->learningobjectives as $learningobjective) {
            $input_learnings_id[$learningobjective->id] = $learningobjective->id;
            if ($learningobjective->id && empty($learningobjective->learningobjective)) {
                $learningobjective->delete();
            } else {
                $learningobjective->save();
            }
        }

        foreach($learning_record_ids as $learning_record_id) {
            if (!in_array($learning_record_id->id, $input_learnings_id)) {
                $learning_objective_plan = \format_udehauthoring\model\learningobjective_plan::instance_by_id($learning_record_id->id);
                $learning_objective_plan->delete();
            }
        }

    }

    public function delete() {
        global $DB;

        $DB->delete_records('udehauthoring_learning_obj', ['audehteachingobjectiveid' => $this->id]);

        return $DB->delete_records('udehauthoring_teaching_obj', ['id' => $this->id]);
    }
}