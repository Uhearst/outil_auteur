<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'format/udehauthoring:redact' => [

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ]
    ],

    'format/udehauthoring:flushpublish' => [

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ]
    ],
];
