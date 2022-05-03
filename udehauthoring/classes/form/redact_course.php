<?php

namespace format_udehauthoring\form;

global $CFG;

use format_udehauthoring\model\course_plan;
use format_udehauthoring\utils;

require_once("$CFG->libdir/formslib.php");

class redact_course extends \moodleform
{

    /**
     * @inheritDoc
     */
    protected function definition()
    {

        global $PAGE;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'course_id');
        $mform->setType('course_id', PARAM_INT);

        $mform->addElement('html', '<h1 class="ml-3 course-title">' . $this->_customdata['coursetitle'] . '</h1>');

        $mform->addElement('html', '<div id="form_container">');

        $this->buildCourseInformations($mform);

        $this->buildTeachingObjective($mform);

        $this->buildSections($mform);

        $this->buildEvaluations($mform);

        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

    }

    function buildCourseInformations($mform) {
        $mform->addElement('html', '<div id="displayable-form-informations-container" style="display: none">');

        if(get_string_manager()->string_exists('instructionscoursegeneralinformations', 'format_udehauthoring') && get_string('instructionscoursegeneralinformations', 'format_udehauthoring')) {
            $mform->addElement('html', '<div class="mt-3">');
            $mform->addElement('html', '<span class="ml-3 page-instructions">' . get_string('instructions', 'format_udehauthoring') . '</span>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '<p class="ml-3 mt-1">' . get_string('instructionscoursegeneralinformations', 'format_udehauthoring') . '</p>');
        }

        $mform->addElement('html', '<h2 class="ml-3 mt-2 page-title">' . get_string('coursegeneralinformations', 'format_udehauthoring') . '</h2>');

        $mform->addElement('textarea', 'course_title', get_string('coursetitle', 'format_udehauthoring'), ['class'=>'inline-element']);
        $mform->setType('course_title', PARAM_RAW);

        $mform->addElement('editor', 'course_question', get_string('coursequestion', 'format_udehauthoring'), ['class'=>'title-editor', 'rows'=>'4']);
        $mform->setType('course_question', PARAM_RAW);

        $options = array(
            'multiple' => true,
            'noselectionstring' => get_string('nocourseunit', 'format_udehauthoring'),
        );
        $mform->addElement('autocomplete', 'units', get_string('courseunit', 'format_udehauthoring'), \format_udehauthoring\model\unit_config::instance_all_values(), $options);

        $mform->addElement('text', 'code', get_string('coursecode', 'format_udehauthoring'));
        $mform->setType('code', PARAM_RAW);

        $mform->addElement('text', 'credit', get_string('coursecredit', 'format_udehauthoring'), array('maxlength'=>'2'));
        $mform->setType('credit', PARAM_INT);

        $teachingblocs = range(1,13);
        $mform->addElement('select', 'bloc', get_string('coursebloc', 'format_udehauthoring'), $teachingblocs);
        $mform->setType('bloc', PARAM_INT);
        $mform->setDefault('bloc', 0);

        $mform->addElement('text', 'teacher_name', get_string('courseteachername', 'format_udehauthoring'));
        $mform->setType('teacher_name', PARAM_RAW);

        $mform->addElement('text', 'teacher_email', get_string('courseteacheremail', 'format_udehauthoring'));
        $mform->setType('teacher_email', PARAM_NOTAGS);
        $mform->addRule('teacher_email', get_string('courseteachernotemail', 'format_udehauthoring'), 'email', null, 'server');

        $phoneattributes = array('placeholder' => '(514) 555-5555 #111');
        $mform->addElement('text', 'teacher_phone', get_string('courseteacherphone', 'format_udehauthoring'), $phoneattributes);
        $mform->setType('teacher_phone', PARAM_NOTAGS);
        $mform->addRule('teacher_phone', get_string('courseteachernotphone', 'format_udehauthoring'), 'regex', '^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})?[-. ]?[#|ext]?([0-9]{3})$^', 'server');

        $mform->addElement('text', 'teacher_cellphone', get_string('courseteachercellphone', 'format_udehauthoring'), $phoneattributes);
        $mform->setType('teacher_cellphone', PARAM_NOTAGS);
        $mform->addRule('teacher_cellphone', get_string('courseteachernotphone', 'format_udehauthoring'), 'regex', '^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})?[-. ]?[#|ext]?([0-9]{3})$^', 'server');

        $mform->addElement('text', 'teacher_contact_hours', get_string('courseteachercontacthours', 'format_udehauthoring'));
        $mform->setType('teacher_contact_hours', PARAM_RAW);

        $mform->addElement('text', 'teacher_zoom_link', get_string('courseteacherzoomlink', 'format_udehauthoring'));
        $mform->setType('teacher_zoom_link', PARAM_RAW);

        $mform->addElement('text', 'course_zoom_link', get_string('coursezoomlink', 'format_udehauthoring'));
        $mform->setType('course_zoom_link', PARAM_RAW);

        $mform->addElement('editor', 'course_description', get_string('coursedescription', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
        $mform->setType('course_description', PARAM_RAW);

        $mform->addElement('html', '<div class="custom-control custom-switch ml-3 mb-2">
          <input type="checkbox" class="custom-control-input" id="embed_selector">
          <label class="custom-control-label" for="embed_selector">' . get_string("iscourseintroductionembed", "format_udehauthoring") . '</label>
        </div>');
/*
        $mform->addElement('advcheckbox', 'isembed', '', 'is embed', [], array(0, 1));
        $mform->setType('isembed', PARAM_INT);*/

        $mform->addElement('hidden', 'isembed');
        $mform->setType('isembed', PARAM_INT);
        $mform->setDefault('isembed', 0);

        $mform->addElement('text', 'course_introduction_embed', get_string('courseintroductionembed', 'format_udehauthoring'));
        $mform->setType('course_introduction_embed', PARAM_RAW);

        $mform->addElement('filemanager', 'course_introduction', get_string('courseintroduction', 'format_udehauthoring'), null,
            array('subdirs' => false));
        $mform->setType('course_introduction', PARAM_RAW);

        $mform->addElement('editor', 'course_problematic', get_string('courseproblematic', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
        $mform->setType('course_problematic', PARAM_RAW);

        $mform->addElement('editor', 'course_place_in_program', get_string('courseplace', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
        $mform->setType('course_place_in_program', PARAM_RAW);

        $mform->addElement('editor', 'course_method', get_string('coursemethod', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
        $mform->setType('course_method', PARAM_RAW);

        $mform->addElement('editor', 'course_annex', get_string('courseannex', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
        $mform->setType('course_annex', PARAM_RAW);

        $mform->addElement('html', '</div>');

        $this->handleHelpButtons(array(
            ['coursetitle', 'course_title'],
            ['coursequestion', 'course_question'],
            ['courseunit', 'unit'],
            ['coursecode', 'code'],
            ['coursecredit', 'credit'],
            ['coursebloc', 'bloc'],
            ['courseteachername', 'teacher_name'],
            ['courseteacheremail', 'teacher_email'],
            ['courseteacherphone', 'teacher_phone'],
            ['courseteachercellphone', 'teacher_cellphone'],
            ['courseteachercontacthours', 'teacher_contact_hours'],
            ['courseteacherzoomlink', 'teacher_zoom_link'],
            ['coursezoomlink', 'course_zoom_link'],
            ['coursedescription', 'course_description'],
            ['courseintroductionembed', 'course_introduction_embed'],
            ['courseintroduction', 'course_introduction'],
            ['courseproblematic', 'course_problematic'],
            ['courseplace', 'course_place_in_program'],
            ['coursemethod', 'course_method'],
            ['courseannex', 'course_annex']), $mform);
    }

    function buildTeachingObjective($mform) {
        $mform->addElement('html', '<div id="displayable-form-objectives-container" style="display: none">');

        if(get_string_manager()->string_exists('instructionscourseteachingobjectives', 'format_udehauthoring') && get_string('instructionscourseteachingobjectives', 'format_udehauthoring')) {
            $mform->addElement('html', '<div class="mt-3">');
            $mform->addElement('html', '<span class="ml-3 page-instructions">' . get_string('instructions', 'format_udehauthoring') . '</span>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '<p class="ml-3 mt-1">' . get_string('instructionscourseteachingobjectives', 'format_udehauthoring') . '</p>');
        }

        $mform->addElement('html', '<h2 class="ml-3 mb-2 page-title">' . get_string('teachingobjectives', 'format_udehauthoring') . '</h2>');

        $repeatarrayteachingobjectives = [];

        $repeatarrayteachingobjectives[] = $mform->createElement('html', '<div class="row row-container mb-3">');
        $repeatarrayteachingobjectives[] = $mform->createElement('html', '<div id="course_teaching_objectives_container_0" class="col-11 accordion-container card">');
        $repeatarrayteachingobjectives[] = $mform->createElement('html', '<div id="course_teaching_objectives_header_0" class="accordion-header card-header"/>
          <a data-toggle="collapse" href="#collapse_teaching_0" role="button" aria-expanded="true" aria-controls="collapse_teaching_0">'
             . get_string('teachingobjective', 'format_udehauthoring') . ' ' . 1 .
          '</a></div>');
        $repeatarrayteachingobjectives[] = $mform->createElement('html', '<div class="collapse show" id="collapse_teaching_0" data-parent="#displayable-form-objectives-container">');
        $repeatarrayteachingobjectives[] = $mform->createElement('html', '<div class="card-body accordion-content" id="course_teaching_objectives_0">');
        $repeatarrayteachingobjectives[] = $mform->createElement('editor', 'course_teaching_objectives_0', '', ['class'=>'title-editor', 'rows'=>'4']);
        $repeatarrayteachingobjectives[] = $this->buildLearningObjective($mform);
        $repeatarrayteachingobjectives[] = $mform->createElement('html', '</div>');
        $repeatarrayteachingobjectives[] = $mform->createElement('html', '</div>');
        $repeatarrayteachingobjectives[] = $mform->createElement('html', '</div>');
        $repeatarrayteachingobjectives[] = $mform->createElement('html', '<div class="col-1 remove_teaching_action_button">');
        $repeatarrayteachingobjectives[] = $mform->createElement('button', 'remove_teaching_objectives_0', '<i class="remove-button-js fa fa-minus-circle fa-2x"></i>');
        $repeatarrayteachingobjectives[] = $mform->createElement('html', '</div>');
        $repeatarrayteachingobjectives[] = $mform->createElement('html', '</div>');

        foreach($repeatarrayteachingobjectives as $obj) {
            if(gettype($obj) == 'array') {
                foreach ($obj as $elem) {
                    $mform->addElement($elem);
                }
            } else {
                $mform->addElement($obj);
            }
        }

        $this->handleHelpButtons(array(
            ['teachingobjective', 'course_teaching_objectives_0'],
            ['learningobjective', 'course_learning_objectives_def_0_0'],
            ['courselearningobjectivescompetencytype', 'course_learning_objectives_competency_type_0_0']
        ), $mform);


        $mform->addElement('html', '<div id="teaching-add-container" class="row accordion-add-container">');
        $mform->addElement('html', '<div class="col-11 add-container-text card-header card">');
        $mform->addElement('html', '<span class="add-text">'. get_string('teachingobjective', 'format_udehauthoring') . ' ' . 2 .'</span>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '<div class="col-1 add_action_button">');
        $mform->addElement('button', 'add_teaching_objective', '<i class="add-button fa fa-plus-circle fa-2x"></i>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $mform->setType('course_teaching_objectives_0', PARAM_RAW);
        $mform->setType('course_learning_objectives_def_0_0', PARAM_RAW);
        $mform->setType('course_learning_objectives_competency_type_0_0', PARAM_INT);
    }

    function buildLearningObjective($mform): array
    {
        $repeatarraylearningobjectives = [];
        $competencytypes = ['CT', 'CC', 'CP'];

        $repeatarraylearningobjectives[] = $mform->createElement('html', '<div id="course_learning_objectives_container_0" class="course_learning_objectives_container">');

        $repeatarraylearningobjectives[] = $mform->createElement('html', '<div class="row row-container-child mb-3">');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '<div id="course_learning_objectives_subcontainer_0_0" class="col-11 accordion-container card">');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '<div id="course_learning_objectives_header_0_0" class="accordion-header card-header"/>
            <a data-toggle="collapse" href="#collapse_learning_0_0" role="button" aria-expanded="true" aria-controls="collapse_learning_0_0">
                '. get_string('learningobjective', 'format_udehauthoring') . ' ' . 1.1 .'
            </a></div>');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '<div class="collapse show" id="collapse_learning_0_0" data-parent="#course_learning_objectives_container_0">');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '<div class="card-body accordion-content" id="course_learning_objectives_0_0">');
        $repeatarraylearningobjectives[] = $mform->createElement('editor', 'course_learning_objectives_def_0_0', '', ['class'=>'title-editor', 'rows'=>'4']);
        $repeatarraylearningobjectives[] = $mform->createElement('select', 'course_learning_objectives_competency_type_0_0', get_string('courselearningobjectivescompetencytype', 'format_udehauthoring'), $competencytypes);
        $repeatarraylearningobjectives[] = $mform->createElement('html', '</div>');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '</div>');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '</div>');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '<div class="col-1 remove_learning_action_button">');
        $repeatarraylearningobjectives[] = $mform->createElement('button', 'remove_learning_objectives_0_0', '<i class="remove-button-js fa fa-minus-circle fa-2x"></i>');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '</div>');

        $repeatarraylearningobjectives[] = $mform->createElement('html', '</div>');

        $repeatarraylearningobjectives[] = $mform->createElement('html', '</div>');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '<div class="add_learning_container">');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '<div class="row course_add_learning_objectives_row">');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '<div class="col-11 add-container-text card-header card">');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '<span class="add-text">'. get_string('learningobjective', 'format_udehauthoring') . ' ' . 1.2 .'</span>');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '</div>');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '<div class="col-1 add_action_button">');
        $repeatarraylearningobjectives[] = $mform->createElement('button', 'add_learning_objectives_0', '<i class="add-button fa fa-plus-circle fa-2x"></i>');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '</div>');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '</div>');
        $repeatarraylearningobjectives[] = $mform->createElement('html', '</div>');
        return $repeatarraylearningobjectives;
    }

    function buildSections($mform) {
        $mform->addElement('html', '<div id="displayable-form-sections-container" style="display: none">');

        if(get_string_manager()->string_exists('instructionscoursesections', 'format_udehauthoring') && get_string('instructionscoursesections', 'format_udehauthoring')) {
            $mform->addElement('html', '<div class="mt-3">');
            $mform->addElement('html', '<span class="ml-3 page-instructions">' . get_string('instructions', 'format_udehauthoring') . '</span>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '<p class="ml-3 mt-1">' . get_string('instructionscoursesections', 'format_udehauthoring') . '</p>');
        }

        $mform->addElement('html', '<h2 class="ml-3 mt-2 mb-1 page-title">' . get_string('coursesections', 'format_udehauthoring') . '</h2>');

        $repeatarray = [];
        $repeatarray[] = $mform->createElement('html', '<div class="row row-container mb-3" id="row_course_module_container_0">');

        $repeatarray[] = $mform->createElement('html', '<div class="col-11 card accordion-container">');

        $repeatarray[] = $mform->createElement('html', '<div id="course_module_header_0" class="accordion-header card-header">
            <a data-toggle="collapse" href="#collapse_module_header_0" role="button" aria-expanded="true" aria-controls="collapse_module_header_0">
                '. get_string('section', 'format_udehauthoring') . ' ' . 1 .'
            </a>');
        $repeatarray[] = $mform->createElement('html', '</div>');

        $repeatarray[] = $mform->createElement('html', '<div class="collapse show" id="collapse_module_header_0" data-parent="#displayable-form-sections-container">');
        $repeatarray[] = $mform->createElement('html', '<div class="card-body accordion-content" id="course_module_0">');
        $repeatarray[] = $mform->createElement('editor', 'section_title_0', get_string('sectiontitle', 'format_udehauthoring'), ['class'=>'title-editor', 'rows'=>'4']);
        $repeatarray[] = $mform->createElement('editor', 'section_question_0', get_string('sectionquestion', 'format_udehauthoring'), ['class'=>'title-editor', 'rows'=>'4']);
        $repeatarray[] = $mform->createElement('editor', 'section_description_0', get_string('sectiondescription', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
        $repeatarray[] = $mform->createElement('html', '</div>');
        $repeatarray[] = $mform->createElement('html', '</div>');

        $repeatarray[] = $mform->createElement('html', '</div>');

        $repeatarray[] = $mform->createElement('html', '<div class="col-1 remove_module_action_button">');
        $repeatarray[] = $mform->createElement('button', 'remove_module_0', '<i class="remove-button-js fa fa-minus-circle fa-2x"></i>');
        $repeatarray[] = $mform->createElement('html', '</div>');

        $repeatarray[] = $mform->createElement('html', '</div>');

        $mform->setType('section_title_0', PARAM_RAW);
        $mform->setType('section_description_0', PARAM_RAW);
        $mform->setType('section_question_0', PARAM_RAW);

        foreach($repeatarray as $obj) {
            $mform->addElement($obj);
        }

        $this->handleHelpButtons(array(
            ['sectiontitle', 'section_title_0'],
            ['sectionquestion', 'section_question_0'],
            ['sectiondescription', 'section_description_0']
        ), $mform);

        $mform->addElement('html', '<div id="section-add-container" class="row accordion-add-container">');

        $mform->addElement('html', '<div class="col-11 add-container-text card-header card">');
        $mform->addElement('html', '<span class="add-text">'. get_string('section', 'format_udehauthoring') . ' ' . 2 .'</span>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-1 add_action_button">');
        $mform->addElement('button', 'add_module', '<i class="add-button fa fa-plus-circle fa-2x"></i>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '</div>');

        $mform->addElement('html', '</div>');
    }

    function buildEvaluations($mform) {
        $mform->addElement('html', '<div id="displayable-form-evaluations-container" style="display: none">');

        if(get_string_manager()->string_exists('instructionscourseevaluations', 'format_udehauthoring') && get_string('instructionscourseevaluations', 'format_udehauthoring')) {
            $mform->addElement('html', '<div class="mt-3">');
            $mform->addElement('html', '<span class="ml-3 page-instructions">' . get_string('instructions', 'format_udehauthoring') . '</span>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '<p class="ml-3 mt-1">' . get_string('instructionscourseevaluations', 'format_udehauthoring') . '</p>');
        }

        $mform->addElement('html', '<h2 class="ml-3 mr-2 page-title">'. get_string('learningevaluations', 'format_udehauthoring') . '</h2>');

        $learningplans = \format_udehauthoring\model\learningobjective_plan::instance_all_by_audeh_course_id($this->_customdata['courseid']);
        $moduleplans = \format_udehauthoring\model\section_plan::instance_all_by_course_plan_id($this->_customdata['courseid']);
        $modulearray = [];
        foreach ($moduleplans as $moduleplan) {
            $modulearray[$moduleplan->id] = $moduleplan->title;
        }
        $modulearray[0] = "Aucun";

        $repeatarrayEval = [];
        $repeatarrayEval[] = $mform->createElement('html', '<div class="row row-container mb-3" id="row_course_evaluation_container_0">');
        $repeatarrayEval[] = $mform->createElement('html', '<div class="col-11 accordion-container card">');
        $repeatarrayEval[] = $mform->createElement('html', '<div id="course_evaluation_objectives_header_0" class="accordion-header card-header"/>
          <a data-toggle="collapse" href="#collapse_evaluation_0" role="button" aria-expanded="true" aria-controls="collapse_evaluation_0">
            '. get_string('evaluation', 'format_udehauthoring') . ' ' . 1 .'
          </a>');
        $repeatarrayEval[] = $mform->createElement('html', '</div>');
        $repeatarrayEval[] = $mform->createElement('html', '<div class="collapse show" id="collapse_evaluation_0" data-parent="#displayable-form-evaluations-container">');
        $repeatarrayEval[] = $mform->createElement('html', '<div class="card-body accordion-content" id="course_evaluation_content_0">');
        $repeatarrayEval[] = $mform->createElement('editor', 'evaluation_title_0', get_string('evaluationtitle', 'format_udehauthoring'), ['class'=>'title-editor', 'rows'=>'4']);
        $repeatarrayEval[] = $mform->createElement('editor', 'evaluation_description_0', get_string('evaluationdescription', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
        $repeatarrayEval[] = $mform->createElement('text', 'evaluation_weight_0', get_string('evaluationweight', 'format_udehauthoring'));

        $repeatarrayEval[] = $mform->createElement('html', '<div id="fitem_id_evaluation_learning_objectives_0" name="evaluation_learning_objectives_0" class="mt-4 mb-3"> <div id="evaluation_learning_objectives_title_0" class="d-flex"> <label for="fitem_id_evaluation_learning_objectives_0" class="d-inline word-break ml-3 eval-obj-title">'. get_string('evaluationlearningobjective', 'format_udehauthoring') . '</label></div>');
        foreach($learningplans as $key=>$learningplan) {
            foreach($learningplan as $learningkey=>$plan) {
                $repeatarrayEval[] = $mform->createElement('advcheckbox',
                    'evaluation_learning_objectives_0[' . $plan->id. ']',
                    '',
                    ($key + 1) . '.' . ($learningkey + 1) . ' - ' . strip_tags($plan->learningobjective));
            }
        }
        $repeatarrayEval[] = $mform->createElement('html', '</div>');
        $repeatarrayEval[] = $mform->createElement('select', 'evaluation_module_0', get_string('evaluationassociatedmodule', 'format_udehauthoring'), $modulearray);
        $repeatarrayEval[] = $mform->createElement('html', '<div class="">');
        $repeatarrayEval[] = $mform->createElement('button', 'generate_eval', '<i class="fa fa-pencil-square-o fa-2x" aria-hidden="true"></i>');
        $repeatarrayEval[] = $mform->createElement('html', '</div>');
        $repeatarrayEval[] = $mform->createElement('html', '</div>');
        $repeatarrayEval[] = $mform->createElement('html', '</div>');
        $repeatarrayEval[] = $mform->createElement('html', '</div>');
        $repeatarrayEval[] = $mform->createElement('html', '<div class="col-1 remove-button-container">');
        $repeatarrayEval[] = $mform->createElement('button', 'remove_evaluation_0', '<i class="remove-button-js fa fa-minus-circle fa-2x"></i>');
        $repeatarrayEval[] = $mform->createElement('html', '</div>');
        $repeatarrayEval[] = $mform->createElement('html', '</div>');

        $mform->setType('evaluation_title_0', PARAM_RAW);
        $mform->setType('evaluation_description_0', PARAM_RAW);
        $mform->setType('evaluation_weight_0', PARAM_INT);
        $mform->setType('evaluation_learning_objectives_0', PARAM_INT);
        $mform->setType('evaluation_module_0', PARAM_INT);
        $mform->setType('evaluation_id', PARAM_INT);

        foreach($repeatarrayEval as $obj) {
            $mform->addElement($obj);
        }

        $this->handleHelpButtons(array(
            ['evaluationtitle', 'evaluation_title_0'],
            ['evaluationdescription', 'evaluation_description_0'],
            ['evaluationweight', 'evaluation_weight_0'],
            ['evaluationlearningobjective', 'evaluation_learning_objectives_0[' . $plan->id. ']'],
            ['evaluationassociatedmodule', 'evaluation_module_0'],
        ), $mform);

        $mform->addElement('html', '<div id="evaluation-add-container" class="row accordion-add-container">');
        $mform->addElement('html', '<div class="col-11 add-container-text card-header card">');
        $mform->addElement('html', '<span class="add-text">'. get_string('evaluation', 'format_udehauthoring') . ' ' . 2 .'</span>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '<div class="col-1 add_action_button">');
        $mform->addElement('button', 'add_evaluation', '<i class="add-button fa fa-plus-circle fa-2x"></i>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '</div>');
    }

    private function handleHelpButtons($elements, $mform) {
        foreach($elements as $element) {
            if(get_string_manager()->string_exists($element[0] . '_help', 'format_udehauthoring') && get_string($element[0]. '_help', 'format_udehauthoring')) {
                $mform->addHelpButton($element[1], $element[0], 'format_udehauthoring', '', true);
            }
        }
    }
}