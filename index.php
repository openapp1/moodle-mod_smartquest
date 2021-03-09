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
 * This script lists all the instances of smartquest in a particular course
 *
 * @package    mod
 * @subpackage smartquest
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once("../../config.php");
require_once($CFG->dirroot.'/mod/smartquest/locallib.php');

$id = required_param('id', PARAM_INT);
$PAGE->set_url('/mod/smartquest/index.php', array('id' => $id));
if (! $course = $DB->get_record('course', array('id' => $id))) {
    print_error('incorrectcourseid', 'smartquest');
}
$coursecontext = context_course::instance($id);
require_login($course->id);
$PAGE->set_pagelayout('incourse');

$event = \mod_smartquest\event\course_module_instance_list_viewed::create(array(
                'context' => context_course::instance($course->id)));
$event->trigger();

// Print the header.
$strsmartquests = get_string("modulenameplural", "smartquest");
$PAGE->navbar->add($strsmartquests);
$PAGE->set_title("$course->shortname: $strsmartquests");
$PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();

// Get all the appropriate data.
if (!$smartquests = get_all_instances_in_course("smartquest", $course)) {
    notice(get_string('thereareno', 'moodle', $strsmartquests), "../../course/view.php?id=$course->id");
    die;
}

// Check if we need the closing date header.
$showclosingheader = false;
foreach ($smartquests as $smartquest) {
    if ($smartquest->closedate != 0) {
        $showclosingheader = true;
    }
    if ($showclosingheader) {
        break;
    }
}

// Configure table for displaying the list of instances.
$headings = array(get_string('name'));
$align = array('left');

if ($showclosingheader) {
    array_push($headings, get_string('smartquestcloses', 'smartquest'));
    array_push($align, 'left');
}

array_unshift($headings, get_string('sectionname', 'format_'.$course->format));
array_unshift($align, 'left');

$showing = '';

// Current user role == admin or teacher.
if (has_capability('mod/smartquest:viewsingleresponse', $coursecontext)) {
    array_push($headings, get_string('responses', 'smartquest'));
    array_push($align, 'center');
    $showing = 'stats';
    array_push($headings, get_string('realm', 'smartquest'));
    array_push($align, 'left');
    // Current user role == student.
} else if (has_capability('mod/smartquest:submit', $coursecontext)) {
    array_push($headings, get_string('status'));
    array_push($align, 'left');
    $showing = 'responses';
}

$table = new html_table();
$table->head = $headings;
$table->align = $align;

// Populate the table with the list of instances.
$currentsection = '';
foreach ($smartquests as $smartquest) {
    $cmid = $smartquest->coursemodule;
    $data = array();
    $realm = $DB->get_field('smartquest_survey', 'realm', array('id' => $smartquest->sid));
    // Template surveys should NOT be displayed as an activity to students.
    if (!($realm == 'template' && !has_capability('mod/smartquest:manage', context_module::instance($cmid)))) {
        // Section number if necessary.
        $strsection = '';
        if ($smartquest->section != $currentsection) {
            $strsection = get_section_name($course, $smartquest->section);
            $currentsection = $smartquest->section;
        }
        $data[] = $strsection;
        // Show normal if the mod is visible.
        $class = '';
        if (!$smartquest->visible) {
            $class = ' class="dimmed"';
        }
        $data[] = "<a$class href=\"view.php?id=$cmid\">$smartquest->name</a>";

        // Close date.
        if ($smartquest->closedate) {
            $data[] = userdate($smartquest->closedate);
        } else if ($showclosingheader) {
            $data[] = '';
        }

        if ($showing == 'responses') {
            $status = '';
            if ($responses = smartquest_get_user_responses($smartquest->sid, $USER->id, $complete = false)) {
                foreach ($responses as $response) {
                    if ($response->complete == 'y') {
                        $status .= get_string('submitted', 'smartquest').' '.userdate($response->submitted).'<br />';
                    } else {
                        $status .= get_string('attemptstillinprogress', 'smartquest').' '.
                            userdate($response->submitted).'<br />';
                    }
                }
            }
            $data[] = $status;
        } else if ($showing == 'stats') {
            $data[] = $DB->count_records('smartquest_response', array('survey_id' => $smartquest->sid, 'complete' => 'y'));
            if ($survey = $DB->get_record('smartquest_survey', array('id' => $smartquest->sid))) {
                // For a public smartquest, look for the original public smartquest that it is based on.
                if ($survey->realm == 'public') {
                    $strpreview = get_string('preview_smartquest', 'smartquest');
                    if ($survey->courseid != $course->id) {
                        $publicoriginal = '';
                        $originalcourse = $DB->get_record('course', ['id' => $survey->courseid]);
                        $originalcoursecontext = context_course::instance($survey->courseid);
                        $originalsmartquest = $DB->get_record('smartquest',
                            ['sid' => $survey->id, 'course' => $survey->courseid]);
                        $cm = get_coursemodule_from_instance("smartquest", $originalsmartquest->id, $survey->courseid);
                        $context = context_course::instance($survey->courseid, MUST_EXIST);
                        $canvieworiginal = has_capability('mod/smartquest:preview', $context, $USER->id, true);
                        // If current user can view smartquests in original course,
                        // provide a link to the original public smartquest.
                        if ($canvieworiginal) {
                            $publicoriginal = '<br />'.get_string('publicoriginal', 'smartquest').'&nbsp;'.
                                '<a href="'.$CFG->wwwroot.'/mod/smartquest/preview.php?id='.
                                $cm->id.'" title="'.$strpreview.']">'.$originalsmartquest->name.' ['.
                                $originalcourse->fullname.']</a>';
                        } else {
                            // If current user is not enrolled as teacher in original course,
                            // only display the original public smartquest's name and course name.
                            $publicoriginal = '<br />'.get_string('publicoriginal', 'smartquest').'&nbsp;'.
                                $originalsmartquest->name.' ['.$originalcourse->fullname.']';
                        }
                        $data[] = get_string($realm, 'smartquest').' '.$publicoriginal;
                    } else {
                        // Original public smartquest was created in current course.
                        // Find which courses it is used in.
                        $publiccopy = '';
                        $select = 'course != '.$course->id.' AND sid = '.$smartquest->sid;
                        if ($copies = $DB->get_records_select('smartquest', $select, null,
                                $sort = 'course ASC', $fields = 'id, course, name')) {
                            foreach ($copies as $copy) {
                                $copycourse = $DB->get_record('course', array('id' => $copy->course));
                                $select = 'course = '.$copycourse->id.' AND sid = '.$smartquest->sid;
                                $copysmartquest = $DB->get_record('smartquest',
                                    array('id' => $copy->id, 'sid' => $survey->id, 'course' => $copycourse->id));
                                $cm = get_coursemodule_from_instance("smartquest", $copysmartquest->id, $copycourse->id);
                                $context = context_course::instance($copycourse->id, MUST_EXIST);
                                $canviewcopy = has_capability('mod/smartquest:view', $context, $USER->id, true);
                                if ($canviewcopy) {
                                    $publiccopy .= '<br />'.get_string('publiccopy', 'smartquest').'&nbsp;:&nbsp;'.
                                        '<a href = "'.$CFG->wwwroot.'/mod/smartquest/preview.php?id='.
                                        $cm->id.'" title = "'.$strpreview.'">'.
                                        $copysmartquest->name.' ['.$copycourse->fullname.']</a>';
                                } else {
                                    // If current user does not have "view" capability in copy course,
                                    // only display the copied public smartquest's name and course name.
                                    $publiccopy .= '<br />'.get_string('publiccopy', 'smartquest').'&nbsp;:&nbsp;'.
                                        $copysmartquest->name.' ['.$copycourse->fullname.']';
                                }
                            }
                        }
                        $data[] = get_string($realm, 'smartquest').' '.$publiccopy;
                    }
                } else {
                    $data[] = get_string($realm, 'smartquest');
                }
            } else {
                // If a smartquest is a copy of a public smartquest which has been deleted.
                $data[] = get_string('removenotinuse', 'smartquest');
            }
        }
    }
    $table->data[] = $data;
} // End of loop over smartquest instances.

echo html_writer::table($table);

// Finish the page.
echo $OUTPUT->footer();