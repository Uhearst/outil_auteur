<?php


namespace format_udehauthoring\model;


use format_udehauthoring\utils;
use moodle_url;

class exploration_plan
{

    public $id = null;
    public $audehsubquestionid = null;
    public $title = null;
    public $question = null;
    public $activitytype = null;
    public $activityfreetype = null;
    public $temporality = null;
    public $location = null;
    public $grouping = null;
    public $ismarked = null;
    public $evaluationtype = null;
    public $length = null;
    public $instructions = null;
    public $timemodified = null;
    public $tooltype = null;
    public $toolcmid = null;

    /**
     * return list of available tools.
     *
     * @return array
     */
    public static function get_available_tools() {
        return [
            get_string('toolassignment', 'format_udehauthoring'),
            get_string('toolh5p', 'format_udehauthoring'),
            get_string('toolquiz', 'format_udehauthoring'),
            'Feedback',
            'Forum',
            get_string('toolscorm', 'format_udehauthoring'),
            get_string('tooljournal', 'format_udehauthoring'),
            get_string('toolzoom', 'format_udehauthoring'),
            get_string('toolglossary', 'format_udehauthoring'),
            'Wiki',
            get_string('toolworkshop', 'format_udehauthoring'),
            'Chat',
            get_string('toolsurvey', 'format_udehauthoring'),
            get_string('toollesson', 'format_udehauthoring'),
            ];
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
            'Production d\'une réflexion critique',
            'Production d\'une synthèse',
            'Production d\'une analyse comparative',
            'Questionnaire',
            'Ajouter votre activité'
        ];
    }

    public static function get_activity_type_from_index($index) {
        $activitytypelist = self::activity_type_list();
        return $activitytypelist[$index];
    }

    public static function locations_list() {
        return [
            'En ligne',
            'A la maison',
            'En salle de classe'
        ];
    }

    public static function get_location_from_index($index) {
        $locationslist = self::locations_list();
        return $locationslist[$index];
    }

    public static function grouping_list() {
        return [
            'Individuel',
            'Paires',
            'Groupes'
        ];
    }

    public static function get_grouping_from_index($index) {
        $groupingslist = self::grouping_list();
        return $groupingslist[$index];
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
            $explorationplan->activityfreetype = $record->activityfreetype;
            $explorationplan->temporality = $record->temporality;
            $explorationplan->grouping = $record->grouping;
            $explorationplan->ismarked = $record->ismarked;
            $explorationplan->evaluationtype = $record->evaluationtype;
            $explorationplan->length = $record->length;
            $explorationplan->instructions = $record->instructions;
            $explorationplan->location = $record->location;
            $explorationplan->timemodified = $record->timemodified;
            $relatedtoolcmid = explorationtool_plan::get_related_cmid($record->id);
            $explorationplan->toolcmid= $relatedtoolcmid;
            $relatedtooltype = explorationtool_plan::get_related_tool_type($record->id);
            $explorationplan->tooltype= $relatedtooltype;

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
            if($key != 'media' && $key != 'toolcmid' && $key != 'tooltype') {
                $explorationplan->$key = $record->$key;
            }
        }

        return $explorationplan;
    }

    public function save() {
        global $DB;

        $record = new \stdClass();
        foreach ($this as $key => $value) {
            if ($key != 'media' && $key != 'timemodified') {
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

        utils::db_bump_timechanged('udehauthoring_sub_question', $this->audehsubquestionid);

        // bump all following siblings
        $following_siblings = $DB->get_records_sql(
            " SELECT id 
                  FROM {udehauthoring_exploration}
                  WHERE audehsubquestionid = ?
                  AND id > ?",
            [ $this->audehsubquestionid, $this->id ]
        );

        foreach ($following_siblings as $following_sibling) {
            utils::db_bump_timechanged('udehauthoring_exploration', $following_sibling->id);
        }

        $DB->delete_records('udehauthoring_exp_tool', ['audehexplorationid' => $this->id]);

        return $DB->delete_records('udehauthoring_exploration', ['id' => $this->id]);
    }

}