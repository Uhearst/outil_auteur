<?php


namespace format_udehauthoring\form;

global $CFG;

use format_udehauthoring\model\evaluation_plan;
use format_udehauthoring\model\exploration_plan;

require_once("$CFG->libdir/formslib.php");

class redact_global_evaluation extends \moodleform
{
    /**
     * @inheritDoc
     */
    protected function definition()
    {
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

        $mform->addElement('hidden', 'course_id');
        $mform->setType('course_id', PARAM_INT);

        $repeatno = $this->_customdata['globalevaluation_count'] == 0 ? 1 : $this->_customdata['globalevaluation_count'];

        for($i = 0; $i < $repeatno; $i++) {
            $btnName = 'tool_group['. $i .'][generate_tool]';
            $mform->registerNoSubmitButton($btnName);
        }

        $mform->addElement('html', '<h1 class="ml-3 course-title">' . $this->_customdata['coursetitle'] . '</h1>');

        if(get_string_manager()->string_exists('instructionsglobalevaluations', 'format_udehauthoring') && get_string('instructionsglobalevaluations', 'format_udehauthoring')) {
            $mform->addElement('html', '<div class="mt-3">');
            $mform->addElement('html', '<span class="ml-3 page-instructions">' . get_string('instructions', 'format_udehauthoring') . '</span>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '<p class="ml-3 mt-1">' . get_string('instructionsglobalevaluations', 'format_udehauthoring') . '</p>');
        }

        $mform->addElement('html', '<div id="global-evaluations-container"><h2 class="mb-3 ml-3 page-title">' . get_string('globalevaluations', 'format_udehauthoring') . '</h2>');

        $repeatarray = [];
        $repeatarray[] = $mform->createElement('html', '<div class="row row-container row_section_subquestion_container mb-3" id="row_section_subquestion_container">');
        $repeatarray[] = $mform->createElement('html', '<div class="col-12 single-accordion-container accordion-container card">');
        $repeatarray[] = $mform->createElement('html', '<div class="accordion-header card-header">');
        $repeatarray[] = $mform->createElement('html', '
          <a data-toggle="collapse" href="#collapseGlobalEvaluation" role="button" aria-expanded="false" aria-controls="collapseGlobalEvaluation" class="collapsed">
          Evaluation Globale
          </a>');
        $repeatarray[] = $mform->createElement('html', '</div>');
        $repeatarray[] = $mform->createElement('html', '<div class="collapse" id="collapseGlobalEvaluation" data-parent="#global-evaluations-container">');
        $repeatarray[] = $mform->createElement('html', '<div class="accordion-content card-body">');
        $repeatarray[] = $mform->createElement('hidden', 'evaluation_id', 0);
        $repeatarray[] = $mform->createElement('hidden', 'audeh_section_id');
        $repeatarray[] = $mform->createElement('hidden', 'audeh_course_id');
        $repeatarray[] = $mform->createElement('hidden', 'evaluation_title');
        $repeatarray[] = $mform->createElement('hidden', 'evaluation_description');
        $repeatarray[] = $mform->createElement('hidden', 'isembed');
        $repeatarray[] = $mform->createElement('hidden', 'evaluation_tool_cmid');

        $repeatarray[] = $mform->createElement('html', '
        <div class="accordion-container card ml-3 mt-2 mb-2" id="evaluation_preview_container_">
            <div id="evaluation_preview_header_" class="card-header accordion-header">
              <a data-toggle="collapse" href="#collapseEvaluationPreview_" role="button" aria-expanded="false" aria-controls="collapseEvaluationPreview_" class="collapsed">
                '. get_string('previousinformations', 'format_udehauthoring') . '
              </a>
            </div>
            <div class="collapse" id="collapseEvaluationPreview_">
              <div class="card-body accordion-content">
                <div id="evaluation_description_">
                    <strong id="evaluation_description_header_">
                    '. get_string('evaluationdescription', 'format_udehauthoring') . '
                    </strong>
                    <div id="evaluation_description_content_"></div>
                </div>
                <div id="evaluation_weight_">
                    <strong id="evaluation_weight_header_">
                    '. get_string('evaluationweight', 'format_udehauthoring') . '
                    </strong>
                    <p id="evaluation_weight_content_"></p>
                </div>
                 <div id="evaluation_obj_">
                    <strong id="evaluation_obj_header_">
                    '. get_string('evaluationlearningobjective', 'format_udehauthoring') . '
                    </strong>
                </div>
              </div>
            </div>
        </div>'
        );


        $repeatarray[] = $mform->createElement('html', '<div class="custom-control custom-switch udeh-custom-switch ml-3 mb-2 mt-5">
          <input type="checkbox" class="custom-control-input" id="embed_selector"/>
          <label class="custom-control-label" for="embed_selector">' . get_string("issectionintroductionembed", "format_udehauthoring") . '</label>
        </div>');


        $repeatarray[] = $mform->createElement('textarea', 'evaluation_introduction_embed', get_string('evaluationintroductionembed', 'format_udehauthoring'), ['rows'=>'4']);

        $repeatarray[] = $mform->createElement('filemanager', 'evaluation_introduction', get_string('evaluationintroduction', 'format_udehauthoring'), null,
            array('maxfiles' => 1, 'subdirs' => false));
        $repeatarray[] = $mform->createElement('filemanager', 'evaluation_files', get_string('evaluationfiles', 'format_udehauthoring'), null,
            ['subdirs' => false]);
        $repeatarray[] = $mform->createElement('editor', 'evaluation_full_description', get_string('evaluationfulldescription', 'format_udehauthoring'), ['class'=>'full-editor', 'rows'=>'4'], $editoroptions);
        $repeatarray[] = $mform->createElement('editor', 'evaluation_instructions', get_string('evaluationinstructions', 'format_udehauthoring'), ['class'=>'full-editor', 'rows'=>'4'], $editoroptions);
        $repeatarray[] = $mform->createElement('editor', 'evaluation_criteria', get_string('evaluationcriteria', 'format_udehauthoring'), ['class'=>'full-editor', 'rows'=>'4'], $editoroptions);
        $repeatarray[] = $mform->createElement('hidden', 'evaluation_weight');

        $grouparray = array();
        $grouparray[] =& $mform->createElement('select', 'evaluation_tool', '', evaluation_plan::get_available_tools());
        $grouparray[] =& $mform->createElement('submit', 'generate_tool', get_string('generatetool', 'format_udehauthoring'));
        $repeatarray[] = $mform->createElement('group', 'tool_group', get_string('evaluationtool', 'format_udehauthoring'), $grouparray, false);

        $urlarray = array();
        $urlarray[] =& $mform->createElement('button', 'delete_tool', '<span aria-hidden="true">&times;</span>');
        $repeatarray[] = $mform->createElement('group', 'url_group', get_string('toolurlgroup', 'format_udehauthoring'), $urlarray);

        $repeatarray[] = $mform->createElement('html', '</div>');
        $repeatarray[] = $mform->createElement('html', '</div>');
        $repeatarray[] = $mform->createElement('html', '</div>');
        $repeatarray[] = $mform->createElement('html', '</div>');

        $repeatarrayel = $this->handleHelpButtonsArray(array(
            ['evaluationintroduction', 'evaluation_introduction'],
            ['evaluationfiles', 'evaluation_files'],
            ['evaluationintroductionembed', 'evaluation_introduction_embed'],
            ['evaluationfulldescription', 'evaluation_full_description'],
            ['evaluationinstructions', 'evaluation_instructions'],
            ['evaluationcriteria', 'evaluation_criteria'],
            ['toolgroup', 'tool_group'],
            ['toolurlgroup', 'url_group']));



        $this->repeat_elements($repeatarray, $repeatno,
            $repeatarrayel, 'globalevaluation_repeats', 'add_global_evaluation', 1, null, true);

        $mform->setType('evaluation_id', PARAM_INT);
        $mform->setType('audeh_section_id', PARAM_INT);
        $mform->setType('audeh_course_id', PARAM_INT);
        $mform->setType('evaluation_title', PARAM_RAW);
        $mform->setType('isembed', PARAM_INT);
        $mform->setDefault('isembed', 0);
        $mform->setType('evaluation_introduction_embed', PARAM_RAW);
        $mform->setType('evaluation_introduction', PARAM_RAW);
        $mform->setType('evaluation_files', PARAM_RAW);
        $mform->setType('evaluation_description', PARAM_RAW);
        $mform->setType('evaluation_full_description', PARAM_RAW);
        $mform->setType('evaluation_instructions', PARAM_RAW);
        $mform->setType('evaluation_criteria', PARAM_RAW);
        $mform->setType('evaluation_weight', PARAM_FLOAT);
        $mform->setDefault('evaluation_weight', 0);
        $mform->setType('evaluation_tool_cmid', PARAM_INT);
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