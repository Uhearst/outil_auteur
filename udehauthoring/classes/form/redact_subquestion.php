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

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'audeh_section_id');
        $mform->setType('audeh_section_id', PARAM_INT);

        $mform->addElement('html', '<h1 class="ml-3 course-title">' . $this->_customdata['coursetitle'] . '</h1>');

        if(get_string_manager()->string_exists('instructionssubquestion', 'format_udehauthoring') && get_string('instructionssubquestion', 'format_udehauthoring')) {
            $mform->addElement('html', '<div class="mt-3">');
            $mform->addElement('html', '<span class="ml-3 page-instructions">' . get_string('instructions', 'format_udehauthoring') . '</span>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '<p class="ml-3 mt-1">' . get_string('instructionssubquestion', 'format_udehauthoring') . '</p>');
        }

        $mform->addElement('html', '
        <div class="accordion-container card ml-3">
        <div id="subquestion_preview_header" class="card-header accordion-header">
          <a data-toggle="collapse" href="#collapseSubQuestionPreview" role="button" aria-expanded="false" aria-controls="collapseSubQuestionPreview" class="collapsed">
            Trame 1.1 - Titre de la trame ' . strip_tags($this->_customdata['section']->title) . '
          </a>
        </div>
        <div class="collapse" id="collapseSubQuestionPreview">
          <div class="card-body accordion-content">
            <div id="module_question">
                <strong id="module_question_header">
                    Question du Module
                </strong>
                <p id="module_question_content">' .
                    strip_tags($this->_customdata['section']->question)
            . '</p>
            </div>
            <div id="subquestion">
                <strong id="subquestion_header">
                    Sous-question
                </strong>
                <p id="module_evaluation_content">' .
                    strip_tags($this->_customdata['subquestion']->title)
            . '</p>
            </div>  
          </div>
          </div>
        </div>'
        );

        $mform->addElement('hidden', 'subquestion_title');
        $mform->setType('subquestion_title', PARAM_TEXT);

        $mform->addElement('editor', 'subquestion_enonce', get_string('subquestionenonce', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
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
          <a data-toggle="collapse" href="#collapseSubQuestionExploration" role="button" aria-expanded="true" aria-controls="collapseSubQuestionExploration">
            Exploration 1.1.1 - 
          </a>');
        $repeatarrayexplorations[] = $mform->createElement('html', '</div>');
        $repeatarrayexplorations[] = $mform->createElement('html', '<div class="collapse show" id="collapseSubQuestionExploration" data-parent="#subquestion-explorations-container">');
        $repeatarrayexplorations[] = $mform->createElement('html', '<div class="card-body accordion-content">');

        $repeatarrayexplorations[] = $mform->createElement('hidden', 'exploration_title');
        $repeatarrayexplorations[] = $mform->createElement('hidden', 'exploration_id', 0);
        $repeatarrayexplorations[] = $mform->createElement('hidden', 'exploration_tool_cmid');

        $repeatarrayexplorations[] = $mform->createElement('hidden', 'exploration_question');

        $repeatarrayexplorations[] = $mform->createElement('select', 'exploration_activity_type', get_string('explorationactivitytype', 'format_udehauthoring'), exploration_plan::activity_type_list());

        $temporality = ['Synchrone', 'Asynchrone'];
        $repeatarrayexplorations[] = $mform->createElement('select', 'exploration_temporality', get_string('explorationtemporality', 'format_udehauthoring'), $temporality);

        $repeatarrayexplorations[] = $mform->createElement('text', 'exploration_length', get_string('explorationlength', 'format_udehauthoring'));

        $location = ['En ligne', 'A la maison', 'En salle de classe'];
        $repeatarrayexplorations[] = $mform->createElement('select', 'exploration_location', get_string('explorationlocation', 'format_udehauthoring'), $location);

        $grouping = ['Individuel', 'Paires', 'Groupes'];
        $repeatarrayexplorations[] = $mform->createElement('select', 'exploration_grouping', get_string('explorationgrouping', 'format_udehauthoring'), $grouping);

        $repeatarrayexplorations[] = $mform->createElement('editor', 'exploration_instructions', get_string('explorationinstructions', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);

        $repeatarrayexplorations[] = $mform->createElement('html', '<p>' . get_string('ismarkedevaluation', 'format_udehauthoring') . '</p>');
        $repeatarrayexplorations[] = $mform->createElement('radio', 'exploration_marked', '', get_string('yes'), 1);
        $repeatarrayexplorations[] = $mform->createElement('radio', 'exploration_marked', '', get_string('no'), 0);

        $evaluationtype = ['Formative', 'Diagnostique', 'Par paires'];
        $repeatarrayexplorations[] = $mform->createElement('select', 'exploration_evaluation_type', get_string('explorationevaluationtype', 'format_udehauthoring'), $evaluationtype);

        $grouparray = array();
        $grouparray[] =& $mform->createElement('select', 'exploration_tool', '', exploration_plan::get_available_tools());
        $grouparray[] =& $mform->createElement('button', 'generate_tool', 'generate tool',  ["courseid" => $this->_customdata['courseid']]);
        $repeatarrayexplorations[] = $mform->createElement('group', 'tool_group', get_string('explorationtool', 'format_udehauthoring'), $grouparray);

        $repeatarrayexplorations[] = $mform->createElement('static', 'exploration_tool_url_display', 'exploration tool url', 'Link');

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
        $mform->setType('exploration_tool_cmid', PARAM_INT);
        $mform->setType('exploration_question', PARAM_TEXT);
        $mform->setType('exploration_activity_type', PARAM_INT);
        $mform->setDefault('exploration_activity_type', 0);
        $mform->setType('exploration_temporality', PARAM_INT);
        $mform->setDefault('exploration_temporality', 0);
        $mform->setType('exploration_length', PARAM_RAW);
        $mform->setType('exploration_location', PARAM_INT);
        $mform->setDefault('exploration_location', 0);
        $mform->setType('exploration_grouping', PARAM_INT);
        $mform->setDefault('exploration_grouping', 0);
        $mform->setType('exploration_instructions', PARAM_RAW);
        $mform->setType('exploration_evaluation_type', PARAM_INT);
        $mform->setDefault('exploration_evaluation_type', 0);

        $repeateloptions = $this->handleHelpButtonsArray(array(
            ['explorationactivitytype', 'exploration_activity_type'],
            ['explorationtemporality', 'exploration_temporality'],
            ['explorationlength', 'exploration_length'],
            ['explorationlocation', 'exploration_location'],
            ['explorationgrouping', 'exploration_grouping'],
            ['explorationinstructions', 'exploration_instructions'],
            ['explorationevaluationtype', 'exploration_evaluation_type']));

        $repeatnoexplorations = $this->_customdata['explorationcount'] == 0 ? 1 : $this->_customdata['explorationcount'];
        $this->repeat_elements($repeatarrayexplorations, $repeatnoexplorations,
            $repeateloptions, 'exploration_repeats', 'add_exploration', 1, null, true, 'remove_exploration');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div id="add_exploration_container" class="row accordion-add-container">');

        $mform->addElement('html', '<div class="col-11 add-container-text card card-header">');
        $mform->addElement('html', '<span class="add-text">Exploration </span>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-1 add_action_button">');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div id="subquestion-resources-container"><h2 class="ml-3 mb-3 page-title">' . get_string('titleresources', 'format_udehauthoring') . '</h2>');

        $repeatarrayresources = [];
        $repeatarrayresources[] = $mform->createElement('html', '<div class="row row-container row_subquestion_resource_container mb-3" id="row_subquestion_resource_container_">');

        $repeatarrayresources[] = $mform->createElement('html', '<div class="col-11 accordion-container card">');
        $repeatarrayresources[] = $mform->createElement('html', '<div class="accordion-header card-header">');
        $repeatarrayresources[] = $mform->createElement('html', '
          <a data-toggle="collapse" href="#collapseSubQuestionResource" role="button" aria-expanded="true" aria-controls="collapseSubQuestionResource">
            Suggestion de ressource 1
          </a>');
        $repeatarrayresources[] = $mform->createElement('html', '</div>');
        $repeatarrayresources[] = $mform->createElement('html', '<div class="collapse show" id="collapseSubQuestionResource" data-parent="#subquestion-resources-container">');
        $repeatarrayresources[] = $mform->createElement('html', '<div class="card-body subquestion_resource_content">');
        $repeatarrayresources[] = $mform->createElement('editor', 'resource_title', get_string('resourcetitle', 'format_udehauthoring'), ['class'=>'title-editor', 'rows'=>'4']);
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

        $mform->addElement('html', '<div class="col-1 add_action_button"></div>');
        $mform->addElement('html', '</div>');
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