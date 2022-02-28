<?php


namespace format_udehauthoring\form;

global $CFG;

require_once("$CFG->libdir/formslib.php");

class redact_evaluation extends \moodleform
{
    /**
     * @inheritDoc
     */
    protected function definition()
    {
        $mform = $this->_form;

        $mform->addElement('html', '<h1 class="ml-3 course-title">' . $this->_customdata['coursetitle'] . '</h1>');

        if(get_string_manager()->string_exists('instructionsevaluation', 'format_udehauthoring') && get_string('instructionsevaluation', 'format_udehauthoring')) {
            $mform->addElement('html', '<div class="mt-3">');
            $mform->addElement('html', '<span class="ml-3 page-instructions">' . get_string('instructions', 'format_udehauthoring') . '</span>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '<p class="ml-3 mt-1">' . get_string('instructionsevaluation', 'format_udehauthoring') . '</p>');
        }

        $mform->addElement('html', '<h2 class="ml-3 page-title">'. get_string('evaluation', 'format_udehauthoring') . '</h2>');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'audeh_section_id');
        $mform->setType('audeh_section_id', PARAM_INT);

        $mform->addElement('hidden', 'audeh_course_id');
        $mform->setType('audeh_course_id', PARAM_INT);

        $mform->addElement('hidden', 'evaluation_title');
        $mform->setType('evaluation_title', PARAM_TEXT);

        $mform->addElement('hidden', 'evaluation_description');
        $mform->setType('evaluation_description', PARAM_TEXT);

        $mform->addElement('filemanager', 'evaluation_introduction', get_string('evaluationintroduction', 'format_udehauthoring'), null,
            array('maxfiles' => 1));

        $mform->addElement('editor', 'evaluation_full_description', get_string('evaluationfulldescription', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
        $mform->setType('evaluation_full_description', PARAM_RAW);

        $mform->addElement('editor', 'evaluation_instructions', get_string('evaluationinstructions', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
        $mform->setType('evaluation_instructions', PARAM_RAW);

        $mform->addElement('editor', 'evaluation_criteria', get_string('evaluationcriteria', 'format_udehauthoring'), ['class'=>'listable-editor', 'rows'=>'4']);
        $mform->setType('evaluation_criteria', PARAM_RAW);

        $mform->addElement('hidden', 'evaluation_weight');
        $mform->setType('evaluation_weight', PARAM_FLOAT);

        $this->handleHelpButtons(array(
            ['evaluationintroduction', 'evaluation_introduction'],
            ['evaluationfulldescription', 'evaluation_full_description'],
            ['evaluationinstructions', 'evaluation_instructions'],
            ['evaluationcriteria', 'evaluation_criteria']), $mform);
    }

    private function handleHelpButtons($elements, $mform) {
        foreach($elements as $element) {
            if(get_string_manager()->string_exists($element[0] . '_help', 'format_udehauthoring') && get_string($element[0]. '_help', 'format_udehauthoring')) {
                $mform->addHelpButton($element[1], $element[0], 'format_udehauthoring', '', true);
            }
        }
    }
}