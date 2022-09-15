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
    public $description = null;
    public $vignette = null;
    public $introductiontext = null;
    public $isembed = null;
    public $embed = null;
    public $introduction = null;
    public $question = null;
    public $subquestions = null;
    public $comments = null;
    public $timemodified = null;

    /**
     * Returns an array of all section_plans included in the course_plan with the specified ID.
     *
     * @param $audehcourseid
     * @return array
     * @throws \dml_exception
     */
    public static function instance_all_by_course_plan_id($audehcourseid) {
        global $DB;

        $records = $DB->get_records('udehauthoring_section', ['audehcourseid' => $audehcourseid]);

        $sectionplans = [];

        foreach ($records as $record) {
            $sectionplan = new self();
            $sectionplan->id = $record->id;
            $sectionplan->audehcourseid = $record->audehcourseid;
            $sectionplan->title = $record->title;
            $sectionplan->description = $record->description;
            $sectionplan->introductiontext = $record->introductiontext;
            $sectionplan->isembed = $record->isembed;
            $sectionplan->embed = $record->embed;
            $sectionplan->question = $record->question;
            $sectionplan->subquestions = subquestion_plan::instance_all_by_section_plan_id($sectionplan->id);
            $sectionplan->comments = $record->comments;
            $sectionplan->timemodified = $record->timemodified;
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
        $sectionplan->introductiontext = $data->section_introduction_text['text'];
        $sectionplan->isembed = $data->isembed;
        $sectionplan->embed = $data->section_introduction_embed;
        $sectionplan->introduction = $data->section_introduction;
        $sectionplan->question = $data->section_question;
        $sectionplan->comments = $data->section_comments;

        $sectionplan->subquestions = [];
        foreach ($data->subquestion_title as $ii => $title) {
            $subquestionid = $data->subquestion_id[$ii];
            if ($subquestionid || $title['text']) {
                $subquestion_plan = new subquestion_plan();
                if ($sectionplan->id) {
                    $subquestion_plan->audehsectionid = $sectionplan->id;
                }
                if ($subquestionid) {
                    $subquestion_plan->id = $subquestionid;
                }
                if ($title['text']) {
                    $subquestion_plan->title = $title['text'];
                }
                $sectionplan->subquestions[] = $subquestion_plan;
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
            } else if($key != 'vignette' && $key != 'introduction') {
                $sectionplan->$key = $record->$key;
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
    public static function get_section_title_by_id($id) {
        global $DB;

        return $DB->get_record('udehauthoring_section', ['id' => $id], 'title', MUST_EXIST);
    }

    /**
     * HTML rending for listing modules in the course main page. Used by the syllabus as well.
     *
     * @param $vignettefilehtml
     * @param $title
     * @param $description
     * @param $url
     * @return string
     * @throws \coding_exception
     */
    public static function render_module_preview($vignettefilehtml, $title, $description, $url) {
        $strexplore = get_string('explore', 'format_udehauthoring');

        return "<div class='udeha-course-section-vignette'>{$vignettefilehtml}</div>" .
            "<div class='udeha-course-section-name'>{$title}</div>" .
            "<div class='udeha-course-section-description'>{$description}</div>" .
            "<div class='udeha-course-section-description-explore'><a class='btn btn-primary' href='{$url}'>$strexplore</a></div>";
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
                'text' => $this->introductiontext,
                'format' => FORMAT_HTML
            ],
            'isembed' => $this->isembed,
            'section_introduction_embed' => $this->embed,
            'section_introduction' => $draftintroductionid,
            'section_question' => $this->question,
            'section_comments' => $this->comments,
            'subquestion_id' => array_map(function($subquestion) { return $subquestion->id; }, $this->subquestions),
            'subquestion_title' => array_map(function($subquestion) {
                return (object)[
                    'text' => $subquestion->title,
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
    public function save(\context_course $context, $fromregularsave = true) {
        global $DB;

        $record = new \stdClass();

        foreach ($this as $key => $value) {
            if (gettype($value) != 'array' && ($key != 'vignette' && $key != 'introduction')) {
                    if($fromregularsave) {
                        if ($key != 'title' && $key != 'question' && $key != 'description' && $key != 'timemodified') {
                            $record->$key = $value;
                        }
                    } else {
                        if (($key == 'title' || $key == 'question' || $key == 'description' || $key == 'id' || $key == 'audehcourseid') && $key != 'timemodified') {
                            $record->$key = $value;
                        }
                    }

            }
        }
        if (!is_null($this->isembed)) {
            $record->isembed = $this->isembed;
        } else {
            $record->isembed = false;
        }
        if($fromregularsave) {
            utils::file_save_draft_area_files($this->vignette, $context->id, 'format_udehauthoring', 'sectionvignette',
                $this->id);
            utils::file_save_draft_area_files($this->introduction, $context->id, 'format_udehauthoring', 'sectionintroduction',
                $this->id);
        }

        if (isset($record->id)) {
            utils::db_update_if_changes('udehauthoring_section', $record);
        } else {
            $record->timemodified = time();
            $this->id = $DB->insert_record('udehauthoring_section', $record);
            if($this->subquestions) {
                foreach ($this->subquestions as $subquestion) {
                    $subquestion->audehsectionid = $this->id;
                }
            }
        }

        if($fromregularsave) {
            $input_subquestions_id = [];
            $subquestion_record_ids = $DB->get_records('udehauthoring_sub_question', ['audehsectionid' => $this->id], '', 'id');

            if($this->subquestions) {
                foreach ($this->subquestions as $subquestion) {
                    $input_subquestions_id[$subquestion->id] = $subquestion->id;
                    if ($subquestion->id && empty($subquestion->title)) {
                        $subquestion->delete();
                    } else {
                        $subquestion->save($context, false);
                    }
                }
            }

            foreach($subquestion_record_ids as $subquestion_record_id) {
                if (!in_array($subquestion_record_id->id, $input_subquestions_id)) {
                    $subquestionplan = \format_udehauthoring\model\subquestion_plan::instance_by_id($subquestion_record_id->id);
                    $subquestionplan->delete();
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

        return $DB->delete_records('udehauthoring_section', ['id' => $this->id]);
    }
}