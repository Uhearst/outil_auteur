<?php


namespace format_udehauthoring\model;


use format_udehauthoring\utils;

class resource_plan
{
    public $id = null;
    public $audehsubquestionid = null;
    public $title = null;
    public $link = null;
    public $vignette = null;
    public $timemodified = null;

    /**
     * Instanciate an object from form data as return by a \moodleform.
     *
     * @param $data object
     * @return resource_plan
     */
    public static function instance_by_form_data($data) {
        $resourceplan = new self();
        $resourceplan->id = $data->id;
        $resourceplan->audehsubquestionid = $data->audeh_subquestion_id;
        $resourceplan->title = $data->resource_title['text'];
        $resourceplan->link = $data->resource_external_link;
        $resourceplan->vignette = $data->resource_vignette;

        return $resourceplan;
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

        $records = $DB->get_records('udehauthoring_resource', ['audehsubquestionid' => $audehsubquestionid]);

        $resourceplans = [];

        foreach ($records as $record) {
            $resourceplan = new self();
            $resourceplan->id = $record->id;
            $resourceplan->audehsubquestionid = $record->audehsubquestionid;
            $resourceplan->title = $record->title;
            $resourceplan->link = $record->link;
            $resourceplan->timemodified = $record->timemodified;
            $resourceplans[] = $resourceplan;
        }

        return $resourceplans;
    }


    /**
     * Instantiate an object by querying the database with the subquestion plan ID. An error is raised if no such subquestion
     * plan exists.
     * @param $id
     * @return resource_plan
     * @throws \dml_exception
     */
    public static function instance_by_id($id) {
        global $DB;

        $record = $DB->get_record('udehauthoring_resource', ['id' => $id], '*', MUST_EXIST);

        $resourceplan = new self();
        foreach($resourceplan as $key => $_) {
            if($key != 'vignette') {
                $resourceplan->$key = $record->$key;
            }
        }

        return $resourceplan;
    }

    /**
     * Produces data in the correct format for filling \format_udehauthoring\form\redact_resource
     *
     * @return object
     */
    public function to_form_data($context) {

        $draftvignetteid = file_get_submitted_draft_itemid('resource_vignette');

        file_prepare_draft_area($draftvignetteid, $context->id, 'format_udehauthoring', 'resourcevignette', $this->id);

        return (object)[
            'id' => $this->id,
            'audeh_subquestion_id' => $this->audehsubquestionid,
            'resource_title' => (object)[
                'text' => $this->title,
                'format' => FORMAT_HTML
            ],
            'resource_external_link' => $this->link,
            'resource_vignette' => $draftvignetteid
        ];
    }

    public function save($context, $fromregularsave = true) {
        global $DB;

        $record = new \stdClass();
        foreach ($this as $key => $value) {
            if (!is_null($value) && $key != 'vignette') {
                $record->$key = $value;
            }
        }

        if (isset($record->id)) {
            utils::db_update_if_changes('udehauthoring_resource', $record);
        } else {
            $record->timemodified = time();
            $this->id = $DB->insert_record('udehauthoring_resource', $record);
        }
        if($fromregularsave) {
            utils::file_save_draft_area_files($this->vignette, $context->id, 'format_udehauthoring', 'resourcevignette',
                $this->id);
        }

    }

    public function delete() {
        global $DB;

        return $DB->delete_records('udehauthoring_resource', ['id' => $this->id]);
    }
}