<?php

namespace format_udehauthoring;

use html_writer;

class titlesUtils
{
    public static function buildTitlesModal($courseId): string
    {
        global $DB;

        $record = $DB->get_record('udehauthoring_title', ['audehcourseid' => $courseId]);

        if (!$record) {
            $record = new \stdClass();
        }

        $inputClass = 'form-control my-3';
        $modal = html_writer::start_tag('div', [
            'id' => 'config-dialog',
            'class' => 'modal',
            'tabindex' => '-1',
            'role' => 'dialog']
        );
        $modal .= html_writer::start_tag('div', ['class' => 'modal-dialog', 'role' => 'document']);
        $modal .= html_writer::start_tag('div', ['class' => 'modal-content']);
        $modal .= html_writer::start_tag('div', ['class' => 'modal-body']);
        $modal .= html_writer::tag('h3', 'Étiquettes :');
        $modal .= html_writer::tag(
            'input',
            null,
            ['id' => 'courseId', 'type' => 'hidden', 'value' => $courseId]
        );
        $modal .= html_writer::tag(
            'input',
            null,
            [
                'id' => 'etiModule',
                'type' => 'text',
                'class' => $inputClass,
                'placeholder' => 'Module',
                'value' => $record->module ?? ''
            ]
        );
        $modal .= html_writer::tag(
            'input',
            null,
            [
                'id' => 'etiQuestion',
                'type' => 'text',
                'class' => $inputClass,
                'placeholder' => 'Question de réflexion',
                'value' => $record->question ?? ''
            ]
        );
        $modal .= html_writer::tag(
            'input',
            null,
            [
                'id' => 'etiExplore',
                'type' => 'text',
                'class' => $inputClass,
                'placeholder' => 'Explorer les sous-questions',
                'value' => $record->question_explore ?? ''
            ]
        );
        $modal .= html_writer::tag(
            'input',
            null,
            [
                'id' => 'etiHide',
                'type' => 'text',
                'class' => $inputClass,
                'placeholder' => 'Cacher les sous-questions',
                'value' => $record->question_hide ?? ''
            ]
        );
        $modal .= html_writer::tag(
            'input',
            null,
            [
                'id' => 'etiSub',
                'type' => 'text',
                'class' => 'form-control mt-3',
                'placeholder' => 'Sous-questions',
                'value' => $record->question_sub ?? ''
            ]
        );
        $modal .= html_writer::end_tag('div');
        $modal .= html_writer::start_tag('div', ['class' => 'modal-footer']);
        $modal .= html_writer::tag(
            'button',
            'Sauvegarder',
            ['type' => 'button', 'class' => 'btn btn-primary']
        );
        $modal .= html_writer::tag(
            'button',
            'Annuler',
            ['type' => 'button', 'class' => 'btn btn-secondary', 'data-dismiss' => 'modal']
        );
        $modal .= html_writer::end_tag('div');
        $modal .= html_writer::end_tag('div');
        $modal .= html_writer::end_tag('div');
        $modal .= html_writer::end_tag('div');
        $modal .= html_writer::start_tag('div', ['id' => 'progress-circle']);
        $modal .= html_writer::tag(
            'img',
            '',
            ['src' => '../assets/icons8-spinning-circle.gif', 'width' => '75', 'height' => '75']
        );
        $modal .= html_writer::end_tag('div');

        return $modal;
    }
}