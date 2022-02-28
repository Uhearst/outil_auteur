<?php

$observers = [
    [
        'eventname' => '\core\event\course_deleted',
        'callback' => '\format_udehauthoring\observer::course_deleted'
    ]
];
