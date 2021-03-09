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

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/smartquest/smartquest.class.php');

$qid = required_param('qid', PARAM_INT);
$rid = required_param('rid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);
$sec = required_param('sec', PARAM_INT);
$null = null;
$referer = $CFG->wwwroot.'/mod/smartquest/report.php';

if (! $smartquest = $DB->get_record("smartquest", array("id" => $qid))) {
    print_error('invalidcoursemodule');
}
if (! $course = $DB->get_record("course", array("id" => $smartquest->course))) {
    print_error('coursemisconf');
}
if (! $cm = get_coursemodule_from_instance("smartquest", $smartquest->id, $course->id)) {
    print_error('invalidcoursemodule');
}

// Check login and get context.
require_login($courseid);

$smartquest = new smartquest(0, $smartquest, $course, $cm);

// Add renderer and page objects to the smartquest object for display use.
$smartquest->add_renderer($PAGE->get_renderer('mod_smartquest'));
if (!empty($rid)) {
    $smartquest->add_page(new \mod_smartquest\output\reportpage());
} else {
    $smartquest->add_page(new \mod_smartquest\output\previewpage());
}

// If you can't view the smartquest, or can't view a specified response, error out.
if (!($smartquest->capabilities->view && (($rid == 0) || $smartquest->can_view_response($rid)))) {
    // Should never happen, unless called directly by a snoop...
    print_error('nopermissions', 'moodle', $CFG->wwwroot.'/mod/smartquest/view.php?id='.$cm->id);
}
$blanksmartquest = true;
if ($rid != 0) {
    $blanksmartquest = false;
}
$url = new moodle_url($CFG->wwwroot.'/mod/smartquest/print.php');
$url->param('qid', $qid);
$url->param('rid', $rid);
$url->param('courseid', $courseid);
$url->param('sec', $sec);
$PAGE->set_url($url);
$PAGE->set_title($smartquest->survey->title);
$PAGE->set_pagelayout('popup');
echo $smartquest->renderer->header();
$smartquest->page->add_to_page('closebutton', $smartquest->renderer->close_window_button());
$smartquest->survey_print_render('', 'print', $courseid, $rid, $blanksmartquest);
echo $smartquest->renderer->render($smartquest->page);
echo $smartquest->renderer->footer();
