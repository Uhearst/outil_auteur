<?php


namespace format_udehauthoring\form;

global $CFG;

use format_udehauthoring\model\evaluation_plan;
use format_udehauthoring\model\section_plan;
use format_udehauthoring\publish\content\syllabus;

require_once("$CFG->libdir/formslib.php");

class redact_evaluation extends \moodleform
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

        $mform->addElement('html', '<h1 class="course-title">' . $this->_customdata['coursetitle'] . '</h1>');

        if(get_string_manager()->string_exists('instructionsevaluation', 'format_udehauthoring') && get_string('instructionsevaluation', 'format_udehauthoring')) {
            $mform->addElement('html', '<div class="mt-3">');
            $mform->addElement('html', '<span class="ml-3 page-instructions">' . get_string('instructions', 'format_udehauthoring') . '</span>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '<p class="ml-3 mt-1">' . get_string('instructionsevaluation', 'format_udehauthoring') . '</p>');
        }

        $moduletitles = '';

        if ($this->_customdata['evaluation']->audehsectionids
            && count($this->_customdata['evaluation']->audehsectionids) > 0) {
            foreach ($this->_customdata['evaluation']->audehsectionids as $key => $audehsectionid) {
                $moduletitles .= file_rewrite_pluginfile_urls(
                    section_plan::get_section_title_by_id($key),
                    'pluginfile.php',
                    $context->id,
                    'format_udehauthoring',
                    'course_section_title_' . $key,
                    0
                );
            }
        } else {
            $moduletitles =  get_string('none', 'theme_remui');
        }

        $learningobjs = '';
        $counter = 0;
        if ($this->_customdata['teachingobectives'] !== []) {
            foreach($this->_customdata['teachingobectives'] as $key => $teachingobj) {
                foreach($teachingobj->learningobjectives as $innerkey => $learningobj) {
                    if(in_array($learningobj->id, array_column($this->_customdata['evaluation']->learningobjectiveids, 'audehlearningobjectiveid'))) {
                        $learningobjs .=
                            '<div id="evaluation_obj_content_' . $counter . '" style="display: flex;">' .
                            '<span class="mr-2">'. ($key + 1) . '.' . ($innerkey + 1) . ' - ' . '</span>' .
                                file_rewrite_pluginfile_urls(
                                    $learningobj->learningobjective,
                                    'pluginfile.php',
                                    $context->id,
                                    'format_udehauthoring',
                                    'course_learningobjective_' . $learningobj->id,
                                    0
                                )
                            . '</div>';
                        $counter = $counter + 1;
                    }
                }
            }
        }

        $mform->addElement('html', '
        <div class="accordion-container card">
            <div id="evaluation_preview_header" class="card-header accordion-header">
              <a data-toggle="collapse" href="#collapseEvaluationPreview" role="button" aria-expanded="false" aria-controls="collapseEvaluationPreview" class="collapsed">
                '. get_string('evaluation', 'format_udehauthoring') . ' - '. strip_tags(syllabus::cleanEditorContentForCoursePlan($this->_customdata['evaluation']->title)) . '
              </a>
            </div>
            <div class="collapse" id="collapseEvaluationPreview">
              <div class="card-body accordion-content">
                <div id="evaluation_description" class="mb-2 mt-2">
                    <strong id="evaluation_description_header">
                    '. get_string('evaluationdescription', 'format_udehauthoring') . '
                    </strong>
                    <div id="evaluation_description_content">' .
                    file_rewrite_pluginfile_urls(
                        $this->_customdata['evaluation']->description,
                        'pluginfile.php',
                        $context->id,
                        'format_udehauthoring',
                        'course_evaluation_description_' . $this->_customdata['evaluation']->id,
                        0
                    )
                . '</div>
                </div>
                <div id="evaluation_weight" class="mb-2 mt-2">
                    <strong id="evaluation_weight_header">
                    '. get_string('evaluationweight', 'format_udehauthoring') . '
                    </strong>
                    <p id="evaluation_weight_content">' .
                    strip_tags($this->_customdata['evaluation']->weight)
                . '</p>
                </div>
                <div id="evaluation_module" class="mb-2 mt-2">
                    <strong id="evaluation_module_header">
                    '. get_string('evaluationassociatedmodule', 'format_udehauthoring') . '
                    </strong>
                    <p id="evaluation_module_content">' .
                        $moduletitles
                    . '</p>
                </div>
                 <div id="evaluation_obj" class="mb-2 mt-2">
                    <strong id="evaluation_obj_header">
                    '. get_string('evaluationlearningobjective', 'format_udehauthoring') . '
                    </strong>
                    ' . $learningobjs . '
                </div>
              </div>
            </div>
        </div>'
        );

        $mform->addElement('html', '<h2 class="page-title">'. get_string('evaluation', 'format_udehauthoring') . '</h2>');

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

        $mform->addElement('hidden', 'evaluation_tool_cmid');
        $mform->setType('evaluation_tool_cmid', PARAM_INT);

        $mform->addElement('html', '<div class="custom-control custom-switch mb-2 mt-5">
          <input type="checkbox" class="custom-control-input" id="embed_selector"/>
          <label class="custom-control-label" for="embed_selector">' . get_string("issectionintroductionembed", "format_udehauthoring") . '</label>
        </div>');

        $mform->addElement('hidden', 'isembed');
        $mform->setType('isembed', PARAM_INT);
        $mform->setDefault('isembed', 0);

        $mform->addElement('textarea', 'evaluation_introduction_embed', get_string('evaluationintroductionembed', 'format_udehauthoring'), ['rows'=>'4']);
        $mform->setType('evaluation_introduction_embed', PARAM_RAW);

        $mform->addElement('filemanager', 'evaluation_introduction', get_string('evaluationintroduction', 'format_udehauthoring'), null,
            array('maxfiles' => 1, 'subdirs' => false));
        $mform->setType('evaluation_introduction', PARAM_RAW);

        $mform->addElement('filemanager', 'evaluation_files', get_string('evaluationfiles', 'format_udehauthoring'), null,
            ['subdirs' => false]);
        $mform->setType('evaluation_files', PARAM_RAW);

        $mform->addElement('editor', 'evaluation_full_description', get_string('evaluationfulldescription', 'format_udehauthoring'), ['class'=>'full-editor', 'rows'=>'4'], $editoroptions);
        $mform->setType('evaluation_full_description', PARAM_RAW);

        $mform->addElement('editor', 'evaluation_instructions', get_string('evaluationinstructions', 'format_udehauthoring'), ['class'=>'full-editor', 'rows'=>'4'], $editoroptions);
        $mform->setType('evaluation_instructions', PARAM_RAW);

        $mform->addElement('editor', 'evaluation_criteria', get_string('evaluationcriteria', 'format_udehauthoring'), ['class'=>'full-editor', 'rows'=>'4'], $editoroptions);
        $mform->setType('evaluation_criteria', PARAM_RAW);

        $mform->addElement('hidden', 'evaluation_weight');
        $mform->setType('evaluation_weight', PARAM_FLOAT);

        $mform->registerNoSubmitButton('generate_tool');
        $grouparray = array();
        $grouparray[] =& $mform->createElement('select', 'evaluation_tool', '', evaluation_plan::get_available_tools());
        $grouparray[] =& $mform->createElement('submit', 'generate_tool', get_string('generatetool', 'format_udehauthoring'));
        $mform->addGroup($grouparray, 'tool_group', get_string('evaluationtool', 'format_udehauthoring'), array(' '),false);

        $urlarray = array();
        $urlarray[] =& $mform->createElement('button', 'delete_tool', '<span aria-hidden="true">&times;</span>');
        $mform->addGroup($urlarray, 'url_group', get_string('toolurlgroup', 'format_udehauthoring'));


        $this->handleHelpButtons(array(
            ['evaluationintroduction', 'evaluation_introduction'],
            ['evaluationfiles', 'evaluation_files'],
            ['evaluationintroductionembed', 'evaluation_introduction_embed'],
            ['evaluationfulldescription', 'evaluation_full_description'],
            ['evaluationinstructions', 'evaluation_instructions'],
            ['evaluationcriteria', 'evaluation_criteria'],
            ['toolgroup', 'tool_group'],
            ['toolurlgroup', 'url_group']), $mform);
    }

    private function handleHelpButtons($elements, $mform) {
        foreach($elements as $element) {
            if(get_string_manager()->string_exists($element[0] . '_help', 'format_udehauthoring') && get_string($element[0]. '_help', 'format_udehauthoring')) {
                $mform->addHelpButton($element[1], $element[0], 'format_udehauthoring', '', true);
            }
        }
    }
}