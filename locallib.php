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
 * This library replaces the phpESP application with Moodle specific code. It will eventually
 * replace all of the phpESP application, removing the dependency on that.
 */

/**
 * Updates the contents of the survey with the provided data. If no data is provided,
 * it checks for posted data.
 *
 * @param int $survey_id The id of the survey to update.
 * @param string $old_tab The function that was being executed.
 * @param object $sdata The data to update the survey with.
 *
 * @return string|boolean The function to go to, or false on error.
 *
 */

/**
 * @package mod_smartquest
 * @copyright  2016 Mike Churchward (mike.churchward@poetgroup.org)
 * @author     Mike Churchward
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
// FIX - tovi@openapp 03/2021
//require_once($CFG->libdir . '/eventslib.php');
require_once($CFG->dirroot . '/calendar/lib.php');
//by Tamard
//require_once($CFG->dirroot . '/local/global_settings/locallib.php');

// Constants.
define('COURSE', 0);
define('SAPEVENT', 1);
define('ROLETYPE', 2);
define('STUDEFFECT', 3);
define('ROLEEFFECT', 4);
define('GUIDELINE', 5);

define('SMARTQUESTUNLIMITED', 0);
define('SMARTQUESTONCE', 1);
define('SMARTQUESTDAILY', 2);
define('SMARTQUESTWEEKLY', 3);
define('SMARTQUESTMONTHLY', 4);

define('SMARTQUEST_STUDENTVIEWRESPONSES_NEVER', 0);
define('SMARTQUEST_STUDENTVIEWRESPONSES_WHENANSWERED', 1);
define('SMARTQUEST_STUDENTVIEWRESPONSES_WHENCLOSED', 2);
define('SMARTQUEST_STUDENTVIEWRESPONSES_ALWAYS', 3);

define('SMARTQUEST_MAX_EVENT_LENGTH', 5 * 24 * 60 * 60);   // 5 days maximum.

define('SMARTQUEST_DEFAULT_PAGE_COUNT', 20);

global $COURSE, $DB;
$context = context_course::instance($COURSE->id);

global $smartquesttypenames;
$smartquesttypenames = [
    COURSE => 'course',
//   SAPEVENT => 'sapevent',
    ROLETYPE => 'roletype',
    STUDEFFECT => 'studeffect',
//  ROLEEFFECT => 'roleeffect',
    GUIDELINE => 'guideline',
];

global $smartquesttypes;
$smartquesttypes = [];

foreach ($smartquesttypenames as $key => $value) {
    if (has_capability('mod/smartquest:create' . $value . 'smartquest', $context)) {
        $smartquesttypes[$key] = get_string($value, 'smartquest');
    }
}

$choose = [
    0 => get_string('choose', 'smartquest')
];

global $roletypes;
$roletypes = get_roles_with_capability('mod/smartquest:canbesurveyed');
$roletypesnames = role_fix_names($roletypes, null, ROLENAME_ORIGINAL, true);
$roletypes = $choose + $roletypesnames;

// Tamard
global $sapevents;
// if($origcourse = $DB->get_field('smartquest_anonymcourses', 'origcourse', ['anonymcourse' => $COURSE->id])) {
  //  $sapevents = local_global_settings_get_sapevents(context_course::instance($origcourse)->id);
// } else {
    // $sapevents = local_global_settings_get_sapevents($context->id);
//}
//$sapevents = $choose + $sapevents;

global $smartquestfreq;
$smartquestfreq = array (
    SMARTQUESTUNLIMITED => get_string('qtypeunlimited', 'smartquest'),
    SMARTQUESTONCE => get_string('qtypeonce', 'smartquest'),
    SMARTQUESTDAILY => get_string('qtypedaily', 'smartquest'),
    SMARTQUESTWEEKLY => get_string('qtypeweekly', 'smartquest'),
    SMARTQUESTMONTHLY => get_string('qtypemonthly', 'smartquest')
);

global $smartquestrespondents;
$smartquestrespondents = array (
    'fullname' => get_string('respondenttypefullname', 'smartquest'),
    'anonymous' => get_string('respondenttypeanonymous', 'smartquest')
);

global $smartquestrealms;
$smartquestrealms = array (
    'private' => get_string('private', 'smartquest'),
    'public' => get_string('public', 'smartquest'),
    'template' => get_string('template', 'smartquest')
);

global $smartquestresponseviewers;
$smartquestresponseviewers = array (
    SMARTQUEST_STUDENTVIEWRESPONSES_WHENANSWERED => get_string('responseviewstudentswhenanswered', 'smartquest'),
    SMARTQUEST_STUDENTVIEWRESPONSES_WHENCLOSED => get_string('responseviewstudentswhenclosed', 'smartquest'),
    SMARTQUEST_STUDENTVIEWRESPONSES_ALWAYS => get_string('responseviewstudentsalways', 'smartquest'),
    SMARTQUEST_STUDENTVIEWRESPONSES_NEVER => get_string('responseviewstudentsnever', 'smartquest')
);

global $autonumbering;
$autonumbering = array (
    0 => get_string('autonumberno', 'smartquest'),
    1 => get_string('autonumberquestions', 'smartquest'),
    2 => get_string('autonumberpages', 'smartquest'),
    3 => get_string('autonumberpagesandquestions', 'smartquest')
);

function smartquest_check_date ($thisdate, $insert=false) {
    $dateformat = get_string('strfdate', 'smartquest');
    if (preg_match('/(%[mdyY])(.+)(%[mdyY])(.+)(%[mdyY])/', $dateformat, $matches)) {
        $datepieces = explode($matches[2], $thisdate);
        foreach ($datepieces as $datepiece) {
            if (!is_numeric($datepiece)) {
                return 'wrongdateformat';
            }
        }
        $pattern = "/[^dmy]/i";
        $dateorder = strtolower(preg_replace($pattern, '', $dateformat));
        $countpieces = count($datepieces);
        if ($countpieces == 1) { // Assume only year entered.
            switch ($dateorder) {
                case 'dmy': // Most countries.
                case 'mdy': // USA.
                    $datepieces[2] = $datepieces[0]; // year
                    $datepieces[0] = '1'; // Assumed 1st month of year.
                    $datepieces[1] = '1'; // Assumed 1st day of month.
                    break;
                case 'ymd': // ISO 8601 standard
                    $datepieces[1] = '1'; // Assumed 1st month of year.
                    $datepieces[2] = '1'; // Assumed 1st day of month.
                    break;
            }
        }
        if ($countpieces == 2) { // Assume only month and year entered.
            switch ($dateorder) {
                case 'dmy': // Most countries.
                    $datepieces[2] = $datepieces[1]; // Year.
                    $datepieces[1] = $datepieces[0]; // Month.
                    $datepieces[0] = '1'; // Assumed 1st day of month.
                    break;
                case 'mdy': // USA
                    $datepieces[2] = $datepieces[1]; // Year.
                    $datepieces[0] = $datepieces[0]; // Month.
                    $datepieces[1] = '1'; // Assumed 1st day of month.
                    break;
                case 'ymd': // ISO 8601 standard
                    $datepieces[2] = '1'; // Assumed 1st day of month.
                    break;
            }
        }
        if (count($datepieces) > 1) {
            if ($matches[1] == '%m') {
                $month = $datepieces[0];
            }
            if ($matches[1] == '%d') {
                $day = $datepieces[0];
            }
            if ($matches[1] == '%y') {
                $year = strftime('%C').$datepieces[0];
            }
            if ($matches[1] == '%Y') {
                $year = $datepieces[0];
            }

            if ($matches[3] == '%m') {
                $month = $datepieces[1];
            }
            if ($matches[3] == '%d') {
                $day = $datepieces[1];
            }
            if ($matches[3] == '%y') {
                $year = strftime('%C').$datepieces[1];
            }
            if ($matches[3] == '%Y') {
                $year = $datepieces[1];
            }

            if ($matches[5] == '%m') {
                $month = $datepieces[2];
            }
            if ($matches[5] == '%d') {
                $day = $datepieces[2];
            }
            if ($matches[5] == '%y') {
                $year = strftime('%C').$datepieces[2];
            }
            if ($matches[5] == '%Y') {
                $year = $datepieces[2];
            }

            $month = min(12, $month);
            $month = max(1, $month);
            if ($month == 2) {
                $day = min(29, $day);
            } else if ($month == 4 || $month == 6 || $month == 9 || $month == 11) {
                $day = min(30, $day);
            } else {
                $day = min(31, $day);
            }
            $day = max(1, $day);
            if (!$thisdate = gmmktime(0, 0, 0, $month, $day, $year)) {
                return 'wrongdaterange';
            } else {
                if ($insert) {
                    $thisdate = trim(userdate ($thisdate, '%Y-%m-%d', '1', false));
                } else {
                    $thisdate = trim(userdate ($thisdate, $dateformat, '1', false));
                }
            }
            return $thisdate;
        }
    } else {
        return ('wrongdateformat');
    }
}

function smartquest_choice_values($content) {

    // If we run the content through format_text first, any filters we want to use (e.g. multilanguage) should work.
    // examines the content of a possible answer from radio button, check boxes or rate question
    // returns ->text to be displayed, ->image if present, ->modname name of modality, image ->title.
    $contents = new stdClass();
    $contents->text = '';
    $contents->image = '';
    $contents->modname = '';
    $contents->title = '';
    // Has image.
    if (preg_match('/(<img)\s .*(src="(.[^"]{1,})")/isxmU', $content, $matches)) {
        $contents->image = $matches[0];
        $imageurl = $matches[3];
        // Image has a title or alt text: use one of them.
        if (preg_match('/(title=.)([^"]{1,})/', $content, $matches)
             || preg_match('/(alt=.)([^"]{1,})/', $content, $matches) ) {
            $contents->title = $matches[2];
        } else {
            // Image has no title nor alt text: use its filename (without the extension).
            preg_match("/.*\/(.*)\..*$/", $imageurl, $matches);
            $contents->title = $matches[1];
        }
        // Content has text or named modality plus an image.
        if (preg_match('/(.*)(<img.*)/', $content, $matches)) {
            $content = $matches[1];
        } else {
            // Just an image.
            return $contents;
        }
    }

    // Check for score value first (used e.g. by personality test feature).
    $r = preg_match_all("/^(\d{1,2}=)(.*)$/", $content, $matches);
    if ($r) {
        $content = $matches[2][0];
    }

    // Look for named modalities.
    $contents->text = $content;
    // DEV JR from version 2.5, a double colon :: must be used here instead of the equal sign.
    if ($pos = strpos($content, '::')) {
        $contents->text = substr($content, $pos + 2);
        $contents->modname = substr($content, 0, $pos);
    }
    return $contents;
}

/**
 * Get the information about the standard smartquest JavaScript module.
 * @return array a standard jsmodule structure.
 */
function smartquest_get_js_module() {
    return array(
            'name' => 'mod_smartquest',
            'fullpath' => '/mod/smartquest/module.js',
            'requires' => array('base', 'dom', 'event-delegate', 'event-key',
                    'core_question_engine', 'moodle-core-formchangechecker'),
            'strings' => array(
                    array('cancel', 'moodle'),
                    array('flagged', 'question'),
                    array('functiondisabledbysecuremode', 'quiz'),
                    array('startattempt', 'quiz'),
                    array('timesup', 'quiz'),
                    array('changesmadereallygoaway', 'moodle'),
            ),
    );
}

/**
 * Get all the smartquest responses for a user
 */
function smartquest_get_user_responses($surveyid, $userid, $complete=true) {
    global $DB;
    $andcomplete = '';
    if ($complete) {
        $andcomplete = " AND complete = 'y' ";
    }
    return $DB->get_records_sql ("SELECT *
        FROM {smartquest_response}
        WHERE survey_id = ?
        AND userid = ?
        ".$andcomplete."
        ORDER BY submitted ASC ", array($surveyid, $userid));
}

/**
 * get the capabilities for the smartquest
 * @param int $cmid
 * @return object the available capabilities from current user
 */
function smartquest_load_capabilities($cmid) {
    static $cb;
    global $smartquesttypes;

    if (isset($cb)) {
        return $cb;
    }

    $context = smartquest_get_context($cmid);

    $cb = new stdClass();
    $cb->view                   = has_capability('mod/smartquest:view', $context);
    $cb->submit                 = has_capability('mod/smartquest:submit', $context);
    $cb->viewsingleresponse     = has_capability('mod/smartquest:viewsingleresponse', $context);
    $cb->submissionnotification = has_capability('mod/smartquest:submissionnotification', $context);
    $cb->downloadresponses      = has_capability('mod/smartquest:downloadresponses', $context);
    $cb->deleteresponses        = has_capability('mod/smartquest:deleteresponses', $context);
    $cb->manage                 = has_capability('mod/smartquest:manage', $context);
    $cb->editquestions          = has_capability('mod/smartquest:editquestions', $context);
    $cb->createtemplates        = has_capability('mod/smartquest:createtemplates', $context);
    $cb->createpublic           = has_capability('mod/smartquest:createpublic', $context);
    $cb->readownresponses       = has_capability('mod/smartquest:readownresponses', $context);
    $cb->readallresponses       = has_capability('mod/smartquest:readallresponses', $context);
    $cb->readallresponseanytime = has_capability('mod/smartquest:readallresponseanytime', $context);
    $cb->printblank             = has_capability('mod/smartquest:printblank', $context);
    $cb->preview                = has_capability('mod/smartquest:preview', $context);

    $cb->viewhiddenactivities   = has_capability('moodle/course:viewhiddenactivities', $context, null, false);

    return $cb;
}

/**
 * returns the context-id related to the given coursemodule-id
 * @param int $cmid the coursemodule-id
 * @return object $context
 */
function smartquest_get_context($cmid) {
    static $context;

    if (isset($context)) {
        return $context;
    }

    if (!$context = context_module::instance($cmid)) {
            print_error('badcontext');
    }
    return $context;
}

// This function *really* shouldn't be needed, but since sometimes we can end up with
// orphaned surveys, this will clean them up.
function smartquest_cleanup() {
    global $DB;

    // Find surveys that don't have smartquests associated with them.
    $sql = 'SELECT qs.* FROM {smartquest_survey} qs '.
           'LEFT JOIN {smartquest} q ON q.sid = qs.id '.
           'WHERE q.sid IS NULL';

    if ($surveys = $DB->get_records_sql($sql)) {
        foreach ($surveys as $survey) {
            smartquest_delete_survey($survey->id, 0);
        }
    }
    // Find deleted questions and remove them from database (with their associated choices, etc.).
    return true;
}

function smartquest_record_submission(&$smartquest, $userid, $rid=0) {
    global $DB;

    $attempt['qid'] = $smartquest->id;
    $attempt['userid'] = $userid;
    $attempt['rid'] = $rid;
    $attempt['timemodified'] = time();
    return $DB->insert_record("smartquest_attempts", (object)$attempt, false);
}

function smartquest_delete_survey($sid, $smartquestid) {
    global $DB;
    $status = true;
    // Delete all survey attempts and responses.
    if ($responses = $DB->get_records('smartquest_response', array('survey_id' => $sid), 'id')) {
        foreach ($responses as $response) {
            $status = $status && smartquest_delete_response($response);
        }
    }

    // There really shouldn't be any more, but just to make sure...
    $DB->delete_records('smartquest_response', array('survey_id' => $sid));
    $DB->delete_records('smartquest_attempts', array('qid' => $smartquestid));

    // Delete all question data for the survey.
    if ($questions = $DB->get_records('smartquest_question', array('survey_id' => $sid), 'id')) {
        foreach ($questions as $question) {
            $DB->delete_records('smartquest_quest_choice', array('question_id' => $question->id));
            smartquest_delete_dependencies($question->id);
        }
        $status = $status && $DB->delete_records('smartquest_question', array('survey_id' => $sid));
        // Just to make sure.
        $status = $status && $DB->delete_records('smartquest_dependency', ['surveyid' => $sid]);
    }

    // Delete all feedback sections and feedback messages for the survey.
    if ($fbsections = $DB->get_records('smartquest_fb_sections', array('survey_id' => $sid), 'id')) {
        foreach ($fbsections as $fbsection) {
            $DB->delete_records('smartquest_feedback', array('section_id' => $fbsection->id));
        }
        $status = $status && $DB->delete_records('smartquest_fb_sections', array('survey_id' => $sid));
    }

    $status = $status && $DB->delete_records('smartquest_survey', array('id' => $sid));

    return $status;
}

function smartquest_delete_response($response, $smartquest='') {
    global $DB;
    $status = true;
    $cm = '';
    $rid = $response->id;
    // The smartquest_delete_survey function does not send the smartquest array.
    if ($smartquest != '') {
        $cm = get_coursemodule_from_instance("smartquest", $smartquest->id, $smartquest->course->id);
    }

    // Delete all of the response data for a response.
    $DB->delete_records('smartquest_response_bool', array('response_id' => $rid));
    $DB->delete_records('smartquest_response_date', array('response_id' => $rid));
    $DB->delete_records('smartquest_resp_multiple', array('response_id' => $rid));
    $DB->delete_records('smartquest_response_other', array('response_id' => $rid));
    $DB->delete_records('smartquest_response_rank', array('response_id' => $rid));
    $DB->delete_records('smartquest_response_relrank', array('response_id' => $rid));
    $DB->delete_records('smartquest_resp_single', array('response_id' => $rid));
    $DB->delete_records('smartquest_response_text', array('response_id' => $rid));

    $status = $status && $DB->delete_records('smartquest_response', array('id' => $rid));
    $status = $status && $DB->delete_records('smartquest_attempts', array('rid' => $rid));

    if ($status && $cm) {
        // Update completion state if necessary.
        $completion = new completion_info($smartquest->course);
        if ($completion->is_enabled($cm) == COMPLETION_TRACKING_AUTOMATIC && $smartquest->completionsubmit) {
            $completion->update_state($cm, COMPLETION_INCOMPLETE, $response->userid);
        }
    }

    return $status;
}

function smartquest_delete_responses($qid) {
    global $DB;

    $status = true;

    // Delete all of the response data for a question.
    $DB->delete_records('smartquest_response_bool', array('question_id' => $qid));
    $DB->delete_records('smartquest_response_date', array('question_id' => $qid));
    $DB->delete_records('smartquest_resp_multiple', array('question_id' => $qid));
    $DB->delete_records('smartquest_response_other', array('question_id' => $qid));
    $DB->delete_records('smartquest_response_rank', array('question_id' => $qid));
    $DB->delete_records('smartquest_response_relrank', array('question_id' => $qid));
    $DB->delete_records('smartquest_resp_single', array('question_id' => $qid));
    $DB->delete_records('smartquest_response_text', array('question_id' => $qid));

    $status = $status && $DB->delete_records('smartquest_response', array('id' => $qid));
    $status = $status && $DB->delete_records('smartquest_attempts', array('rid' => $qid));

    return $status;
}

function smartquest_delete_dependencies($qid) {
    global $DB;

    $status = true;

    // Delete all dependencies for this question.
    $DB->delete_records('smartquest_dependency', ['questionid' => $qid]);
    $DB->delete_records('smartquest_dependency', ['dependquestionid' => $qid]);

    return $status;
}

function smartquest_get_survey_list($courseid = 0, $type = '', $withanonymous = true) {
    global $DB;

    if ($courseid == 0) {
        if (isadmin()) {
            $sql = "SELECT id,name,courseid,realm,status " .
                   "{smartquest_survey} " .
                   "ORDER BY realm,name ";
            $params = null;
        } else {
            return false;
        }
    } else {
        if ($type == 'public') {
            $sql = "SELECT s.id,s.name,s.courseid,s.realm,s.status,s.title,q.id as qid,q.name as qname " .
                   "FROM {smartquest} q " .
                   "INNER JOIN {smartquest_survey} s ON s.id = q.sid AND s.courseid = q.course " .
                   "WHERE realm = ? " .
                   "ORDER BY realm,name ";
            $params = [$type];
        } else if ($type == 'template') {
            $where = "WHERE (realm = ?) ";

            if(!$withanonymous) {
                $where .= " AND (s.anonymoustemplate = 0 OR s.courseid = ?)";
            }

            $sql = "SELECT s.id,s.name,s.courseid,s.realm,s.status,s.title,q.id as qid,q.name as qname " .
                   "FROM {smartquest} q " .
                   "INNER JOIN {smartquest_survey} s ON s.id = q.sid AND s.courseid = q.course " .
		   "INNER JOIN {course_modules} cm ON cm.instance = q.id " .
		   "INNER JOIN {modules} m ON m.id = cm.module " .
                    $where . " AND m.name =? AND cm.deletioninprogress = 0 " .
                   "ORDER BY realm, qname ";
            $params = [$type, $courseid, 'smartquest'];
        } else if ($type == 'private') {
            $sql = "SELECT s.id,s.name,s.courseid,s.realm,s.status,q.id as qid,q.name as qname " .
                "FROM {smartquest} q " .
                "INNER JOIN {smartquest_survey} s ON s.id = q.sid " .
                "WHERE s.courseid = ? and realm = ? " .
                "ORDER BY realm,name ";
            $params = [$courseid, $type];

        } else {
            // Current get_survey_list is called from function smartquest_reset_userdata so we need to get a
            // complete list of all smartquests in current course to reset them.
            $sql = "SELECT s.id,s.name,s.courseid,s.realm,s.status,q.id as qid,q.name as qname " .
                   "FROM {smartquest} q " .
                    "INNER JOIN {smartquest_survey} s ON s.id = q.sid AND s.courseid = q.course " .
                   "WHERE s.courseid = ? " .
                   "ORDER BY realm,name ";
            $params = [$courseid];
        }
    }
    return $DB->get_records_sql($sql, $params);
}

function smartquest_get_survey_select($courseid = 0, $type = '', $withanonymous = true) {
    global $OUTPUT, $DB;

    $surveylist = array();

    if ($surveys = smartquest_get_survey_list($courseid, $type, $withanonymous)) {
        $strpreview = get_string('preview_smartquest', 'smartquest');
        foreach ($surveys as $survey) {
            $originalcourse = $DB->get_record('course', ['id' => $survey->courseid]);
            if (!$originalcourse) {
                // This should not happen, but we found a case where a public survey
                // still existed in a course that had been deleted, and so this
                // code lead to a notice, and a broken link. Since that is useless
                // we just skip surveys like this.
                continue;
            }

            // Prevent creating a copy of a public smartquest IN THE SAME COURSE as the original.
            if (($type == 'public') && ($survey->courseid == $courseid)) {
                continue;
            } else {
                $args = "sid={$survey->id}&popup=1";
                if (!empty($survey->qid)) {
                    $args .= "&qid={$survey->qid}";
                }
                $link = new moodle_url("/mod/smartquest/preview.php?{$args}");
                $action = new popup_action('click', $link);
                $label = $OUTPUT->action_link($link, $survey->qname.' ['.$originalcourse->fullname.']',
                    $action, array('title' => $strpreview));
                $surveylist[$type.'-'.$survey->id] = $label;
            }
        }
    }
    return $surveylist;
}

function smartquest_get_type ($id) {
    switch ($id) {
        case 1:
            return get_string('yesno', 'smartquest');
        case 2:
            return get_string('textbox', 'smartquest');
        case 3:
            return get_string('essaybox', 'smartquest');
        case 4:
            return get_string('radiobuttons', 'smartquest');
        case 5:
            return get_string('checkboxes', 'smartquest');
        case 6:
            return get_string('dropdown', 'smartquest');
        case 8:
            return get_string('ratescale', 'smartquest');
        case 9:
            return get_string('date', 'smartquest');
        case 10:
            return get_string('numeric', 'smartquest');
        case 11:
            return get_string('relevantratescale', 'smartquest');
        case 100:
            return get_string('sectiontext', 'smartquest');
        case 99:
            return get_string('sectionbreak', 'smartquest');
        default:
        return $id;
    }
}

/**
 * This creates new events given as opendate and closedate by $smartquest.
 * @param object $smartquest
 * @return void
 */
 /* added by JR 16 march 2009 based on lesson_process_post_save script */

function smartquest_set_events($smartquest) {
    // Adding the smartquest to the eventtable.
    global $DB;
    if ($events = $DB->get_records('event', array('modulename' => 'smartquest', 'instance' => $smartquest->id))) {
        foreach ($events as $event) {
            $event = calendar_event::load($event);
            $event->delete();
        }
    }

    // The open-event.
    $event = new stdClass;
    $event->description = $smartquest->name;
    $event->courseid = $smartquest->course;
    $event->groupid = 0;
    $event->userid = 0;
    $event->modulename = 'smartquest';
    $event->instance = $smartquest->id;
    $event->eventtype = 'open';
    $event->timestart = $smartquest->opendate;
    $event->visible = instance_is_visible('smartquest', $smartquest);
    $event->timeduration = ($smartquest->closedate - $smartquest->opendate);

    if ($smartquest->closedate && $smartquest->opendate && ($event->timeduration <= SMARTQUEST_MAX_EVENT_LENGTH)) {
        // Single event for the whole smartquest.
        $event->name = $smartquest->name;
        calendar_event::create($event);
    } else {
        // Separate start and end events.
        $event->timeduration  = 0;
        if ($smartquest->opendate) {
            $event->name = $smartquest->name.' ('.get_string('smartquestopens', 'smartquest').')';
            calendar_event::create($event);
            unset($event->id); // So we can use the same object for the close event.
        }
        if ($smartquest->closedate) {
            $event->name = $smartquest->name.' ('.get_string('smartquestcloses', 'smartquest').')';
            $event->timestart = $smartquest->closedate;
            $event->eventtype = 'close';
            calendar_event::create($event);
        }
    }
}

/**
 * Get users who have not completed the smartquest
 *
 * @global object
 * @uses CONTEXT_MODULE
 * @param object $cm
 * @param int $group single groupid
 * @param string $sort
 * @param int $startpage
 * @param int $pagecount
 * @return object the userrecords
 */
function smartquest_get_incomplete_users($cm, $sid,
                $group = false,
                $sort = '',
                $startpage = false,
                $pagecount = false) {

    global $DB;

    $context = context_module::instance($cm->id);

    if (!$course = $DB->get_record("course", array("id" => $cm->course))) {
        print_error('coursemisconf');
    }

    if (!$smartquest = $DB->get_record("smartquest", array("id" => $cm->instance))) {
        print_error('invalidcoursemodule');
    }

    $smartquest = new smartquest(0, $smartquest, $course, $cm);
    // First get all users who can complete this smartquest.
    
    if($smartquest->rtype == STUDEFFECT || $smartquest->rtype == GUIDELINE) {
        $cap = 'mod/smartquest:canbestudsurveyed';
    } else {
        $cap = 'mod/smartquest:submit';
    }

    $fields = 'u.id, u.username';
    if (!$allusers = get_users_by_capability($context,
                    $cap,
                    $fields,
                    $sort,
                    '',
                    '',
                    $group,
                    '',
                    true)) {
        return false;
    }
    $allusers = array_keys($allusers);

    // Nnow get all completed smartquests.
    $params = array('survey_id' => $sid, 'complete' => 'y');

    if($smartquest->rtype == STUDEFFECT || $smartquest->rtype == GUIDELINE) {
        $sql = "SELECT aboutuserid as userid
                FROM {smartquest_response} " .
                "WHERE survey_id = :survey_id AND complete = :complete AND aboutuserid != 0 " .
                "GROUP BY aboutuserid ";
    } else {
        $sql = "SELECT userid 
                FROM {smartquest_response} " .
               "WHERE survey_id = :survey_id AND complete = :complete " .
               "GROUP BY userid ";
    }
    
    if (!$completedusers = $DB->get_records_sql($sql, $params)) {
        return $allusers;
    }
    $completedusers = array_keys($completedusers);
    // Now strike all completedusers from allusers.
    $allusers = array_diff($allusers, $completedusers);
    // For paging I use array_slice().
    if (($startpage !== false) && ($pagecount !== false)) {
        $allusers = array_slice($allusers, $startpage, $pagecount);
    }
    return $allusers;
}

/**
 * Called by HTML editor in showrespondents and Essay question. Based on question/essay/renderer.
 * Pending general solution to using the HTML editor outside of moodleforms in Moodle pages.
 */
function smartquest_get_editor_options($context) {
    return array(
                    'subdirs' => 0,
                    'maxbytes' => 0,
                    'maxfiles' => -1,
                    'context' => $context,
                    'noclean' => 0,
                    'trusttext' => 0
    );
}

// Get the parent of a child question.
// TODO - This needs to be refactored or removed.
function smartquest_get_parent ($question) {
    global $DB;
    $qid = $question->id;
    $parent = array();
    $dependquestion = $DB->get_record('smartquest_question', ['id' => $question->dependquestionid],
        'id, position, name, type_id');
    if (is_object($dependquestion)) {
        $qdependchoice = '';
        switch ($dependquestion->type_id) {
            case QUESRADIO:
            case QUESDROP:
            case QUESCHECK:
                $dependchoice = $DB->get_record('smartquest_quest_choice', ['id' => $question->dependchoiceid], 'id,content');
                $qdependchoice = $dependchoice->id;
                $dependchoice = $dependchoice->content;

                $contents = smartquest_choice_values($dependchoice);
                if ($contents->modname) {
                    $dependchoice = $contents->modname;
                }
                break;
            case QUESYESNO:
                switch ($question->dependchoiceid) {
                    case 0:
                        $dependchoice = get_string('yes');
                        $qdependchoice = 'y';
                        break;
                    case 1:
                        $dependchoice = get_string('no');
                        $qdependchoice = 'n';
                        break;
                }
                break;
        }
        // Qdependquestion, parenttype and qdependchoice fields to be used in preview mode.
        $parent [$qid]['qdependquestion'] = 'q'.$dependquestion->id;
        $parent [$qid]['qdependchoice'] = $qdependchoice;
        $parent [$qid]['parenttype'] = $dependquestion->type_id;
        // Other fields to be used in Questions edit mode.
        $parent [$qid]['position'] = $question->position;
        $parent [$qid]['name'] = $question->name;
        $parent [$qid]['content'] = $question->content;
        $parent [$qid]['parentposition'] = $dependquestion->position;
        $parent [$qid]['parent'] = $dependquestion->name.'->'.$dependchoice;
    }
    return $parent;
}

/**
 * Get parent position of all child questions in current smartquest.
 * Use the parent with the largest position value.
 *
 * @param array $questions
 * @return array An array with Child-ID->Parentposition.
 */
function smartquest_get_parent_positions ($questions) {
    $parentpositions = array();
    foreach ($questions as $question) {
        foreach ($question->dependencies as $dependency) {
            $dependquestion = $dependency->dependquestionid;
            if (isset($dependquestion) && $dependquestion != 0) {
                $childid = $question->id;
                $parentpos = $questions[$dependquestion]->position;

                if (!isset($parentpositions[$childid])) {
                    $parentpositions[$childid] = $parentpos;
                }
                if (isset ($parentpositions[$childid]) && $parentpos > $parentpositions[$childid]) {
                    $parentpositions[$childid] = $parentpos;
                }
            }
        }
    }
    return $parentpositions;
}

/**
 * Get child position of all parent questions in current smartquest.
 * Use the child with the smallest position value.
 *
 * @param array $questions
 * @return array An array with Parent-ID->Childposition.
 */
function smartquest_get_child_positions ($questions) {
    $childpositions = array();
    foreach ($questions as $question) {
        foreach ($question->dependencies as $dependency) {
            $dependquestion = $dependency->dependquestionid;
            if (isset($dependquestion) && $dependquestion != 0) {
                $parentid = $questions[$dependquestion]->id; // Equals $dependquestion?.
                $childpos = $question->position;

                if (!isset($childpositions[$parentid])) {
                    $childpositions[$parentid] = $childpos;
                }

                if (isset ($childpositions[$parentid]) && $childpos < $childpositions[$parentid]) {
                    $childpositions[$parentid] = $childpos;
                }
            }
        }
    }
    return $childpositions;
}

// Check that the needed page breaks are present to separate child questions.
function smartquest_check_page_breaks($smartquest) {
    global $DB;
    $msg = '';
    // Store the new page breaks ids.
    $newpbids = array();
    $delpb = 0;
    $sid = $smartquest->survey->id;
    $questions = $DB->get_records('smartquest_question', array('survey_id' => $sid, 'deleted' => 'n'), 'id');
    $positions = array();
    foreach ($questions as $key => $qu) {
        $positions[$qu->position]['question_id'] = $key;
        $positions[$qu->position]['type_id'] = $qu->type_id;
        $positions[$qu->position]['qname'] = $qu->name;
        $positions[$qu->position]['qpos'] = $qu->position;

        $dependencies = $DB->get_records('smartquest_dependency', array('questionid' => $key , 'surveyid' => $sid),
                'id ASC', 'id, dependquestionid, dependchoiceid, dependlogic');
        $positions[$qu->position]['dependencies'] = $dependencies;
    }
    $count = count($positions);

    for ($i = $count; $i > 0; $i--) {
        $qu = $positions[$i];
        $questionnb = $i;
        if ($qu['type_id'] == QUESPAGEBREAK) {
            $questionnb--;
            // If more than one consecutive page breaks, remove extra one(s).
            $prevqu = null;
            $prevtypeid = null;
            if ($i > 1) {
                $prevqu = $positions[$i - 1];
                $prevtypeid = $prevqu['type_id'];
            }
            // If $i == $count then remove that extra page break in last position.
            if ($prevtypeid == QUESPAGEBREAK || $i == $count || $qu['qpos'] == 1) {
                $qid = $qu['question_id'];
                $delpb ++;
                $msg .= get_string("checkbreaksremoved", "smartquest", $delpb).'<br />';
                // Need to reload questions.
                $questions = $DB->get_records('smartquest_question', array('survey_id' => $sid, 'deleted' => 'n'), 'id');
                $DB->set_field('smartquest_question', 'deleted', 'y', array('id' => $qid, 'survey_id' => $sid));
                $select = 'survey_id = '.$sid.' AND deleted = \'n\' AND position > '.
                                $questions[$qid]->position;
                if ($records = $DB->get_records_select('smartquest_question', $select, null, 'position ASC')) {
                    foreach ($records as $record) {
                        $DB->set_field('smartquest_question', 'position', $record->position - 1, array('id' => $record->id));
                    }
                }
            }
        }
        // Add pagebreak between question child and not dependent question that follows.
        if ($qu['type_id'] != QUESPAGEBREAK) {
            $j = $i - 1;
            if ($j != 0) {
                $prevtypeid = $positions[$j]['type_id'];
                $prevdependencies = $positions[$j]['dependencies'];

                $outerdependencies = count($qu['dependencies']) >= count($prevdependencies) ? $qu['dependencies'] : $prevdependencies;
                $innerdependencies = count($qu['dependencies']) < count($prevdependencies) ? $qu['dependencies'] : $prevdependencies;

                foreach ($outerdependencies as $okey => $outerdependency) {
                    foreach ($innerdependencies as $ikey => $innerdependency) {
                        if ($outerdependency->dependquestionid === $innerdependency->dependquestionid &&
                            $outerdependency->dependchoiceid === $innerdependency->dependchoiceid &&
                            $outerdependency->dependlogic === $innerdependency->dependlogic) {
                            unset($outerdependencies[$okey]);
                            unset($innerdependencies[$ikey]);
                        }
                    }
                }

                $diffdependencies = count($outerdependencies) + count($innerdependencies);

                if (($prevtypeid != QUESPAGEBREAK && $diffdependencies != 0)
                        || (!isset($qu['dependencies']) && isset($prevdependencies))) {
                    $sql = 'SELECT MAX(position) as maxpos FROM {smartquest_question} ' .
                        'WHERE survey_id = ' . $smartquest->survey->id . ' AND deleted = \'n\'';
                    if ($record = $DB->get_record_sql($sql)) {
                        $pos = $record->maxpos + 1;
                    } else {
                        $pos = 1;
                    }
                    $question = new stdClass();
                    $question->survey_id = $smartquest->survey->id;
                    $question->type_id = QUESPAGEBREAK;
                    $question->position = $pos;
                    $question->content = 'break';

                    if (!($newqid = $DB->insert_record('smartquest_question', $question))) {
                        return (false);
                    }
                    $newpbids[] = $newqid;
                    $movetopos = $i;
                    $smartquest = new smartquest($smartquest->id, null, $course, $cm);
                    $smartquest->move_question($newqid, $movetopos);
                }
            }
        }
    }
    if (empty($newpbids) && !$msg) {
        $msg = get_string('checkbreaksok', 'smartquest');
    } else if ($newpbids) {
        $msg .= get_string('checkbreaksadded', 'smartquest').'&nbsp;';
        $newpbids = array_reverse ($newpbids);
        $smartquest = new smartquest($smartquest->id, null, $course, $cm);
        foreach ($newpbids as $newpbid) {
            $msg .= $smartquest->questions[$newpbid]->position.'&nbsp;';
        }
    }
    return($msg);
}

/**
 * Code snippet used to set up the questionform.
 */
function smartquest_prep_for_questionform($smartquest, $qid, $qtype) {
    $context = context_module::instance($smartquest->cm->id);
    if ($qid != 0) {
        $question = clone($smartquest->questions[$qid]);
        $question->qid = $question->id;
        $question->sid = $smartquest->survey->id;
        $question->id = $smartquest->cm->id;
        $draftideditor = file_get_submitted_draft_itemid('question');
        $content = file_prepare_draft_area($draftideditor, $context->id, 'mod_smartquest', 'question',
                                           $qid, array('subdirs' => true), $question->content);
        $question->content = array('text' => $content, 'format' => FORMAT_HTML, 'itemid' => $draftideditor);

        if (isset($question->dependencies)) {
            foreach ($question->dependencies as $dependencies) {
                if ($dependencies->dependandor === "and") {
                    $question->dependquestions_and[] = $dependencies->dependquestionid.','.$dependencies->dependchoiceid;
                    $question->dependlogic_and[] = $dependencies->dependlogic;
                } else if ($dependencies->dependandor === "or") {
                    $question->dependquestions_or[] = $dependencies->dependquestionid.','.$dependencies->dependchoiceid;
                    $question->dependlogic_or[] = $dependencies->dependlogic;
                }
            }
        }
    } else {
        $question = \mod_smartquest\question\base::question_builder($qtype);
        $question->sid = $smartquest->survey->id;
        $question->id = $smartquest->cm->id;
        $question->type_id = $qtype;
        $question->type = '';
        $draftideditor = file_get_submitted_draft_itemid('question');
        $content = file_prepare_draft_area($draftideditor, $context->id, 'mod_smartquest', 'question',
                                           null, array('subdirs' => true), '');
        $question->content = array('text' => $content, 'format' => FORMAT_HTML, 'itemid' => $draftideditor);
    }
    return $question;
}

/**
 * Get the standard page contructs and check for validity.
 * @param int $id The coursemodule id.
 * @param int $a  The module instance id.
 * @return array An array with the $cm, $course, and $smartquest records in that order.
 */
function smartquest_get_standard_page_items($id = null, $a = null) {
    global $DB;

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
        if (! $smartquest = $DB->get_record("smartquest", array("id" => $a))) {
            print_error('invalidcoursemodule');
        }
        if (! $course = $DB->get_record("course", array("id" => $smartquest->course))) {
            print_error('coursemisconf');
        }
        if (! $cm = get_coursemodule_from_instance("smartquest", $smartquest->id, $course->id)) {
            print_error('invalidcoursemodule');
        }
    }

    return (array($cm, $course, $smartquest));
}

function get_roles_and_users() {
    global $DB, $COURSE;

    $context = context_course::instance($COURSE->id);
    $roletypes = get_roles_with_capability('mod/smartquest:canbesurveyed');
    $roletypes = role_fix_names($roletypes, null, ROLENAME_ORIGINAL, true);
    $roletypes[0] = get_string('choose', 'smartquest');
    $userroles = [];

    foreach($roletypes as $roleid => $name) {
        $userroles[$roleid][0] = get_string('choose', 'smartquest');
        $users = get_role_users($roleid, $context, true, 'u.id, CONCAT(u.firstname, " ", u.lastname) AS fullname, u.lastname, u.firstname');
        foreach($users as $user) {
            $userroles[$roleid][$user->id] = $user->fullname;
        }
    }

    return [$roletypes, $userroles];

}

function get_users_in_role($roleid, $courseid) {
    global $DB;
 //Tamard
    //if($origcourse = $DB->get_field('smartquest_anonymcourses', 'origcourse', ['anonymcourse' => $courseid])) {
      //  $context = context_course::instance($origcourse);
   // } else {
        $context = context_course::instance($courseid);
    //}
    $returnusers = [
        0 => get_string('choose', 'smartquest')
    ];

    if (!$roleid) {
        return $returnusers;
    }

    $users = get_role_users($roleid, $context, true, 'ra.id, u.id userid, CONCAT(u.firstname, " ", u.lastname) AS fullname, u.lastname, u.firstname');

    foreach ($users as $user) {
        $returnusers[$user->userid] = $user->fullname;
    }

    //if($origcourse = $DB->get_field('smartquest_anonymcourses', 'origcourse', ['anonymcourse' => $courseid])) {
      //  $users = get_role_users($roleid, context_course::instance($origcourse), true, 'ra.id, u.id userid, CONCAT(u.firstname, " ", u.lastname) AS fullname, u.lastname, u.firstname');
    // }

    foreach ($users as $user) {
        $returnusers[$user->userid] = $user->fullname;
    }

    return $returnusers;
}

function smartquest_get_studeffect_users($sid, $userid = 0, $all= 0) {
    global $DB, $COURSE;

    $context = context_course::instance($COURSE->id);
    $withoutusers = [];
    $users = get_users_by_capability($context, 'mod/smartquest:canbestudsurveyed');
    if ($all == 0) {
        $withoutusers = array_values($DB->get_records_select_menu('smartquest_response', 'survey_id = ? AND aboutuserid != 0', [$sid], '', 'id, aboutuserid'));
    } else { // Tami: add if/else to open all users in the list of the aswer questions - so u can fill in many times on echone.
         $withoutusersobj = $DB->get_records('user', ['suspended' => 1], 'id', 'id');
         foreach ($withoutusersobj as $value) 
             $withoutusers[] = $value->id;
       // $withoutusers = [];
        }

    $returnusers = [];

    usort($users, function($a, $b) {
        return strcmp($a->firstname, $b->firstname);
    });
    //<fix by elisheva@openapp 02_08_2020
    if($userid == 0) {
	$selected = 1;
    }
    else {
	$selected = 0;
    }
    //>
    foreach ($users as $id => $user) {
        if (is_array($withoutusers)) {
            if (in_array($user->id, $withoutusers)) {
                //print_r($user->id . '  |  ');
                continue;
            }
        }
        // Tami 24.11.19 add team & department to each user in the list.
        $select = 'SELECT uif.id, uid.data
                   FROM {user_info_data} uid
                   join {user_info_field} uif on uid.fieldid = uif.id
                   where uid.userid = ? and (uif.id = 4 or uif.id = 5) and uid.data is not null and uid.data != ""';
        $data = $DB->get_records_sql($select, [$user->id]);

        // End tami.
        //$selected = $id == $userid ? 1 : 0;
	//<fix by elisheva@openapp 02_08_2020
	if($user->id == $userid) {
	    $selected = 1;
	}
	//>
        $name = $user->firstname . ' ' . $user->lastname. ' ';
        $name = isset($data[5]) ? $name . '| '. $data[5]->data. ' ' : $name;
        $name = isset($data[4]) ? $name .'| '. $data[4]->data. ' ' : $name;
        $returnusers[] = [
            'selected' => $selected,
            'value' => $user->id,
            'label' => $name
        ];
        $selected = 0;
    }
    return $returnusers;
}

