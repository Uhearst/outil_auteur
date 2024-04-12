<?php


namespace format_udehauthoring\form;

global $CFG;

use format_udehauthoring\model\exploration_plan;

require_once("$CFG->libdir/formslib.php");

class redact_subquestion extends \moodleform
{
    /**
     * @inheritDoc
     */
    protected function definition()
    {
        global $CFG;

        $mform = $this->_form;

        $courseid = $this->_customdata['courseid'];

        if (empty($courseid)) {
            $context = \context_system::instance();
        } else {
            $context = \context_course::instance($courseid);
        }

        $editoroptions = array(
            'subdirs' => 1,
            'maxbytes' => 100000000,
            'maxfiles' => 1,
            'changeformat' => 0,
            'context' => $context,
            'noclean' => 1,
            'trusttext' => 1
        );

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'audeh_section_id');
        $mform->setType('audeh_section_id', PARAM_INT);

        $mform->addElement('html', '<h1 class="course-title">' . $this->_customdata['coursetitle'] . '</h1>');

        if(get_string_manager()->string_exists('instructionssubquestion', 'format_udehauthoring') && get_string('instructionssubquestion', 'format_udehauthoring')) {
            $mform->addElement('html', '<div class="mt-3">');
            $mform->addElement('html', '<span class="ml-3 page-instructions">' . get_string('instructions', 'format_udehauthoring') . '</span>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '<p class="ml-3 mt-1">' . get_string('instructionssubquestion', 'format_udehauthoring') . '</p>');
        }

        $mform->addElement('html', '
        <div class="accordion-container card">
        <div id="subquestion_preview_header" class="card-header accordion-header">
          <a data-toggle="collapse" href="#collapseSubQuestionPreview" role="button" aria-expanded="false" aria-controls="collapseSubQuestionPreview" class="collapsed">
            ' . get_string("subquestion", "format_udehauthoring") . ' - ' . strip_tags($this->_customdata['subquestion']->title) . '
          </a>
        </div>
        <div class="collapse" id="collapseSubQuestionPreview">
          <div class="card-body accordion-content">
            <div id="module_question" class="mt-2 mb-2">
                <strong id="module_question_header">
                    ' . get_string("sectionquestion", "format_udehauthoring") . '
                </strong>
                <div id="module_question_content">' .
                    file_rewrite_pluginfile_urls(
                        $this->_customdata['section']->question,
                        'pluginfile.php',
                        $context->id,
                        'format_udehauthoring',
                        'course_section_question_' . $this->_customdata['section']->id,
                        0
                    )
            . '</div>
            </div> 
          </div>
          </div>
        </div>'
        );

        $repeatnoexplorations = $this->_customdata['explorationcount'] == 0 ? 1 : $this->_customdata['explorationcount'];

        for($i = 0; $i < $repeatnoexplorations; $i++) {
            $btnName = 'tool_group['. $i .'][generate_tool]';
            $mform->registerNoSubmitButton($btnName);
        }

        $mform->addElement('hidden', 'subquestion_title');
        $mform->setType('subquestion_title', PARAM_TEXT);

        $mform->addElement('editor', 'subquestion_enonce', get_string('subquestionenonce', 'format_udehauthoring'), ['class'=>'full-editor', 'rows'=>'4'], $editoroptions);
        $mform->setType('subquestion_enonce', PARAM_RAW);

        $mform->addElement('filemanager', 'subquestion_vignette', get_string('subquestionvignette', 'format_udehauthoring'), null,
            array('subdirs' => false, 'maxfiles' => 1, 'accepted_types' => array('jpeg', 'jpg', 'png')));

        $this->handleHelpButtons(array(
            ['subquestionvignette', 'subquestion_vignette'],
            ['subquestionenonce', 'subquestion_enonce']), $mform);

        $mform->addElement('html', '<div id="subquestion-explorations-container"><h2 class="ml-3 mb-3 page-title">' . get_string('titleexplorations', 'format_udehauthoring') . '</h2>');

        $repeatarrayexplorations = [];
        $repeatarrayexplorations[] = $mform->createElement('html', '<div class="row row-container row_subquestion_exploration_container mb-3" id="row_subquestion_exploration_container">');

        $repeatarrayexplorations[] = $mform->createElement('html', '<div class="col-11 accordion-container card">');
        $repeatarrayexplorations[] = $mform->createElement('html', '<div class="accordion-header card-header">');
        $repeatarrayexplorations[] = $mform->createElement('html', '
          <a data-toggle="collapse" href="#collapseSubQuestionExploration" role="button" aria-expanded="false" aria-controls="collapseSubQuestionExploration" class="collapsed">
            Exploration 1.1.1 - 
          </a>');
        $repeatarrayexplorations[] = $mform->createElement('html', '</div>');
        $repeatarrayexplorations[] = $mform->createElement('html', '<div class="collapse" id="collapseSubQuestionExploration" data-parent="#subquestion-explorations-container">');
        $repeatarrayexplorations[] = $mform->createElement('html', '<div class="card-body accordion-content">');

        $repeatarrayexplorations[] = $mform->createElement('hidden', 'exploration_title');
        $repeatarrayexplorations[] = $mform->createElement('hidden', 'exploration_id', 0);
        $repeatarrayexplorations[] = $mform->createElement('hidden', 'exploration_tool_cmid');
        $repeatarrayexplorations[] = $mform->createElement('hidden', 'exploration_question');

        $repeatarrayexplorations[] = $mform->createElement('select', 'exploration_activity_type', get_string('explorationactivitytype', 'format_udehauthoring'), exploration_plan::activity_type_list());

        $repeatarrayexplorations[] = $mform->createElement('editor', 'exploration_activity_free_type', get_string('explorationactivityfreetype', 'format_udehauthoring'), ['class'=>'full-editor', 'rows'=>'4'], $editoroptions);

        $temporality = ['Synchrone', 'Asynchrone'];
        $repeatarrayexplorations[] = $mform->createElement('select', 'exploration_temporality', get_string('explorationtemporality', 'format_udehauthoring'), $temporality);

        $repeatarrayexplorations[] = $mform->createElement('text', 'exploration_length', get_string('explorationlength', 'format_udehauthoring'));

        $location = exploration_plan::locations_list();
        $repeatarrayexplorations[] = $mform->createElement('select', 'exploration_location', get_string('explorationlocation', 'format_udehauthoring'), $location);

        $party = exploration_plan::party_list();
        $repeatarrayexplorations[] = $mform->createElement('select', 'exploration_party', get_string('explorationparty', 'format_udehauthoring'), $party);

        $repeatarrayexplorations[] = $mform->createElement('editor', 'exploration_instructions', get_string('explorationinstructions', 'format_udehauthoring'), ['class'=>'full-editor', 'rows'=>'4'], $editoroptions);

        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'exploration_marked', '', get_string('yes'), 1);
        $radioarray[] =  $mform->createElement('radio', 'exploration_marked', '', get_string('no'), 0);
        $repeatarrayexplorations[] = $mform->createElement('group', 'marked_group', get_string('ismarkedevaluation', 'format_udehauthoring'), $radioarray, null, false, ['class'=>'ml-3']);

        $evaluationtype = ['Formative', 'Diagnostique', 'Par pairs'];
        $repeatarrayexplorations[] = $mform->createElement('select', 'exploration_evaluation_type', get_string('explorationevaluationtype', 'format_udehauthoring'), $evaluationtype);

        $grouparray = array();
        $grouparray[] =& $mform->createElement('select', 'exploration_tool', '', exploration_plan::get_available_tools());
        $grouparray[] =& $mform->createElement('submit', 'generate_tool', get_string('generatetool', 'format_udehauthoring'));
        $repeatarrayexplorations[] = $mform->createElement('group', 'tool_group', get_string('explorationtool', 'format_udehauthoring'), $grouparray, false);

        $urlarray = array();
        $urlarray[] =& $mform->createElement('button', 'delete_tool', '<span aria-hidden="true">&times;</span>');
        $repeatarrayexplorations[] = $mform->createElement('group', 'url_group', get_string('toolurlgroup', 'format_udehauthoring'), $urlarray);

        $repeatarrayexplorations[] = $mform->createElement('html', '</div>');
        $repeatarrayexplorations[] = $mform->createElement('html', '</div>');
        $repeatarrayexplorations[] = $mform->createElement('html', '</div>');

        $repeatarrayexplorations[] = $mform->createElement('html', '<div class="col-1 remove-button-container">');
        $repeatarrayexplorations[] = $mform->createElement('submit', 'remove_exploration', 'Remove exploration');
        $mform->registerNoSubmitButton('remove_exploration');
        $repeatarrayexplorations[] = $mform->createElement('html', '</div>');

        $repeatarrayexplorations[] = $mform->createElement('html', '</div>');

        $mform->setType('exploration_title', PARAM_TEXT);
        $mform->setType('exploration_id', PARAM_INT);
        $mform->setType('exploration_question', PARAM_RAW);
        $mform->setType('exploration_tool_cmid', PARAM_INT);
        $mform->setType('exploration_activity_free_type', PARAM_RAW);
        $mform->setType('exploration_activity_type', PARAM_INT);
        $mform->setType('exploration_free_type', PARAM_RAW);
        $mform->setDefault('exploration_activity_type', 0);
        $mform->setType('exploration_temporality', PARAM_INT);
        $mform->setDefault('exploration_temporality', 0);
        $mform->setType('exploration_length', PARAM_RAW);
        $mform->setType('exploration_location', PARAM_INT);
        $mform->setDefault('exploration_location', 0);
        $mform->setType('exploration_party', PARAM_INT);
        $mform->setDefault('exploration_party', 0);
        $mform->setType('exploration_instructions', PARAM_RAW);
        $mform->setType('exploration_marked', PARAM_INT);
        $mform->setType('exploration_evaluation_type', PARAM_INT);
        $mform->setDefault('exploration_evaluation_type', 0);
        $mform->setType('exploration_tool', PARAM_INT);
        $mform->setDefault('exploration_tool', 0);

        $repeateloptions = $this->handleHelpButtonsArray(array(
            ['explorationactivitytype', 'exploration_activity_type'],
            ['explorationtemporality', 'exploration_temporality'],
            ['explorationlength', 'exploration_length'],
            ['explorationlocation', 'exploration_location'],
            ['explorationparty', 'exploration_party'],
            ['explorationinstructions', 'exploration_instructions'],
            ['explorationevaluationtype', 'exploration_evaluation_type'],
            ['explorationmarked', 'marked_group'],
            ['toolgroup', 'tool_group'],
            ['toolurlgroup', 'url_group']));

        $this->repeat_elements($repeatarrayexplorations, $repeatnoexplorations,
            $repeateloptions, 'exploration_repeats', 'add_exploration', 1, null, true, 'remove_exploration');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div id="add_exploration_container" class="row accordion-add-container">');

        $mform->addElement('html', '<div class="col-11 add-container-text card card-header">');
        $mform->addElement('html', '<span class="add-text">Exploration </span>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-1"><div class="form-group row  fitem femptylabel" style="display: flex;justify-content: center;margin-top: 0.2rem;">
        <div class="col-lg-9 col-md-8 form-inline align-items-center felement p-0 add_action_button" data-fieldtype="submit" style="justify-content: unset;position: relative;left: -3px;">');
        $mform->addElement('html', '</div></div></div>');

        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div id="subquestion-resources-container"><h2 class="ml-3 mb-3 page-title">' . get_string('titleresources', 'format_udehauthoring') . '</h2>
            <i class="legend">' . get_string('mandatoryfield', 'format_udehauthoring') . '</i>');

        $repeatarrayresources = [];
        $repeatarrayresources[] = $mform->createElement('html', '<div class="row row-container row_subquestion_resource_container mb-3" id="row_subquestion_resource_container_">');

        $repeatarrayresources[] = $mform->createElement('html', '<div class="col-11 accordion-container card">');
        $repeatarrayresources[] = $mform->createElement('html', '<div class="accordion-header card-header">');
        $repeatarrayresources[] = $mform->createElement('html', '
          <a data-toggle="collapse" href="#collapseSubQuestionResource" role="button" aria-expanded="false" aria-controls="collapseSubQuestionResource" class="collapsed">
            ' . get_string('resource', 'format_udehauthoring') . ' 1
          </a>');
        $repeatarrayresources[] = $mform->createElement('html', '</div>');
        $repeatarrayresources[] = $mform->createElement('html', '<div class="collapse" id="collapseSubQuestionResource" data-parent="#subquestion-resources-container">');
        $repeatarrayresources[] = $mform->createElement('html', '<div class="card-body subquestion_resource_content">');
        $repeatarrayresources[] = $mform->createElement('editor', 'resource_title', get_string('resourcetitle', 'format_udehauthoring') . ' <i class="star">*</i> ', ['class'=>'title-editor', 'rows'=>'4'], $editoroptions);
        $repeatarrayresources[] = $mform->createElement('hidden', 'resource_id', 0);
        $repeatarrayresources[] = $mform->createElement('text', 'resource_external_link', get_string('resourceexternallink', 'format_udehauthoring'));
        $repeatarrayresources[] = $mform->createElement('filemanager', 'resource_vignette', get_string('resourcevignette', 'format_udehauthoring'), null,
            array('subdirs' => false, 'maxfiles' => 1, 'accepted_types' => array('jpeg', 'jpg', 'png')));

        $repeatarrayresources[] = $mform->createElement('html', '</div>');
        $repeatarrayresources[] = $mform->createElement('html', '</div>');
        $repeatarrayresources[] = $mform->createElement('html', '</div>');

        $repeatarrayresources[] = $mform->createElement('html', '<div class="col-1 remove-button-container">');
        $repeatarrayresources[] = $mform->createElement('submit', 'remove_resource', 'Remove resource');
        $mform->registerNoSubmitButton('remove_resource');
        $repeatarrayresources[] = $mform->createElement('html', '</div>');

        $repeatarrayresources[] = $mform->createElement('html', '</div>');

        $mform->setType('resource_title', PARAM_RAW);
        $mform->setType('resource_id', PARAM_INT);
        $mform->setType('resource_external_link', PARAM_RAW);

        $repeatelresourceoptions = $this->handleHelpButtonsArray(array(
            ['resourcetitle', 'resource_title'],
            ['resourceexternallink', 'resource_external_link'],
            ['resourcevignette', 'resource_vignette']));

        $repeatnoresource = $this->_customdata['resourcecount'] == 0 ? 1 : $this->_customdata['resourcecount'];
        $this->repeat_elements($repeatarrayresources, $repeatnoresource,
            $repeatelresourceoptions, 'resource_repeats', 'add_resource', 1, null, true, 'remove_resource');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div id="add_resource_container" class="row accordion-add-container">');

        $mform->addElement('html', '<div class="col-11 add-container-text card card-header">');
        $mform->addElement('html', '<span class="add-text">Ressource</span>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-1"><div class="form-group row  fitem femptylabel" style="display: flex;justify-content: center;margin-top: 0.2rem;">
        <div class="col-lg-9 col-md-8 form-inline align-items-center felement p-0 add_action_button" data-fieldtype="submit" style="justify-content: unset;position: relative;left: -3px;">');
        $mform->addElement('html', '</div></div></div>');
    }

    private function handleHelpButtonsArray($elements) {
        $repeateloptions = [];
        foreach($elements as $element) {
            if(get_string_manager()->string_exists($element[0] . '_help', 'format_udehauthoring') && get_string($element[0]. '_help', 'format_udehauthoring')) {
                $repeateloptions[$element[1]]['helpbutton'] = array($element[0], 'format_udehauthoring');
            }
        }
        return $repeateloptions;
    }

    private function handleHelpButtons($elements, $mform) {
        foreach($elements as $element) {
            if(get_string_manager()->string_exists($element[0] . '_help', 'format_udehauthoring') && get_string($element[0]. '_help', 'format_udehauthoring')) {
                $mform->addHelpButton($element[1], $element[0], 'format_udehauthoring', '', true);
            }
        }
    }
}