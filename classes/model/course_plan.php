<?php

namespace format_udehauthoring\model;
use context;
use context_course;
use format_udehauthoring\utils;

require_once($CFG->dirroot. '/course/format/udehauthoring/lib.php');

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
    public $questionformat = null;
    public $description = null;
    public $descriptionformat = null;
    public $isembed = null;
    public $embed = null;
    public $introduction = null;
    public $vignette = null;
    public $additionalinformation = null;
    public $sections = null;
    public $teachingobjectives = null;
    public $problematic = null;
    public $problematicformat = null;
    public $place = null;
    public $placeformat = null;
    public $method = null;
    public $methodformat = null;
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
            case 'displayable-form-additional-information-container':
                $courseplan = course_plan::instance_additional_info_by_form_data($data, $postdata);
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
        $courseplan->question_editor = $data->course_question;
        $courseplan->description_editor = $data->course_description;
        $courseplan->isembed = $data->isembed;
        $courseplan->embed = $data->course_introduction_embed;
        $courseplan->introduction = $data->course_introduction;
        $courseplan->vignette = $data->course_vignette;
        $courseplan->problematic_editor = $data->course_problematic;
        $courseplan->place_editor = $data->course_place_in_program;
        $courseplan->method_editor = $data->course_method;

        return $courseplan;
    }

    /**
     * Instantiate fields for Additional Informations.
     * @param $data object
     * @param $postdata array
     * @return course_plan
     */
    private static function instance_additional_info_by_form_data($data, $postdata) {
        $courseplan = new self();
        if ($data->id) {
            $courseplan->id = $data->id;
        }
        $courseplan->additionalinformation = [];
        $addinfoid = null;


        foreach($postdata as $key => $_) {
            if(preg_match('/add_info/', $key)) {
                if(preg_match('/id_value/', $key)) {
                    $addinfoid = $_;
                } else if(preg_match('/add_info_title/', $key)) {
                    $addinfoplan = new additionalinformation_plan();
                    if($courseplan->id) {
                        $addinfoplan->audehcourseid = $courseplan->id;
                    }
                    $addinfoplan->id = $addinfoid;
                    if(gettype($_) == 'array') {
                        $addinfoplan->title = $_['text'];
                    } else {
                        $addinfoplan->title = $_;
                    }
                    $courseplan->additionalinformation[] = $addinfoplan;
                    $addinfoid = null;
                } else if(preg_match('/add_info_content/', $key)) {
                    $currentAddInfo = $courseplan->additionalinformation[count($courseplan->additionalinformation) - 1];
                    $currentAddInfo->content_editor = $_;
                }
            }
        }

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
                    $teachingobjectiveplan->teachingobjective_editor = $_;
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
                    $learningobjectiveplan->learningobjective_editor = $_;
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
                } else if(preg_match('/section_title/', $key)) {
                    $sectionplan = new section_plan();
                    if ($courseplan->id) {
                        $sectionplan->audehcourseid = $courseplan->id;
                    }
                    $sectionplan->title_editor = $_;
                    $sectionplan->id = $sectionid;
                    $sectionplan->isvisiblepreview =
                        array_key_exists('section_isvisible_' . count($courseplan->sections), $postdata) ? 1 : 0;
                    $courseplan->sections[] = $sectionplan;
                    $sectionid = null;
                } else if(preg_match('/section_description/', $key)){
                    $currentSection = $courseplan->sections[count($courseplan->sections) - 1];
                    $currentSection->description_editor = $_;
                } else if(preg_match('/section_question/', $key)){
                    $currentSection = $courseplan->sections[count($courseplan->sections) - 1];
                    $currentSection->question_editor = $_;
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
                    $evaluationplan->title_editor = $_;
                    $evaluationplan->id = $evaluationid;
                    $evaluationplan->learningobjectiveids = [];
                    $courseplan->evaluations[] = $evaluationplan;
                    $evaluationid = null;
                } else if(preg_match('/evaluation_description/', $key)){
                    $currentEvaluation = $courseplan->evaluations[count($courseplan->evaluations) - 1];
                    $currentEvaluation->description_editor = $_;
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
                    $currentEvaluation->audehsectionids = $_;
                }
            }
        }
        return $courseplan;
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
                $courseplan->$key = section_plan::instance_all_by_course_plan_id($courseplan->id, $context);
            } else if('teachingobjectives' === $key) {
                $courseplan->$key = teachingobjective_plan::instance_all_by_course_plan_id($courseplan->id, $context);
            } else if('additionalinformation' === $key) {
                $courseplan->$key = additionalinformation_plan::instance_all_by_course_plan_id($courseplan->id, $context);
            } else if('evaluations' === $key) {
                $courseplan->$key = evaluation_plan::instance_all_by_course_plan_id($courseplan->id, $context);
            } else if('units' === $key) {
                $courseplan->$key = unit_plan::instance_all_by_course_plan_id($courseplan->id);
            } else if($key != 'introduction' && $key != 'vignette' && $key != 'additionalinformation') {
                $courseplan->$key = $record->$key;
            }
        }

        $editors = ['question', 'description', 'problematic', 'place', 'method'];
        $options = format_udehauthoring_get_editor_options($context);

        foreach ($editors as $editor) {
            $courseplan = file_prepare_standard_editor(
                $courseplan,
                $editor,
                $options,
                $context,
                'format_udehauthoring',
                'course_'.$editor,
                0
            );
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
            else if('additionalinformation' === $key) {
                $courseplan->$key = additionalinformation_plan::instance_all_by_course_plan_id($courseplan->id);
            }
            else if('evaluations' === $key) {
                $courseplan->$key = evaluation_plan::instance_all_by_course_plan_id($courseplan->id);
            }
            else if('units' === $key) {
                $courseplan->$key = unit_plan::instance_all_by_course_plan_id($courseplan->id);
            }
            else if($key != 'introduction' && $key !='vignette' && !str_ends_with($key, 'editor')) {
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
        $draftitemidvignette = file_get_submitted_draft_itemid('course_vignette');

        file_prepare_draft_area($draftitemid, $context->id, 'format_udehauthoring', 'courseintroduction', 0, ['subdirs' => 1]);
        file_prepare_draft_area($draftitemidvignette, $context->id, 'format_udehauthoring', 'coursevignette', 0, ['subdirs' => 1]);

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
            $obj= (object)[
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
                'course_description' => self::prepareEditor($context, 'description', $this, 'course_'),
                'course_question' => self::prepareEditor($context, 'question', $this, 'course_'),
                'course_introduction' => $draftitemid,
                'isembed' => $this->isembed,
                'course_introduction_embed' => $this->embed,
                'course_vignette' => $draftitemidvignette,
                'course_problematic' => self::prepareEditor($context, 'problematic', $this, 'course_'),
                'course_place_in_program' => self::prepareEditor($context, 'place', $this, 'course_'),
                'course_method' => self::prepareEditor($context, 'method', $this, 'course_'),
                'add_info_title_0' =>
                    count($this->additionalinformation) > 0 ? $this->additionalinformation[0]->title : '',
                'add_info_content_0' =>
                    self::prepareEditorForArrayElm($context, 'content', $this->additionalinformation, 'course_additional_info_'),
                'course_teaching_objectives_0' =>
                    self::prepareEditorForArrayElm($context, 'teachingobjective', $this->teachingobjectives, 'course_'),
                'course_learning_objectives_def_0_0' => count($this->teachingobjectives) > 0
                    && count($this->teachingobjectives[0]->learningobjectives) > 0
                    ? (object)[
                        'text' => file_rewrite_pluginfile_urls(
                            $this->teachingobjectives[0]->learningobjectives[0]->learningobjective,
                            'pluginfile.php',
                            $context->id,
                            'format_udehauthoring',
                            'course_learningobjective_' . $this->teachingobjectives[0]->learningobjectives[0]->id,
                            0
                        ),
                        'format' => FORMAT_HTML
                    ] :
                    (object)[
                        'text' => '',
                        'format' => FORMAT_HTML
                    ],
                'section_title_0' =>
                    self::prepareEditorForArrayElm($context, 'title', $this->sections, 'course_section_'),
                'section_question_0' =>
                    self::prepareEditorForArrayElm($context, 'question', $this->sections, 'course_section_'),
                'section_description_0' =>
                    self::prepareEditorForArrayElm($context, 'description', $this->sections, 'course_section_'),
                'evaluation_title_0' =>
                    self::prepareEditorForArrayElm($context, 'title', $this->evaluations, 'course_evaluation_'),
                'evaluation_description_0' =>
                    self::prepareEditorForArrayElm($context, 'description', $this->evaluations, 'course_evaluation_'),
            ];

            return $obj;
        }
    }


    private static function prepareEditorForArrayElm($context, $field, $array, $prefix) {
        return !empty($array)
            ? self::prepareEditor($context, $field, $array[0], $prefix, true)
            : (object)[
                    'text' => '',
                    'format' => FORMAT_HTML
                ];
    }

    private static function prepareEditor($context, $field, $plan, $prefix, $withId = false) {
        return (object)[
            'text' => file_rewrite_pluginfile_urls(
                $plan->{$field},
                'pluginfile.php',
                $context->id,
                'format_udehauthoring',
                $withId ? $prefix . $field . '_' . $plan->id : $prefix . $field,

                0
            ),
            'format' => FORMAT_HTML
        ];
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
            case 'displayable-form-additional-information-container':
                $this->save_additional_infos($context);
                return $isgood;
        }
        return '';
    }

    private function save_general_infos($context) {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/user/lib.php');

        $record = new \stdClass();
        $record->courseid = $this->courseid;
        if($this->id) $record->id = $this->id;
        $record->code = $this->code;
        $record->credit = $this->credit;
        $record->bloc = $this->bloc;
        $record->teachername = $this->teachername;
        $record->teacherphone = $this->teacherphone;
        $record->teachercellphone = $this->teachercellphone;
        $record->teacheremail = $this->teacheremail;
        $record->teacherzoomlink = $this->teacherzoomlink;
        $record->coursezoomlink = $this->coursezoomlink;
        $record->teachercontacthours = $this->teachercontacthours;
        $record->title = $this->title;
        $record->isembed = $this->isembed;
        $record->embed = $this->embed;
        $record->annex = '';

        utils::file_save_draft_area_files($this->introduction, $context->id, 'format_udehauthoring', 'courseintroduction', 0, ['subdirs' => 1]);
        utils::file_save_draft_area_files($this->vignette, $context->id, 'format_udehauthoring', 'coursevignette', 0, ['subdirs' => 1]);

        $editors = ['question', 'description', 'problematic', 'place', 'method'];
        foreach ($editors as $editor) {
            if (!empty($this->{$editor.'_editor'})) {
                $record = utils::prepareEditorContent($this, $record, $context, $editor, 'course_', false);
            }
        }

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

    private function save_additional_infos($context) {

        global  $DB;
        $input_infos_id = [];
        $info_record_ids = $DB->get_records('udehauthoring_add_info', ['audehcourseid' => $this->id], '', 'id');

        foreach ($this->additionalinformation as $additionalinformation) {
            $input_infos_id[$additionalinformation->id] = $additionalinformation->id;
            if ($additionalinformation->id && empty($additionalinformation->title)) {
                $additionalinformation->delete();
            } else {
                $additionalinformation->save($context, false);
            }
        }

        foreach ($info_record_ids as $info_record_id) {
            if (!in_array($info_record_id->id, $input_infos_id)) {
                $additionalinformationplan =
                    \format_udehauthoring\model\additionalinformation_plan::instance_by_id($info_record_id->id);
                $additionalinformationplan->delete();
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
            if ($teaching_objective->id && (empty($teaching_objective->teachingobjective) && $teaching_objective->teachingobjective_editor['text'] === '')) {
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
            $section->save($context, false);
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
        $dontsavemodules = false;

        for($i = 0; $i < count($this->evaluations); $i++) {
            $currentevaluationmodule = $this->evaluations[$i]->audehsectionids;

            $skip = true;

            foreach($currentevaluationmodule as $key => $value) {
                if(intval($value) !== 0) {
                    $skip = false;
                    break;
                }
            }

            for($j = 0; $j < count($this->evaluations); $j++) {
                if($i !== $j) {
                    $compareevaluationmodule = $this->evaluations[$j]->audehsectionids;

                    foreach($compareevaluationmodule as $key => $value) {
                        if($value === $currentevaluationmodule[$key] && intval($value) !== 0) {
                            $dontsavemodules = true;
                        }
                    }
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
            $evaluation->save($context, false, $dontsavemodules);
        }

        foreach($evaluation_record_ids as $evaluation_record_id) {
            if (!in_array($evaluation_record_id->id, $input_evaluations_id)) {
                $evaluationplan = \format_udehauthoring\model\evaluation_plan::instance_by_id($evaluation_record_id->id);
                $evaluationplan->delete();
            }
        }

        if(!$dontsavemodules) {
            return '';
        } else {
            return get_string('evaluationsaveerror', 'format_udehauthoring');
        }
    }
}