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

$instance = optional_param('instance', false, PARAM_INT);   // Smsrtquest ID.
$action = optional_param('action', 'vall', PARAM_ALPHA);
$sid = optional_param('sid', null, PARAM_INT);              // Survey id.
$rid = optional_param('rid', false, PARAM_INT);
$type = optional_param('type', '', PARAM_ALPHA);
$byresponse = optional_param('byresponse', false, PARAM_INT);
$individualresponse = optional_param('individualresponse', false, PARAM_INT);
$currentgroupid = optional_param('group', 0, PARAM_INT); // Groupid.
$user = optional_param('user', '', PARAM_INT);
$userid = $USER->id;
switch ($action) {
    case 'vallasort':
        $sort = 'ascending';
       break;
    case 'vallarsort':
        $sort = 'descending';
       break;
    default:
        $sort = 'default';
}

if ($instance === false) {
    if (!empty($SESSION->instance)) {
        $instance = $SESSION->instance;
    } else {
        print_error('requiredparameter', 'smartquest');
    }
}
$SESSION->instance = $instance;
$usergraph = get_config('smartquest', 'usergraph');

if (! $smartquest = $DB->get_record("smartquest", array("id" => $instance))) {
    print_error('incorrectsmartquest', 'smartquest');
}
if (! $course = $DB->get_record("course", array("id" => $smartquest->course))) {
    print_error('coursemisconf');
}
if (! $cm = get_coursemodule_from_instance("smartquest", $smartquest->id, $course->id)) {
    print_error('invalidcoursemodule');
}

require_course_login($course, true, $cm);

$smartquest = new smartquest(0, $smartquest, $course, $cm);

// Add renderer and page objects to the smartquest object for display use.
$smartquest->add_renderer($PAGE->get_renderer('mod_smartquest'));
$smartquest->add_page(new \mod_smartquest\output\reportpage());

// If you can't view the smartquest, or can't view a specified response, error out.
$context = context_module::instance($cm->id);
if (!has_capability('mod/smartquest:readallresponseanytime', $context) &&
  !($smartquest->capabilities->view && $smartquest->can_view_response($rid))) {
    // Should never happen, unless called directly by a snoop...
    print_error('nopermissions', 'moodle', $CFG->wwwroot.'/mod/smartquest/view.php?id='.$cm->id);
}

$smartquest->canviewallgroups = has_capability('moodle/site:accessallgroups', $context);
$sid = $smartquest->survey->id;

$url = new moodle_url($CFG->wwwroot.'/mod/smartquest/report.php');
if ($instance) {
    $url->param('instance', $instance);
}

$url->param('action', $action);

if ($type) {
    $url->param('type', $type);
}
if ($byresponse || $individualresponse) {
    $url->param('byresponse', 1);
}
if ($user) {
    $url->param('user', $user);
}
if ($action == 'dresp') {
    $url->param('action', 'dresp');
    $url->param('byresponse', 1);
    $url->param('rid', $rid);
    $url->param('individualresponse', 1);
}
if ($currentgroupid !== null) {
    $url->param('group', $currentgroupid);
}

$PAGE->set_url($url);
$PAGE->set_context($context);

// Tab setup.
if (!isset($SESSION->smartquest)) {
    $SESSION->smartquest = new stdClass();
}
$SESSION->smartquest->current_tab = 'allreport';

// Get all responses for further use in viewbyresp and deleteall etc.
// All participants.
$params = array('survey_id' => $sid, 'complete' => 'y');
$respsallparticipants = $DB->get_records('smartquest_response', $params, 'id', 'id,survey_id,submitted,userid');
$SESSION->smartquest->numrespsallparticipants = count ($respsallparticipants);
$SESSION->smartquest->numselectedresps = $SESSION->smartquest->numrespsallparticipants;

// Available group modes (0 = no groups; 1 = separate groups; 2 = visible groups).
$groupmode = groups_get_activity_groupmode($cm, $course);
$smartquestgroups = '';
$groupscount = 0;
$SESSION->smartquest->respscount = 0;
$SESSION->smartquest_survey_id = $sid;

if ($groupmode > 0) {
    if ($groupmode == 1) {
        $smartquestgroups = groups_get_all_groups($course->id, $userid);
    }
    if ($groupmode == 2 || $smartquest->canviewallgroups) {
        $smartquestgroups = groups_get_all_groups($course->id);
    }

    if (!empty($smartquestgroups)) {
        $groupscount = count($smartquestgroups);
        foreach ($smartquestgroups as $key) {
            $firstgroupid = $key->id;
            break;
        }
        if ($groupscount === 0 && $groupmode == 1) {
            $currentgroupid = 0;
        }
        if ($groupmode == 1 && !$smartquest->canviewallgroups && $currentgroupid == 0) {
            $currentgroupid = $firstgroupid;
        }
    } else {
        // Groupmode = separate groups but user is not member of any group
        // and does not have moodle/site:accessallgroups capability -> refuse view responses.
        if (!$smartquest->canviewallgroups) {
            $currentgroupid = 0;
        }
    }

    if ($currentgroupid > 0) {
        $groupname = get_string('group').' <strong>'.groups_get_group_name($currentgroupid).'</strong>';
    } else {
        $groupname = '<strong>'.get_string('allparticipants').'</strong>';
    }
}
if ($usergraph) {
    $charttype = $smartquest->survey->chart_type;
    if ($charttype) {
        $PAGE->requires->js('/mod/smartquest/javascript/RGraph/RGraph.common.core.js');

        switch ($charttype) {
            case 'bipolar':
                $PAGE->requires->js('/mod/smartquest/javascript/RGraph/RGraph.bipolar.js');
                break;
            case 'hbar':
                $PAGE->requires->js('/mod/smartquest/javascript/RGraph/RGraph.hbar.js');
                break;
            case 'radar':
                $PAGE->requires->js('/mod/smartquest/javascript/RGraph/RGraph.radar.js');
                break;
            case 'rose':
                $PAGE->requires->js('/mod/smartquest/javascript/RGraph/RGraph.rose.js');
                break;
            case 'vprogress':
                $PAGE->requires->js('/mod/smartquest/javascript/RGraph/RGraph.vprogress.js');
                break;
        }
    }
}

switch ($action) {

    case 'dresp':  // Delete individual response? Ask for confirmation.

        require_capability('mod/smartquest:deleteresponses', $context);

        if (empty($smartquest->survey)) {
            $id = $smartquest->survey;
            notify ("smartquest->survey = /$id/");
            print_error('surveynotexists', 'smartquest');
        } else if ($smartquest->survey->courseid != $course->id) {
            print_error('surveyowner', 'smartquest');
        } else if (!$rid || !is_numeric($rid)) {
            print_error('invalidresponse', 'smartquest');
        } else if (!($resp = $DB->get_record('smartquest_response', array('id' => $rid)))) {
            print_error('invalidresponserecord', 'smartquest');
        }

        $ruser = false;
        if (!empty($resp->userid)) {
            if ($user = $DB->get_record('user', ['id' => $resp->userid])) {
                $ruser = fullname($user);
            } else {
                $ruser = '- '.get_string('unknown', 'smartquest').' -';
            }
        } else {
            $ruser = $resp->userid;
        }

        // Print the page header.
        $PAGE->set_title(get_string('deletingresp', 'smartquest'));
        $PAGE->set_heading(format_string($course->fullname));
        echo $smartquest->renderer->header();

        // Print the tabs.
        $SESSION->smartquest->current_tab = 'deleteresp';
        include('tabs.php');

        $timesubmitted = '<br />'.get_string('submitted', 'smartquest').'&nbsp;'.userdate($resp->submitted);
        if ($smartquest->respondenttype == 'anonymous') {
                $ruser = '- '.get_string('anonymous', 'smartquest').' -';
                $timesubmitted = '';
        }

        // Print the confirmation.
        $msg = '<div class="warning centerpara">';
        $msg .= get_string('confirmdelresp', 'smartquest', $ruser.$timesubmitted);
        $msg .= '</div>';
        $urlyes = new moodle_url('report.php', array('action' => 'dvresp',
                'rid' => $rid, 'individualresponse' => 1, 'instance' => $instance, 'group' => $currentgroupid));
        $urlno = new moodle_url('report.php', array('action' => 'vresp', 'instance' => $instance,
                'rid' => $rid, 'individualresponse' => 1, 'group' => $currentgroupid));
        $buttonyes = new single_button($urlyes, get_string('delete'), 'post');
        $buttonno = new single_button($urlno, get_string('cancel'), 'get');
        $smartquest->page->add_to_page('notifications', $smartquest->renderer->confirm($msg, $buttonyes, $buttonno));
        echo $smartquest->renderer->render($smartquest->page);
        // Finish the page.
        echo $smartquest->renderer->footer($course);
        break;

    case 'delallresp': // Delete all responses? Ask for confirmation.
        require_capability('mod/smartquest:deleteresponses', $context);

        if ($DB->count_records('smartquest_response', array('survey_id' => $sid, 'complete' => 'y'))) {

            // Print the page header.
            $PAGE->set_title(get_string('deletingresp', 'smartquest'));
            $PAGE->set_heading(format_string($course->fullname));
            echo $smartquest->renderer->header();

            // Print the tabs.
            $SESSION->smartquest->current_tab = 'deleteall';
            include('tabs.php');

            // Print the confirmation.
            $msg = '<div class="warning centerpara">';
            if ($groupmode == 0) {   // No groups or visible groups.
                $msg .= get_string('confirmdelallresp', 'smartquest');
            } else {                 // Separate groups.
                $msg .= get_string('confirmdelgroupresp', 'smartquest', $groupname);
            }
            $msg .= '</div>';

            $urlyes = new moodle_url('report.php', array('action' => 'dvallresp', 'sid' => $sid,
                             'instance' => $instance, 'group' => $currentgroupid));
            $urlno = new moodle_url('report.php', array('instance' => $instance, 'group' => $currentgroupid));
            $buttonyes = new single_button($urlyes, get_string('delete'), 'post');
            $buttonno = new single_button($urlno, get_string('cancel'), 'get');

            $smartquest->page->add_to_page('notifications', $smartquest->renderer->confirm($msg, $buttonyes, $buttonno));
            echo $smartquest->renderer->render($smartquest->page);
            // Finish the page.
            echo $smartquest->renderer->footer($course);
        }
        break;

    case 'dvresp': // Delete single response. Do it!

        require_capability('mod/smartquest:deleteresponses', $context);

        if (empty($smartquest->survey)) {
            print_error('surveynotexists', 'smartquest');
        } else if ($smartquest->survey->courseid != $course->id) {
            print_error('surveyowner', 'smartquest');
        } else if (!$rid || !is_numeric($rid)) {
            print_error('invalidresponse', 'smartquest');
        } else if (!($response = $DB->get_record('smartquest_response', array('id' => $rid)))) {
            print_error('invalidresponserecord', 'smartquest');
        }

        if (smartquest_delete_response($response, $smartquest)) {
            if (!$DB->count_records('smartquest_response', array('survey_id' => $sid, 'complete' => 'y'))) {
                $redirection = $CFG->wwwroot.'/mod/smartquest/view.php?id='.$cm->id;
            } else {
                $redirection = $CFG->wwwroot.'/mod/smartquest/report.php?action=vresp&amp;instance='.
                    $instance.'&amp;byresponse=1';
            }

            // Log this smartquest delete single response action.
            $params = array('objectid' => $smartquest->survey->id,
                            'context' => $smartquest->context,
                            'courseid' => $smartquest->course->id,
                            'relateduserid' => $response->userid);
            $event = \mod_smartquest\event\response_deleted::create($params);
            $event->trigger();

            redirect($redirection);
        } else {
            if ($smartquest->respondenttype == 'anonymous') {
                    $ruser = '- '.get_string('anonymous', 'smartquest').' -';
            } else if (!empty($response->userid)) {
                if ($user = $DB->get_record('user', ['id' => $response->userid])) {
                    $ruser = fullname($user);
                } else {
                    $ruser = '- '.get_string('unknown', 'smartquest').' -';
                }
            } else {
                $ruser = $response->userid;
            }
            error (get_string('couldnotdelresp', 'smartquest').$rid.get_string('by', 'smartquest').$ruser.'?',
                   $CFG->wwwroot.'/mod/smartquest/report.php?action=vresp&amp;sid='.$sid.'&amp;&amp;instance='.
                   $instance.'byresponse=1');
        }
        break;

    case 'dvallresp': // Delete all responses in smartquest (or group). Do it!

        require_capability('mod/smartquest:deleteresponses', $context);

        if (empty($smartquest->survey)) {
            print_error('surveynotexists', 'smartquest');
        } else if ($smartquest->survey->courseid != $course->id) {
            print_error('surveyowner', 'smartquest');
        }

        // Available group modes (0 = no groups; 1 = separate groups; 2 = visible groups).
        if ($groupmode > 0) {
            switch ($currentgroupid) {
                case 0:     // All participants.
                    $resps = $respsallparticipants;
                    break;
                default:     // Members of a specific group.
                    $sql = "SELECT r.id, r.survey_id, r.submitted, r.userid
                        FROM {smartquest_response} r,
                            {groups_members} gm
                         WHERE r.survey_id = ? AND
                           r.complete ='y' AND
                           gm.groupid = ? AND r.userid = gm.userid
                        ORDER BY r.id";
                    if (!($resps = $DB->get_records_sql($sql, array($sid, $currentgroupid)))) {
                        $resps = array();
                    }
            }
            if (empty($resps)) {
                $noresponses = true;
            } else {
                if ($rid === false) {
                    $resp = current($resps);
                    $rid = $resp->id;
                } else {
                    $resp = $DB->get_record('smartquest_response', array('id' => $rid));
                }
                if (!empty($resp->userid)) {
                    if ($user = $DB->get_record('user', ['id' => $resp->userid])) {
                        $ruser = fullname($user);
                    } else {
                        $ruser = '- '.get_string('unknown', 'smartquest').' -';
                    }
                } else {
                    $ruser = $resp->userid;
                }
            }
        } else {
            $resps = $respsallparticipants;
        }

        if (!empty($resps)) {
            foreach ($resps as $response) {
                smartquest_delete_response($response, $smartquest);
            }
            if (!$DB->count_records('smartquest_response', array('survey_id' => $sid, 'complete' => 'y'))) {
                $redirection = $CFG->wwwroot.'/mod/smartquest/view.php?id='.$cm->id;
            } else {
                $redirection = $CFG->wwwroot.'/mod/smartquest/report.php?action=vall&amp;sid='.$sid.'&amp;instance='.$instance;
            }

            // Log this smartquest delete all responses action.
            $context = context_module::instance($smartquest->cm->id);
            $anonymous = $smartquest->respondenttype == 'anonymous';

            $event = \mod_smartquest\event\all_responses_deleted::create(array(
                            'objectid' => $smartquest->id,
                            'anonymous' => $anonymous,
                            'context' => $context
            ));
            $event->trigger();

            redirect($redirection);
        } else {
            error (get_string('couldnotdelresp', 'smartquest'),
                   $CFG->wwwroot.'/mod/smartquest/report.php?action=vall&amp;sid='.$sid.'&amp;instance='.$instance);
        }
        break;

    case 'dwnpg': // Download page options.

        require_capability('mod/smartquest:downloadresponses', $context);

        $PAGE->set_title(get_string('smartquestreport', 'smartquest'));
        $PAGE->set_heading(format_string($course->fullname));
        echo $smartquest->renderer->header();

        // Print the tabs.
        // Tab setup.
        if (empty($user)) {
            $SESSION->smartquest->current_tab = 'downloadcsv';
        } else {
            $SESSION->smartquest->current_tab = 'mydownloadcsv';
        }

        include('tabs.php');

        $groupname = '';
        if ($groupmode > 0) {
            switch ($currentgroupid) {
                case 0:     // All participants.
                    $groupname = get_string('allparticipants');
                    break;
                default:     // Members of a specific group.
                    $groupname = get_string('membersofselectedgroup', 'group').' '.get_string('group').' '.
                        $smartquestgroups[$currentgroupid]->name;
            }
        }
        $output = '';
        $output .= "<br /><br />\n";
        $output .= $smartquest->renderer->help_icon('downloadtextformat', 'smartquest');
        $output .= '&nbsp;'.(get_string('downloadtext')).':&nbsp;'.get_string('responses', 'smartquest').'&nbsp;'.$groupname;
        $output .= $smartquest->renderer->heading(get_string('textdownloadoptions', 'smartquest'));
        $output .= $smartquest->renderer->box_start();
        $output .= "<form action=\"{$CFG->wwwroot}/mod/smartquest/report.php\" method=\"GET\">\n";
        $output .= "<input type=\"hidden\" name=\"instance\" value=\"$instance\" />\n";
        $output .= "<input type=\"hidden\" name=\"user\" value=\"$user\" />\n";
        $output .= "<input type=\"hidden\" name=\"sid\" value=\"$sid\" />\n";
        $output .= "<input type=\"hidden\" name=\"action\" value=\"dcsv\" />\n";
        $output .= "<input type=\"hidden\" name=\"group\" value=\"$currentgroupid\" />\n";
        $output .= html_writer::checkbox('choicecodes', 1, true, get_string('includechoicecodes', 'smartquest'));
        $output .= "<br />\n";
        $output .= html_writer::checkbox('choicetext', 1, true, get_string('includechoicetext', 'smartquest'));
        $output .= "<br />\n";
        $output .= "<br />\n";
        $output .= "<input type=\"submit\" name=\"submit\" value=\"".get_string('download', 'smartquest')."\" />\n";
        $output .= "</form>\n";
        $output .= $smartquest->renderer->box_end();

        $smartquest->page->add_to_page('respondentinfo', $output);
        echo $smartquest->renderer->render($smartquest->page);

        echo $smartquest->renderer->footer('none');

        // Log saved as text action.
        $params = array('objectid' => $smartquest->id,
                        'context' => $smartquest->context,
                        'courseid' => $course->id,
                        'other' => array('action' => $action, 'instance' => $instance, 'currentgroupid' => $currentgroupid)
        );
        $event = \mod_smartquest\event\all_responses_saved_as_text::create($params);
        $event->trigger();

        exit();
        break;

    case 'dcsv': // Download responses data as text (cvs) format.
        require_capability('mod/smartquest:downloadresponses', $context);

        // Use the smartquest name as the file name. Clean it and change any non-filename characters to '_'.
        $name = clean_param($smartquest->name, PARAM_FILE);
        $name = preg_replace("/[^A-Z0-9]+/i", date('d-m-y')."", trim($name));

        $choicecodes = optional_param('choicecodes', '0', PARAM_INT);
        $choicetext  = optional_param('choicetext', '0', PARAM_INT);
        
        $output = $smartquest->generate_csv('', $user, $choicecodes, $choicetext, $currentgroupid);
        //print_r($output);die;
        // Generate xl file.
        to_xl($output, $name);

        // CSV
        // SEP. 2007 JR changed file extension to *.txt for non-English Excel users' sake
        // and changed separator to tabulation
        // JAN. 2008 added \r carriage return for better Windows implementation.

        // header("Content-Disposition: attachment; filename=$name.csv");
        // header("Content-Type: text/comma-separated-values");
        foreach ($output as $row) {
            $text = implode("\t", $row);
            echo $text."\r\n";
        }
        exit();
        break;

    case 'vall':         // View all responses.
    case 'vallasort':    // View all responses sorted in ascending order.
    case 'vallarsort':   // View all responses sorted in descending order.

        $PAGE->set_title(get_string('smartquestreport', 'smartquest'));
        $PAGE->set_heading(format_string($course->fullname));
        echo $smartquest->renderer->header();
        if (!$smartquest->capabilities->readallresponses && !$smartquest->capabilities->readallresponseanytime) {
            // Should never happen, unless called directly by a snoop.
            print_error('nopermissions', '', '', get_string('viewallresponses', 'smartquest'));
            // Finish the page.
            echo $smartquest->renderer->footer($course);
            break;
        }

        // Print the tabs.
        switch ($action) {
            case 'vallasort':
                $SESSION->smartquest->current_tab = 'vallasort';
                break;
            case 'vallarsort':
                $SESSION->smartquest->current_tab = 'vallarsort';
                break;
            default:
                $SESSION->smartquest->current_tab = 'valldefault';
        }
        include('tabs.php');

        $respinfo = '';
        $resps = array();
        // Enable choose_group if there are smartquest groups and groupmode is not set to "no groups"
        // and if there are more goups than 1 (or if user can view all groups).
        if (is_array($smartquestgroups) && $groupmode > 0) {
            $groupselect = groups_print_activity_menu($cm, $url->out(), true);
            // Count number of responses in each group.
            foreach ($smartquestgroups as $group) {
                $sql = 'SELECT COUNT(r.id) ' .
                       'FROM {smartquest_response} r ' .
                       'INNER JOIN {groups_members} gm ON r.userid = gm.userid ' .
                       'WHERE r.survey_id = ? AND r.complete = ? AND gm.groupid = ?';
                $respscount = $DB->count_records_sql($sql, array($sid, 'y', $group->id));
                $thisgroupname = groups_get_group_name($group->id);
                $escapedgroupname = preg_quote($thisgroupname, '/');
                if (!empty ($respscount)) {
                    // Add number of responses to name of group in the groups select list.
                    $groupselect = preg_replace('/\<option value="'.$group->id.'">'.$escapedgroupname.'<\/option>/',
                        '<option value="'.$group->id.'">'.$thisgroupname.' ('.$respscount.')</option>', $groupselect);
                } else {
                    // Remove groups with no responses from the groups select list.
                    $groupselect = preg_replace('/\<option value="'.$group->id.'">'.$escapedgroupname.
                            '<\/option>/', '', $groupselect);
                }
            }
            $respinfo .= isset($groupselect) ? ($groupselect . ' ') : '';
            $currentgroupid = groups_get_activity_group($cm);
        }
        if ($currentgroupid > 0) {
             $groupname = get_string('group').': <strong>'.groups_get_group_name($currentgroupid).'</strong>';
        } else {
            $groupname = '<strong>'.get_string('allparticipants').'</strong>';
        }

        // Available group modes (0 = no groups; 1 = separate groups; 2 = visible groups).
        if ($groupmode > 0) {
            switch ($currentgroupid) {
                case 0:     // All participants.
                    $resps = $respsallparticipants;
                    break;
                default:     // Members of a specific group.
                    $sql = 'SELECT r.id, gm.id as groupid ' .
                           'FROM {smartquest_response} r ' .
                           'INNER JOIN {groups_members} gm ON r.userid = gm.userid ' .
                           'WHERE r.survey_id = ? AND r.complete = ? AND gm.groupid = ?';
                    if (!($resps = $DB->get_records_sql($sql, array($sid, 'y', $currentgroupid)))) {
                        $resps = '';
                    }
            }
            if (empty($resps)) {
                $noresponses = true;
            }
        } else {
            $resps = $respsallparticipants;
        }
        if (!empty($resps)) {
            // NOTE: response_analysis uses $resps to get the id's of the responses only.
            // Need to figure out what this function does.
            $feedbackmessages = $smartquest->response_analysis($rid = 0, $resps, $compare = false,
                $isgroupmember = false, $allresponses = true, $currentgroupid);

            if ($feedbackmessages) {
                $msgout = '';
                foreach ($feedbackmessages as $msg) {
                    $msgout .= $msg;
                }
                $smartquest->page->add_to_page('feedbackmessages', $msgout);
            }
        }

        $params = array('objectid' => $smartquest->id,
                        'context' => $context,
                        'courseid' => $course->id,
                        'other' => array('action' => $action, 'instance' => $instance, 'groupid' => $currentgroupid)
        );
        $event = \mod_smartquest\event\all_responses_viewed::create($params);
        $event->trigger();

        $respinfo .= get_string('viewallresponses', 'smartquest').'. '.$groupname.'. ';
        $strsort = get_string('order_'.$sort, 'smartquest');
        $respinfo .= $strsort;
        $respinfo .= $smartquest->renderer->help_icon('orderresponses', 'smartquest');
        $smartquest->page->add_to_page('respondentinfo', $respinfo);

        $ret = $smartquest->survey_results(1, 1, '', '', '', $uid = false, $currentgroupid, $sort);

        echo $smartquest->renderer->render($smartquest->page);

        // Finish the page.
        echo $smartquest->renderer->footer($course);
        break;

    case 'vresp': // View by response.
        //!!!!!!!!!!!!!!!!!!!!!!!!!
    default:
        if (empty($smartquest->survey)) {
            print_error('surveynotexists', 'smartquest');
        } else if ($smartquest->survey->courseid != $course->id) {
            print_error('surveyowner', 'smartquest');
        }
        $ruser = false;
        $noresponses = false;
        if ($usergraph) {
            $charttype = $smartquest->survey->chart_type;
            if ($charttype) {
                $PAGE->requires->js('/mod/smartquest/javascript/RGraph/RGraph.common.core.js');

                switch ($charttype) {
                    case 'bipolar':
                        $PAGE->requires->js('/mod/smartquest/javascript/RGraph/RGraph.bipolar.js');
                        break;
                    case 'hbar':
                        $PAGE->requires->js('/mod/smartquest/javascript/RGraph/RGraph.hbar.js');
                        break;
                    case 'radar':
                        $PAGE->requires->js('/mod/smartquest/javascript/RGraph/RGraph.radar.js');
                        break;
                    case 'rose':
                        $PAGE->requires->js('/mod/smartquest/javascript/RGraph/RGraph.rose.js');
                        break;
                    case 'vprogress':
                        $PAGE->requires->js('/mod/smartquest/javascript/RGraph/RGraph.vprogress.js');
                        break;
                }
            }
        }

        if ($byresponse || $rid) {
            // Available group modes (0 = no groups; 1 = separate groups; 2 = visible groups).
            if ($groupmode > 0) {
                switch ($currentgroupid) {
                    case 0:     // All participants.
                        $resps = $respsallparticipants;
                        break;
                    default:     // Members of a specific group.
                        $sql = 'SELECT r.id, r.survey_id, r.submitted, r.userid ' .
                               'FROM {smartquest_response} r ' .
                               'INNER JOIN {groups_members} gm ON r.userid = gm.userid ' .
                               'WHERE r.survey_id = ? AND r.complete = ? AND gm.groupid = ? ' .
                               'ORDER BY r.id';
                        $resps = $DB->get_records_sql($sql, array($sid, 'y', $currentgroupid));
                }
                if (empty($resps)) {
                    $noresponses = true;
                } else {
                    if ($rid === false) {
                        $resp = current($resps);
                        $rid = $resp->id;
                    } else {
                        $resp = $DB->get_record('smartquest_response', array('id' => $rid));
                    }
                    if (!empty($resp->userid)) {
                        if ($user = $DB->get_record('user', ['id' => $resp->userid])) {
                            $ruser = fullname($user);
                        } else {
                            $ruser = '- '.get_string('unknown', 'smartquest').' -';
                        }
                    } else {
                        $ruser = $resp->userid;
                    }
                }
            } else {
                $resps = $respsallparticipants;
            }
        } else {
            $resps = $respsallparticipants;
        }

        $rids = array_keys($resps);
        if (!$rid && !$noresponses ) {
            $rid = $rids[0];
        }
        
        // Print the page header.
        $PAGE->set_title(get_string('smartquestreport', 'smartquest'));
        $PAGE->set_heading(format_string($course->fullname));
        echo $smartquest->renderer->header();

        // Print the tabs.
        if ($byresponse) {
            $SESSION->smartquest->current_tab = 'vrespsummary';
        }
        if ($individualresponse) {
            $SESSION->smartquest->current_tab = 'individualresp';
        }
        include('tabs.php');

        // Print the main part of the page.
        // TODO provide option to select how many columns and/or responses per page.

        if ($noresponses) {
            $smartquest->page->add_to_page('respondentinfo',
                get_string('group').' <strong>'.groups_get_group_name($currentgroupid).'</strong>: '.
                get_string('noresponses', 'smartquest'));
        } else {
            $groupname = get_string('group').': <strong>'.groups_get_group_name($currentgroupid).'</strong>';
            if ($currentgroupid == 0 ) {
                $groupname = get_string('allparticipants');
            }
            if ($byresponse) {
                $respinfo = '';
                $respinfo .= $smartquest->renderer->box_start();
                $respinfo .= $smartquest->renderer->help_icon('viewindividualresponse', 'smartquest').'&nbsp;';
                $respinfo .= get_string('viewindividualresponse', 'smartquest').' <strong> : '.$groupname.'</strong>';
                $respinfo .= $smartquest->renderer->box_end();
                $smartquest->page->add_to_page('respondentinfo', $respinfo);
            }
            $smartquest->survey_results_navbar_alpha($rid, $currentgroupid, $cm, $byresponse."hhhhh");
           
            // print_r('<pre>');
            // print_r($resps);
       
            if (!$byresponse) { // Show respondents individual responses.
                $smartquest->view_response($rid, $referer = '', $blanksmartquest = false, $resps, $compare = true,
                    $isgroupmember = true, $allresponses = false, $currentgroupid);
            }
        }
        echo $smartquest->renderer->render($smartquest->page);

        // Finish the page.
        echo $smartquest->renderer->footer($course);
    break;
}

function to_xl($table, $name = 'smartquest') {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/lib/excellib.class.php');

    $downloadfilename = $name . '.xls';
    // Creating a workbook.
    $workbook = new MoodleExcelWorkbook("-");
    // Sending HTTP headers.
    $workbook->send($downloadfilename);
    // Adding the worksheet.
    $myxls = $workbook->add_worksheet($name);
    
    //Tami 25.11.19 // Add columns to xl:
    $subarray = array('workerid' ,'phone' ,'brigate' ,'wing','userdepartment' ,'stuff','manager');
    $headers = $table[0];
    array_splice( $headers, 8, 0, $subarray );

    $head = count($headers);
    for ($i = 1; $i < $head; $i++) {
        if (!preg_match('/[^A-Za-z]/' , $headers[$i])){
            $name =get_string($headers[$i],'smartquest');
            $myxls->write_string(
                0,
                $i,
                $name,
                ['bg_color' => '#00B0F0',
                 'color' => 'white',
                 'bold' => 1,
                 'border' => 1]
            );
        } else 
        {   
        $myxls->write_string(
            0,
            $i,
            $headers[$i],
            ['bg_color' => '#00B0F0',
             'color' => 'white',
             'bold' => 1,
             'border' => 1]
        );
        }
    }

    $rows = count($table);
    $jtable = 0;

    for ($i =1; $i < $rows ; $i++) 
    {
        $jtable = 0;
        $aboutuserid = $DB->get_field('smartquest_response','aboutuserid', array('id'=>  $table[$i][0]));
        
        for ($j = 0; $j < $head; $j++)
        {
            switch($headers[$j]) {
                case('workerid'):
                    {
                        $data = $DB->get_field('user','idnumber', array('id'=>  $aboutuserid));
                        //print_r($data);die;
                        $myxls->write_string($i, $j, $data);

                    }break;
                case('phone'):
                    {
                        $data = $DB->get_field('user','phone1', array('id'=>  $aboutuserid));
                        $myxls->write_string($i, $j, $data);
                    }break;
                case('brigate'):
                    {
                        $data = $DB->get_field('user_info_data','data', array('userid'=>  $aboutuserid, 'fieldid'=>1));
                        $myxls->write_string($i , $j, $data);
                    }break;
                case('wing'):
                    {
                        $data = $DB->get_field('user_info_data','data', array('userid'=>  $aboutuserid, 'fieldid'=>3));
                        $myxls->write_string($i , $j, $data);
                    }break;
                case('userdepartment'):
                    {
                        $data = $DB->get_field('user_info_data','data', array('userid'=>  $aboutuserid, 'fieldid'=>4));
                        $myxls->write_string($i , $j, $data);
                    }break;
                case('stuff'):
                    {
                        $data = $DB->get_field('user_info_data','data', array('userid'=>  $aboutuserid, 'fieldid'=>5));
                        $myxls->write_string($i , $j, $data);
                    }break;
                case('manager'):
                    {
                        $data = $DB->get_field('user_info_data','data', array('userid'=>  $aboutuserid, 'fieldid'=>18));
                        $myxls->write_string($i , $j, $data);
                    }break;
                      
                default:
                    $myxls->write_string($i , $j, $table[$i][$jtable++]);

            };
        }
    }    
    // End Tami
    $workbook->close();
    exit;
}
