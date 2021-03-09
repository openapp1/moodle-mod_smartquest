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

/**
 * prints the tabbed bar
 *
 * @package mod_smartquest
 * @copyright  2016 Mike Churchward (mike.churchward@poetgroup.org)
 * @author     Mike Churchward
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB, $SESSION;
$tabs = array();
$row  = array();
$inactive = array();
$activated = array();
if (!isset($SESSION->smartquest)) {
    $SESSION->smartquest = new stdClass();
}
$currenttab = $SESSION->smartquest->current_tab;

// In a smartquest instance created "using" a PUBLIC smartquest, prevent anyone from editing settings, editing questions,
// viewing all responses...except in the course where that PUBLIC smartquest was originally created.

$owner = !empty($smartquest->sid) && ($smartquest->survey->courseid == $smartquest->course->id);
if ($smartquest->capabilities->manage  && $owner) {
    $row[] = new tabobject('settings', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/qsettings.php?'.
            'id='.$smartquest->cm->id), get_string('advancedsettings'));
}

if ($smartquest->capabilities->editquestions && $owner) {
//Tamard
    //if($DB->record_exists('smartquest_anonymcourses', ['anonymcourse' => $smartquest->cm->course])) {
      //  $context = context_module::instance($smartquest->cm->id);
	//if(has_capability('block/smartquest:addquestionssmartquest', $context)) {
          //  $row[] = new tabobject('questions', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/questions.php?'.
//		     'id='.$smartquest->cm->id), get_string('questions', 'smartquest'));
        //}
   // }
}

if ($smartquest->capabilities->preview && $owner) {
    if (!empty($smartquest->questions)) {
        $row[] = new tabobject('preview', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/preview.php?'.
                        'id='.$smartquest->cm->id), get_string('preview_label', 'smartquest'));
    }
}

$usernumresp = $smartquest->count_submissions($USER->id);

if ($smartquest->capabilities->readownresponses && ($usernumresp > 0)) {
    $argstr = 'instance='.$smartquest->id.'&user='.$USER->id.'&group='.$currentgroupid;
    if ($usernumresp == 1) {
        $argstr .= '&byresponse=1&action=vresp';
        $yourrespstring = get_string('yourresponse', 'smartquest');
    } else {
        $yourrespstring = get_string('yourresponses', 'smartquest');
    }
    $row[] = new tabobject('myreport', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/myreport.php?'.
                           $argstr), $yourrespstring);

    if ($usernumresp > 1 && in_array($currenttab, array('mysummary', 'mybyresponse', 'myvall', 'mydownloadcsv'))) {
        $inactive[] = 'myreport';
        $activated[] = 'myreport';
        $row2 = array();
        $argstr2 = $argstr.'&action=summary';
        $row2[] = new tabobject('mysummary', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/myreport.php?'.$argstr2),
                                get_string('summary', 'smartquest'));
        $argstr2 = $argstr.'&byresponse=1&action=vresp';
        $row2[] = new tabobject('mybyresponse', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/myreport.php?'.$argstr2),
                                get_string('viewindividualresponse', 'smartquest'));
        $argstr2 = $argstr.'&byresponse=0&action=vall&group='.$currentgroupid;
        $row2[] = new tabobject('myvall', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/myreport.php?'.$argstr2),
                                get_string('myresponses', 'smartquest'));
        if ($smartquest->capabilities->downloadresponses) {
            $argstr2 = $argstr.'&action=dwnpg';
            $link  = $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2);
            $row2[] = new tabobject('mydownloadcsv', $link, get_string('downloadtext'));
        }
    } else if (in_array($currenttab, array('mybyresponse', 'mysummary'))) {
        $inactive[] = 'myreport';
        $activated[] = 'myreport';
    }
}

$numresp = $smartquest->count_submissions();
// Number of responses in currently selected group (or all participants etc.).
if (isset($SESSION->smartquest->numselectedresps)) {
    $numselectedresps = $SESSION->smartquest->numselectedresps;
} else {
    $numselectedresps = $numresp;
}

// If smartquest is set to separate groups, prevent user who is not member of any group
// to view All responses.
$canviewgroups = true;
$groupmode = groups_get_activity_groupmode($cm, $course);
if ($groupmode == 1) {
    $canviewgroups = groups_has_membership($cm, $USER->id);
}
$canviewallgroups = has_capability('moodle/site:accessallgroups', $context);

if (($canviewallgroups || ($canviewgroups && $smartquest->capabilities->readallresponseanytime))
                && $numresp > 0 && $owner && $numselectedresps > 0) {
    $argstr = 'instance='.$smartquest->id;
    $row[] = new tabobject('allreport', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.
                           $argstr.'&action=vall'), get_string('viewallresponses', 'smartquest'));
    if (in_array($currenttab, array('vall', 'vresp', 'valldefault', 'vallasort', 'vallarsort', 'deleteall', 'downloadcsv',
                                     'vrespsummary', 'individualresp', 'printresp', 'deleteresp'))) {
        $inactive[] = 'allreport';
        $activated[] = 'allreport';
        if ($currenttab == 'vrespsummary' || $currenttab == 'valldefault') {
            $inactive[] = 'vresp';
        }
        $row2 = array();
        $argstr2 = $argstr.'&action=vall&group='.$currentgroupid;
        $row2[] = new tabobject('vall', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2),
                                get_string('summary', 'smartquest'));
        if ($smartquest->capabilities->viewsingleresponse) {
            $argstr2 = $argstr.'&byresponse=1&action=vresp&group='.$currentgroupid;
            $row2[] = new tabobject('vrespsummary', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2),
                                get_string('viewbyresponse', 'smartquest'));
            if ($currenttab == 'individualresp' || $currenttab == 'deleteresp') {
                $argstr2 = $argstr.'&byresponse=1&action=vresp';
                $row2[] = new tabobject('vresp', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2),
                        get_string('viewindividualresponse', 'smartquest'));
            }
        }
    }
    if (in_array($currenttab, array('valldefault',  'vallasort', 'vallarsort', 'deleteall', 'downloadcsv'))) {
        $activated[] = 'vall';
        $row3 = array();

        $argstr2 = $argstr.'&action=vall&group='.$currentgroupid;
        $row3[] = new tabobject('valldefault', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2),
                                get_string('order_default', 'smartquest'));
        if ($currenttab != 'downloadcsv' && $currenttab != 'deleteall') {
            $argstr2 = $argstr.'&action=vallasort&group='.$currentgroupid;
            $row3[] = new tabobject('vallasort', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2),
                                    get_string('order_ascending', 'smartquest'));
            $argstr2 = $argstr.'&action=vallarsort&group='.$currentgroupid;
            $row3[] = new tabobject('vallarsort', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2),
                                    get_string('order_descending', 'smartquest'));
        }
        if ($smartquest->capabilities->deleteresponses) {
            $argstr2 = $argstr.'&action=delallresp&group='.$currentgroupid;
            $row3[] = new tabobject('deleteall', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2),
                                    get_string('deleteallresponses', 'smartquest'));
        }

        if ($smartquest->capabilities->downloadresponses) {
            $argstr2 = $argstr.'&action=dwnpg&group='.$currentgroupid;
            $link  = $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2);
            $row3[] = new tabobject('downloadcsv', $link, get_string('downloadtext'));
        }
    }

    if (in_array($currenttab, array('individualresp', 'deleteresp'))) {
        $inactive[] = 'vresp';
        if ($currenttab != 'deleteresp') {
            $activated[] = 'vresp';
        }
        if ($smartquest->capabilities->deleteresponses) {
            $argstr2 = $argstr.'&action=dresp&rid='.$rid.'&individualresponse=1';
            $row2[] = new tabobject('deleteresp', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2),
                            get_string('deleteresp', 'smartquest'));
        }

    }
} else if ($canviewgroups && $smartquest->capabilities->readallresponses && ($numresp > 0) && $canviewgroups &&
           // If resp_view is set to SMARTQUEST_STUDENTVIEWRESPONSES_NEVER, then this will always be false.
           ($smartquest->resp_view == SMARTQUEST_STUDENTVIEWRESPONSES_ALWAYS ||
            ($smartquest->resp_view == SMARTQUEST_STUDENTVIEWRESPONSES_WHENCLOSED
                && $smartquest->is_closed()) ||
            ($smartquest->resp_view == SMARTQUEST_STUDENTVIEWRESPONSES_WHENANSWERED
                && $usernumresp > 0 )) &&
           $smartquest->is_survey_owner()) {
    $argstr = 'instance='.$smartquest->id.'&sid='.$smartquest->sid;
    $row[] = new tabobject('allreport', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.
                           $argstr.'&action=vall&group='.$currentgroupid), get_string('viewallresponses', 'smartquest'));
    if (in_array($currenttab, array('valldefault',  'vallasort', 'vallarsort', 'deleteall', 'downloadcsv'))) {
        $inactive[] = 'vall';
        $activated[] = 'vall';
        $row2 = array();
        $argstr2 = $argstr.'&action=vall&group='.$currentgroupid;
        $row2[] = new tabobject('valldefault', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2),
                                get_string('summary', 'smartquest'));
        $inactive[] = $currenttab;
        $activated[] = $currenttab;
        $row3 = array();
        $argstr2 = $argstr.'&action=vall&group='.$currentgroupid;
        $row3[] = new tabobject('valldefault', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2),
                                get_string('order_default', 'smartquest'));
        $argstr2 = $argstr.'&action=vallasort&group='.$currentgroupid;
        $row3[] = new tabobject('vallasort', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2),
                                get_string('order_ascending', 'smartquest'));
        $argstr2 = $argstr.'&action=vallarsort&group='.$currentgroupid;
        $row3[] = new tabobject('vallarsort', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2),
                                get_string('order_descending', 'smartquest'));
        if ($smartquest->capabilities->deleteresponses) {
            $argstr2 = $argstr.'&action=delallresp';
            $row2[] = new tabobject('deleteall', $CFG->wwwroot.htmlspecialchars('/mod/smartquest/report.php?'.$argstr2),
                                    get_string('deleteallresponses', 'smartquest'));
        }

        if ($smartquest->capabilities->downloadresponses) {
            $argstr2 = $argstr.'&action=dwnpg';
            $link  = htmlspecialchars('/mod/smartquest/report.php?'.$argstr2);
            $row2[] = new tabobject('downloadcsv', $link, get_string('downloadtext'));
        }
        if (count($row2) <= 1) {
            $currenttab = 'allreport';
        }
    }
}

if ($smartquest->capabilities->viewsingleresponse && ($canviewallgroups || $canviewgroups)) {
    $nonrespondenturl = new moodle_url('/mod/smartquest/show_nonrespondents.php', array('id' => $smartquest->cm->id));
    $row[] = new tabobject('nonrespondents',
                    $nonrespondenturl->out(),
                    get_string('show_nonrespondents', 'smartquest'));
}

if ((count($row) > 1) || (!empty($row2) && (count($row2) > 1))) {
    $tabs[] = $row;

    if (!empty($row2) && (count($row2) > 1)) {
        $tabs[] = $row2;
    }

    if (!empty($row3) && (count($row3) > 1)) {
        $tabs[] = $row3;
    }

    $smartquest->page->add_to_page('tabsarea', print_tabs($tabs, $currenttab, $inactive, $activated, true));
}
