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

// Library of functions and constants for module smartquest.

/**
 * @package mod_smartquest
 * @copyright  2016 Mike Churchward (mike.churchward@poetgroup.org)
 * @author     Mike Churchward
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('SMARTQUEST_RESETFORM_RESET', 'smartquest_reset_data_');
define('SMARTQUEST_RESETFORM_DROP', 'smartquest_drop_smartquest_');
global $PAGE;
$PAGE->requires->js('/mod/smartquest/javascript/script.js');

function smartquest_supports($feature) {
    switch($feature) {
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;

        default:
            return null;
    }
}

/**
 * @return array all other caps used in module
 */
function smartquest_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

function smartquest_add_instance($smartquest) {
    $userid = optional_param('user_id', 0, PARAM_INT);
    $smartquest->user_id = $userid;
    // Given an object containing all the necessary data,
    // (defined by the form in mod.html) this function
    // will create a new instance and return the id number
    // of the new instance.
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/smartquest/smartquest.class.php');
    require_once($CFG->dirroot.'/mod/smartquest/locallib.php');

    // Check the realm and set it to the survey if it's set.

    if (empty($smartquest->sid)) {
        // Create a new survey.
        $course = get_course($smartquest->course);
        $cm = new stdClass();
        $qobject = new smartquest(0, $smartquest, $course, $cm);

        if ($smartquest->create == 'new-0') {
            $sdata = new stdClass();
            $sdata->name = $smartquest->name;
            $sdata->realm = 'private';
            $sdata->title = $smartquest->name;
            $sdata->subtitle = '';
            $sdata->info = '';
            $sdata->theme = ''; // Theme is deprecated.
            $sdata->thanks_page = '';
            $sdata->thank_head = '';
            $sdata->thank_body = '';
            $sdata->email = '';
            $sdata->feedbacknotes = '';
            $sdata->courseid = $course->id;
            $sdata->anonymoustemplate = 0;
            if (!($sid = $qobject->survey_update($sdata))) {
                print_error('couldnotcreatenewsurvey', 'smartquest');
            }
        } else {
            $copyid = explode('-', $smartquest->create);
            $copyrealm = $copyid[0];
            $copyid = $copyid[1];
            if (empty($qobject->survey)) {
                $qobject->add_survey($copyid);
                $qobject->add_questions($copyid);
            }
            // New smartquests created as "use public" should not create a new survey instance.
            if ($copyrealm == 'public') {
                $sid = $copyid;
            } else {
                $sid = $qobject->sid = $qobject->survey_copy($course->id);
                // All new smartquests should be created as "private".
                // Even if they are *copies* of public or template smartquests.
                $DB->set_field('smartquest_survey', 'realm', 'private', array('id' => $sid));
            }
            // If the survey has dependency data, need to set the smartquest to allow dependencies.
            if ($DB->count_records('smartquest_dependency', ['surveyid' => $sid]) > 0) {
                $smartquest->navigate = 1;
            }
        }
        $smartquest->sid = $sid;
    }

    $smartquest->timemodified = time();

    // May have to add extra stuff in here.
    if (empty($smartquest->useopendate)) {
        $smartquest->opendate = 0;
    }
    if (empty($smartquest->useclosedate)) {
        $smartquest->closedate = 0;
    }

    if ($smartquest->resume == '1') {
        $smartquest->resume = 1;
    } else {
        $smartquest->resume = 0;
    }

    if (!$smartquest->id = $DB->insert_record("smartquest", $smartquest)) {
        return false;
    }

    smartquest_set_events($smartquest);

    $completiontimeexpected = !empty($smartquest->completionexpected) ? $smartquest->completionexpected : null;
    // Core_completion\api::update_completion_date_event($smartquest->coursemodule, 'smartquest',
    // $smartquest->id, $completiontimeexpected);

    return $smartquest->id;
}

// Given an object containing all the necessary data,
// (defined by the form in mod.html) this function
// will update an existing instance with new data.
function smartquest_update_instance($smartquest) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/smartquest/locallib.php');

    // Check the realm and set it to the survey if its set.
    if (!empty($smartquest->sid) && !empty($smartquest->realm)) {
        $DB->set_field('smartquest_survey', 'realm', $smartquest->realm, array('id' => $smartquest->sid));
    }

    $smartquest->timemodified = time();
    $smartquest->id = $smartquest->instance;

    // May have to add extra stuff in here.
    if (empty($smartquest->useopendate)) {
        $smartquest->opendate = 0;
    }
    if (empty($smartquest->useclosedate)) {
        $smartquest->closedate = 0;
    }

    if ($smartquest->resume == '1') {
        $smartquest->resume = 1;
    } else {
        $smartquest->resume = 0;
    }

    // Get existing grade item.
    smartquest_grade_item_update($smartquest);

    smartquest_set_events($smartquest);

    $completiontimeexpected = !empty($smartquest->completionexpected) ? $smartquest->completionexpected : null;
   // Core_completion\api::update_completion_date_event($smartquest->coursemodule, 'smartquest', $smartquest->id, $completiontimeexpected);

    return $DB->update_record("smartquest", $smartquest);
}

// Given an ID of an instance of this module,
// this function will permanently delete the instance
// and any data that depends on it.
function smartquest_delete_instance($id) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/smartquest/locallib.php');

    if (! $smartquest = $DB->get_record('smartquest', array('id' => $id))) {
        return false;
    }

    $result = true;

    if ($events = $DB->get_records('event', array("modulename" => 'smartquest', "instance" => $smartquest->id))) {
        foreach ($events as $event) {
            $event = calendar_event::load($event);
            $event->delete();
        }
    }

    if (! $DB->delete_records('smartquest', array('id' => $smartquest->id))) {
        $result = false;
    }

    if ($survey = $DB->get_record('smartquest_survey', array('id' => $smartquest->sid))) {
        // If this survey is owned by this course, delete all of the survey records and responses.
        if ($survey->courseid == $smartquest->course) {
            $result = $result && smartquest_delete_survey($smartquest->sid, $smartquest->id);
        }
    }

    return $result;
}

// Return a small object with summary information about what a
// user has done with a given particular instance of this module
// Used for user activity reports.
// $return->time = the time they did it
// $return->info = a short text description.
/**
 * $course and $mod are unused, but API requires them. Suppress PHPMD warning.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function smartquest_user_outline($course, $user, $mod, $smartquest) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/smartquest/locallib.php');

    $result = new stdClass();
    if ($responses = smartquest_get_user_responses($smartquest->sid, $user->id, true)) {
        $n = count($responses);
        if ($n == 1) {
            $result->info = $n.' '.get_string("response", "smartquest");
        } else {
            $result->info = $n.' '.get_string("responses", "smartquest");
        }
        $lastresponse = array_pop($responses);
        $result->time = $lastresponse->submitted;
    } else {
        $result->info = get_string("noresponses", "smartquest");
    }
    return $result;
}

// Print a detailed representation of what a  user has done with
// a given particular instance of this module, for user activity reports.
/**
 * $course and $mod are unused, but API requires them. Suppress PHPMD warning.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function smartquest_user_complete($course, $user, $mod, $smartquest) {
    global $CFG;
    require_once($CFG->dirroot . '/mod/smartquest/locallib.php');

    if ($responses = smartquest_get_user_responses($smartquest->sid, $user->id, false)) {
        foreach ($responses as $response) {
            if ($response->complete == 'y') {
                echo get_string('submitted', 'smartquest').' '.userdate($response->submitted).'<br />';
            } else {
                echo get_string('attemptstillinprogress', 'smartquest').' '.userdate($response->submitted).'<br />';
            }
        }
    } else {
        print_string('noresponses', 'smartquest');
    }

    return true;
}

// Given a course and a time, this module should find recent activity
// that has occurred in smartquest activities and print it out.
// Return true if there was output, or false is there was none.
/**
 * $course, $isteacher and $timestart are unused, but API requires them. Suppress PHPMD warning.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function smartquest_print_recent_activity($course, $isteacher, $timestart) {
    return false;  // True if anything was printed, otherwise false.
}

// Must return an array of grades for a given instance of this module,
// indexed by user.  It also returns a maximum allowed grade.
/**
 * $smartquestid is unused, but API requires it. Suppress PHPMD warning.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function smartquest_grades($smartquestid) {
    return null;
}

/**
 * Return grade for given user or all users.
 *
 * @param int $smartquestid id of assignment
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function smartquest_get_user_grades($smartquest, $userid=0) {
    global $DB;
    $params = array();
    $usersql = '';
    if (!empty($userid)) {
        $usersql = "AND u.id = ?";
        $params[] = $userid;
    }

    $sql = "SELECT a.id, u.id AS userid, r.grade AS rawgrade, r.submitted AS dategraded, r.submitted AS datesubmitted
            FROM {user} u, {smartquest_attempts} a, {smartquest_response} r
            WHERE u.id = a.userid AND a.qid = $smartquest->id AND r.id = a.rid $usersql";
    return $DB->get_records_sql($sql, $params);
}

/**
 * Update grades by firing grade_updated event
 *
 * @param object $assignment null means all assignments
 * @param int $userid specific user only, 0 mean all
 *
 * $nullifnone is unused, but API requires it. Suppress PHPMD warning.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function smartquest_update_grades($smartquest=null, $userid=0, $nullifnone=true) {
    global $CFG, $DB;

    if (!function_exists('grade_update')) { // Workaround for buggy PHP versions.
        require_once($CFG->libdir.'/gradelib.php');
    }

    if ($smartquest != null) {
        if ($graderecs = smartquest_get_user_grades($smartquest, $userid)) {
            $grades = array();
            foreach ($graderecs as $v) {
                if (!isset($grades[$v->userid])) {
                    $grades[$v->userid] = new stdClass();
                    if ($v->rawgrade == -1) {
                        $grades[$v->userid]->rawgrade = null;
                    } else {
                        $grades[$v->userid]->rawgrade = $v->rawgrade;
                    }
                    $grades[$v->userid]->userid = $v->userid;
                } else if (isset($grades[$v->userid]) && ($v->rawgrade > $grades[$v->userid]->rawgrade)) {
                    $grades[$v->userid]->rawgrade = $v->rawgrade;
                }
            }
            smartquest_grade_item_update($smartquest, $grades);
        } else {
            smartquest_grade_item_update($smartquest);
        }

    } else {
        $sql = "SELECT q.*, cm.idnumber as cmidnumber, q.course as courseid
                  FROM {smartquest} q, {course_modules} cm, {modules} m
                 WHERE m.name='smartquest' AND m.id=cm.module AND cm.instance=q.id";
        if ($rs = $DB->get_recordset_sql($sql)) {
            foreach ($rs as $smartquest) {
                if ($smartquest->grade != 0) {
                    smartquest_update_grades($smartquest);
                } else {
                    smartquest_grade_item_update($smartquest);
                }
            }
            $rs->close();
        }
    }
}

/**
 * Create grade item for given smartquest
 *
 * @param object $smartquest object with extra cmidnumber
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function smartquest_grade_item_update($smartquest, $grades = null) {
    global $CFG;
    if (!function_exists('grade_update')) { // Workaround for buggy PHP versions.
        require_once($CFG->libdir.'/gradelib.php');
    }

    if (!isset($smartquest->courseid)) {
        $smartquest->courseid = $smartquest->course;
    }

    if ($smartquest->cmidnumber != '') {
        $params = array('itemname' => $smartquest->name, 'idnumber' => $smartquest->cmidnumber);
    } else {
        $params = array('itemname' => $smartquest->name);
    }

    if ($smartquest->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $smartquest->grade;
        $params['grademin']  = 0;

    } else if ($smartquest->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$smartquest->grade;

    } else if ($smartquest->grade == 0) { // No Grade..be sure to delete the grade item if it exists.
        $grades = null;
        $params = array('deleted' => 1);

    } else {
        $params = null; // Allow text comments only.
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/smartquest', $smartquest->courseid, 'mod', 'smartquest',
                    $smartquest->id, 0, $grades, $params);
}

/**
 * This function returns if a scale is being used by one smartquest
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 * @param $smartquestid int
 * @param $scaleid int
 * @return boolean True if the scale is used by any smartquest
 *
 * Function parameters are unused, but API requires them. Suppress PHPMD warning.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function smartquest_scale_used ($smartquestid, $scaleid) {
    return false;
}

/**
 * Checks if scale is being used by any instance of smartquest
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any smartquest
 *
 * Function parameters are unused, but API requires them. Suppress PHPMD warning.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function smartquest_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * Serves the smartquest attachments. Implements needed access control ;-)
 *
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool false if file not found, does not return if found - justsend the file
 *
 * $forcedownload is unused, but API requires it. Suppress PHPMD warning.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function smartquest_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    $fileareas = ['intro', 'info', 'thankbody', 'question', 'feedbacknotes', 'sectionheading', 'feedback'];
    if (!in_array($filearea, $fileareas)) {
        return false;
    }

    $componentid = (int)array_shift($args);

    if ($filearea == 'question') {
        if (!$DB->record_exists('smartquest_question', ['id' => $componentid])) {
            return false;
        }
    } else if ($filearea == 'sectionheading') {
        if (!$DB->record_exists('smartquest_fb_sections', ['id' => $componentid])) {
            return false;
        }
    } else if ($filearea == 'feedback') {
        if (!$DB->record_exists('smartquest_feedback', ['id' => $componentid])) {
            return false;
        }
    } else {
        if (!$DB->record_exists('smartquest_survey', ['id' => $componentid])) {
            return false;
        }
    }

    if (!$DB->record_exists('smartquest', ['id' => $cm->instance])) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_smartquest/$filearea/$componentid/$relativepath";
    if (!($file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
        return false;
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, true); // Download MUST be forced - security!
}
/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $smartquestnode The node to add module settings to
 *
 * $settings is unused, but API requires it. Suppress PHPMD warning.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function smartquest_extend_settings_navigation(settings_navigation $settings,
        navigation_node $smartquestnode) {

    global $PAGE, $DB, $USER, $CFG;
    $individualresponse = optional_param('individualresponse', false, PARAM_INT);
    $rid = optional_param('rid', false, PARAM_INT); // Response id.
    $currentgroupid = optional_param('group', 0, PARAM_INT); // Group id.

    require_once($CFG->dirroot.'/mod/smartquest/smartquest.class.php');

    $context = $PAGE->cm->context;
    $cmid = $PAGE->cm->id;
    $cm = $PAGE->cm;
    $course = $PAGE->course;

    if (! $smartquest = $DB->get_record("smartquest", array("id" => $cm->instance))) {
        print_error('invalidcoursemodule');
    }

    $courseid = $course->id;
    $smartquest = new smartquest(0, $smartquest, $course, $cm);

    if ($owner = $DB->get_field('smartquest_survey', 'courseid', ['id' => $smartquest->sid])) {
        $owner = (trim($owner) == trim($courseid));
    } else {
        $owner = true;
    }

    // On view page, currentgroupid is not yet sent as an optional_param, so get it.
    $groupmode = groups_get_activity_groupmode($cm, $course);
    if ($groupmode > 0 && $currentgroupid == 0) {
        $currentgroupid = groups_get_activity_group($smartquest->cm);
        if (!groups_is_member($currentgroupid, $USER->id)) {
            $currentgroupid = 0;
        }
    }

    // We want to add these new nodes after the Edit settings node, and before the
    // Locally assigned roles node. Of course, both of those are controlled by capabilities.
    $keys = $smartquestnode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if (($i === false) && array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }

    if (has_capability('mod/smartquest:manage', $context) && $owner) {
        $url = '/mod/smartquest/qsettings.php';
        $node = navigation_node::create(get_string('advancedsettings'),
            new moodle_url($url, array('id' => $cmid)),
            navigation_node::TYPE_SETTING, null, 'advancedsettings',
            new pix_icon('t/edit', ''));
        $smartquestnode->add_node($node, $beforekey);
    }

    if (has_capability('mod/smartquest:editquestions', $context) && $owner) {
//Tamard
//        if(!$DB->record_exists('smartquest_anonymcourses', ['anonymcourse' => $courseid]) || has_capability('block/smartquest:addquestionssmartquest', $context)) { 
//	        $url = '/mod/smartquest/questions.php';
  //      	$node = navigation_node::create(get_string('questions', 'smartquest'),
    //        	new moodle_url($url, array('id' => $cmid)),
      //      	navigation_node::TYPE_SETTING, null, 'questions',
        //    	new pix_icon('t/edit', ''));
        //	$smartquestnode->add_node($node, $beforekey);
//	}
    }

    if (has_capability('mod/smartquest:preview', $context)) {
        $url = '/mod/smartquest/preview.php';
        $node = navigation_node::create(get_string('preview_label', 'smartquest'),
            new moodle_url($url, array('id' => $cmid)),
            navigation_node::TYPE_SETTING, null, 'preview',
            new pix_icon('t/preview', ''));
        $smartquestnode->add_node($node, $beforekey);
    }

    if (has_capability('mod/smartquest:editquestions', $context)) {
        $url = '/mod/smartquest/questions.php';
        $node = navigation_node::create(get_string('edit_questions', 'smartquest'),
            new moodle_url($url, array('id' => $cmid)),
            navigation_node::TYPE_SETTING, null, 'edit');
        $smartquestnode->add_node($node, $beforekey);
    }

    if ($smartquest->user_can_take($USER->id) && (($smartquest->rtype != STUDEFFECT && $smartquest->rtype != GUIDELINE) || count(smartquest_get_studeffect_users($smartquest->survey->id)))) {
        $url = '/mod/smartquest/complete.php';
        if ($smartquest->user_has_saved_response($USER->id)) {
            $args = ['id' => $cmid, 'resume' => 1];
            $text = get_string('resumesurvey', 'smartquest');
        } else {
            $args = ['id' => $cmid];
            $text = get_string('answerquestions', 'smartquest');
        }
        $node = navigation_node::create($text, new moodle_url($url, $args),
            navigation_node::TYPE_SETTING, null, '', new pix_icon('i/info', 'answerquestions'));
        $smartquestnode->add_node($node, $beforekey);
    }
    $usernumresp = $smartquest->count_submissions($USER->id);

    if ($smartquest->capabilities->readownresponses && ($usernumresp > 0)) {
        $url = '/mod/smartquest/myreport.php';

        if ($usernumresp > 1) {
            $urlargs = array('instance' => $smartquest->id, 'userid' => $USER->id,
                'byresponse' => 0, 'action' => 'summary', 'group' => $currentgroupid);
            $node = navigation_node::create(get_string('yourresponses', 'smartquest'),
                new moodle_url($url, $urlargs), navigation_node::TYPE_SETTING, null, 'yourresponses');
            $myreportnode = $smartquestnode->add_node($node, $beforekey);

            $urlargs = array('instance' => $smartquest->id, 'userid' => $USER->id,
                'byresponse' => 0, 'action' => 'summary', 'group' => $currentgroupid);
            $myreportnode->add(get_string('summary', 'smartquest'), new moodle_url($url, $urlargs));

            $urlargs = array('instance' => $smartquest->id, 'userid' => $USER->id,
                'byresponse' => 1, 'action' => 'vresp', 'group' => $currentgroupid);
            $byresponsenode = $myreportnode->add(get_string('viewindividualresponse', 'smartquest'),
                new moodle_url($url, $urlargs));

            $urlargs = array('instance' => $smartquest->id, 'userid' => $USER->id,
                'byresponse' => 0, 'action' => 'vall', 'group' => $currentgroupid);
            $myreportnode->add(get_string('myresponses', 'smartquest'), new moodle_url($url, $urlargs));
            if ($smartquest->capabilities->downloadresponses) {
                $urlargs = array('instance' => $smartquest->id, 'user' => $USER->id,
                    'action' => 'dwnpg', 'group' => $currentgroupid);
                $myreportnode->add(get_string('downloadtext'), new moodle_url('/mod/smartquest/report.php', $urlargs));
            }
        } else {
            $urlargs = array('instance' => $smartquest->id, 'userid' => $USER->id,
                'byresponse' => 1, 'action' => 'vresp', 'group' => $currentgroupid);
            $node = navigation_node::create(get_string('yourresponse', 'smartquest'),
                new moodle_url($url, $urlargs), navigation_node::TYPE_SETTING, null, 'yourresponse');
            $myreportnode = $smartquestnode->add_node($node, $beforekey);
        }
    }

    // If smartquest is set to separate groups, prevent user who is not member of any group
    // and is not a non-editing teacher to view All responses.
    if ($smartquest->can_view_all_responses($usernumresp)) {

        $url = '/mod/smartquest/report.php';
        $node = navigation_node::create(get_string('viewallresponses', 'smartquest'),
            new moodle_url($url, array('instance' => $smartquest->id, 'action' => 'vall')),
            navigation_node::TYPE_SETTING, null, 'vall');
        $reportnode = $smartquestnode->add_node($node, $beforekey);

        if ($smartquest->capabilities->viewsingleresponse) {
            $summarynode = $reportnode->add(get_string('summary', 'smartquest'),
                new moodle_url('/mod/smartquest/report.php',
                    array('instance' => $smartquest->id, 'action' => 'vall')));
        } else {
            $summarynode = $reportnode;
        }
        $summarynode->add(get_string('order_default', 'smartquest'),
            new moodle_url('/mod/smartquest/report.php',
                array('instance' => $smartquest->id, 'action' => 'vall', 'group' => $currentgroupid)));
        $summarynode->add(get_string('order_ascending', 'smartquest'),
            new moodle_url('/mod/smartquest/report.php',
                array('instance' => $smartquest->id, 'action' => 'vallasort', 'group' => $currentgroupid)));
        $summarynode->add(get_string('order_descending', 'smartquest'),
            new moodle_url('/mod/smartquest/report.php',
                array('instance' => $smartquest->id, 'action' => 'vallarsort', 'group' => $currentgroupid)));

        if ($smartquest->capabilities->deleteresponses) {
            $summarynode->add(get_string('deleteallresponses', 'smartquest'),
                new moodle_url('/mod/smartquest/report.php',
                    array('instance' => $smartquest->id, 'action' => 'delallresp', 'group' => $currentgroupid)));
        }

        if ($smartquest->capabilities->downloadresponses) {
            $summarynode->add(get_string('downloadtextformat', 'smartquest'),
                new moodle_url('/mod/smartquest/report.php',
                    array('instance' => $smartquest->id, 'action' => 'dwnpg', 'group' => $currentgroupid)));
        }
        if ($smartquest->capabilities->viewsingleresponse) {
            $byresponsenode = $reportnode->add(get_string('viewbyresponse', 'smartquest'),
                new moodle_url('/mod/smartquest/report.php',
                    array('instance' => $smartquest->id, 'action' => 'vresp', 'byresponse' => 1, 'group' => $currentgroupid)));

            $byresponsenode->add(get_string('view', 'smartquest'),
                new moodle_url('/mod/smartquest/report.php',
                    array('instance' => $smartquest->id, 'action' => 'vresp', 'byresponse' => 1, 'group' => $currentgroupid)));

            if ($individualresponse) {
                $byresponsenode->add(get_string('deleteresp', 'smartquest'),
                    new moodle_url('/mod/smartquest/report.php',
                        array('instance' => $smartquest->id, 'action' => 'dresp', 'byresponse' => 1,
                            'rid' => $rid, 'group' => $currentgroupid, 'individualresponse' => 1)));
            }
        }
    }

    $canviewgroups = true;
    $groupmode = groups_get_activity_groupmode($cm, $course);
    if ($groupmode == 1) {
        $canviewgroups = groups_has_membership($cm, $USER->id);
    }
    $canviewallgroups = has_capability('moodle/site:accessallgroups', $context);
    if ($smartquest->capabilities->viewsingleresponse && ($canviewallgroups || $canviewgroups)) {
        $url = '/mod/smartquest/show_nonrespondents.php';
        $node = navigation_node::create(get_string('show_nonrespondents', 'smartquest'),
            new moodle_url($url, array('id' => $cmid)),
            navigation_node::TYPE_SETTING, null, 'nonrespondents');
        $smartquestnode->add_node($node, $beforekey);

    }
}

// Any other smartquest functions go here.  Each of them must have a name that
// starts with smartquest_.

function smartquest_get_view_actions() {
    return array('view', 'view all');
}

function smartquest_get_post_actions() {
    return array('submit', 'update');
}

function smartquest_get_recent_mod_activity(&$activities, &$index, $timestart,
                $courseid, $cmid, $userid = 0, $groupid = 0) {

    global $CFG, $COURSE, $USER, $DB;
    require_once($CFG->dirroot . '/mod/smartquest/locallib.php');

    if ($COURSE->id == $courseid) {
        $course = $COURSE;
    } else {
        $course = $DB->get_record('course', array('id' => $courseid));
    }

    $modinfo = get_fast_modinfo($course);

    $cm = $modinfo->cms[$cmid];
    $smartquest = $DB->get_record('smartquest', array('id' => $cm->instance));

    $context = context_module::instance($cm->id);
    $grader = has_capability('mod/smartquest:viewsingleresponse', $context);

    // If this is a copy of a public smartquest whose original is located in another course,
    // current user (teacher) cannot view responses.
    if ($grader && $survey = $DB->get_record('smartquest_survey', array('id' => $smartquest->sid))) {
        // For a public smartquest, look for the original public smartquest that it is based on.
        if ($survey->realm == 'public' && $survey->courseid != $course->id) {
            // For a public smartquest, look for the original public smartquest that it is based on.
            $originalsmartquest = $DB->get_record('smartquest', ['sid' => $survey->id, 'course' => $survey->courseid]);
            $cmoriginal = get_coursemodule_from_instance("smartquest", $originalsmartquest->id, $survey->courseid);
            $contextoriginal = context_course::instance($survey->courseid, MUST_EXIST);
            if (!has_capability('mod/smartquest:viewsingleresponse', $contextoriginal)) {
                $tmpactivity = new stdClass();
                $tmpactivity->type = 'smartquest';
                $tmpactivity->cmid = $cm->id;
                $tmpactivity->cannotview = true;
                $tmpactivity->anonymous = false;
                $activities[$index++] = $tmpactivity;
                return $activities;
            }
        }
    }

    if ($userid) {
        $userselect = "AND u.id = :userid";
        $params['userid'] = $userid;
    } else {
        $userselect = '';
    }

    if ($groupid) {
        $groupselect = 'AND gm.groupid = :groupid';
        $groupjoin   = 'JOIN {groups_members} gm ON  gm.userid=u.id';
        $params['groupid'] = $groupid;
    } else {
        $groupselect = '';
        $groupjoin   = '';
    }

    $params['timestart'] = $timestart;
    $params['smartquestid'] = $smartquest->sid;

    $ufields = user_picture::fields('u', null, 'useridagain');
    if (!$attempts = $DB->get_records_sql("
                    SELECT qr.*,
                    {$ufields}
                    FROM {smartquest_response} qr
                    JOIN {user} u ON u.id = qr.userid
                    $groupjoin
                    WHERE qr.submitted > :timestart
                    AND qr.survey_id = :smartquestid
                    $userselect
                    $groupselect
                    ORDER BY qr.submitted ASC", $params)) {
        return;
    }

    $accessallgroups = has_capability('moodle/site:accessallgroups', $context);
    $viewfullnames   = has_capability('moodle/site:viewfullnames', $context);
    $groupmode       = groups_get_activity_groupmode($cm, $course);

    $usersgroups = null;
    $aname = format_string($cm->name, true);
    $userattempts = array();
    foreach ($attempts as $attempt) {
        if ($smartquest->respondenttype != 'anonymous') {
            if (!isset($userattempts[$attempt->lastname])) {
                $userattempts[$attempt->lastname] = 1;
            } else {
                $userattempts[$attempt->lastname]++;
            }
        }
        if ($attempt->userid != $USER->id) {
            if (!$grader) {
                // View complete individual responses permission required.
                continue;
            }

            if (($groupmode == SEPARATEGROUPS) && !$accessallgroups) {
                if ($usersgroups === null) {
                    $usersgroups = groups_get_all_groups($course->id,
                    $attempt->userid, $cm->groupingid);
                    if (is_array($usersgroups)) {
                        $usersgroups = array_keys($usersgroups);
                    } else {
                         $usersgroups = array();
                    }
                }
                if (!array_intersect($usersgroups, $modinfo->groups[$cm->id])) {
                    continue;
                }
            }
        }

        $tmpactivity = new stdClass();

        $tmpactivity->type       = 'smartquest';
        $tmpactivity->cmid       = $cm->id;
        $tmpactivity->cminstance = $cm->instance;
        // Current user is admin - or teacher enrolled in original public course.
        if (isset($cmoriginal)) {
            $tmpactivity->cminstance = $cmoriginal->instance;
        }
        $tmpactivity->cannotview = false;
        $tmpactivity->anonymous  = false;
        $tmpactivity->name       = $aname;
        $tmpactivity->sectionnum = $cm->sectionnum;
        $tmpactivity->timestamp  = $attempt->submitted;
        $tmpactivity->groupid    = $groupid;
        if (isset($userattempts[$attempt->lastname])) {
            $tmpactivity->nbattempts = $userattempts[$attempt->lastname];
        }

        $tmpactivity->content = new stdClass();
        $tmpactivity->content->attemptid = $attempt->id;

        $userfields = explode(',', user_picture::fields());
        $tmpactivity->user = new stdClass();
        foreach ($userfields as $userfield) {
            if ($userfield == 'id') {
                $tmpactivity->user->{$userfield} = $attempt->userid;
            } else {
                if (!empty($attempt->{$userfield})) {
                    $tmpactivity->user->{$userfield} = $attempt->{$userfield};
                } else {
                    $tmpactivity->user->{$userfield} = null;
                }
            }
        }
        if ($smartquest->respondenttype != 'anonymous') {
            $tmpactivity->user->fullname  = fullname($attempt, $viewfullnames);
        } else {
            $tmpactivity->user = '';
            unset ($tmpactivity->user);
            $tmpactivity->anonymous = true;
        }
        $activities[$index++] = $tmpactivity;
    }
}

/**
 * Prints all users who have completed a specified smartquest since a given time
 *
 * @global object
 * @param object $activity
 * @param int $courseid
 * @param string $detail not used but needed for compability
 * @param array $modnames
 * @return void Output is echo'd
 *
 * $details and $modenames are unused, but API requires them. Suppress PHPMD warning.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function smartquest_print_recent_mod_activity($activity, $courseid, $detail, $modnames) {
    global $OUTPUT;

    // If the smartquest is "anonymous", then $activity->user won't have been set, so do not display respondent info.
    if ($activity->anonymous) {
        $stranonymous = ' ('.get_string('anonymous', 'smartquest').')';
        $activity->nbattempts = '';
    } else {
        $stranonymous = '';
    }
    // Current user cannot view responses to public smartquest.
    if ($activity->cannotview) {
        $strcannotview = get_string('cannotviewpublicresponses', 'smartquest');
    }
    echo html_writer::start_tag('div');
    echo html_writer::start_tag('span', array('class' => 'clearfix',
                    'style' => 'margin-top:0px; background-color: white; display: inline-block;'));

    if (!$activity->anonymous && !$activity->cannotview) {
        echo html_writer::tag('div', $OUTPUT->user_picture($activity->user, array('courseid' => $courseid)),
                        array('style' => 'float: left; padding-right: 10px;'));
    }
    if (!$activity->cannotview) {
        echo html_writer::start_tag('div');
        echo html_writer::start_tag('div');

        $urlparams = array('action' => 'vresp', 'instance' => $activity->cminstance,
                        'group' => $activity->groupid, 'rid' => $activity->content->attemptid, 'individualresponse' => 1);

        $context = context_module::instance($activity->cmid);
        if (has_capability('mod/smartquest:viewsingleresponse', $context)) {
            $report = 'report.php';
        } else {
            $report = 'myreport.php';
        }
        echo html_writer::tag('a', get_string('response', 'smartquest').' '.$activity->nbattempts.$stranonymous,
                        array('href' => new moodle_url('/mod/smartquest/'.$report, $urlparams)));
        echo html_writer::end_tag('div');
    } else {
        echo html_writer::start_tag('div');
        echo html_writer::start_tag('div');
        echo html_writer::tag('div', $strcannotview);
        echo html_writer::end_tag('div');
    }
    if (!$activity->anonymous  && !$activity->cannotview) {
        $url = new moodle_url('/user/view.php', array('course' => $courseid, 'id' => $activity->user->id));
        $name = $activity->user->fullname;
        $link = html_writer::link($url, $name);
        echo html_writer::start_tag('div', array('class' => 'user'));
        echo $link .' - '. userdate($activity->timestamp);
        echo html_writer::end_tag('div');
    }

    echo html_writer::end_tag('div');
    echo html_writer::end_tag('span');
    echo html_writer::end_tag('div');

    return;
}

/**
 * Prints smartquest summaries on 'My home' page
 *
 * Prints smartquest name, due date and attempt information on
 * smartquests that have a deadline that has not already passed
 * and it is available for taking.
 *
 * @global object
 * @global stdClass
 * @global object
 * @uses CONTEXT_MODULE
 * @param array $courses An array of course objects to get smartquest instances from
 * @param array $htmlarray Store overview output array( course ID => 'smartquest' => HTML output )
 * @return void
 */
function smartquest_print_overview($courses, &$htmlarray) {
    global $USER, $CFG, $DB, $OUTPUT;

    require_once($CFG->dirroot . '/mod/smartquest/locallib.php');

    if (!$smartquests = get_all_instances_in_courses('smartquest', $courses)) {
        return;
    }

    // Get Necessary Strings.
    $strsmartquest       = get_string('modulename', 'smartquest');
    $strnotattempted = get_string('noattempts', 'smartquest');
    $strattempted    = get_string('attempted', 'smartquest');
    $strsavedbutnotsubmitted = get_string('savedbutnotsubmitted', 'smartquest');

    $now = time();
    foreach ($smartquests as $smartquest) {

        // The smartquest has a deadline.
        if (($smartquest->closedate != 0)
                        // And it is before the deadline has been met.
                        && ($smartquest->closedate >= $now)
                        // And the smartquest is available.
                        && (($smartquest->opendate == 0) || ($smartquest->opendate <= $now))) {
            if (!$smartquest->visible) {
                $class = ' class="dimmed"';
            } else {
                $class = '';
            }
            $str = $OUTPUT->box("$strsmartquest:
                            <a$class href=\"$CFG->wwwroot/mod/smartquest/view.php?id=$smartquest->coursemodule\">".
                            format_string($smartquest->name).'</a>', 'name');

            // Deadline.
            $str .= $OUTPUT->box(get_string('closeson', 'smartquest', userdate($smartquest->closedate)), 'info');
            $select = 'qid = '.$smartquest->id.' AND userid = '.$USER->id;
            $attempts = $DB->get_records_select('smartquest_attempts', $select);
            $nbattempts = count($attempts);

            // Do not display a smartquest as due if it can only be sumbitted once and it has already been submitted!
            if ($nbattempts != 0 && $smartquest->qtype == SMARTQUESTONCE) {
                continue;
            }

            // Attempt information.
            if (has_capability('mod/smartquest:manage', context_module::instance($smartquest->coursemodule))) {
                // Number of user attempts.
                $attempts = $DB->count_records('smartquest_attempts', array('id' => $smartquest->id));
                $str .= $OUTPUT->box(get_string('numattemptsmade', 'smartquest', $attempts), 'info');
            } else {
                if ($responses = smartquest_get_user_responses($smartquest->sid, $USER->id, false)) {
                    foreach ($responses as $response) {
                        if ($response->complete == 'y') {
                            $str .= $OUTPUT->box($strattempted, 'info');
                            break;
                        } else {
                            $str .= $OUTPUT->box($strsavedbutnotsubmitted, 'info');
                        }
                    }
                } else {
                    $str .= $OUTPUT->box($strnotattempted, 'info');
                }
            }
            $str = $OUTPUT->box($str, 'smartquest overview');

            if (empty($htmlarray[$smartquest->course]['smartquest'])) {
                $htmlarray[$smartquest->course]['smartquest'] = $str;
            } else {
                $htmlarray[$smartquest->course]['smartquest'] .= $str;
            }
        }
    }
}


/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the smartquest.
 *
 * @param $mform the course reset form that is being built.
 */
function smartquest_reset_course_form_definition($mform) {
    $mform->addElement('header', 'smartquestheader', get_string('modulenameplural', 'smartquest'));
    $mform->addElement('advcheckbox', 'reset_smartquest',
                    get_string('removeallsmartquestattempts', 'smartquest'));
}

/**
 * Course reset form defaults.
 * @return array the defaults.
 *
 * Function parameters are unused, but API requires them. Suppress PHPMD warning.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function smartquest_reset_course_form_defaults($course) {
    return array('reset_smartquest' => 1);
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * smartquest responses for course $data->courseid, if $data->reset_smartquest_attempts is
 * set and true.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function smartquest_reset_userdata($data) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/questionlib.php');
    require_once($CFG->dirroot.'/mod/smartquest/locallib.php');

    $componentstr = get_string('modulenameplural', 'smartquest');
    $status = array();

    if (!empty($data->reset_smartquest)) {
        $surveys = smartquest_get_survey_list($data->courseid, '');

        // Delete responses.
        foreach ($surveys as $survey) {
            // Get all responses for this smartquest.
            $sql = "SELECT R.id, R.survey_id, R.submitted, R.userid
                 FROM {smartquest_response} R
                 WHERE R.survey_id = ?
                 ORDER BY R.id";
            $resps = $DB->get_records_sql($sql, array($survey->id));
            if (!empty($resps)) {
                $smartquest = $DB->get_record("smartquest", ["sid" => $survey->id, "course" => $survey->courseid]);
                $smartquest->course = $DB->get_record("course", array("id" => $smartquest->course));
                foreach ($resps as $response) {
                    smartquest_delete_response($response, $smartquest);
                }
            }
            // Remove this smartquest's grades (and feedback) from gradebook (if any).
            $select = "itemmodule = 'smartquest' AND iteminstance = ".$survey->qid;
            $fields = 'id';
            if ($itemid = $DB->get_record_select('grade_items', $select, null, $fields)) {
                $itemid = $itemid->id;
                $DB->delete_records_select('grade_grades', 'itemid = '.$itemid);

            }
        }
        $status[] = array(
                        'component' => $componentstr,
                        'item' => get_string('deletedallresp', 'smartquest'),
                        'error' => false);

        $status[] = array(
                        'component' => $componentstr,
                        'item' => get_string('gradesdeleted', 'smartquest'),
                        'error' => false);
    }
    return $status;
}

/**
 * Obtains the automatic completion state for this smartquest based on the condition
 * in smartquest settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 *
 * $course is unused, but API requires it. Suppress PHPMD warning.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function smartquest_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    // Get smartquest details.
    $smartquest = $DB->get_record('smartquest', array('id' => $cm->instance), '*', MUST_EXIST);

    // If completion option is enabled, evaluate it and return true/false.
    if ($smartquest->completionsubmit) {
        $params = array('userid' => $userid, 'qid' => $smartquest->id);
        return $DB->record_exists('smartquest_attempts', $params);
    } else {
        // Completion option is not enabled so just return $type.
        return $type;
    }
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_smartquest_core_calendar_provide_event_action(calendar_event $event,
                                                            \core_calendar\action_factory $factory) {
    $cm = get_fast_modinfo($event->courseid)->instances['smartquest'][$event->instance];

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    return $factory->create_instance(
            get_string('view'),
            new \moodle_url('/mod/smartquest/view.php', ['id' => $cm->id]),
            1,
            true
    );
}

