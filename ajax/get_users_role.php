<?php

define('AJAX_SCRIPT', true);

require_once __DIR__ . '/../../../config.php';
global $CFG;

require_once($CFG->dirroot . '/mod/smartquest/locallib.php');

$roleid = required_param('roleid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
echo json_encode(get_users_in_role($roleid, $courseid));


