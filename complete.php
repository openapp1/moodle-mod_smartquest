<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// This page prints a particular instance of smartquest.

require_once("../../config.php");
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot.'/mod/smartquest/smartquest.class.php');
if (!isset($SESSION->smartquest)) {
    $SESSION->smartquest = new stdClass();
}
$SESSION->smartquest->current_tab = 'view';

$id = optional_param('id', null, PARAM_INT);    // Course Module ID.
$a = optional_param('a', null, PARAM_INT);      // smartquest ID.

$sid = optional_param('sid', null, PARAM_INT);  // Survey id.
$resume = optional_param('resume', null, PARAM_INT);    // Is this attempt a resume of a saved attempt?


list($cm, $course, $smartquest) = smartquest_get_standard_page_items($id, $a);
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Check login and get context.

require_capability('mod/smartquest:view', $context);
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/smartquest/javascript/changetitle.js'));

$url = new moodle_url($CFG->wwwroot.'/mod/smartquest/complete.php');
if (isset($id)) {
    $url->param('id', $id);
} else {
    $url->param('a', $a);
}

$PAGE->set_url($url);
//$PAGE->set_context($context);
$smartquest = new smartquest(0, $smartquest, $course, $cm);

// Add renderer and page objects to the smartquest object for display use.
$smartquest->add_renderer($PAGE->get_renderer('mod_smartquest'));
$smartquest->add_page(new \mod_smartquest\output\completepage());

$smartquest->strsmartquests = get_string("modulenameplural", "smartquest");
$smartquest->strsmartquest  = get_string("modulename", "smartquest");

// Mark as viewed.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

if ($resume) {
    $context = context_module::instance($smartquest->cm->id);
    $anonymous = $smartquest->respondenttype == 'anonymous';
    $event = \mod_smartquest\event\attempt_resumed::create(array(
                    'objectid' => $smartquest->id,
                    'anonymous' => $anonymous,
                    'context' => $context
    ));
    $event->trigger();
}

global $DB;
// Generate the view HTML in the page.
$smartquest->view();


// Output the page.
echo $smartquest->renderer->header();
echo $smartquest->renderer->render($smartquest->page);
echo $smartquest->renderer->footer($course);
