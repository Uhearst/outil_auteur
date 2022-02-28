<?php


namespace format_udehauthoring\form;

global $CFG;

require_once("$CFG->libdir/formslib.php");

class redact_global_evaluation extends \moodleform
{
    /**
     * @inheritDoc
     */
    protected function definition()
    {
        $mform = $this->_form;

        $mform->addElement('hidden', 'course_id');
        $mform->setType('course_id', PARAM_INT);

        $mform->addElement('html', '<h1 class="ml-3 course-title">' . $this->_customdata['coursetitle'] . '</h1>');

        if(get_string_manager()->string_exists('instructionsglobalevaluations', 'format_udehauthoring') && get_string('instructionsglobalevaluations', 'format_udehauthoring')) {
            $mform->addElement('html', '<div class="mt-3">');
            $mform->addElement('html', '<span class="ml-3 page-instructions">' . get_string('instructions', 'format_udehauthoring') . '</span>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '<p class="ml-3 mt-1">' . get_string('instructionsglobalevaluations', 'format_udehauthoring') . '</p>');
        }

        $mform->addElement('html', '<div id="global-evaluations-container"><h2 class="mb-3 ml-3 page-title">' . get_string('globalevaluation', 'format_udehauthoring') . '</h2>');

        $repeatarray = [];
        $repeatarray[] = $mform->createElement('html', '<div class="row row-container row_section_subquestion_container mb-3" id="row_section_subquestion_container">');
        $repeatarray[] = $mform->createElement('html', '<div class="col-12 single-accordion-container accordion-container card">');
        $repeatarray[] = $mform->createElement('html', '<div class="accordion-header card-header">');
        $repeatarray[] = $mform->createElement('html', '
          <a data-toggle="collapse" href="#collapseGlobalEvaluation" role="button" aria-expanded="false" aria-controls="collapseGlobalEvaluation">
          Evaluation Globale
          </a>');
        $repeatarray[] = $mform->createElement('html', '</div>');
        $repeatarray[] = $mform->createElement('html', '<div class="collapse show" id="collapseGlobalEvaluation" data-parent="#global-evaluations-container">');
        $repeatarray[] = $mform->createElement('html', '<div class="accordion-content card-body">');
        $repeatarray[] = $mform->createElement('hidden', 'evaluation_id', 0);
        $repeatarray[] = $mform->createElement('hidden', 'audeh_section_id');
        $repeatarray[] = $mform->createElement('hidden', 'audeh_course_id');
        $repeatarray[] = $mform->createElement('hidden', 'evaluation_title');
        $repeatarray[] = $mform->createElement('hidden', 'evaluation_description');
        $repeatarray[] = $mform->createElement('filemanager', 'evaluation_introduction', get_string('evaluationintroduction', 'format_udehauthoring'), null,
            array('maxfiles' => 1));
        $repeatarray[] = $mform->createElement('editor', 'evaluation_full_description', get_string('evaluationfulldescription', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
        $repeatarray[] = $mform->createElement('editor', 'evaluation_instructions', get_string('evaluationinstructions', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
        $repeatarray[] = $mform->createElement('editor', 'evaluation_criteria', get_string('evaluationcriteria', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
        $repeatarray[] = $mform->createElement('hidden', 'evaluation_weight');
        $repeatarray[] = $mform->createElement('html', '</div>');
        $repeatarray[] = $mform->createElement('html', '</div>');
        $repeatarray[] = $mform->createElement('html', '</div>');
        $repeatarray[] = $mform->createElement('html', '</div>');

        $repeatarrayel = $this->handleHelpButtonsArray(array(
            ['evaluationintroduction', 'evaluation_introduction'],
            ['evaluationfulldescription', 'evaluation_full_description'],
            ['evaluationinstructions', 'evaluation_instructions'],
            ['evaluationcriteria', 'evaluation_criteria']));


        $repeatno = $this->_customdata['globalevaluation_count'] == 0 ? 1 : $this->_customdata['globalevaluation_count'];
        $this->repeat_elements($repeatarray, $repeatno,
            $repeatarrayel, 'globalevaluation_repeats', 'add_global_evaluation', 1, null, true);

        $mform->setType('evaluation_id', PARAM_INT);
        $mform->setType('audeh_section_id', PARAM_INT);
        $mform->setType('audeh_course_id', PARAM_INT);
        $mform->setType('evaluation_title', PARAM_RAW);
        $mform->setType('evaluation_description', PARAM_RAW);
        $mform->setType('evaluation_full_description', PARAM_RAW);
        $mform->setType('evaluation_instructions', PARAM_RAW);
        $mform->setType('evaluation_criteria', PARAM_RAW);
        $mform->setType('evaluation_weight', PARAM_FLOAT);
        $mform->setDefault('evaluation_weight', 0);
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

}