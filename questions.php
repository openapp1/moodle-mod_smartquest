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
require_once($CFG->dirroot.'/mod/smartquest/classes/question/base.php'); // Needed for question type constants.

$id     = required_param('id', PARAM_INT);                 // Course module ID
$action = optional_param('action', 'main', PARAM_ALPHA);   // Screen.
$qid    = optional_param('qid', 0, PARAM_INT);             // Question id.
$moveq  = optional_param('moveq', 0, PARAM_INT);           // Question id to move.
$delq   = optional_param('delq', 0, PARAM_INT);             // Question id to delete
$qtype  = optional_param('type_id', 0, PARAM_INT);         // Question type.
$currentgroupid = optional_param('group', 0, PARAM_INT); // Group id.

if (! $cm = get_coursemodule_from_id('smartquest', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}

if (! $smartquest = $DB->get_record("smartquest", array("id" => $cm->instance))) {
    print_error('invalidcoursemodule');
}

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
//Tamard
//if($DB->record_exists('smartquest_anonymcourses', ['anonymcourse' => $cm->course])) {
//	require_capability('block/smartquest:addquestionssmartquest', $context);
//}
$url = new moodle_url($CFG->wwwroot.'/mod/smartquest/questions.php');
$url->param('id', $id);
if ($qid) {
    $url->param('qid', $qid);
}

$PAGE->set_url($url);
$PAGE->set_context($context);

$smartquest = new smartquest(0, $smartquest, $course, $cm);

// Add renderer and page objects to the smartquest object for display use.
$smartquest->add_renderer($PAGE->get_renderer('mod_smartquest'));
$smartquest->add_page(new \mod_smartquest\output\questionspage());

if (!$smartquest->capabilities->editquestions) {
    print_error('nopermissions', 'error', 'mod:smartquest:edit');
}

$smartquesthasdependencies = $smartquest->has_dependencies();
$haschildren = [];
if (!isset($SESSION->smartquest)) {
    $SESSION->smartquest = new stdClass();
}
$SESSION->smartquest->current_tab = 'questions';
$reload = false;
$sid = $smartquest->survey->id;
// Process form data.

// Delete question button has been pressed in questions_form AND deletion has been confirmed on the confirmation page.
if ($delq) {
    $qid = $delq;
    $sid = $smartquest->survey->id;
    $smartquestid = $smartquest->id;

    // Need to reload questions before setting deleted question to 'y'.
    $questions = $DB->get_records('smartquest_question', array('survey_id' => $sid, 'deleted' => 'n'), 'id');
    $DB->set_field('smartquest_question', 'deleted', 'y', array('id' => $qid, 'survey_id' => $sid));

    // Delete all dependency records for this question.
    smartquest_delete_dependencies($qid);

    // Just in case the page is refreshed (F5) after a question has been deleted.
    if (isset($questions[$qid])) {
        $select = 'survey_id = '.$sid.' AND deleted = \'n\' AND position > '.
                        $questions[$qid]->position;
    } else {
        redirect($CFG->wwwroot.'/mod/smartquest/questions.php?id='.$smartquest->cm->id);
    }

    if ($records = $DB->get_records_select('smartquest_question', $select, null, 'position ASC')) {
        foreach ($records as $record) {
            $DB->set_field('smartquest_question', 'position', $record->position - 1, array('id' => $record->id));
        }
    }
    // Delete section breaks without asking for confirmation.
    // No need to delete responses to those "question types" which are not real questions.
    if (!$smartquest->questions[$qid]->supports_responses()) {
        $reload = true;
    } else {
        // Delete responses to that deleted question.
        smartquest_delete_responses($qid);

        // If no questions left in this smartquest, remove all attempts and responses.
        if (!$questions = $DB->get_records('smartquest_question', array('survey_id' => $sid, 'deleted' => 'n'), 'id') ) {
            $DB->delete_records('smartquest_response', array('survey_id' => $sid));
            $DB->delete_records('smartquest_attempts', array('qid' => $smartquestid));
        }
    }

    // Log question deleted event.
    $context = context_module::instance($smartquest->cm->id);
    $questiontype = \mod_smartquest\question\base::qtypename($smartquest->questions[$qid]->type_id);
    $params = array(
                    'context' => $context,
                    'courseid' => $smartquest->course->id,
                    'other' => array('questiontype' => $questiontype)
    );
    $event = \mod_smartquest\event\question_deleted::create($params);
    $event->trigger();

    if ($smartquesthasdependencies) {
        $SESSION->smartquest->validateresults = smartquest_check_page_breaks($smartquest);
    }
    $reload = true;
}

if ($action == 'main') {
    $questionsform = new \mod_smartquest\questions_form('questions.php', $moveq);
    $sdata = clone($smartquest->survey);
    $sdata->sid = $smartquest->survey->id;
    $sdata->id = $cm->id;
    if (!empty($smartquest->questions)) {
        $pos = 1;
        foreach ($smartquest->questions as $qidx => $question) {
            $sdata->{'pos_'.$qidx} = $pos;
            $pos++;
        }
    }
    $questionsform->set_data($sdata);
    if ($questionsform->is_cancelled()) {
        // Switch to main screen.
        $action = 'main';
        redirect($CFG->wwwroot.'/mod/smartquest/questions.php?id='.$smartquest->cm->id);
        $reload = true;
    }
    if ($qformdata = $questionsform->get_data()) {
        // Quickforms doesn't return values for 'image' input types using 'exportValue', so we need to grab
        // it from the raw submitted data.
        $exformdata = data_submitted();

        if (isset($exformdata->movebutton)) {
            $qformdata->movebutton = $exformdata->movebutton;
        } else if (isset($exformdata->moveherebutton)) {
            $qformdata->moveherebutton = $exformdata->moveherebutton;
        } else if (isset($exformdata->editbutton)) {
            $qformdata->editbutton = $exformdata->editbutton;
        } else if (isset($exformdata->removebutton)) {
            $qformdata->removebutton = $exformdata->removebutton;
        } else if (isset($exformdata->requiredbutton)) {
            $qformdata->requiredbutton = $exformdata->requiredbutton;
        }

        // Insert a section break.
        if (isset($qformdata->removebutton)) {
            // Need to use the key, since IE returns the image position as the value rather than the specified
            // value in the <input> tag.
            $qid = key($qformdata->removebutton);
            $qtype = $smartquest->questions[$qid]->type_id;

            // Delete section breaks without asking for confirmation.
            if ($qtype == QUESPAGEBREAK) {
                redirect($CFG->wwwroot.'/mod/smartquest/questions.php?id='.$smartquest->cm->id.'&amp;delq='.$qid);
            }
            if ($smartquesthasdependencies) {
                // Important: due to possibly multiple parents per question
                // just remove the dependency and inform the user about it.
                $haschildren = $smartquest->get_all_dependants($qid);
            }
            if (count($haschildren) != 0) {
                $action = "confirmdelquestionparent";
            } else {
                $action = "confirmdelquestion";
            }

        } else if (isset($qformdata->editbutton)) {
            // Switch to edit question screen.
            $action = 'question';
            // Need to use the key, since IE returns the image position as the value rather than the specified
            // value in the <input> tag.
            $qid = key($qformdata->editbutton);
            $reload = true;

        } else if (isset($qformdata->requiredbutton)) {
            // Need to use the key, since IE returns the image position as the value rather than the specified
            // value in the <input> tag.

            $qid = key($qformdata->requiredbutton);
            if ($smartquest->questions[$qid]->required()) {
                $smartquest->questions[$qid]->set_required(false);

            } else {
                $smartquest->questions[$qid]->set_required(true);
            }

            $reload = true;

        } else if (isset($qformdata->addqbutton)) {
            if ($qformdata->type_id == QUESPAGEBREAK) { // Adding section break is handled right away....
                $questionrec = new stdClass();
                $questionrec->survey_id = $qformdata->sid;
                $questionrec->type_id = QUESPAGEBREAK;
                $questionrec->content = 'break';
                $question = \mod_smartquest\question\base::question_builder(QUESPAGEBREAK);
                $question->add($questionrec);
                $reload = true;
            } else {
                // Switch to edit question screen.
                $action = 'question';
                $qtype = $qformdata->type_id;
                $qid = 0;
                $reload = true;
            }

        } else if (isset($qformdata->movebutton)) {
            // Nothing I do will seem to reload the form with new data, except for moving away from the page, so...
            redirect($CFG->wwwroot.'/mod/smartquest/questions.php?id='.$smartquest->cm->id.
                     '&moveq='.key($qformdata->movebutton));
            $reload = true;



        } else if (isset($qformdata->moveherebutton)) {
            // Need to use the key, since IE returns the image position as the value rather than the specified
            // value in the <input> tag.

            // No need to move question if new position = old position!
            $qpos = key($qformdata->moveherebutton);
            if ($qformdata->moveq != $qpos) {
                $smartquest->move_question($qformdata->moveq, $qpos);
            }
            if ($smartquesthasdependencies) {
                $SESSION->smartquest->validateresults = smartquest_check_page_breaks($smartquest);
            }
            // Nothing I do will seem to reload the form with new data, except for moving away from the page, so...
            redirect($CFG->wwwroot.'/mod/smartquest/questions.php?id='.$smartquest->cm->id);
            $reload = true;

        } else if (isset($qformdata->validate)) {
            // Validates page breaks for depend questions.
            $SESSION->smartquest->validateresults = smartquest_check_page_breaks($smartquest);
            $reload = true;
        }
    }


} else if ($action == 'question') {
    $question = smartquest_prep_for_questionform($smartquest, $qid, $qtype);
    $questionsform = new \mod_smartquest\edit_question_form('questions.php');
    $questionsform->set_data($question);
    if ($questionsform->is_cancelled()) {
        // Switch to main screen.
        $action = 'main';
        $reload = true;

    } else if ($qformdata = $questionsform->get_data()) {
        // Saving question data.
        if (isset($qformdata->makecopy)) {
            $qformdata->qid = 0;
        }

        $question->form_update($qformdata, $smartquest);

        // Make these field values 'sticky' for further new questions.
        if (!isset($qformdata->required)) {
            $qformdata->required = 'n';
        }

        smartquest_check_page_breaks($smartquest);
        $SESSION->smartquest->required = $qformdata->required;
        $SESSION->smartquest->type_id = $qformdata->type_id;
        // Switch to main screen.
        $action = 'main';
        $reload = true;
    }

    // Log question created event.
    if (isset($qformdata)) {
        $context = context_module::instance($smartquest->cm->id);
        $questiontype = \mod_smartquest\question\base::qtypename($qformdata->type_id);
        $params = array(
                        'context' => $context,
                        'courseid' => $smartquest->course->id,
                        'other' => array('questiontype' => $questiontype)
        );
        $event = \mod_smartquest\event\question_created::create($params);
        $event->trigger();
    }

    $questionsform->set_data($question);
}

// Reload the form data if called for...
if ($reload) {
    unset($questionsform);
    $smartquest = new smartquest($smartquest->id, null, $course, $cm);
    // Add renderer and page objects to the smartquest object for display use.
    $smartquest->add_renderer($PAGE->get_renderer('mod_smartquest'));
    $smartquest->add_page(new \mod_smartquest\output\questionspage());
    if ($action == 'main') {
        $questionsform = new \mod_smartquest\questions_form('questions.php', $moveq);
        $sdata = clone($smartquest->survey);
        $sdata->sid = $smartquest->survey->id;
        $sdata->id = $cm->id;
        if (!empty($smartquest->questions)) {
            $pos = 1;
            foreach ($smartquest->questions as $qidx => $question) {
                $sdata->{'pos_'.$qidx} = $pos;
                $pos++;
            }
        }
        $questionsform->set_data($sdata);
    } else if ($action == 'question') {
        $question = smartquest_prep_for_questionform($smartquest, $qid, $qtype);
        $questionsform = new \mod_smartquest\edit_question_form('questions.php');
        $questionsform->set_data($question);
    }
}

// Print the page header.
if ($action == 'question') {
    if (isset($question->qid)) {
        $streditquestion = get_string('editquestion', 'smartquest', smartquest_get_type($question->type_id));
    } else {
        $streditquestion = get_string('addnewquestion', 'smartquest', smartquest_get_type($question->type_id));
    }
} else {
    $streditquestion = get_string('managequestions', 'smartquest');
}

$PAGE->set_title($streditquestion);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add($streditquestion);
echo $smartquest->renderer->header();
require('tabs.php');

if ($action == "confirmdelquestion" || $action == "confirmdelquestionparent") {

    $qid = key($qformdata->removebutton);
    $childq = $DB->get_records('smartquest_question', ['sourceid' => $qid]);
    $question = $smartquest->questions[$qid];
    $qtype = $question->type_id;

    // Count responses already saved for that question.
    $countresps = 0;
    if ($qtype != QUESSECTIONTEXT) {
        $responsetable = $DB->get_field('smartquest_question_type', 'response_table', array('typeid' => $qtype));
        if (!empty($responsetable)) {
            $countresps = $DB->count_records('smartquest_'.$responsetable, array('question_id' => $qid));
        }
    }

    // Needed to print potential media in question text.

    // If question text is "empty", i.e. 2 non-breaking spaces were inserted, do not display any question text.

    if ($question->content == '<p>  </p>') {
        $question->content = '';
    }

    $qname = '';
    if ($question->name) {
        $qname = ' ('.$question->name.')';
    }

    $num = get_string('position', 'smartquest');
    $pos = $question->position.$qname;

    if ($childq) {
        $msg = '<div class="warning centerpara"><p>'.get_string('haschilds', 'smartquest').'</p>';
        $msg .= '</div>';
        $args = "id={$smartquest->cm->id}";
        $urlno = new moodle_url("/mod/smartquest/questions.php?{$args}");
        $cancel = new single_button($urlno, get_string('ok'));
        $output = $smartquest->renderer->box_start('generalbox modal modal-dialog modal-in-page show', 'notice');
        $output .= $smartquest->renderer->box_start('modal-content', 'modal-content');
        $output .= $smartquest->renderer->box_start('modal-header', 'modal-header');
        $output .= html_writer::tag('h4', get_string('confirm'));
        $output .= $smartquest->renderer->box_end();
        $output .= $smartquest->renderer->box_start('modal-body', 'modal-body');
        $output .= html_writer::tag('p', $msg);
        $output .= $smartquest->renderer->box_end();
        $output .= $smartquest->renderer->box_start('modal-footer', 'modal-footer');
        $output .= html_writer::tag('div', $smartquest->renderer->render($cancel), array('class' => 'buttons'));
        $output .= $smartquest->renderer->box_end();
        $output .= $smartquest->renderer->box_end();
        $output .= $smartquest->renderer->box_end();
        $smartquest->page->add_to_page('formarea', $output);
    } else {
        $msg = '<div class="warning centerpara"><p>'.get_string('confirmdelquestion', 'smartquest', $pos).'</p>';
        if ($countresps !== 0) {
            $msg .= '<p>'.get_string('confirmdelquestionresps', 'smartquest', $countresps).'</p>';
        }
        $msg .= '</div>';
        $msg .= '<div class = "qn-container">'.$num.' '.$pos.'<div class="qn-question">'.$question->content.'</div></div>';
        $args = "id={$smartquest->cm->id}";
        $urlno = new moodle_url("/mod/smartquest/questions.php?{$args}");
        $args .= "&delq={$qid}";
        $urlyes = new moodle_url("/mod/smartquest/questions.php?{$args}");
        $buttonyes = new single_button($urlyes, get_string('yes'));
        $buttonno = new single_button($urlno, get_string('no'));
        if ($action == "confirmdelquestionparent") {
            $strnum = get_string('position', 'smartquest');
            $qid = key($qformdata->removebutton);
            // Show the dependencies and inform about the dependencies to be removed.
            // Split dependencies in direct and indirect ones to separate for the confirm-dialogue. Only direct ones will be deleted.
            // List direct dependencies.
            $msg .= $smartquest->renderer->dependency_warnings($haschildren->directs, 'directwarnings', $strnum);
            // List indirect dependencies.
            $msg .= $smartquest->renderer->dependency_warnings($haschildren->indirects, 'indirectwarnings', $strnum);
        }
        $smartquest->page->add_to_page('formarea', $smartquest->renderer->confirm($msg, $buttonyes, $buttonno));
    }

} else {
    $smartquest->page->add_to_page('formarea', $questionsform->render());
}
echo $smartquest->renderer->render($smartquest->page);
echo $smartquest->renderer->footer();
