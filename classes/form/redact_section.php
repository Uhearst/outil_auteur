<?php

namespace format_udehauthoring\form;

global $CFG;

require_once("$CFG->libdir/formslib.php");

class redact_section extends \moodleform
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

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'audeh_course_id');
        $mform->setType('audeh_course_id', PARAM_INT);

        $mform->addElement('html', '<h1 class="ml-3 course-title">' . $this->_customdata['coursetitle'] . '</h1>');

        if(get_string_manager()->string_exists('instructionssections', 'format_udehauthoring') && get_string('instructionssections', 'format_udehauthoring')) {
            $mform->addElement('html', '<div class="mt-3">');
            $mform->addElement('html', '<span class="ml-3 page-instructions">' . get_string('instructions', 'format_udehauthoring') . '</span>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '<p class="ml-3 mt-1">' . get_string('instructionssections', 'format_udehauthoring') . '</p>');
        }

        $evalForHtml = '';
        if($this->_customdata['evaluation']) {
            $evalForHtml = file_rewrite_pluginfile_urls(
                $this->_customdata['evaluation']->title,
                'pluginfile.php',
                $context->id,
                'format_udehauthoring',
                'course_evaluation_title_' . $this->_customdata['evaluation']->id,
                0
            );
        } else {
            $evalForHtml = strip_tags($evalForHtml);
        }

        $mform->addElement('html', '
        <div class="accordion-container card">
        <div id="section_preview_header" class="card-header accordion-header">
          <a data-toggle="collapse" href="#collapseSectionPreview" role="button" aria-expanded="false" aria-controls="collapseSectionPreview" class="collapsed">
            '. get_string('section', 'format_udehauthoring') . ' - '. get_string('sectiontitle', 'format_udehauthoring') . '
          </a>
        </div>
        <div class="collapse" id="collapseSectionPreview">
          <div class="card-body accordion-content">
            <div id="module_question" class="mt-2 mb-2">
                <strong id="module_question_header">
                '. get_string('sectionquestion', 'format_udehauthoring') . '
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
            <div id="module_description" class="mt-2 mb-2">
                <strong id="module_description_header">
                '. get_string('sectiondescription', 'format_udehauthoring') . '
                </strong>
                <div id="module_description_content">' .
                    file_rewrite_pluginfile_urls(
                        $this->_customdata['section']->description,
                        'pluginfile.php',
                        $context->id,
                        'format_udehauthoring',
                        'course_section_description_' . $this->_customdata['section']->id,
                        0
                    )
                . '</div>
            </div>
            <div id="module_evaluation" class="mt-2 mb-2">
                <strong id="module_evaluation_header">
                '. get_string('summativeevaluation', 'format_udehauthoring') . '
                </strong>
                <div id="module_evaluation_content">' .
                    $evalForHtml
                . '</div>
            </div>  
          </div>
          </div>
        </div>'
        );

        $mform->addElement('hidden', 'section_title');
        $mform->setType('section_title', PARAM_TEXT);

        $mform->addElement('hidden', 'section_question');
        $mform->setType('section_question', PARAM_TEXT);

        $mform->addElement('hidden', 'section_description');
        $mform->setType('section_description', PARAM_TEXT);

        $mform->addElement('hidden', 'section_comments');
        $mform->setType('section_comments', PARAM_TEXT);

        $mform->addElement('filemanager', 'section_vignette', get_string('sectionimage', 'format_udehauthoring'), null,
            array('subdirs' => false, 'maxfiles' => 1, 'accepted_types' => array('jpeg', 'jpg', 'png')));

        $mform->addElement('editor', 'section_introduction_text', get_string('sectionintroductiontext', 'format_udehauthoring'), ['class'=>'full-editor', 'rows'=>'4'], $editoroptions);
        $mform->setType('section_introduction_text', PARAM_RAW);

        $mform->addElement('html', '<div class="custom-control custom-switch mb-2 mt-5">
          <input type="checkbox" class="custom-control-input" id="embed_selector" />         
          <label class="custom-control-label" for="embed_selector">' . get_string("issectionintroductionembed", "format_udehauthoring") . '</label>
        </div>');

        $mform->addElement('hidden', 'isembed');
        $mform->setType('isembed', PARAM_INT);
        $mform->setDefault('isembed', 0);

        $mform->addElement('hidden', 'isvisible');
        $mform->setType('isvisible', PARAM_INT);

        $mform->addElement('textarea', 'section_introduction_embed', get_string('sectionintroductionembed', 'format_udehauthoring'), ['class'=>'ml-n3', 'rows'=>'4']);
        $mform->setType('section_introduction_embed', PARAM_RAW);

        $mform->addElement('filemanager', 'section_introduction', get_string('sectionintroduction', 'format_udehauthoring'), null,
            array('subdirs' => false, 'maxfiles' => 1));
        $mform->setType('section_introduction', PARAM_RAW);

        $this->handleHelpButtons(array(
            ['sectionimage', 'section_vignette'],
            ['sectionintroductiontext', 'section_introduction_text'],
            ['sectionintroduction', 'section_introduction'],
            ['sectionintroductionembed', 'section_introduction_embed']), $mform);

        $mform->addElement('html', '<div id="section-subquestions-container"><h2 class="ml-3 mb-3 mt-3 page-title">' . get_string('sectionsubquestion', 'format_udehauthoring') . '</h2>
            <i class="legend">' . get_string('mandatoryfield', 'format_udehauthoring') . '</i>');

        $repeatarray = [];
        $repeatarray[] = $mform->createElement('html', '<div class="row row-container row_section_subquestion_container mb-3" id="row_section_subquestion_container">');
        $repeatarray[] = $mform->createElement('html', '<div class="col-11 accordion-container card">');
        $repeatarray[] = $mform->createElement('html', '<div class="accordion-header card-header">');
        $repeatarray[] = $mform->createElement('html', '
          <a data-toggle="collapse" href="#collapseSectionSubQuestion" role="button" aria-expanded="false" aria-controls="collapseSectionSubQuestion" class="collapsed">
          </a>');
        $repeatarray[] = $mform->createElement('html', '</div>');
        $repeatarray[] = $mform->createElement('html', '<div class="collapse" id="collapseSectionSubQuestion" data-parent="#section-subquestions-container">');
        $repeatarray[] = $mform->createElement('html', '<div class="card-body accordion-content">');
        $repeatarray[] = $mform->createElement('editor', 'subquestion_title', get_string('subquestiontitle', 'format_udehauthoring') . ' <i class="star">*</i> ', ['class'=>'title-editor', 'rows'=>'4'], $editoroptions);
        $repeatarray[] = $mform->createElement('hidden', 'subquestion_id', 0);
        $repeatarray[] = $mform->createElement('html', '</div>');
        $repeatarray[] = $mform->createElement('html', '</div>');
        $repeatarray[] = $mform->createElement('html', '</div>');

        $repeatarray[] = $mform->createElement('html', '<div class="col-1 remove-button-container">');
        $repeatarray[] = $mform->createElement('submit', 'remove_section', 'Remove section');
        $mform->registerNoSubmitButton('remove_section');
        $repeatarray[] = $mform->createElement('html', '</div>');

        $repeatarray[] = $mform->createElement('html', '</div>');

        $mform->setType('subquestion_title', PARAM_RAW);
        $mform->setType('subquestion_id', PARAM_INT);
        $repeateloptions = array();
        if(get_string_manager()->string_exists('subquestiontitle_help', 'format_udehauthoring') && get_string('subquestiontitle_help', 'format_udehauthoring')) {
            $repeateloptions['subquestion_title']['helpbutton'] = array('subquestiontitle', 'format_udehauthoring');
        }

        $repeatno = $this->_customdata['subquestioncount'] == 0 ? 1 : $this->_customdata['subquestioncount'];
        $this->repeat_elements($repeatarray, $repeatno,
            $repeateloptions, 'subquestion_repeats', 'add_subquestion', 1, null, true, 'remove_section');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div id="add_subquestion_container" class="row accordion-add-container">');

        $mform->addElement('html', '<div class="col-11 add-container-text card card-header">');
        $mform->addElement('html', '<span class="add-text">'. get_string('subquestion', 'format_udehauthoring') . ' ' .'</span>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="col-1"><div id="fitem_id_add_section_1" class="form-group row  fitem femptylabel  ">
        <div class="col-lg-3 col-md-4 col-form-label p-0">
            <div class="d-flex align-items-center flex-gap-1 inner">
                <div class="form-label-addon d-flex align-items-center h-100 flex-gap-1"> </div>
            </div>
        </div>
        <div class="col-lg-9 col-md-8 form-inline align-items-center felement p-0 add_action_button">');
        $mform->addElement('html', '</div>');

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