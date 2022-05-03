<?php


namespace format_udehauthoring\model;


use format_udehauthoring\utils;

class evaluation_plan
{
    public $id = null;
    public $audehsectionid = null;
    public $audehcourseid = null;
    public $title = null;
    public $description = null;
    public $introduction = null;
    public $descriptionfull = null;
    public $instructions = null;
    public $criteria = null;
    public $weight = null;
    public $timemodified = null;
    public $learningobjectiveids = null;

    /**
     * Returns an array of all evaluation_plan included in the section_plan with the specified ID.
     *
     * @param $audehcourseid
     * @return evaluation_plan
     * @throws \dml_exception
     */
    public static function instance_by_section_plan_id($audehsectionid) {
        global $DB;

        $record = $DB->get_record('udehauthoring_evaluation', ['audehsectionid' => $audehsectionid], '*', IGNORE_MULTIPLE);

        if (!$record) {
            return false;
        }

        $recordlearningobjectives = evaluationobjective_plan::instance_all_by_evaluation_plan_id($record->id);
        $evaluationplan = new self();
        $evaluationplan->id = $record->id;
        $evaluationplan->title = $record->title;
        $evaluationplan->audehsectionid = $record->audehsectionid;
        $evaluationplan->description = $record->description;
        $evaluationplan->descriptionfull = $record->descriptionfull;
        $evaluationplan->instructions = $record->instructions;
        $evaluationplan->criteria = $record->criteria;
        $evaluationplan->weight = $record->weight;
        $evaluationplan->audehcourseid = $record->audehcourseid;
        $evaluationplan->learningobjectiveids = $recordlearningobjectives;

        return $evaluationplan;
    }

    /**
     * Returns an array of all global evaluation_plan included in the course_plan with the specified ID.
     *
     * @param $audehcourseid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_global_by_course_plan_id($audehcourseid) {
        global $DB;

        $records = $DB->get_records('udehauthoring_evaluation', ['audehcourseid' => $audehcourseid, 'audehsectionid' => 0]);

        $evaluationplans = [];

        foreach ($records as $record) {
            $evaluationplan = new self();
            $evaluationplan->id = $record->id;
            $evaluationplan->title = $record->title;
            $evaluationplan->audehsectionid = $record->audehsectionid;
            $evaluationplan->description = $record->description;
            $evaluationplan->descriptionfull = $record->descriptionfull;
            $evaluationplan->instructions = $record->instructions;
            $evaluationplan->criteria = $record->criteria;
            $evaluationplan->weight = $record->weight;
            $evaluationplan->audehcourseid = $record->audehcourseid;
            $evaluationplan->learningobjectiveids = evaluationobjective_plan::instance_all_by_evaluation_plan_id($record->id);

            $evaluationplans[] = $evaluationplan;
        }

        return $evaluationplans;
    }

    /**
     * Returns an array of all evaluation_plan included in the course_plan with the specified ID.
     *
     * @param $audehcourseid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_course_plan_id($audehcourseid) {
        global $DB;

        $records = $DB->get_records('udehauthoring_evaluation', ['audehcourseid' => $audehcourseid]);

        $evaluationplans = [];

        foreach ($records as $record) {
            $evaluationplan = new self();
            $evaluationplan->id = $record->id;
            $evaluationplan->title = $record->title;
            $evaluationplan->audehsectionid = $record->audehsectionid;
            $evaluationplan->description = $record->description;
            $evaluationplan->descriptionfull = $record->descriptionfull;
            $evaluationplan->instructions = $record->instructions;
            $evaluationplan->criteria = $record->criteria;
            $evaluationplan->weight = $record->weight;
            $evaluationplan->audehcourseid = $record->audehcourseid;
            $evaluationplan->timemodified = $record->timemodified;
            $evaluationplan->learningobjectiveids = evaluationobjective_plan::instance_all_by_evaluation_plan_id($record->id);

            $evaluationplans[] = $evaluationplan;
        }

        return $evaluationplans;
    }

    /**
     * Instantiate an object by querying the database with the evaluation plan ID. An error is raised if no such evaluation
     * plan exists.
     * @param $id
     * @return evaluation_plan
     * @throws \dml_exception
     */
    public static function instance_by_id($id) {
        global $DB;

        $record = $DB->get_record('udehauthoring_evaluation', ['id' => $id], '*', MUST_EXIST);

        $evaluation_plan = new self();
        foreach($evaluation_plan as $key => $_) {
            if($key == 'learningobjectiveids') {
                $evaluation_plan->$key = evaluationobjective_plan::instance_all_by_evaluation_plan_id($record->id);
            } else if($key != 'introduction') {
                $evaluation_plan->$key = $record->$key;
            }
        }

        return $evaluation_plan;
    }

    /**
     * Produces data in the correct format for filling \format_udehauthoring\form\redact_evaluation
     *
     * @return object
     */
    public function to_form_data($context) {

        $draftintroductionid = file_get_submitted_draft_itemid('evaluation_introduction');

        file_prepare_draft_area($draftintroductionid, $context->id, 'format_udehauthoring', 'evaluationintroduction', $this->id);

        return (object)[
            'id' => $this->id,
            'audeh_section_id' => $this->audehsectionid,
            'audeh_course_id' => $this->audehcourseid,
            'evaluation_title' => $this->title,
            'evaluation_introduction' => $draftintroductionid,
            'evaluation_description' => $this->description,
            'evaluation_full_description' => (object)[
                'text' => $this->descriptionfull,
                'format' => FORMAT_HTML
            ],
            'evaluation_instructions' => (object) [
                'text' => $this->instructions,
                'format' => FORMAT_HTML
            ],
            'evaluation_criteria' => (object)[
                'text' => $this->criteria,
                'format' => FORMAT_HTML
            ],
            'evaluation_weight' => $this->weight,
        ];
    }

    public static function to_form_data_global($context, $evaluations, $courseid) {

        $drafintroductionids = [];
        foreach ($evaluations as $i=>$evaluation) {
            $drafintroductionid = file_get_submitted_draft_itemid('evaluation_introduction[' . $i . ']');
            file_prepare_draft_area($drafintroductionid, $context->id, 'format_udehauthoring', 'evaluationintroduction', $evaluation->id);
            $drafintroductionids[] = $drafintroductionid;
        }

        $test = (object)[
            'course_id' => $courseid,
            'evaluation_id' => array_map(function($evaluation) { return $evaluation->id; }, $evaluations),
            'audeh_section_id' => array_map(function($evaluation) { return $evaluation->audehsectionid; }, $evaluations),
            'audeh_course_id' => array_map(function($evaluation) { return $evaluation->audehcourseid; }, $evaluations),
            'evaluation_title' => array_map(function($evaluation) {
                return $evaluation->title;
            }, $evaluations),
            'evaluation_introduction' => array_map(function($mediaid) { return $mediaid; }, $drafintroductionids),
            'evaluation_description' => array_map(function($evaluation) {
                return $evaluation->description;
            }, $evaluations),
            'evaluation_full_description' => array_map(function($evaluation) {
                return (object)[
                    'text' => $evaluation->descriptionfull,
                    'format' => FORMAT_HTML
                ];
            }, $evaluations),
            'evaluation_instructions' => array_map(function($evaluation) {
                return (object)[
                    'text' => $evaluation->instructions,
                    'format' => FORMAT_HTML
                ];
            }, $evaluations),
            'evaluation_criteria' => array_map(function($evaluation) {
                return (object)[
                    'text' => $evaluation->criteria,
                    'format' => FORMAT_HTML
                ];
            }, $evaluations),
            'evaluation_weight' => array_map(function($evaluation) { return $evaluation->weight; }, $evaluations),
        ];

        return $test;
    }

    /**
     * Instanciate an object from form data as return by a \moodleform.
     *
     * @param $data object
     * @return evaluation_plan
     */
    public static function instance_by_form_data($data) {
        $evaluation_plan = new self();
        $evaluation_plan->id = $data->id;
        $evaluation_plan->audehsectionid = $data->audeh_section_id;
        $evaluation_plan->audehcourseid = $data->audeh_course_id;
        $evaluation_plan->title = $data->evaluation_title;
        $evaluation_plan->description = $data->evaluation_description;
        $evaluation_plan->introduction = $data->evaluation_introduction;
        $evaluation_plan->descriptionfull = $data->evaluation_full_description['text'];
        $evaluation_plan->instructions = $data->evaluation_instructions['text'];
        $evaluation_plan->criteria = $data->evaluation_criteria['text'];
        $evaluation_plan->weight = $data->evaluation_weight;
        return $evaluation_plan;
    }

    /**
     * Instanciate an object from form data as return by a \moodleform.
     *
     * @param $data object
     * @return array
     */
    public static function instance_by_form_data_global($data) {
        $global_evaluations_plan = [];

        foreach ($data->evaluation_id as $ii => $id) {
            $evaluationid = $data->evaluation_id[$ii];
            if ($evaluationid) {
                $evaluation_plan = new evaluation_plan();
                if ($evaluationid) {
                    $evaluation_plan->id = $evaluationid;
                }
                $evaluation_plan->title = $data->evaluation_title[$ii];
                $evaluation_plan->audehsectionid = $data->audeh_section_id[$ii];
                $evaluation_plan->audehcourseid = $data->audeh_course_id[$ii];
                $evaluation_plan->description = $data->evaluation_description[$ii];
                $evaluation_plan->introduction = $data->evaluation_introduction[$ii];
                $evaluation_plan->descriptionfull = $data->evaluation_full_description[$ii]['text'];
                $evaluation_plan->instructions = $data->evaluation_instructions[$ii]['text'];
                $evaluation_plan->criteria = $data->evaluation_criteria[$ii]['text'];
                $evaluation_plan->weight = $data->evaluation_weight[$ii];
                $global_evaluations_plan[] = $evaluation_plan;
            }
        }

        return $global_evaluations_plan;
    }

    public function save($context, $fromregularsave = true) {
        global $DB;

        $record = new \stdClass();
        foreach ($this as $key => $value) {
            if (!is_null($value) && $key != 'introduction' && $key != 'learningobjectiveids') {
                $record->$key = $value;
            }
        }
        if($fromregularsave) {
            utils::file_save_draft_area_files($this->introduction, $context->id, 'format_udehauthoring', 'evaluationintroduction',
                $this->id);
        }

        if (isset($record->id)) {
            utils::db_update_if_changes('udehauthoring_evaluation', $record);
        } else {
            $record->timemodified = time();
            $this->id = $DB->insert_record('udehauthoring_evaluation', $record);
        }

        if($fromregularsave) {
            foreach ($this->learningobjectiveids as $learningobjectiveid => $value) {
                $evaluation_obj = new evaluationobjective_plan();
                $evaluation_obj->audehlearningobjectiveid = (int) $learningobjectiveid;
                $evaluation_obj->audehevaluationid = $this->id;
                $evaluation_obj->audehcourseid = $this->audehcourseid;
                if($value == 1 && !$evaluation_obj->instance_exists()) {
                    $evaluation_obj->save();
                } else if($value == 0 && $evaluation_obj->instance_exists()) {
                    $evaluation_obj->delete();
                }
            }
        }
    }

    public function delete() {
        global $DB;

        $DB->delete_records('udehauthoring_evaluation_obj', ['audehevaluationid' => $this->id]);

        return $DB->delete_records('udehauthoring_evaluation', ['id' => $this->id]);
    }
}