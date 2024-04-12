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
    public $activityfreetypeformat = null;
    public $temporality = null;
    public $location = null;
    public $party = null;
    public $ismarked = null;
    public $evaluationtype = null;
    public $length = null;
    public $instructions = null;
    public $instructionsformat = null;
    public $timemodified = null;
    public $tooltype = null;
    public $toolcmid = null;

    const EDITORS = ['activityfreetype', 'instructions'];

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
            get_string('explorationhome', 'format_udehauthoring'),
            get_string('explorationbrainstorming', 'format_udehauthoring'),
            get_string('explorationpresentation', 'format_udehauthoring'),
            get_string('explorationgroupdiscussion', 'format_udehauthoring'),
            get_string('explorationdebate', 'format_udehauthoring'),
            get_string('explorationinterview', 'format_udehauthoring'),
            get_string('explorationcasestudy', 'format_udehauthoring'),
            get_string('explorationsimulation', 'format_udehauthoring'),
            get_string('explorationlaboratory', 'format_udehauthoring'),
            get_string('explorationguidedpractice', 'format_udehauthoring'),
            get_string('explorationindependentpractice', 'format_udehauthoring'),
            get_string('explorationdocumentaryresearch', 'format_udehauthoring'),
            get_string('explorationinfographiccreation', 'format_udehauthoring'),
            get_string('explorationaudioproduction', 'format_udehauthoring'),
            get_string('explorationvideoproduction', 'format_udehauthoring'),
            get_string('explorationcriticalreflection', 'format_udehauthoring'),
            get_string('explorationsynthesisproduction', 'format_udehauthoring'),
            get_string('explorationcomparativeanalysis', 'format_udehauthoring'),
            get_string('explorationquestionnaire', 'format_udehauthoring'),
            get_string('explorationaddactivity', 'format_udehauthoring')
        ];
    }

    public static function get_activity_type_from_index($index) {
        $activitytypelist = self::activity_type_list();
        return $activitytypelist[$index];
    }

    public static function locations_list() {
        return [
            get_string('explorationonline', 'format_udehauthoring'),
            get_string('explorationathome', 'format_udehauthoring'),
            get_string('explorationinclassroom', 'format_udehauthoring')
        ];
    }

    public static function get_location_from_index($index) {
        $locationslist = self::locations_list();
        return $locationslist[$index];
    }

    // TODO
    public static function party_list() {
        return [
            get_string('explorationindividual', 'format_udehauthoring'),
            get_string('explorationpairs', 'format_udehauthoring'),
            get_string('explorationgroups', 'format_udehauthoring')
        ];
    }

    public static function get_party_from_index($index) {
        $partyslist = self::party_list();
        return $partyslist[$index];
    }

    /**
     * Returns an array of all section_plans included in the course_plan with the specified ID.
     *
     * @param $audehsubquestionid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_subquestion_plan_id($audehsubquestionid, $context = null) {
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
            $explorationplan->party = $record->party;
            $explorationplan->ismarked = $record->ismarked;
            $explorationplan->evaluationtype = $record->evaluationtype;
            $explorationplan->length = $record->length;
            $explorationplan->instructions = $record->instructions;
            $explorationplan->location = $record->location;
            $explorationplan->timemodified = $record->timemodified;
            $explorationplan->toolcmid= explorationtool_plan::get_related_cmid($record->id);
            $explorationplan->tooltype= explorationtool_plan::get_related_tool_type($record->id);

            if ($context) {
                $options = format_udehauthoring_get_editor_options($context);
                foreach (self::EDITORS as $editor) {
                    $explorationplan = file_prepare_standard_editor(
                        $explorationplan,
                        $editor,
                        $options,
                        $context,
                        'format_udehauthoring',
                        'course_exploration_' . $editor . '_' . $explorationplan->id,
                        0
                    );
                }
            }

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
            if($key != 'media' && $key != 'toolcmid' && $key != 'tooltype' && !str_ends_with($key, 'format')) {
                $explorationplan->$key = $record->$key;
            }
        }

        return $explorationplan;
    }

    public function save($context) {
        global $DB;

        $record = new \stdClass();
        foreach ($this as $key => $value) {
            if ($key != 'media' && $key != 'timemodified') {
                $record->$key = $value;
            }
        }

        if (!isset($record->id)) {
            $record->timemodified = time();
            $this->id = $DB->insert_record('udehauthoring_exploration', $record);
            $record->id = $this->id;
        }

        foreach (self::EDITORS as $editor) {
            if (!empty($this->{$editor.'_editor'})) {
                $record = utils::prepareEditorContent($this, $record, $context, $editor, 'course_exploration_');
            }
        }

        if (isset($record->id)) {
            utils::db_update_if_changes('udehauthoring_exploration', $record);
        }
    }

    public function delete($context) {
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

        foreach (self::EDITORS as $editor) {
            utils::deleteAssociatedAutoSavesAndFiles($context, 'course_exploration_' . $editor .'_' . $this->id);
        }

        return $DB->delete_records('udehauthoring_exploration', ['id' => $this->id]);
    }

}