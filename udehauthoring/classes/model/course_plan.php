<?php

namespace format_udehauthoring\model;
use context;
use context_course;
use format_udehauthoring\utils;

/**
 * General information about the course written in the authoring tool.
 * Responsible for CRUD database operations and data structure conversions.
 */
class course_plan
{
    public $id = null;
    public $courseid = null;
    public $units = [];
    public $code = null;
    public $credit = null;
    public $bloc = null;
    public $teachername = null;
    public $teacheremail = null;
    public $teacherphone = null;
    public $teachercellphone = null;
    public $teachercontacthours = null;
    public $teacherzoomlink = null;
    public $coursezoomlink = null;
    public $title = null;
    public $question = null;
    public $description = null;
    public $isembed = null;
    public $embed = null;
    public $introduction = null;
    public $sections = null;
    public $teachingobjectives = null;
    public $problematic = null;
    public $place = null;
    public $method = null;
    public $attendance = null;
    public $plagiarism = null;
    public $annex = null;
    public $evaluations = null;
    public $timemodified = null;

    /**
     * Instantiate an object from form data as return by a \moodleform.
     *
     * @param $data object
     * @param $postdata array
     * @return course_plan
     */
    public static function instance_by_form_data($data, $postdata) {
        $courseplan = null;
        switch ($postdata['anchor']) {
            case 'displayable-form-informations-container':
                $courseplan = course_plan::instance_general_info_by_form_data($data);
                break;
            case 'displayable-form-objectives-container':
                $courseplan = course_plan::instance_objectives_by_form_data($data, $postdata);
                break;
            case 'displayable-form-sections-container':
                $courseplan = course_plan::instance_sections_by_form_data($data, $postdata);
                break;
            case 'displayable-form-evaluations-container':
                $courseplan = course_plan::instance_evaluation_by_form_data($data, $postdata);
                break;
        }

        return $courseplan;
    }

    /**
     * Instantiate fields for General Informations.
     * @param $data object
     * @return course_plan
     */
    private static function instance_general_info_by_form_data($data) {
        $courseplan = new self();
        if ($data->id) {
            $courseplan->id = $data->id;
        }
        $courseplan->courseid = $data->course_id;
        $courseplan->units = $data->units;
        $courseplan->code = $data->code;
        $courseplan->credit = $data->credit;
        $courseplan->bloc = $data->bloc;
        $courseplan->teachername = $data->teacher_name;
        $courseplan->teacheremail = $data->teacher_email;
        $courseplan->teacherphone = $data->teacher_phone;
        $courseplan->teachercellphone = $data->teacher_cellphone;
        $courseplan->teachercontacthours = $data->teacher_contact_hours;
        $courseplan->teacherzoomlink = $data->teacher_zoom_link;
        $courseplan->coursezoomlink = $data->course_zoom_link;
        $courseplan->title = $data->course_title;
        $courseplan->question = $data->course_question['text'];
        $courseplan->description = $data->course_description['text'];
        $courseplan->isembed = $data->isembed;
        $courseplan->embed = $data->course_introduction_embed;
        $courseplan->introduction = $data->course_introduction;
        $courseplan->problematic = $data->course_problematic['text'];
        $courseplan->place = $data->course_place_in_program['text'];
        $courseplan->method = $data->course_method['text'];
        $courseplan->annex = $data->course_annex['text'];

        return $courseplan;
    }

    /**
     * Instantiate fields for Objectives.
     * @param $data object
     * @param $postdata array
     * @return course_plan
     */
    private static function instance_objectives_by_form_data($data, $postdata) {
        $courseplan = new self();
        if ($data->id) {
            $courseplan->id = $data->id;
        }
        $courseplan->teachingobjectives = [];
        $courseteachingid = null;
        $courselearningid = null;
        foreach($postdata as $key => $_) {
            if(preg_match('/course_teaching_objectives/', $key)) {
                if(preg_match('/id_value/', $key)) {
                    $courseteachingid = $_;
                } else {
                    $teachingobjectiveplan = new teachingobjective_plan();
                    if ($courseplan->id) {
                        $teachingobjectiveplan->audehcourseid = $courseplan->id;
                    }
                    $teachingobjectiveplan->id = $courseteachingid;
                    if(gettype($_) == 'array') {
                        $teachingobjectiveplan->teachingobjective = $_['text'];
                    } else {
                        $teachingobjectiveplan->teachingobjective = $_;
                    }
                    $teachingobjectiveplan->learningobjectives = [];
                    $courseplan->teachingobjectives[] = $teachingobjectiveplan;
                    $courseteachingid = null;
                }

            } else if(preg_match('/course_learning_objectives/', $key)) {
                if(preg_match('/id_value/', $key)) {
                    $courselearningid = $_;
                }
                else if(preg_match('/course_learning_objectives_def/', $key)) {
                    $learningobjectiveplan = new learningobjective_plan();
                    if(gettype($_) == 'array') {
                        $learningobjectiveplan->learningobjective = $_['text'];
                    } else {
                        $learningobjectiveplan->learningobjective = $_;
                    }

                    $learningobjectiveplan->audehteachingobjectiveid = $courseplan->teachingobjectives[count($courseplan->teachingobjectives) - 1]->id;
                    $learningobjectiveplan->id = $courselearningid;
                    $courseplan->teachingobjectives[count($courseplan->teachingobjectives) - 1]->learningobjectives[] = $learningobjectiveplan;
                    $courselearningid = null;
                } else if(preg_match('/course_learning_objectives_competency_type/', $key)){
                    $currentArray = $courseplan->teachingobjectives[count($courseplan->teachingobjectives) - 1]->learningobjectives;
                    $currentArray[count($currentArray) - 1]->learningobjectivecompetency = intval($_);
                }

            }
        }

        return $courseplan;
    }

    /**
     * Instantiate fields for Modules.
     * @param $data object
     * @return course_plan
     */
    private static function instance_sections_by_form_data($data, $postdata) {
        $courseplan = new self();
        if ($data->id) {
            $courseplan->id = $data->id;
        }
        $sectionid = null;
        $courseplan->sections = [];
        foreach($postdata as $key => $_) {
            if(preg_match('/section/', $key)) {
                if(preg_match('/id_value/', $key)) {
                    $sectionid = $_;
                }
                else if(preg_match('/section_title/', $key)) {
                    $sectionplan = new section_plan();
                    if ($courseplan->id) {
                        $sectionplan->audehcourseid = $courseplan->id;
                    }
                    if(gettype($_) == 'array') {
                        $sectionplan->title = $_['text'];
                    } else {
                        $sectionplan->title = $_;
                    }
                    $sectionplan->id = $sectionid;
                    $courseplan->sections[] = $sectionplan;
                    $sectionid = null;
                } else if(preg_match('/section_description/', $key)){
                    $currentSection = $courseplan->sections[count($courseplan->sections) - 1];
                    if(gettype($_) == 'array') {
                        $currentSection->description = $_['text'];
                    } else {
                        $currentSection->description = $_;
                    }
                } else if(preg_match('/section_question/', $key)){
                    $currentSection = $courseplan->sections[count($courseplan->sections) - 1];
                    if(gettype($_) == 'array') {
                        $currentSection->question = $_['text'];
                    } else {
                        $currentSection->question = $_;
                    }
                }

            }
        }

        return $courseplan;
    }

    /**
     * Instantiate fields for Modules.
     * @param $data object
     * @return course_plan
     */
    private static function instance_evaluation_by_form_data($data, $postdata) {
        $courseplan = new self();
        if ($data->id) {
            $courseplan->id = $data->id;
        }
        $evaluationid = null;
        $courseplan->evaluations = [];
        $courseplan->teachingobjectives = [];
        foreach($postdata as $key => $_) {
            if(preg_match('/evaluation/', $key)) {
                if(preg_match('/id_value/', $key)) {
                    $evaluationid = $_;
                }
                else if(preg_match('/evaluation_title/', $key)) {
                    $evaluationplan = new evaluation_plan();
                    if ($courseplan->id) {
                        $evaluationplan->audehcourseid = $courseplan->id;
                    }
                    if(gettype($_) == 'array') {
                        $evaluationplan->title = $_['text'];
                    } else {
                        $evaluationplan->title = $_;
                    }
                    $evaluationplan->id = $evaluationid;
                    $evaluationplan->learningobjectiveids = [];
                    $courseplan->evaluations[] = $evaluationplan;
                    $evaluationid = null;
                } else if(preg_match('/evaluation_description/', $key)){
                    $currentEvaluation = $courseplan->evaluations[count($courseplan->evaluations) - 1];
                    if(gettype($_) == 'array') {
                        $currentEvaluation->description = $_['text'];
                    } else {
                        $currentEvaluation->description = $_;
                    }
                } else if(preg_match('/evaluation_weight/', $key)){
                    $currentEvaluation = $courseplan->evaluations[count($courseplan->evaluations) - 1];
                    $currentEvaluation->weight = $_;
                } else if(preg_match('/evaluation_learning_objectives/', $key)){
                    $currentEvaluation = $courseplan->evaluations[count($courseplan->evaluations) - 1];
                    foreach ($_ as $innerkey => $item) {
                        if(gettype($item) == 'array') {
                            foreach ($item as $currentkey => $currentitem) {
                                $currentEvaluation->learningobjectiveids[$currentkey] = $currentitem;
                            }
                        } else {
                            $currentEvaluation->learningobjectiveids[$innerkey] = $item;
                        }

                    }
                } else if(preg_match('/evaluation_module/', $key)){
                    $currentEvaluation = $courseplan->evaluations[count($courseplan->evaluations) - 1];
                    $currentEvaluation->audehsectionid = intval($_);
                }
            }
        }
        return $courseplan;
    }


    public static function instance_by_courseid_exists($courseid) {
        global $DB;
        $record = $DB->get_record('udehauthoring_course', ['courseid' => $courseid]);
        if (!$record) {
            return false;
        }
        return true;
    }

    /**
     * Instantiate an object by querying the database with the course ID.
     * Returns false if no record exists yet.
     *
     * @param $courseid int
     * @param $context context
     * @param $iscourseform boolean
     * @return false|course_plan
     * @throws \dml_exception
     */
    public static function instance_by_courseid($courseid, $context) {
        global $DB;

        $record = $DB->get_record('udehauthoring_course', ['courseid' => $courseid]);
        if (!$record) {
            return false;
        }

        $courseplan = new self();
        foreach($courseplan as $key => $_) {
            if ('sections' === $key) {
                $courseplan->$key = section_plan::instance_all_by_course_plan_id($courseplan->id);
            } else if('teachingobjectives' === $key) {
                $courseplan->$key = teachingobjective_plan::instance_all_by_course_plan_id($courseplan->id);
            } else if('evaluations' === $key) {
                $courseplan->$key = evaluation_plan::instance_all_by_course_plan_id($courseplan->id);
            } else if('units' === $key) {
                $courseplan->$key = unit_plan::instance_all_by_course_plan_id($courseplan->id);
            } else if($key != 'introduction') {
                $courseplan->$key = $record->$key;
            }
        }
        return $courseplan;
    }

    /**
     * Instantiate an object with base information.
     *
     * @param $courseid int
     * @param $context context
     * @return course_plan
     * @throws \dml_exception
     */
    public static function base_instance($courseid, $context) {
        global $DB;
        $courseplan = new self();
        $courseplan->courseid = $courseid;
        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $teacherArray = get_role_users($role->id, $context);
        $teacher = reset($teacherArray);
        if($teacher) {
            $teacherRecord = $DB->get_record('user', array('id' => $teacher->id));
            foreach($courseplan as $key => $_) {
                if(substr($key, 0, strlen('teacher') ) === 'teacher' && ($key != 'teachercontacthours' || $key != 'teacherzoomlink')) {
                    $courseplan->handle_teacher_instanciation($key, $teacherRecord);
                }
            }
        }

        return $courseplan;
    }

    private function handle_teacher_instanciation($key, $teacher) {
        switch($key) {
            case 'teachername':
                $this->$key = $teacher->firstname . ' ' . $teacher->lastname;
                break;
            case 'teacheremail':
                $this->$key = $teacher->email;
                break;
            case 'teacherphone':
                $this->$key = $teacher->phone1;
                break;
            case 'teachercellphone':
                $this->$key = $teacher->phone2;
                break;
        }
    }

    /**
     * Instantiate an object by querying the database with the course plan ID. An error is raised if no such course
     * plan exists.
     * @param $id
     * @return course_plan
     * @throws \dml_exception
     */
    public static function instance_by_id($id) {
        global $DB;

        $record = $DB->get_record('udehauthoring_course', ['id' => $id], '*', MUST_EXIST);

        $courseplan = new self();
        foreach($courseplan as $key => $_) {
            if ('sections' === $key) {
                $courseplan->$key = section_plan::instance_all_by_course_plan_id($courseplan->id);
            }
            else if('teachingobjectives' === $key) {
                $courseplan->$key = teachingobjective_plan::instance_all_by_course_plan_id($courseplan->id);
            }
            else if('evaluations' === $key) {
                $courseplan->$key = evaluation_plan::instance_all_by_course_plan_id($courseplan->id);
            }
            else if('units' === $key) {
                $courseplan->$key = unit_plan::instance_all_by_course_plan_id($courseplan->id);
            }
            else if($key != 'introduction') {
                $courseplan->$key = $record->$key;
            }
        }
        return $courseplan;
    }

    /**
     * Produces data in the correct format for filling \format_udehauthoring\form\redact_course
     *
     * @return object
     */
    public function to_form_data($context, $isbase) {

        $draftitemid = file_get_submitted_draft_itemid('course_introduction');

        file_prepare_draft_area($draftitemid, $context->id, 'format_udehauthoring', 'courseintroduction', 0, ['subdirs' => 1]);

        if($isbase) {
            return (object)[
                'course_id' => $this->courseid,
                'teacher_name' => $this->teachername,
                'teacher_email' => $this->teacheremail,
                'teacher_phone' => $this->teacherphone,
                'teacher_cellphone' => $this->teachercellphone,
                'course_introduction' => $draftitemid,
            ];
        } else {
            return (object)[
                'id' => $this->id,
                'course_id' => $this->courseid,
                'units' => array_map(function($unit) { return $unit->audehunitid; }, $this->units),
                'code' => $this->code,
                'credit' => $this->credit,
                'bloc' => $this->bloc,
                'teacher_name' => $this->teachername,
                'teacher_email' => $this->teacheremail,
                'teacher_phone' => $this->teacherphone,
                'teacher_cellphone' => $this->teachercellphone,
                'teacher_contact_hours' => $this->teachercontacthours,
                'teacher_zoom_link' => $this->teacherzoomlink,
                'course_zoom_link' => $this->coursezoomlink,
                'course_title' => $this->title,
                'course_question' => (object)[
                    'text' => $this->question,
                    'format' => FORMAT_HTML
                ],
                'course_description' => (object)[
                    'text' => $this->description,
                    'format' => FORMAT_HTML
                ],
                'course_introduction' => $draftitemid,
                'isembed' => $this->isembed,
                'course_introduction_embed' => $this->embed,
                'course_problematic' => (object)[
                    'text' => $this->problematic,
                    'format' => FORMAT_HTML
                ],
                'course_place_in_program' => (object)[
                    'text' => $this->place,
                    'format' => FORMAT_HTML
                ],
                'course_method' => (object)[
                    'text' => $this->method,
                    'format' => FORMAT_HTML
                ],
                'course_annex' => (object)[
                    'text' => $this->annex,
                    'format' => FORMAT_HTML
                ],
            ];
        }
    }

    /**
     * Save object to database
     *
     * @param \context_course $context
     * @param \string $anchor
     * @throws \dml_exception
     */
    public function save(\context_course $context, $anchor) {
        global $CFG;
        require_once($CFG->dirroot.'/user/lib.php');

        $isgood = '';

        switch ($anchor) {
            case 'displayable-form-informations-container':
                $this->save_general_infos($context);
                return $isgood;
            case 'displayable-form-objectives-container':
                $this->save_objectives($context);
                return $isgood;
            case 'displayable-form-sections-container':
                $this->save_sections($context);
                return $isgood;
            case 'displayable-form-evaluations-container':
                return $this->save_evaluations($context);
        }
        return '';
    }

    private function save_general_infos($context) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/lib.php');

        $record = new \stdClass();
        $record->courseid = $this->courseid;
        if($this->id) $record->id = $this->id;
        if($this->code) $record->code = $this->code;
        if($this->credit) $record->credit = $this->credit;
        $record->bloc = $this->bloc;
        if($this->teachername) $record->teachername = $this->teachername;
        if($this->teacherphone) $record->teacherphone = $this->teacherphone;
        if($this->teachercellphone) $record->teachercellphone = $this->teachercellphone;
        if($this->teacheremail) $record->teacheremail = $this->teacheremail;
        if($this->teacherzoomlink) $record->teacherzoomlink = $this->teacherzoomlink;
        if($this->coursezoomlink) $record->coursezoomlink = $this->coursezoomlink;
        if($this->teachercontacthours) $record->teachercontacthours = $this->teachercontacthours;
        if($this->title) $record->title = $this->title;
        $record->isembed = $this->isembed;
        if($this->embed) $record->embed = $this->embed;
        if($this->question) $record->question = $this->question;
        if($this->description) $record->description = $this->description;
        if($this->problematic) $record->problematic = $this->problematic;
        if($this->place) $record->place = $this->place;
        if($this->method) $record->method = $this->method;
        if($this->annex) $record->annex = $this->annex;

        utils::file_save_draft_area_files($this->introduction, $context->id, 'format_udehauthoring', 'courseintroduction', 0, ['subdirs' => 1]);

        if (isset($record->id)) {
            utils::db_update_if_changes('udehauthoring_course', $record);
        } else {
            $record->timemodified = time();
            $this->id = $DB->insert_record('udehauthoring_course', $record);
        }

        $holderrecords = $DB->get_records('udehauthoring_unit', ['audehcourseid' => $this->id], '', 'audehunitid');
        $previousunitsid = [];
        foreach ($holderrecords as $holder) {
            $previousunitsid[] = $holder->audehunitid;
        }
        $input_units_id = [];

        if($this->units !== []) {
            foreach ($this->units as $unit) {
                if(!in_array($unit, $previousunitsid)) {
                    $unitplan = new unit_plan();
                    $unitplan->audehcourseid = $this->id;
                    $unitplan->audehunitid = (int)$unit;
                    $unitplan->save();
                }
                $input_units_id[] = $unit;
            }
        }

        foreach ($previousunitsid as $previousunitid) {
            if (!in_array($previousunitid, $input_units_id)) {
                $unitplan = unit_plan::instance_by_config_id($previousunitid);
                $unitplan->delete();
            }
        }


    }

    private function save_objectives($context) {
        global  $DB;
        // save teaching objectives
        $input_teachings_id = [];
        $teaching_record_ids = $DB->get_records('udehauthoring_teaching_obj', ['audehcourseid' => $this->id], '', 'id');

        foreach ($this->teachingobjectives as $teaching_objective) {
            $input_teachings_id[$teaching_objective->id] = $teaching_objective->id;
            if ($teaching_objective->id && empty($teaching_objective->teachingobjective)) {
                $teaching_objective->delete();
            } else {
                $teaching_objective->save($context, false);
            }
        }

        foreach($teaching_record_ids as $teaching_record_id) {
            if (!in_array($teaching_record_id->id, $input_teachings_id)) {
                $teaching_objective_plan = \format_udehauthoring\model\teachingobjective_plan::instance_by_id($teaching_record_id->id);
                $teaching_objective_plan->delete();
            }
        }
    }

    private function save_sections($context) {
        global  $DB;
        // save sections
        $input_sections_id = [];
        $section_record_ids = $DB->get_records('udehauthoring_section', ['audehcourseid' => $this->id], '', 'id');

        foreach ($this->sections as $section) {
            $input_sections_id[$section->id] = $section->id;
            if ($section->id && empty($section->title)) {
                $section->delete();
            } else {
                $section->save($context, false);
            }

        }

        foreach($section_record_ids as $section_record_id) {
            if (!in_array($section_record_id->id, $input_sections_id)) {
                $sectionplan = \format_udehauthoring\model\section_plan::instance_by_id($section_record_id->id);
                $sectionplan->delete();
            }
        }

    }

    private function save_evaluations($context) {
        global  $DB;
        $input_evaluations_id = [];

        for($i = 0; $i < count($this->evaluations) - 1; $i++) {
            $currentevaluationmodule = $this->evaluations[$i]->audehsectionid;
            for($j = $i + 1; $j < count($this->evaluations); $j++) {
                if(($currentevaluationmodule !== null || $currentevaluationmodule != '')
                    && $currentevaluationmodule === $this->evaluations[$j]->audehsectionid) {
                    return get_string('evaluationsaveerror', 'format_udehauthoring');
                }
            }
        }

        for($i = 0; $i < count($this->evaluations); $i++) {
            $currentevaluationObjs = $this->evaluations[$i]->learningobjectiveids;
            $forcomparison = null;
            foreach ($currentevaluationObjs as $currentevaluationObj) {
                if($currentevaluationObj == "0") {
                    $forcomparison = 0;
                } else {
                    $forcomparison = 1;
                    break;
                }
            }
            if($forcomparison == 0) {
                return get_string('evaluationsaveerrorobj', 'format_udehauthoring');
            }
        }

        $evaluation_record_ids = $DB->get_records('udehauthoring_evaluation', ['audehcourseid' => $this->id], '', 'id');

        foreach ($this->evaluations as $evaluation) {
            $input_evaluations_id[$evaluation->id] = $evaluation->id;
            if ($evaluation->id && empty($evaluation->title)) {
                $evaluation->delete();
            } else {
                $evaluation->save($context, true);
            }
        }

        foreach($evaluation_record_ids as $evaluation_record_id) {
            if (!in_array($evaluation_record_id->id, $input_evaluations_id)) {
                $evaluationplan = \format_udehauthoring\model\evaluation_plan::instance_by_id($evaluation_record_id->id);
                $evaluationplan->delete();
            }
        }
        return '';
    }
}