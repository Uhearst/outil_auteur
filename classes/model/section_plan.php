<?php

namespace format_udehauthoring\model;

use format_udehauthoring\utils;

global $CFG;

require_once $CFG->dirroot.  '/course/modlib.php';
require_once $CFG->dirroot.  '/course/lib.php';
require_once $CFG->dirroot.  '/lib/resourcelib.php';

/**
 * General information about a course module written in the authoring tool.
 * Responsible for CRUD database operations and data structure conversions.
 */
class section_plan
{
    public $id = null;
    public $audehcourseid = null;
    public $title = null;
    public $titleformat = null;
    public $description = null;
    public $descriptionformat = null;
    public $vignette = null;
    public $introductiontext = null;
    public $introductiontextformat = null;
    public $isembed = null;
    public $embed = null;
    public $introduction = null;
    public $question = null;
    public $questionformat = null;
    public $subquestions = null;
    public $comments = null;
    public $isvisible = null;
    public $isvisiblepreview = null;
    public $timemodified = null;

    /**
     * Returns an array of all section_plans included in the course_plan with the specified ID.
     *
     * @param $audehcourseid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_course_plan_id($audehcourseid, $context = null) {
        global $DB;

        $records = $DB->get_records('udehauthoring_section', ['audehcourseid' => $audehcourseid]);

        $sectionplans = [];

        foreach ($records as $record) {
            $sectionplan = new self();
            $sectionplan->id = $record->id;
            $sectionplan->audehcourseid = $record->audehcourseid;
            $sectionplan->title = $record->title;
            $sectionplan->question = $record->question;
            $sectionplan->description = $record->description;
            $sectionplan->introductiontext = $record->introductiontext;
            $sectionplan->isembed = $record->isembed;
            $sectionplan->embed = $record->embed;
            $sectionplan->subquestions = subquestion_plan::instance_all_by_section_plan_id($sectionplan->id, $context);
            $sectionplan->comments = $record->comments;
            $sectionplan->isvisible = $record->isvisible;
            $sectionplan->isvisiblepreview = $record->isvisiblepreview;
            $sectionplan->timemodified = $record->timemodified;

            if ($context) {
                $options = format_udehauthoring_get_editor_options($context);
                $editors = ['title', 'question', 'description', 'introductiontext'];
                foreach ($editors as $editor) {
                    $sectionplan = file_prepare_standard_editor(
                        $sectionplan,
                        $editor,
                        $options,
                        $context,
                        'format_udehauthoring',
                        'course_section_' . $editor . '_' . $sectionplan->id,
                        0
                    );
                }
            }


            $sectionplans[] = $sectionplan;
        }

        return $sectionplans;
    }

    /**
     * Instanciate an object from form data as return by a \moodleform.
     *
     * @param $data object
     * @return section_plan
     */
    public static function instance_by_form_data($data) {
        $sectionplan = new self();
        $sectionplan->id = $data->id;
        $sectionplan->audehcourseid = $data->audeh_course_id;
        $sectionplan->title = $data->section_title;
        $sectionplan->description = $data->section_description;
        $sectionplan->vignette = $data->section_vignette;
        $sectionplan->introductiontext_editor = $data->section_introduction_text;
        $sectionplan->isembed = $data->isembed;
        $sectionplan->embed = $data->section_introduction_embed;
        $sectionplan->introduction = $data->section_introduction;
        $sectionplan->question = $data->section_question;
        $sectionplan->comments = $data->section_comments;
        $sectionplan->isvisiblepreview = $data->isvisible;

        $sectionplan->subquestions = [];
        if(property_exists($data, 'subquestion_title')) {
            foreach ($data->subquestion_title as $ii => $title) {
                $subquestionid = $data->subquestion_id[$ii];
                if ($subquestionid || $title) {
                    $subquestion_plan = new subquestion_plan();
                    if ($sectionplan->id) {
                        $subquestion_plan->audehsectionid = $sectionplan->id;
                    }
                    if ($subquestionid) {
                        $subquestion_plan->id = $subquestionid;
                    }
                    if ($title) {
                        $subquestion_plan->title_editor = $title;
                    }
                    $sectionplan->subquestions[] = $subquestion_plan;
                }
            }
        }


        return $sectionplan;
    }

    /**
     * Instantiate an object by querying the database with the section plan ID. An error is raised if no such section
     * plan exists.
     * @param $id
     * @return section_plan
     * @throws \dml_exception
     */
    public static function instance_by_id($id) {
        global $DB;

        $record = $DB->get_record('udehauthoring_section', ['id' => $id], '*', MUST_EXIST);

        $sectionplan = new self();
        foreach($sectionplan as $key => $_) {
            if('subquestions' === $key) {
                $sectionplan->$key = subquestion_plan::instance_all_by_section_plan_id($sectionplan->id);
            } else if($key != 'vignette' && $key != 'introduction' && !str_contains($key, 'format')) {
                $sectionplan->$key = $record->$key;
            }

        }

        return $sectionplan;
    }

    /**
     * Instantiate an object by querying the database with the section plan ID. An error is raised if no such section
     * plan exists.
     * @param $id
     * @return string
     * @throws \dml_exception
     */
    public static function get_section_title_by_id($id) {
        global $DB;

        $sectionplan = $DB->get_record('udehauthoring_section', ['id' => $id], '*', MUST_EXIST);
        $courseId =  $DB->get_record(
            'udehauthoring_course',
            ['id' => $sectionplan->audehcourseid],
            'courseid',
            MUST_EXIST
        )->courseid;
        $context = \context_course::instance($courseId);
        $options = format_udehauthoring_get_editor_options($context);
        $sectionplan->titleformat = FORMAT_HTML;
        $sectionplan = file_prepare_standard_editor(
            $sectionplan,
            'title',
            $options,
            $context,
            'format_udehauthoring',
            'course_section_title_' . $sectionplan->id,
            0
        );
        return $sectionplan->title;
    }

    /**
     * Produces data in the correct format for filling \format_udehauthoring\form\redact_section
     *
     * @return object
     */
    public function to_form_data($context) {

        $draftvignetteid = file_get_submitted_draft_itemid('section_vignette');
        $draftintroductionid = file_get_submitted_draft_itemid('section_introduction');

        file_prepare_draft_area($draftvignetteid, $context->id, 'format_udehauthoring', 'sectionvignette', $this->id);
        file_prepare_draft_area($draftintroductionid, $context->id, 'format_udehauthoring', 'sectionintroduction', $this->id);

        return (object)[
            'id' => $this->id,
            'audeh_course_id' => $this->audehcourseid,
            'section_title' => $this->title,
            'section_vignette' => $draftvignetteid,
            'section_description' => $this->description,
            'section_introduction_text' => (object)[
                'text' => file_rewrite_pluginfile_urls(
                    $this->introductiontext,
                    'pluginfile.php',
                    $context->id,
                    'format_udehauthoring',
                    'course_section_introductiontext_' . $this->id,
                    0
                ),
                'format' => $this->introductiontextformat,
            ],
            'isembed' => $this->isembed,
            'section_introduction_embed' => $this->embed,
            'section_introduction' => $draftintroductionid,
            'section_question' => $this->question,
            'section_comments' => $this->comments,
            'isvisible' => $this->isvisiblepreview,
            'subquestion_id' => array_map(function($subquestion) { return $subquestion->id; }, $this->subquestions),
            'subquestion_title' => array_map(function($subquestion) use ($context) {
                return (object)[
                    'text' => file_rewrite_pluginfile_urls(
                        $subquestion->title,
                        'pluginfile.php',
                        $context->id,
                        'format_udehauthoring',
                        'course_subquestion_title_' . $subquestion->id,
                        0
                    ),
                    'format' => FORMAT_HTML
                ];
            }, $this->subquestions)
        ];
    }

    /**
     * Save object to database
     *
     * @param \context_course $context
     * @param bool $fromregularsave
     * @throws \dml_exception
     */
    public function updateVisibility($id, $visibility = true) {
        global $DB;

        $record = new \stdClass();
        $record->id = $id;
        $record->isvisible = $visibility;

        $DB->update_record('udehauthoring_section', $record);
    }

    /**
     * Save object to database
     *
     * @param \context_course $context
     * @param bool $fromregularsave
     * @throws \dml_exception
     */
    public function save(\context_course $context, $fromregularsave = true) {
        global $DB;

        $record = new \stdClass();

        foreach ($this as $key => $value) {
            if (gettype($value) != 'array' && ($key != 'vignette' && $key != 'introduction')) {
                    if($fromregularsave) {
                        if (!str_starts_with($key, 'title')
                            && !str_starts_with($key, 'question')
                            && !str_starts_with($key, 'description')
                            && $key != 'timemodified') {
                            $record->$key = $value;
                        }
                    } else {
                        if (($key == 'id' || $key == 'audehcourseid') && $key != 'timemodified') {
                            $record->$key = $value;
                        }
                    }

            }
        }

        if (!isset($record->id) || $record->id === '') {
            $record->timemodified = time();
            if (!isset($record->title)) { $record->title = ''; }
            $this->id = $DB->insert_record('udehauthoring_section', $record);
            $record->id = $this->id;
            if ($this->subquestions) {
                foreach ($this->subquestions as $subquestion) {
                    $subquestion->audehsectionid = $this->id;
                }
            }
        }

        if (!is_null($this->isembed)) {
            $record->isembed = $this->isembed;
        } else {
            $record->isembed = false;
        }

        if (!is_null($this->isvisible)) {
            $record->isvisible = $this->isvisible;
        } else {
            $record->isvisible = 1;
        }

        if (!is_null($this->isvisiblepreview)) {
            $record->isvisiblepreview = $this->isvisiblepreview;
        } else {
            $record->isvisiblepreview = 1;
        }

        if($fromregularsave) {
            utils::file_save_draft_area_files($this->vignette, $context->id, 'format_udehauthoring', 'sectionvignette',
                $this->id);
            utils::file_save_draft_area_files($this->introduction, $context->id, 'format_udehauthoring', 'sectionintroduction',
                $this->id);
        }

        $editors = ['title', 'question', 'description', 'introductiontext'];
        foreach ($editors as $editor) {
            if (!empty($this->{$editor.'_editor'})) {
                $record = utils::prepareEditorContent($this, $record, $context, $editor, 'course_section_');
            }
        }

        if (isset($record->id)) {
            utils::db_update_if_changes('udehauthoring_section', $record);
        }

        if($fromregularsave) {
            $input_subquestions_id = [];
            $subquestion_record_ids = $DB->get_records('udehauthoring_sub_question', ['audehsectionid' => $this->id], '', 'id');

            if($this->subquestions) {
                foreach ($this->subquestions as $subquestion) {
                    if(empty($subquestion->id) && empty($subquestion->title) && empty($subquestion->title_editor['text'])) {
                        continue;
                    }
                    $input_subquestions_id[$subquestion->id] = $subquestion->id;
                    if ($subquestion->id && empty($subquestion->title) && empty($subquestion->title_editor['text'])) {
                        $subquestion->delete($context);
                    } else {
                        $subquestion->save($context, false);
                    }
                }
            }

            foreach($subquestion_record_ids as $subquestion_record_id) {
                if (!in_array($subquestion_record_id->id, $input_subquestions_id)) {
                    $subquestionplan = \format_udehauthoring\model\subquestion_plan::instance_by_id($subquestion_record_id->id);
                    $subquestionplan->delete($context);
                }
            }
        }

    }

    public function delete() {
        global $DB;

        utils::db_bump_timechanged('udehauthoring_course', $this->audehcourseid);

        $DB->execute(' UPDATE {udehauthoring_evaluation}
            SET audehsectionid = 0
            WHERE audehsectionid = ?
        ', [$this->id]);

        // bump all following siblings
        $following_siblings = $DB->get_records_sql(
            " SELECT id 
                  FROM {udehauthoring_section}
                  WHERE audehcourseid = ?
                  AND id > ?",
            [ $this->audehcourseid, $this->id ]
        );

        foreach ($following_siblings as $following_sibling) {
            utils::db_bump_timechanged('udehauthoring_section', $following_sibling->id);
        }

        $courseId = $DB->get_record('udehauthoring_course', ['id' => $this->audehcourseid])->courseid;
        $context = \context_course::instance($courseId);
        utils::deleteAssociatedAutoSavesAndFiles($context, 'course_section_title_' . $this->id);
        utils::deleteAssociatedAutoSavesAndFiles($context, 'course_section_question_' . $this->id);
        utils::deleteAssociatedAutoSavesAndFiles($context, 'course_section_description_' . $this->id);
        utils::deleteAssociatedAutoSavesAndFiles($context, 'course_section_introductiontext_' . $this->id);

        return $DB->delete_records('udehauthoring_section', ['id' => $this->id]);
    }
}