<?php

namespace format_udehauthoring;

use html_writer;

class modalUtils
{
    public static function buildWarningModal() {


        $modal = html_writer::tag('div', '', ['id' => 'save-warning-container']);
        $modal .= html_writer::start_tag(
            'div',
            ['class' => 'modal save-warning', 'tabindex' => '-1', 'role' => 'dialog']
        );
        $modal .= html_writer::start_tag('div', ['class' => 'modal-dialog', 'role' => 'document']);
        $modal .= html_writer::start_tag('div', ['class' => 'modal-content']);
        $modal .= html_writer::start_tag('div', ['class' => 'modal-body']);
        $modal .= html_writer::tag('p', get_string('savewarning', 'format_udehauthoring'));
        $modal .= html_writer::end_tag('div');
        $modal .= html_writer::start_tag('div', ['class' => 'modal-footer']);
        $modal .= html_writer::tag(
            'button',
                get_string('continue', 'theme_remui'),
            ['class' => 'btn btn-secondary']
        );
        $modal .= html_writer::tag(
            'button',
            get_string('cancel', 'moodle'),
            ['class' => 'btn btn-primary', "data-dismiss" => "modal"]
        );
        $modal .= html_writer::end_tag('div');
        $modal .= html_writer::end_tag('div');
        $modal .= html_writer::end_tag('div');
        $modal .= html_writer::end_tag('div');

        return $modal;
    }
}