<?php


namespace format_udehauthoring\model;


use format_udehauthoring\utils;

class exploration_plan
{

    public $id = null;
    public $audehsubquestionid = null;
    public $title = null;
    public $question = null;
    public $activitytype = null;
    public $temporality = null;
    public $location = null;
    public $grouping = null;
    public $ismarked = null;
    public $evaluationtype = null;
    public $length = null;
    public $instructions = null;
    public $timemodified = null;

    /**
     * Instanciate an object from form data as return by a \moodleform.
     *
     * @param $data object
     * @return exploration_plan
     */
    public static function instance_by_form_data($data) {
        $explorationplan = new self();
        $explorationplan->id = $data->id;
        $explorationplan->audehsubquestionid = $data->audeh_subquestion_id;
        $explorationplan->title = $data->exploration_title['text'];
        $explorationplan->question = $data->exploration_question['text'];
        $explorationplan->activitytype = $data->exploration_activity_type;
        $explorationplan->temporality = $data->exploration_temporality;
        $explorationplan->location = $data->exploration_location;
        $explorationplan->grouping = $data->exploration_grouping;
        $explorationplan->ismarked = $data->exploration_marked;
        $explorationplan->evaluationtype = $data->exploration_evaluation_type;
        $explorationplan->length = $data->exploration_length['text'];
        $explorationplan->instructions= $data->exploration_instructions['text'];

        return $explorationplan;
    }

    /**
     * Returns an array of all section_plans included in the course_plan with the specified ID.
     *
     * @param $audehsubquestionid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_subquestion_plan_id($audehsubquestionid) {
        global $DB;

        $records = $DB->get_records('udehauthoring_exploration', ['audehsubquestionid' => $audehsubquestionid]);

        $explorationplans = [];

        foreach ($records as $record) {
            $explorationplan = new self();
            $explorationplan->id = $record->id;
            $explorationplan->audehsubquestionid = $record->audehsubquestionid;
            $explorationplan->activitytype = $record->activitytype;
            $explorationplan->temporality = $record->temporality;
            $explorationplan->location = $record->location;
            $explorationplan->grouping = $record->grouping;
            $explorationplan->ismarked = $record->ismarked;
            $explorationplan->evaluationtype = $record->evaluationtype;
            $explorationplan->length = $record->length;
            $explorationplan->instructions = $record->instructions;
            $explorationplan->location = $record->location;
            $explorationplan->timemodified = $record->timemodified;
            $explorationplans[] = $explorationplan;
        }

        return $explorationplans;
    }


    /**
     * Instantiate an object by querying the database with the subquestion plan ID. An error is raised if no such subquestion
     * plan exists.
     * @param $id
     * @return exploration_plan
     * @throws \dml_exception
     */
    public static function instance_by_id($id) {
        global $DB;

        $record = $DB->get_record('udehauthoring_exploration', ['id' => $id], '*', MUST_EXIST);

        $explorationplan = new self();
        foreach($explorationplan as $key => $_) {
            if($key != 'media') {
                $explorationplan->$key = $record->$key;
            }
        }

        return $explorationplan;
    }

    /**
     * Produces data in the correct format for filling \format_udehauthoring\form\redact_subquestion
     *
     * @return object
     */
    public function to_form_data($context) {

        return (object)[
            'id' => $this->id,
            'audeh_subquestion_id' => $this->audehsubquestionid,
            'exploration_title' => (object)[
                'text' => $this->title,
                'format' => FORMAT_HTML
            ],
            'exploration_question' => (object)[
                'text' => $this->question,
                'format' => FORMAT_HTML
            ],
            'exploration_activity_type' => $this->activitytype,
            'exploration_temporality' => $this->temporality,
            'exploration_length' => $this->length,
            'exploration_location' => $this->location,
            'exploration_grouping' => $this->grouping,
            'exploration_instructions' => (object)[
                'text' => $this->instructions,
                'format' => FORMAT_HTML
            ],
            'exploration_marked' => $this->ismarked,
            'exploration_evaluation_type' => $this->evaluationtype
        ];
    }

    public function save() {
        global $DB;

        $record = new \stdClass();
        foreach ($this as $key => $value) {
            if (!is_null($value) && $key != 'media') {
                $record->$key = $value;
            }
        }

        if (isset($record->id)) {
            utils::db_update_if_changes('udehauthoring_exploration', $record);
        } else {
            $record->timemodified = time();
            $this->id = $DB->insert_record('udehauthoring_exploration', $record);
        }

    }

    public function delete() {
        global $DB;

        return $DB->delete_records('udehauthoring_exploration', ['id' => $this->id]);
    }

    public static function activity_type_list() {
        return [
            'Accueil et brise glace',
            'Remues-méninges',
            'Exposé/Présentation',
            'Discussion de groupes',
            'Débat',
            'Entretien',
            'Étude de cas',
            'Simulation',
            'Laboratoire',
            'Pratique guidée (modélisation)',
            'Pratique Autonome',
            'Exploration/recherche documentaire',
            'Création d\'une infographie',
            'Production audio',
            'Production vidéo',
            'production d\'une réflexion critique',
            'Production d\'une synthèse',
            'Production d\'une analyse comparative',
            'Questionnaire',
            'Ajouter votre activité'
        ];
    }

    public static function getActivityTypeFromIndex($index) {
        $activitytypelist = exploration_plan::activity_type_list();
        return $activitytypelist[$index];
    }
}