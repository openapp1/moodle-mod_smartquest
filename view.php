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
require_once($CFG->dirroot.'/mod/smartquest/locallib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot.'/mod/smartquest/smartquest.class.php');

if (!isset($SESSION->smartquest)) {
    $SESSION->smartquest = new stdClass();
}
$SESSION->smartquest->current_tab = 'view';

$id = optional_param('id', null, PARAM_INT);    // Course Module ID.
$a = optional_param('a', null, PARAM_INT);      // Or smartquest ID.

$sid = optional_param('sid', null, PARAM_INT);  // Survey id.

list($cm, $course, $smartquest) = smartquest_get_standard_page_items($id, $a);

// Check login and get context.
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);

$url = new moodle_url($CFG->wwwroot.'/mod/smartquest/view.php');
if (isset($id)) {
    $url->param('id', $id);
} else {
    $url->param('a', $a);
}
if (isset($sid)) {
    $url->param('sid', $sid);
}

$PAGE->set_url($url);
$PAGE->set_context($context);
$smartquest = new smartquest(0, $smartquest, $course, $cm);
// Add renderer and page objects to the smartquest object for display use.
$smartquest->add_renderer($PAGE->get_renderer('mod_smartquest'));
$smartquest->add_page(new \mod_smartquest\output\viewpage());

$PAGE->set_title(format_string($smartquest->name));
$PAGE->set_heading(format_string($course->fullname));

echo $smartquest->renderer->header();
$smartquest->page->add_to_page('smartquestname', format_string($smartquest->name));

// Print the main part of the page.
if ($smartquest->intro) {
    $smartquest->page->add_to_page('intro', format_module_intro('smartquest', $smartquest, $cm->id));
}

$cm = $smartquest->cm;
$currentgroupid = groups_get_activity_group($cm);
if (!groups_is_member($currentgroupid, $USER->id)) {
    $currentgroupid = 0;
}

if (!$smartquest->is_active()) {
    if ($smartquest->capabilities->manage) {
        $msg = 'removenotinuse';
    } else {
        $msg = 'notavail';
    }
    $smartquest->page->add_to_page('message', get_string($msg, 'smartquest'));

} else if ($smartquest->survey->realm == 'template') {
    // If this is a template survey, notify and exit.
    $smartquest->page->add_to_page('message', get_string('templatenotviewable', 'smartquest'));
    echo $smartquest->renderer->render($smartquest->page);
    echo $smartquest->renderer->footer($smartquest->course);
    exit();

} else if (!$smartquest->is_open()) {
    $smartquest->page->add_to_page('message', get_string('notopen', 'smartquest', userdate($smartquest->opendate)));

} else if ($smartquest->is_closed()) {
    $smartquest->page->add_to_page('message', get_string('closed', 'smartquest', userdate($smartquest->closedate)));

} else if (!$smartquest->user_is_eligible($USER->id)) {
    if ($smartquest->questions) {
        $smartquest->page->add_to_page('message', get_string('noteligible', 'smartquest'));
    }

} else if(!$smartquest->is_complete_definition()) {
    $smartquest->page->add_to_page('message', '<h5><b>' . get_string('notcompletedefinition', 'smartquest') . '</b></h5>');
} else if (!$smartquest->user_can_take($USER->id)) {
    switch ($smartquest->qtype) {
        case SMARTQUESTDAILY:
            $msgstring = ' '.get_string('today', 'smartquest');
            break;
        case SMARTQUESTWEEKLY:
            $msgstring = ' '.get_string('thisweek', 'smartquest');
            break;
        case SMARTQUESTMONTHLY:
            $msgstring = ' '.get_string('thismonth', 'smartquest');
            break;
        default:
            $msgstring = '';
            break;
    }
    $smartquest->page->add_to_page('message', get_string("alreadyfilled", "smartquest", $msgstring));

} else if ($smartquest->user_can_take($USER->id)) {
    if ($smartquest->questions) { // Sanity check.
        if (!$smartquest->user_has_saved_response($USER->id)) {
            if (($smartquest->rtype != STUDEFFECT && $smartquest->rtype != GUIDELINE) || count(smartquest_get_studeffect_users($smartquest->survey->id))) {
                $smartquest->page->add_to_page('complete',
                    '<h4>' . $smartquest->get_rtype_text() . '</h4>' . 
                    '<a href="'.$CFG->wwwroot.htmlspecialchars('/mod/smartquest/complete.php?' .
                    'id='.$smartquest->cm->id).'">'.get_string('answerquestions', 'smartquest').'</a>');
            } else {
                $smartquest->page->add_to_page('complete',
                    '<h4>' . $smartquest->get_rtype_text() . '</h4>' . 
                    '<h6>' . get_string('allstudeffectcomplete', 'smartquest') . '</h6>');
            }
        } else if(($smartquest->rtype != STUDEFFECT && $smartquest->rtype != GUIDELINE) || count(smartquest_get_studeffect_users($smartquest->survey->id))) {
            $smartquest->page->add_to_page('complete',
                '<a href="'.$CFG->wwwroot.htmlspecialchars('/mod/smartquest/complete.php?' .
                'id='.$smartquest->cm->id.'&resume=1').'">'.get_string('resumesurvey', 'smartquest').'</a>');
        }
    } else {
        $smartquest->page->add_to_page('message', get_string('noneinuse', 'smartquest'));
    }
}

if ($smartquest->capabilities->editquestions && !$smartquest->questions && $smartquest->is_active()) {
    $smartquest->page->add_to_page('complete',
        '<a href="'.$CFG->wwwroot.htmlspecialchars('/mod/smartquest/questions.php?'.
        'id='.$smartquest->cm->id).'">'.'<strong>'.get_string('addquestions', 'smartquest').'</strong></a>');
}

if (isguestuser()) {
    $guestno = html_writer::tag('p', get_string('noteligible', 'smartquest'));
    $liketologin = html_writer::tag('p', get_string('liketologin'));
    $smartquest->page->add_to_page('guestuser',
        $smartquest->renderer->confirm($guestno."\n\n".$liketologin."\n", get_login_url(), get_local_referer(false)));
}

// Log this course module view.
// Needed for the event logging.
$context = context_module::instance($smartquest->cm->id);
$anonymous = $smartquest->respondenttype == 'anonymous';

$event = \mod_smartquest\event\course_module_viewed::create(array(
                'objectid' => $smartquest->id,
                'anonymous' => $anonymous,
                'context' => $context
));
$event->trigger();

$usernumresp = $smartquest->count_submissions($USER->id);

if ($smartquest->capabilities->readownresponses && ($usernumresp > 0)) {
    $argstr = 'instance='.$smartquest->id.'&user='.$USER->id;
    if ($usernumresp > 1) {
        $titletext = get_string('viewyourresponses', 'smartquest', $usernumresp);
    } else {
        $titletext = get_string('yourresponse', 'smartquest');
        $argstr .= '&byresponse=1&action=vresp';
    }
    $smartquest->page->add_to_page('yourresponse',
        '<a href="'.$CFG->wwwroot.htmlspecialchars('/mod/smartquest/myreport.php?'.$argstr).'">'.$titletext.'</a>');
}

if ($smartquest->can_view_all_responses($usernumresp) && has_capability('mod/smartquest:readallresponseanytime', $context)) {
    $argstr = 'instance='.$smartquest->id.'&group='.$currentgroupid;
    $smartquest->page->add_to_page('allresponses',
        '<a href="'.$CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr).'">'.
        get_string('viewallresponses', 'smartquest').'</a>');
}

echo $smartquest->renderer->render($smartquest->page);
echo $smartquest->renderer->footer();
