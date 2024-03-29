<?php


namespace format_udehauthoring\model;


use format_udehauthoring\utils;

class learningobjective_plan
{
    public $id = null;
    public $audehteachingobjectiveid = null;
    public $learningobjective = null;
    public $learningobjectivecompetency = null;
    public $audehevaluationid = null;
    public $timemodified;

    /**
     * Returns an array of all learningobjective_plans included in the course_plan.
     *
     * @param $audehcourseid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_audeh_course_id($audehcourseid) {
        global $DB;

        $i = 0;
        $j = 0;
        $learningobjectiveplans = [];
        $teachingrecords = $DB->get_records('udehauthoring_teaching_obj', ['audehcourseid' => $audehcourseid], '', 'id');
        foreach ($teachingrecords as $teachingrecord) {
            $learningrecords = $DB->get_records('udehauthoring_learning_obj', ['audehteachingobjectiveid' => $teachingrecord->id]);
            $learningobjectiveplanArray = [];
            foreach ($learningrecords as $record) {
                $learningobjectiveplan = new self();
                $learningobjectiveplan->id = $record->id;
                $learningobjectiveplan->audehteachingobjectiveid = $record->audehteachingobjectiveid;
                $learningobjectiveplan->learningobjective = $record->learningobjective;
                $learningobjectiveplan->learningobjectivecompetency = $record->learningobjectivecompetency;
                $learningobjectiveplan->audehevaluationid = $record->audehevaluationid;
                $learningobjectiveplanArray[$j] = $learningobjectiveplan;
                $learningobjectiveplans[$i] = $learningobjectiveplanArray;
                $j = $j + 1;
            }
            $j = 0;
            $i = $i + 1;
        }

        return $learningobjectiveplans;
    }

    /**
     * Returns an array of all learningobjective_plans included in the course_plan with the specified ID.
     *
     * @param $audehcourseid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_teaching_objective_plan_id($audehteachingobjectiveid) {
        global $DB;

        $records = $DB->get_records('udehauthoring_learning_obj', ['audehteachingobjectiveid' => $audehteachingobjectiveid]);

        $learningobjectiveplans = [];

        foreach ($records as $record) {
            $learningobjectiveplan = new self();
            $learningobjectiveplan->id = $record->id;
            $learningobjectiveplan->audehteachingobjectiveid = $record->audehteachingobjectiveid;
            $learningobjectiveplan->learningobjective = $record->learningobjective;
            $learningobjectiveplan->learningobjectivecompetency = $record->learningobjectivecompetency;
            $learningobjectiveplan->audehevaluationid = $record->audehevaluationid;
            $learningobjectiveplan->timemodified = $record->timemodified;
            $learningobjectiveplans[] = $learningobjectiveplan;
        }

        return $learningobjectiveplans;
    }

    /**
     * Instantiate an object by querying the database with the learning_plan plan ID. An error is raised if no such section
     * plan exists.
     * @param $id
     * @return learningobjective_plan
     * @throws \dml_exception
     */
    public static function instance_by_id($id) {
        global $DB;

        $record = $DB->get_record('udehauthoring_learning_obj', ['id' => $id], '*', MUST_EXIST);

        $learningobjectiveplan = new self();
        foreach($learningobjectiveplan as $key => $_) {
            $learningobjectiveplan->$key = $record->$key;

        }
        return $learningobjectiveplan;
    }

    /**
     * Instantiate an object by querying the database with the section plan ID. An error is raised if no such section
     * plan exists.
     * @param $id
     * @return section_plan
     * @throws \dml_exception
     */
    public static function get_associated_learning_obj_indexes_and_title_by_id($id) {
        global $DB;

        $record = $DB->get_record('udehauthoring_learning_obj', ['id' => $id], '*', MUST_EXIST);

        $learningobjectiveplan = new self();
        foreach($learningobjectiveplan as $key => $_) {
            $learningobjectiveplan->$key = $record->$key;

        }
        $relatedteachingobjective = teachingobjective_plan::instance_by_id($learningobjectiveplan->audehteachingobjectiveid);
        $teachingobjectives = teachingobjective_plan::instance_all_by_course_plan_id($relatedteachingobjective->audehcourseid);

    }

    public function save() {
        global $DB;

        $record = new \stdClass();
        $record->audehteachingobjectiveid = $this->audehteachingobjectiveid;
        if($this->id) $record->id = $this->id;
        if($this->learningobjective) $record->learningobjective = $this->learningobjective;
        $record->learningobjectivecompetency = $this->learningobjectivecompetency;

        if (isset($record->id)) {
            if (isset($record->learningobjective)) {
                utils::db_update_if_changes('udehauthoring_learning_obj', $record);
            } else {
                $this->delete();
            }
        } else if (isset($record->learningobjective)) {
            $record->timemodified = time();
            $this->id = $DB->insert_record('udehauthoring_learning_obj', $record);
        }
    }

    public function update_related_evaluation() {
        global $DB;

        $record = $DB->get_record('udehauthoring_learning_obj', ['id' => $this->id]);

        $record->audehevaluationid = $this->audehevaluationid;

        $DB->update_record('udehauthoring_learning_obj', $record);

    }

    public function delete() {
        global $DB;

        utils::db_bump_timechanged('udehauthoring_teaching_obj', $this->audehteachingobjectiveid);

        // bump all following siblings
        $following_siblings = $DB->get_records_sql(
            " SELECT id 
                  FROM {udehauthoring_learning_obj}
                  WHERE audehteachingobjectiveid = ?
                  AND id > ?",
            [ $this->audehteachingobjectiveid, $this->id ]
        );

        foreach ($following_siblings as $following_sibling) {
            utils::db_bump_timechanged('udehauthoring_learning_obj', $following_sibling->id);
        }

        return $DB->delete_records('udehauthoring_learning_obj', ['id' => $this->id]);
    }
}