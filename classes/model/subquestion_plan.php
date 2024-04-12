<?php


namespace format_udehauthoring\model;
use format_udehauthoring\utils;

global $CFG;

require_once $CFG->dirroot.  '/lib/resourcelib.php';

class subquestion_plan
{

    public $id = null;
    public $audehsectionid = null;
    public $title = null;
    public $titleformat = null;
    public $enonce = null;
    public $enonceformat = null;
    public $vignette = null;
    public $explorations = null;
    public $resources = null;
    public $timemodified = null;

    /**
     * Returns an array of all subquestion_plan included in the section_plan with the specified ID.
     *
     * @param $audehcourseid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_section_plan_id($audehsectionid, $context = null) {
        global $DB;

        $records = $DB->get_records('udehauthoring_sub_question', ['audehsectionid' => $audehsectionid]);

        $subquestionplans = [];

        foreach ($records as $record) {
            $subquestionplan = new self();
            $subquestionplan->id = $record->id;
            $subquestionplan->audehsectionid = $record->audehsectionid;
            $subquestionplan->title = $record->title;
            $subquestionplan->enonce = $record->enonce;
            $subquestionplan->explorations =
                exploration_plan::instance_all_by_subquestion_plan_id($subquestionplan->id, $context);
            $subquestionplan->resources =
                resource_plan::instance_all_by_subquestion_plan_id($subquestionplan->id, $context);
            $subquestionplan->timemodified = $record->timemodified;

            if ($context) {
                $options = format_udehauthoring_get_editor_options($context);
                $editors = ['title', 'enonce'];
                foreach ($editors as $editor) {
                    $subquestionplan = file_prepare_standard_editor(
                        $subquestionplan,
                        $editor,
                        $options,
                        $context,
                        'format_udehauthoring',
                        'course_subquestion_' . $editor . '_' . $subquestionplan->id,
                        0
                    );
                }
            }

            $subquestionplans[] = $subquestionplan;
        }

        return $subquestionplans;
    }

    /**
     * Instanciate an object from form data as return by a \moodleform.
     *
     * @param $data object
     * @return subquestion_plan
     */
    public static function instance_by_form_data($data) {
        $subquestionplan = new self();
        $subquestionplan->id = $data->id;
        $subquestionplan->audehsectionid = $data->audeh_section_id;
        $subquestionplan->title = $data->subquestion_title;
        $subquestionplan->enonce_editor = $data->subquestion_enonce;
        $subquestionplan->vignette = $data->subquestion_vignette;

        $subquestionplan->explorations = [];
        foreach ($data->exploration_instructions as $ii => $explorationinstructions) {
            $explorationid = $data->exploration_id[$ii];
            if ($explorationid || $explorationinstructions) {
                $explorationplan = new exploration_plan();
                if ($subquestionplan->id) {
                    $explorationplan->audehsubquestionid = $subquestionplan->id;
                }
                if($explorationid) {
                    $explorationplan->id = $explorationid;
                }

                $explorationplan->title = $ii >= count($data->exploration_title) ? '' : $data->exploration_title[$ii];
                $explorationplan->toolcmid = $ii >= count($data->exploration_tool_cmid) ? '' : $data->exploration_tool_cmid[$ii];
                if(isset($data->tool_group)) {
                    $explorationplan->tooltype = $ii >= count($data->tool_group) ? 0 : $data->tool_group[$ii]['exploration_tool'];
                }
                $explorationplan->question = $ii >= count($data->exploration_question) ? '' : $data->exploration_question[$ii];
                $explorationplan->activitytype = $ii >= count($data->exploration_activity_type) ? '' : $data->exploration_activity_type[$ii];
                $explorationplan->activityfreetype_editor = $data->exploration_activity_free_type[$ii];
                $explorationplan->activityfreetype = $data->exploration_activity_free_type[$ii]['text'];
                $explorationplan->temporality = $ii >= count($data->exploration_temporality) ? '' : $data->exploration_temporality[$ii];
                $explorationplan->location = $ii >= count($data->exploration_location) ? '' : $data->exploration_location[$ii];
                $explorationplan->party = $ii >= count($data->exploration_party) ? '' : $data->exploration_party[$ii];
                $explorationplan->ismarked = $data->exploration_marked[$ii];
                $explorationplan->evaluationtype = $ii >= count($data->exploration_evaluation_type) ? '' : $data->exploration_evaluation_type[$ii];
                $explorationplan->length = $ii >= count($data->exploration_length) ? '' : $data->exploration_length[$ii];
                $explorationplan->instructions_editor = $data->exploration_instructions[$ii];
                $explorationplan->instructions = $data->exploration_instructions[$ii]['text'];

                $subquestionplan->explorations[] = $explorationplan;
            }
        }

        $subquestionplan->resources = [];
        foreach ($data->resource_title as $ii => $resourcetitle) {
            $resourceid = $data->resource_id[$ii];
            if ($resourceid || $resourcetitle['text']) {
                $resourceplan = new resource_plan();
                if ($subquestionplan->id) {
                    $resourceplan->audehsubquestionid = $subquestionplan->id;
                }

                if($resourceid) {
                    $resourceplan->id = $resourceid;
                }

                $resourceplan->title_editor = $resourcetitle;
                $resourceplan->vignette = $data->resource_vignette[$ii];
                $resourceplan->link = $data->resource_external_link[$ii];

                $subquestionplan->resources[] = $resourceplan;
            }
        }



        return $subquestionplan;
    }

    /**
     * Instantiate an object by querying the database with the subquestion plan ID. An error is raised if no such subquestion
     * plan exists.
     * @param $id
     * @return subquestion_plan
     * @throws \dml_exception
     */
    public static function instance_by_id($id) {
        global $DB;

        $record = $DB->get_record('udehauthoring_sub_question', ['id' => $id], '*', MUST_EXIST);

        $subquestionplan = new self();
        foreach($subquestionplan as $key => $_) {
            if ('resources' === $key) {
                $subquestionplan->$key = resource_plan::instance_all_by_subquestion_plan_id($subquestionplan->id);
            } else if('explorations' === $key) {
                $subquestionplan->$key = exploration_plan::instance_all_by_subquestion_plan_id($subquestionplan->id);
            }
            else if($key != 'vignette' && !str_ends_with($key, 'format')) {
                $subquestionplan->$key = $record->$key;
            }
        }

        return $subquestionplan;
    }

    /**
     * Produces data in the correct format for filling \format_udehauthoring\form\redact_subquestion
     *
     * @return object
     */
    public function to_form_data($context) {

        $draftvignetteid = file_get_submitted_draft_itemid('subquestion_vignette');
        file_prepare_draft_area($draftvignetteid, $context->id, 'format_udehauthoring', 'subquestionvignette', $this->id);

        $draftresourcevignetteids = [];
        foreach ($this->resources as $i=>$resource) {
            $currentId = 'resource_vignette[' . $i . ']';
            $draftresourcevignetteid = file_get_submitted_draft_itemid($currentId);
            file_prepare_draft_area($draftresourcevignetteid, $context->id, 'format_udehauthoring', 'resourcevignette', $resource->id);
            $draftresourcevignetteids[] = $draftresourcevignetteid;
        }

        $toreturn = (object)[
            'id' => $this->id,
            'audeh_section_id' => $this->audehsectionid,
            'subquestion_title' => $this->title,
            'subquestion_enonce' => (object)[
                'text' => file_rewrite_pluginfile_urls(
                    $this->enonce,
                    'pluginfile.php',
                    $context->id,
                    'format_udehauthoring',
                    'course_subquestion_enonce_' . $this->id,
                    0
                ),
                'format' => FORMAT_HTML
            ],
            'subquestion_vignette' => $draftvignetteid,
            'exploration_id' => array_map(function($exploration) { return $exploration->id; }, $this->explorations),
            'exploration_title' => array_map(function($exploration) {
                return $exploration->title;
            }, $this->explorations),
            'exploration_question' => array_map(function($exploration) {
                return $exploration->question;
            }, $this->explorations),
            'exploration_activity_type' => array_map(function($exploration) { return $exploration->activitytype; }, $this->explorations),
            'exploration_activity_free_type' => array_map(function($exploration) use ($context) {
                return (object)[
                    'text' => file_rewrite_pluginfile_urls(
                        $exploration->activityfreetype,
                        'pluginfile.php',
                        $context->id,
                        'format_udehauthoring',
                        'course_exploration_activityfreetype_' . $exploration->id,
                        0
                    ),
                    'format' => FORMAT_HTML
                ];
            }, $this->explorations),
            'exploration_temporality' => array_map(function($exploration) { return $exploration->temporality; }, $this->explorations),
            'exploration_length' => array_map(function($exploration) { return $exploration->length; }, $this->explorations),
            'exploration_location' => array_map(function($exploration) { return $exploration->location; }, $this->explorations),
            'exploration_party' => array_map(function($exploration) { return $exploration->party; }, $this->explorations),
            'exploration_instructions' => array_map(function($exploration) use ($context) {
                return (object)[
                    'text' => file_rewrite_pluginfile_urls(
                        $exploration->instructions,
                        'pluginfile.php',
                        $context->id,
                        'format_udehauthoring',
                        'course_exploration_instructions_' . $exploration->id,
                        0
                    ),
                    'format' => FORMAT_HTML
                ];
            }, $this->explorations),
            'exploration_marked' => array_map(function($exploration) { return $exploration->ismarked; }, $this->explorations),
            'exploration_evaluation_type' => array_map(function($exploration) { return $exploration->evaluationtype; }, $this->explorations),
            'exploration_tool_cmid' => array_map(function($exploration) { return $exploration->toolcmid; }, $this->explorations),
            'resource_id' => array_map(function($resource) { return $resource->id; }, $this->resources),
            'resource_title' => array_map(function($resource) use ($context) {
                return (object)[
                    'text' => file_rewrite_pluginfile_urls(
                        $resource->title,
                        'pluginfile.php',
                        $context->id,
                        'format_udehauthoring',
                        'course_resource_title_' . $resource->id,
                        0
                    ),
                    'format' => FORMAT_HTML
                ];
            }, $this->resources),
            'resource_vignette' => array_map(function($mediaid) { return $mediaid; }, $draftresourcevignetteids),
            'resource_external_link' => array_map(function($resource) { return $resource->link; }, $this->resources),
        ];

        for($i = 0; $i < count($this->explorations); $i++) {
            $property = 'tool_group[' . $i . '][exploration_tool]';
            $toreturn->$property = $this->explorations[$i]->tooltype;
        }

        return $toreturn;
    }

    public function save($context, $fromregularsave = true) {
        global $DB;

        $record = new \stdClass();
        foreach ($this as $key => $value) {
            if ($key != 'vignette' && $key != 'timemodified') {
                if($fromregularsave) {
                    if ($key != 'title' && $key != 'timemodified' && !str_ends_with($key, 'format')) {
                        $record->$key = $value;
                    }
                } else {
                    if (($key == 'title' || $key == 'id' || $key == 'audehsectionid')
                        && $key != 'timemodified'
                        && !str_ends_with($key, 'format')) {
                        $record->$key = $value;
                    }
                }
            }
        }


        if (!isset($record->id)) {
            $record->timemodified = time();
            $this->id = $DB->insert_record('udehauthoring_sub_question', $record);
            $record->id = $this->id;
            if ($this->explorations) {
                foreach ($this->explorations as $exploration) {
                    $exploration->audehsubquestionid = $this->id;
                }
            }

            if ($this->resources) {
                foreach ($this->resources as $resource) {
                    $resource->audehsubquestionid = $this->id;
                }
            }
        }

        if($fromregularsave) {
            utils::file_save_draft_area_files($this->vignette, $context->id, 'format_udehauthoring', 'subquestionvignette',
                $this->id);
        }

        $editors = ['title', 'enonce'];
        foreach ($editors as $editor) {
            if (!empty($this->{$editor.'_editor'})) {
                $record = utils::prepareEditorContent($this, $record, $context, $editor, 'course_subquestion_');
            }
        }

        if (isset($record->id)) {
            utils::db_update_if_changes('udehauthoring_sub_question', $record);
        }

        // save exploration
        if($fromregularsave) {
            $input_explorations_id = [];
            $exploration_record_ids = $DB->get_records('udehauthoring_exploration', ['audehsubquestionid' => $this->id], '', 'id');

            if ($this->explorations) {
                foreach ($this->explorations as $exploration) {
                    $input_explorations_id[$exploration->id] = $exploration->id;
                    if ($exploration->id
                        && (empty($exploration->instructions) && empty($resource->title_editor['text']))) {
                        $exploration->delete($context);
                    } else {
                        $exploration->save($context);
                    }
                }
            }

            foreach ($exploration_record_ids as $exploration_record_id) {
                if (!in_array($exploration_record_id->id, $input_explorations_id)) {
                    $explorationplan = \format_udehauthoring\model\exploration_plan::instance_by_id($exploration_record_id->id);
                    $explorationplan->delete($context);
                }
            }

            // save resources
            $input_resources_id = [];
            $resource_record_ids = $DB->get_records('udehauthoring_resource', ['audehsubquestionid' => $this->id], '', 'id');

            if ($this->resources) {
                foreach ($this->resources as $resource) {
                    $input_resources_id[$resource->id] = $resource->id;
                    if ($resource->id && (empty($resource->title) && empty($resource->title_editor['text']))) {
                        $resource->delete($context);
                    } else {
                        $resource->save($context, true);
                    }
                }
            }

            foreach ($resource_record_ids as $resource_record_id) {
                if (!in_array($resource_record_id->id, $input_resources_id)) {
                    $resourceplan = \format_udehauthoring\model\resource_plan::instance_by_id($resource_record_id->id);
                    $resourceplan->delete($context);
                }
            }
        }
    }

    public function delete($context) {
        global $DB;
        if (isset($this->explorations)) {
            foreach ($this->explorations as $exploration) {
                $exploration->delete();
            }
        }

        utils::db_bump_timechanged('udehauthoring_section', $this->audehsectionid);


        $following_siblings = $DB->get_records_sql(
            " SELECT id 
                  FROM {udehauthoring_sub_question}
                  WHERE audehsectionid = ?
                  AND id > ?",
            [ $this->audehsectionid, $this->id ]
        );

        foreach ($following_siblings as $following_sibling) {
            utils::db_bump_timechanged('udehauthoring_sub_question', $following_sibling->id);
        }

        utils::deleteAssociatedAutoSavesAndFiles($context, 'course_subquestion_title_' . $this->id);
        utils::deleteAssociatedAutoSavesAndFiles($context, 'course_subquestion_enonce_' . $this->id);

        return $DB->delete_records('udehauthoring_sub_question', ['id' => $this->id]);
    }
}