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
 *
 * @author Joseph Rézeau (copied from feedback plugin show_nonrespondents by original author Andreas Grabs)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package    mod
 * @subpackage smartquest
 *
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/smartquest/locallib.php');
require_once($CFG->dirroot.'/mod/smartquest/smartquest.class.php');
require_once($CFG->libdir.'/tablelib.php');

// Get the params.
$id = required_param('id', PARAM_INT);
$subject = optional_param('subject', '', PARAM_CLEANHTML);
$message = optional_param('message', '', PARAM_CLEANHTML);
$format = optional_param('format', FORMAT_MOODLE, PARAM_INT);
$messageuser = optional_param_array('messageuser', false, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$selectedanonymous = optional_param('selectedanonymous', '', PARAM_ALPHA);
$perpage = optional_param('perpage', SMARTQUEST_DEFAULT_PAGE_COUNT, PARAM_INT);  // How many per page.
$showall = optional_param('showall', false, PARAM_INT);  // Should we show all users?
$sid    = optional_param('sid', 0, PARAM_INT);
$qid    = optional_param('qid', 0, PARAM_INT);
$currentgroupid = optional_param('group', 0, PARAM_INT); // Groupid.

if (!isset($SESSION->smartquest)) {
    $SESSION->smartquest = new stdClass();
}

$SESSION->smartquest->current_tab = 'nonrespondents';

// Get the objects.

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
}

if (!$context = context_module::instance($cm->id)) {
        print_error('badcontext');
}

// We need the coursecontext to allow sending of mass mails.
if (!$coursecontext = context_course::instance($course->id)) {
        print_error('badcontext');
}

require_course_login($course, true, $cm);

$url = new moodle_url('/mod/smartquest/show_nonrespondents.php', array('id' => $cm->id));
$PAGE->set_url($url);

$smartquest = new smartquest($sid, $smartquest, $course, $cm);

// Add renderer and page objects to the smartquest object for display use.
$smartquest->add_renderer($PAGE->get_renderer('mod_smartquest'));
$smartquest->add_page(new \mod_smartquest\output\nonrespondentspage());

$resume = $smartquest->resume;
$fullname = $smartquest->respondenttype == 'fullname';
$sid = $smartquest->sid;

if (($formdata = data_submitted()) && !confirm_sesskey()) {
    print_error('invalidsesskey');
}

require_capability('mod/smartquest:viewsingleresponse', $context);

// Anonymous smartquest.
if (!$fullname) {
    $nonrespondents = smartquest_get_incomplete_users($cm, $sid);
    $countnonrespondents = count($nonrespondents);
    if ($resume) {
        $countstarted = 0;
        $countnotstarted = 0;
        $params = ['survey_id' => $sid, 'complete' => 'n'];
        if ($startedusers = $DB->get_records('smartquest_response', $params, '', 'userid')) {
            $startedusers = array_keys($startedusers);
            $countstarted = count($startedusers);
            $countnotstarted = $countnonrespondents - $countstarted;
        }
    }
}

if ($action == 'sendmessage' && !empty($subject) && !empty($message)) {
    if (!$fullname) {
        switch ($selectedanonymous) {
            case 'none':
                $messageuser = '';
                break;
            case 'all':
                $messageuser = $nonrespondents;
                break;
            case 'started':
                $messageuser = $startedusers;
                break;
            case 'notstarted':
                $messageuser = array_diff($nonrespondents, $startedusers);
        }
    }

    $shortname = format_string($course->shortname,
                            true,
                            array('context' => context_course::instance($course->id)));
    $strsmartquests = get_string("modulenameplural", "smartquest");

    $htmlmessage = "<body id=\"email\">";

    $link1 = $CFG->wwwroot.'/mod/smartquest/view.php?id='.$cm->id;

    $htmlmessage .= '<div class="navbar">'.
    '<a target="_blank" href="'.$link1.'">'.format_string($smartquest->name, true).'</a>'.
    '</div>';

    $htmlmessage .= $message;
    $htmlmessage .= '</body>';

    $good = 1;

    if (is_array($messageuser)) {
        foreach ($messageuser as $userid) {
            $senduser = $DB->get_record('user', array('id' => $userid));
            $eventdata = new \core\message\message();
            $eventdata->courseid         = $course->id;
            $eventdata->name             = 'message';
            $eventdata->component        = 'mod_smartquest';
            $eventdata->userfrom         = $USER;
            $eventdata->userto           = $senduser;
            $eventdata->subject          = $subject;
            $eventdata->fullmessage      = html_to_text($htmlmessage);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml  = $htmlmessage;
            $eventdata->smallmessage     = '';
            $good = $good && message_send($eventdata);
        }
        if (!empty($good)) {
            $msg = $smartquest->renderer->heading(get_string('messagedselectedusers'));
        } else {
            $msg = $smartquest->renderer->heading(get_string('messagedselectedusersfailed'));
        }

        $url = new moodle_url('/mod/smartquest/view.php', array('id' => $cm->id));
        redirect($url, $msg, 4);
        exit;
    }
}

// Get the responses of given user.
// Print the page header.
$PAGE->navbar->add(get_string('show_nonrespondents', 'smartquest'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($smartquest->name));
echo $smartquest->renderer->header();
$isstudeffect = $smartquest->rtype == STUDEFFECT || $smartquest->rtype == GUIDELINE ? 1 : 0;

require('tabs.php');

$usedgroupid = false;
$sort = '';
$startpage = false;
$pagecount = false;

if ($fullname) {
    // Print the main part of the page.
    // Print the users with no responses
    // Get the effective groupmode of this course and module.
    $groupmode = groups_get_activity_groupmode($cm, $course);

    $groupselect = groups_print_activity_menu($cm, $url->out(), true);
    $mygroupid = groups_get_activity_group($cm);

    // Preparing the table for output.
    $baseurl = new moodle_url('/mod/smartquest/show_nonrespondents.php');
    $baseurl->params(array('id' => $cm->id, 'showall' => $showall));

    $tablecolumns = array('userpic', 'fullname');

    // Extra columns copied from participants view.
    $extrafields = get_extra_user_fields($context);
    $tableheaders = array(get_string('userpic'), get_string('fullnameuser'));

    if (in_array('email', $extrafields) || has_capability('moodle/course:viewhiddenuserfields', $context)) {
        $tablecolumns[] = 'email';
        $tableheaders[] = get_string('email');
    }

    if (!isset($hiddenfields['city'])) {
        $tablecolumns[] = 'city';
        $tableheaders[] = get_string('city');
    }
    if (!isset($hiddenfields['country'])) {
        $tablecolumns[] = 'country';
        $tableheaders[] = get_string('country');
    }
    if (!isset($hiddenfields['lastaccess'])) {
        $tablecolumns[] = 'lastaccess';
        $tableheaders[] = get_string('lastaccess');
    }
    if ($resume) {
        $tablecolumns[] = 'status';
        $tableheaders[] = get_string('status');
    }
    if (has_capability('mod/smartquest:message', $context) && !$isstudeffect) {
        $tablecolumns[] = 'select';
        $tableheaders[] = get_string('select');
    }
    $tablecolumns[] = 'forfeedback';
    $tableheaders[] = get_string('forfeedback', 'smartquest');

    $table = new flexible_table('smartquest-shownonrespondents-'.$course->id);

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($baseurl);

    $table->sortable(true, 'lastname', SORT_DESC);
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('id', 'showentrytable');
    $table->set_attribute('class', 'flexible generaltable generalbox');
    $table->set_control_variables(array(
                TABLE_VAR_SORT    => 'ssort',
                TABLE_VAR_IFIRST  => 'sifirst',
                TABLE_VAR_ILAST   => 'silast',
                TABLE_VAR_PAGE    => 'spage'
                ));

    $table->no_sorting('status');
    $table->no_sorting('select');
    $table->no_sorting('forfeedback');


    $table->setup();

    if ($table->get_sql_sort()) {
        $sort = $table->get_sql_sort();
    } else {
        $sort = '';
    }

    // Get students in conjunction with groupmode.
    if ($groupmode > 0) {
        if ($mygroupid > 0) {
            $usedgroupid = $mygroupid;
        } else {
            $usedgroupid = false;
        }
    } else {
        $usedgroupid = false;
    }
    $nonrespondents = smartquest_get_incomplete_users($cm, $sid, $usedgroupid);
    $countnonrespondents = count($nonrespondents);

    $table->initialbars(false);

    if ($showall) {
        $startpage = false;
        $pagecount = false;
    } else {
        $table->pagesize($perpage, $countnonrespondents);
        $startpage = $table->get_page_start();
        $pagecount = $table->get_page_size();
    }
}

$nonrespondents = smartquest_get_incomplete_users($cm, $sid, $usedgroupid, $sort, $startpage, $pagecount);

// Viewreports-start.
// Print the list of students.

$smartquest->page->add_to_page('formarea', (isset($groupselect) ? $groupselect : ''));
$smartquest->page->add_to_page('formarea', html_writer::tag('div', '', ['class' => 'clearer']));
$smartquest->page->add_to_page('formarea', $smartquest->renderer->box_start('left-align'));

$countries = get_string_manager()->get_list_of_countries();

$strnever = get_string('never');

$datestring = new stdClass();
$datestring->year  = get_string('year');
$datestring->years = get_string('years');
$datestring->day   = get_string('day');
$datestring->days  = get_string('days');
$datestring->hour  = get_string('hour');
$datestring->hours = get_string('hours');
$datestring->min   = get_string('min');
$datestring->mins  = get_string('mins');
$datestring->sec   = get_string('sec');
$datestring->secs  = get_string('secs');

if (!$nonrespondents) {
    $smartquest->page->add_to_page('formarea',
        $smartquest->renderer->notification(get_string('noexistingparticipants', 'enrol')));
} else {
    
    $smartquest->page->add_to_page('formarea', $isstudeffect ? get_string('non_respondentsstudeffect', 'smartquest') : get_string('non_respondents', 'smartquest'));
    $smartquest->page->add_to_page('formarea', ' ('.$countnonrespondents.')');
    if (!$fullname) {
        $smartquest->page->add_to_page('formarea', ' ['.get_string('anonymous', 'smartquest').']');
    }
    $smartquest->page->add_to_page('formarea', html_writer::start_tag('form',
        ['class' => 'mform', 'action' => 'show_nonrespondents.php', 'method' => 'post', 'id' => 'smartquest_sendmessageform']));

    $buffering = false;
    if ($fullname) {
        // Since flexible tables only writes out directly, we need to start buffering in case anything gets written...
        ob_start();
        $buffering = true;
        foreach ($nonrespondents as $nonrespondent) {
            $user = $DB->get_record('user', array('id' => $nonrespondent));
            // Userpicture and link to the profilepage.
            $profileurl = $CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$course->id;
            $profilelink = '<strong><a href="'.$profileurl.'">'.fullname($user).'</a></strong>';
            $data = array ($smartquest->renderer->user_picture($user, array('courseid' => $course->id)), $profilelink);
            if (in_array('email', $tablecolumns)) {
                $data[] = $user->email;
            }
            if (!isset($hiddenfields['city'])) {
                $data[] = $user->city;
            }
            if (!isset($hiddenfields['country'])) {
                $data[] = (!empty($user->country)) ? $countries[$user->country] : '';
            }
            if ($user->lastaccess) {
                $lastaccess = format_time(time() - $user->lastaccess, $datestring);
            } else {
                $lastaccess = get_string('never');
            }
            $data[] = $lastaccess;
            if (has_capability('mod/smartquest:message', $context) && !$isstudeffect) {
                // If smartquest is set to "resume", look for saved (not completed) responses
                // we use the alt attribute of the checkboxes to store the started/not started value!
                $checkboxaltvalue = '';
                if ($resume) {
                    if ($DB->record_exists('smartquest_response', ['survey_id' => $sid,
                            'userid' => $nonrespondent, 'complete' => 'n']) ) {
                        $data[] = get_string('started', 'smartquest');
                        $checkboxaltvalue = 1;
                    } else {
                        $data[] = get_string('not_started', 'smartquest');
                        $checkboxaltvalue = 0;
                    }
                }
                $data[] = '<input type="checkbox" class="usercheckbox" name="messageuser[]" value="'.
                    $user->id.'" alt="'.$checkboxaltvalue.'" />';
            }
            $feedbackurl = 'https://video.openapp.co.il/dev/mod/smartquest/complete.php?id=' . $cm->id . '&user=' . $user->id;
            $feedbacklink = '<strong><a href="'.$feedbackurl.'">'.get_string('forfeedback', 'smartquest').'</a></strong>';
            $data[] = $feedbacklink;  
            $table->add_data($data);

        }

        if (isset($table)) {
            $smartquest->page->add_to_page('formarea', $smartquest->renderer->flexible_table($table, $buffering));
        } else if ($buffering) {
            ob_end_clean();
        }
        $allurl = new moodle_url($baseurl);
        if ($showall) {
            $allurl->param('showall', 0);
            $smartquest->page->add_to_page('formarea',
                $smartquest->renderer->container(html_writer::link($allurl,
                    get_string('showperpage', '', SMARTQUEST_DEFAULT_PAGE_COUNT)), array(), 'showall'));

        } else if ($countnonrespondents > 0 && $perpage < $countnonrespondents) {
            $allurl->param('showall', 1);
            $smartquest->page->add_to_page('formarea', $smartquest->renderer->container(html_writer::link($allurl,
                get_string('showall', '', $countnonrespondents)), array(), 'showall'));
        }
        if (has_capability('mod/smartquest:message', $context) && !$isstudeffect) {
            $smartquest->page->add_to_page('formarea',
                $smartquest->renderer->box_start('mdl-align')); // Selection buttons container.
            $smartquest->page->add_to_page('formarea', '<div class="buttons">');
            $smartquest->page->add_to_page('formarea',
                '<input type="button" id="checkall" value="'.get_string('selectall').'" /> ');
            $smartquest->page->add_to_page('formarea',
                '<input type="button" id="checknone" value="'.get_string('deselectall').'" /> ');
            if ($resume) {
                if ($perpage >= $countnonrespondents) {
                    $smartquest->page->add_to_page('formarea',
                        '<input type="button" id="checkstarted" value="'.get_string('checkstarted', 'smartquest').'" />'."\n");
                    $smartquest->page->add_to_page('formarea', '<input type="button" id="checknotstarted" value="'.
                        get_string('checknotstarted', 'smartquest').'" />'."\n");
                }
            }
            $smartquest->page->add_to_page('formarea', '</div>');
            $smartquest->page->add_to_page('formarea', $smartquest->renderer->box_end());
            if ($action == 'sendmessage' && !is_array($messageuser)) {
                $smartquest->page->add_to_page('formarea',
                    $smartquest->renderer->notification(get_string('nousersselected', 'smartquest')));
            }
        }
    } else {// Anonymous smartquest.
        if (has_capability('mod/smartquest:message', $context) && !$isstudeffect) {
            $smartquest->page->add_to_page('formarea', '<fieldset>');
            $smartquest->page->add_to_page('formarea', '<legend>'.get_string('send_message_to', 'smartquest').'</legend>');
            $checked = ($selectedanonymous == '' || $selectedanonymous == 'none') ? 'checked = "checked"' : '';
            $smartquest->page->add_to_page('formarea',
                '&nbsp;&nbsp;<input type="radio" name="selectedanonymous" value="none" id="none" '.$checked.' />
                <label for="none">'.get_string('none').'</label>');
            $checked = ($selectedanonymous == 'all') ? 'checked = "checked"' : '';
            $smartquest->page->add_to_page('formarea',
                '<input type="radio" name="selectedanonymous" value="all" id="nonrespondents" '.$checked.' />
                <label for="all">'.get_string('all', 'smartquest').'</label>');
            if ($resume) {
                if ($countstarted > 0) {
                        $checked = ($selectedanonymous == 'started') ? 'checked = "checked"' : '';
                        $smartquest->page->add_to_page('formarea',
                            '<input type="radio" name="selectedanonymous" value="started" id="started" '.$checked.' />
                            <label for="started">'.get_string('status').': '.
                            get_string('started', 'smartquest').' ('.$countstarted.')</label>');
                }
                if ($countnotstarted > 0) {
                    if ($selectedanonymous == 'notstarted') {
                        $checked = 'checked = "checked"';
                    } else {
                        $checked = '';
                    }
                    $checked = ($selectedanonymous == 'notstarted') ? 'checked = "checked"' : '';
                    $smartquest->page->add_to_page('formarea',
                        '<input type="radio" name="selectedanonymous" value="notstarted" id="notstarted" '.$checked.' />
                        <label for="notstarted">'.get_string('status').': '.
                        get_string('not_started', 'smartquest').' ('.$countnotstarted.')</label>');
                }
            }
            if ($action == 'sendmessage' && $selectedanonymous == 'none') {
                $smartquest->page->add_to_page('formarea',
                    $smartquest->renderer->notification(get_string('nousersselected', 'smartquest')));
            }
            $smartquest->page->add_to_page('formarea', '</fieldset>');
        }
    }
    if (has_capability('mod/smartquest:message', $context) && !$isstudeffect) {
        // Message editor.
        // Prepare data.
        $smartquest->page->add_to_page('formarea', '<fieldset class="clearfix">');
        if ($action == 'sendmessage' && (empty($subject) || empty($message))) {
            $smartquest->page->add_to_page('formarea', $smartquest->renderer->notification(get_string('allfieldsrequired')));
        }
        $smartquest->page->add_to_page('formarea',
            '<legend class="ftoggler">'.get_string('send_message', 'smartquest').'</legend>');
        $id = 'message' . '_id';
        $subjecteditor = '&nbsp;&nbsp;&nbsp;<input type="text" id="smartquest_subject" size="65"
            maxlength="255" name="subject" value="'.$subject.'" />';
        $format = '';
            $editor = editors_get_preferred_editor();
            $editor->use_editor($id, smartquest_get_editor_options($context));
            $texteditor = html_writer::tag('div', html_writer::tag('textarea', $message,
                    array('id' => $id, 'name' => "message", 'rows' => '10', 'cols' => '60')));
            $smartquest->page->add_to_page('formarea', '<input type="hidden" name="format" value="'.FORMAT_HTML.'" />');


        // Print editor.
        $table = new html_table();
        $table->align = array('left', 'left');
        $table->data[] = array( '<strong>'.get_string('subject', 'smartquest').'</strong>', $subjecteditor);
        $table->data[] = array('<strong>'.get_string('messagebody').'</strong>', $texteditor);

        $smartquest->page->add_to_page('formarea', html_writer::table($table));

        // Send button.
        $smartquest->page->add_to_page('formarea', $smartquest->renderer->box_start('mdl-left'));
        $smartquest->page->add_to_page('formarea', '<div class="buttons">');
        $smartquest->page->add_to_page('formarea',
            '<input type="submit" name="send_message" value="'.get_string('send', 'smartquest').'" />');
        $smartquest->page->add_to_page('formarea', '</div>');
        $smartquest->page->add_to_page('formarea', $smartquest->renderer->box_end());

        $smartquest->page->add_to_page('formarea', '<input type="hidden" name="sesskey" value="'.sesskey().'" />');
        $smartquest->page->add_to_page('formarea', '<input type="hidden" name="action" value="sendmessage" />');
        $smartquest->page->add_to_page('formarea', '<input type="hidden" name="id" value="'.$cm->id.'" />');

        $smartquest->page->add_to_page('formarea', '</fieldset>');

        $smartquest->page->add_to_page('formarea', html_writer::end_tag('form'));

        // Include the needed js.
        $module = array('name' => 'mod_smartquest', 'fullpath' => '/mod/smartquest/module.js');
        $PAGE->requires->js_init_call('M.mod_smartquest.init_sendmessage', null, false, $module);
    }
}
$smartquest->page->add_to_page('formarea', $smartquest->renderer->box_end());

// Finish the page.
echo $smartquest->renderer->render($smartquest->page);
echo $smartquest->renderer->footer();

// Log this smartquest show non-respondents action.
$context = context_module::instance($smartquest->cm->id);
$anonymous = $smartquest->respondenttype == 'anonymous';

$event = \mod_smartquest\event\non_respondents_viewed::create(array(
                'objectid' => $smartquest->id,
                'anonymous' => $anonymous,
                'context' => $context
));
$event->trigger();
