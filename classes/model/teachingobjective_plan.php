<?php


namespace format_udehauthoring\model;


use format_udehauthoring\utils;

class teachingobjective_plan
{
    public $id = null;
    public $audehcourseid = null;
    public $teachingobjective = null;
    public $teachingobjectiveformat = null;
    public $learningobjectives = null;
    public $timemodified = null;

    /**
     * Returns an array of all teachingobjective_plans included in the course_plan with the specified ID.
     *
     * @param $audehcourseid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_course_plan_id($audehcourseid, $context = null) {
        global $DB;

        $records = $DB->get_records('udehauthoring_teaching_obj', ['audehcourseid' => $audehcourseid]);

        $teachingobjectiveplans = [];

        foreach ($records as $record) {
            $teachingobjective = new self();
            $teachingobjective->id = $record->id;
            $teachingobjective->audehcourseid = $record->audehcourseid;
            $teachingobjective->teachingobjective = $record->teachingobjective;
            $teachingobjective->learningobjectives =
                learningobjective_plan::instance_all_by_teaching_objective_plan_id($teachingobjective->id);
            $teachingobjective->timemodified = $record->timemodified;

            if ($context) {
                $options = format_udehauthoring_get_editor_options($context);
                $teachingobjective = file_prepare_standard_editor(
                    $teachingobjective,
                    'teachingobjective',
                    $options,
                    $context,
                    'format_udehauthoring',
                    'course_teachingobjective_' . $teachingobjective->id,
                    0
                );
            }


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
            }  elseif(!str_contains($key, 'format')) {
                $teachingobjectiveplan->$key = $record->$key;
            }
        }
        return $teachingobjectiveplan;
    }

    public function save($context) {
        global $DB;
        $record = new \stdClass();
        $record->audehcourseid = $this->audehcourseid;
        if($this->id) $record->id = $this->id;
        if($this->teachingobjective) $record->teachingobjective = $this->teachingobjective;

        if ((!isset($record->id) || $record->id === '') && (isset($record->teachingobjective) || $this->teachingobjective_editor['text'] !== '')) {
            $record->timemodified = time();
            if (!isset($record->title)) { $record->title = ''; }
            $this->id = $DB->insert_record('udehauthoring_teaching_obj', $record);
            $record->id = $this->id;
            foreach ($this->learningobjectives as $learningobjective) {
                $learningobjective->audehteachingobjectiveid = $this->id;
            }
        }

        if (!empty($this->teachingobjective_editor)) {
            $record = utils::prepareEditorContent(
                $this,
                $record,
                $context,
                'teachingobjective',
                'course_'
            );
        }

        if (isset($record->id)) {
            if (isset($record->teachingobjective)) {
                utils::db_update_if_changes('udehauthoring_teaching_obj', $record);
            } else {
                $this->delete();
            }
        }

        // save teaching objectives
        $input_learnings_id = [];
        $learning_record_ids = $DB->get_records('udehauthoring_learning_obj', ['audehteachingobjectiveid' => $this->id], '', 'id');
        foreach ($this->learningobjectives as $learningobjective) {
            $input_learnings_id[$learningobjective->id] = $learningobjective->id;
            if ($learningobjective->id && (empty($learningobjective->learningobjective) && $this->teachingobjective_editor['text'] === '')) {
                $learningobjective->delete();
            } else {
                $learningobjective->save($context);
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

        utils::db_bump_timechanged('udehauthoring_course', $this->audehcourseid);

        // bump all following siblings
        $following_siblings = $DB->get_records_sql(
            " SELECT id 
                  FROM {udehauthoring_teaching_obj}
                  WHERE audehcourseid = ?
                  AND id > ?",
            [ $this->audehcourseid, $this->id ]
        );

        foreach ($following_siblings as $following_sibling) {
            utils::db_bump_timechanged('udehauthoring_teaching_obj', $following_sibling->id);
        }
        $learningObjs = $DB->get_records('udehauthoring_learning_obj', ['audehteachingobjectiveid' => $this->id]);
        $courseId = $DB->get_record('udehauthoring_course', ['id' => $this->audehcourseid])->courseid;
        $context = \context_course::instance($courseId);
        foreach ($learningObjs as $learningObj) {
            utils::deleteAssociatedAutoSavesAndFiles(
                $context,
                'course_learningobjective_' . $this->id . '_' . $learningObj->id
            );
            $DB->delete_records('udehauthoring_learning_obj', ['id' => $learningObj->id]);
        }


        $courseId = $DB->get_record('udehauthoring_course', ['id' => $this->audehcourseid])->courseid;
        $context = \context_course::instance($courseId);
        utils::deleteAssociatedAutoSavesAndFiles($context, 'course_teachingobjective_' . $this->id);

        return $DB->delete_records('udehauthoring_teaching_obj', ['id' => $this->id]);
    }
}