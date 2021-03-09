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

// This page displays a non-completable instance of smartquest.

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/smartquest/smartquest.class.php');

$id     = optional_param('id', 0, PARAM_INT);
$sid    = optional_param('sid', 0, PARAM_INT);
$popup  = optional_param('popup', 0, PARAM_INT);
$qid    = optional_param('qid', 0, PARAM_INT);
$currentgroupid = optional_param('group', 0, PARAM_INT); // Groupid.

if ($id) {
    if (! $cm = get_coursemodule_from_id('smartquest', $id)) {
        print_error('invalidcoursemodule');
    }

    if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
        print_error('coursemisconf');
    }

    if (! $smartquest = $DB->get_record("smartquest", array("id" => $cm->instance))) {
        print_error('invalidcoursemodule');
    }
} else {
    if (! $survey = $DB->get_record("smartquest_survey", array("id" => $sid))) {
        print_error('surveynotexists', 'smartquest');
    }
    if (! $course = $DB->get_record("course", ["id" => $survey->courseid])) {
        print_error('coursemisconf');
    }
    // Dummy smartquest object.
    $smartquest = new stdClass();
    $smartquest->id = 0;
    $smartquest->course = $course->id;
    $smartquest->name = $survey->title;
    $smartquest->sid = $sid;
    $smartquest->resume = 0;
    // Dummy cm object.
    if (!empty($qid)) {
        $cm = get_coursemodule_from_instance('smartquest', $qid, $course->id);
    } else {
        $cm = false;
    }
}

// Check login and get context.
// Do not require login if this smartquest is viewed from the Add smartquest page
// to enable teachers to view template or public smartquests located in a course where they are not enroled.
if (!$popup) {
    require_login($course->id, false, $cm);
}
$context = $cm ? context_module::instance($cm->id) : false;

$url = new moodle_url('/mod/smartquest/preview.php');
if ($id !== 0) {
    $url->param('id', $id);
}
if ($sid) {
    $url->param('sid', $sid);
}
$PAGE->set_url($url);

$PAGE->set_context($context);
$PAGE->set_cm($cm);   // CONTRIB-5872 - I don't know why this is needed.

$smartquest = new smartquest($qid, $smartquest, $course, $cm);

// Add renderer and page objects to the smartquest object for display use.
$smartquest->add_renderer($PAGE->get_renderer('mod_smartquest'));
$smartquest->add_page(new \mod_smartquest\output\previewpage());

$canpreview = (!isset($smartquest->capabilities) &&
               has_capability('mod/smartquest:preview', context_course::instance($course->id))) ||
              (isset($smartquest->capabilities) && $smartquest->capabilities->preview);
if (!$canpreview && !$popup) {
    // Should never happen, unless called directly by a snoop...
    print_error('nopermissions', 'smartquest', $CFG->wwwroot.'/mod/smartquest/view.php?id='.$cm->id);
}

if (!isset($SESSION->smartquest)) {
    $SESSION->smartquest = new stdClass();
}
$SESSION->smartquest->current_tab = new stdClass();
$SESSION->smartquest->current_tab = 'preview';

$qp = get_string('preview_smartquest', 'smartquest');
$pq = get_string('previewing', 'smartquest');

// Print the page header.
if ($popup) {
    $PAGE->set_pagelayout('popup');
}
$PAGE->set_title(format_string($qp));
if (!$popup) {
    $PAGE->set_heading(format_string($course->fullname));
}

// Include the needed js.


$PAGE->requires->js('/mod/smartquest/module.js');
// Print the tabs.


echo $smartquest->renderer->header();
if (!$popup) {
    require('tabs.php');
}
$smartquest->page->add_to_page('heading', clean_text($pq));

if ($smartquest->capabilities->printblank) {
    // Open print friendly as popup window.

    $linkname = '&nbsp;'.get_string('printblank', 'smartquest');
    $title = get_string('printblanktooltip', 'smartquest');
    $url = '/mod/smartquest/print.php?qid='.$smartquest->id.'&amp;rid=0&amp;'.'courseid='.
            $smartquest->course->id.'&amp;sec=1';
    $options = array('menubar' => true, 'location' => false, 'scrollbars' => true, 'resizable' => true,
                    'height' => 600, 'width' => 800, 'title' => $title);
    $name = 'popup';
    $link = new moodle_url($url);
    $action = new popup_action('click', $link, $name, $options);
    $class = "floatprinticon";
    $smartquest->page->add_to_page('printblank',
        $smartquest->renderer->action_link($link, $linkname, $action, array('class' => $class, 'title' => $title),
            new pix_icon('t/print', $title)));
}
$smartquest->survey_print_render('', 'preview', $course->id, $rid = 0, $popup);
if ($popup) {
    $smartquest->page->add_to_page('closebutton', $smartquest->renderer->close_window_button());
}
echo $smartquest->renderer->render($smartquest->page);
echo $smartquest->renderer->footer($course);

// Log this smartquest preview.
$context = context_module::instance($smartquest->cm->id);
$anonymous = $smartquest->respondenttype == 'anonymous';

$event = \mod_smartquest\event\smartquest_previewed::create(array(
                'objectid' => $smartquest->id,
                'anonymous' => $anonymous,
                'context' => $context
));
$event->trigger();
