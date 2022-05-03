<?php

require_once('../../../../config.php');
global $DB;

$units = \format_udehauthoring\model\unit_config::instance_all();
echo json_encode(array('success' => 1, 'data' => $units));