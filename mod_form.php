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
 * print the form to add or edit a smartquest-instance
 *
 * @author Mike Churchward
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package smartquest
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/smartquest/smartquest.class.php');
require_once($CFG->dirroot.'/mod/smartquest/locallib.php');

class mod_smartquest_mod_form extends moodleform_mod {

    protected function definition() {
        global $COURSE, $PAGE;
        global $smartquestfreq, $smartquestrespondents, $smartquestresponseviewers, $autonumbering, $smartquesttypes, $roletypes, $sapevents;
        $PAGE->requires->js('/mod/smartquest/javascript/script.js');
        
        $smartquest = new smartquest($this->_instance, null, $COURSE, $this->_cm);

        $mform = &$this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'smartquest'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements(get_string('description'));

        $mform->addElement('header', 'timinghdr', get_string('timing', 'form'));

        $enableopengroup = array();
        $enableopengroup[] =& $mform->createElement('checkbox', 'useopendate', get_string('opendate', 'smartquest'));
        $enableopengroup[] =& $mform->createElement('date_time_selector', 'opendate', '');
        $mform->addGroup($enableopengroup, 'enableopengroup', get_string('opendate', 'smartquest'), ' ', false);
        $mform->addHelpButton('enableopengroup', 'opendate', 'smartquest');
        $mform->disabledIf('enableopengroup', 'useopendate', 'notchecked');

        $enableclosegroup = array();
        $enableclosegroup[] =& $mform->createElement('checkbox', 'useclosedate', get_string('closedate', 'smartquest'));
        $enableclosegroup[] =& $mform->createElement('date_time_selector', 'closedate', '');
        $mform->addGroup($enableclosegroup, 'enableclosegroup', get_string('closedate', 'smartquest'), ' ', false);
        $mform->addHelpButton('enableclosegroup', 'closedate', 'smartquest');
        $mform->disabledIf('enableclosegroup', 'useclosedate', 'notchecked');

        $mform->addElement('header', 'smartquesthtype', get_string('smartquesthtype', 'smartquest'));
        // $typegroup = [];
        // $typegroup[] =& $mform->createElement('select', 'rtype', get_string('rtype', 'smartquest'), $smartquesttypes);
        // $typegroup[] =& $mform->createElement('select', 'sapevent', get_string('sapevent', 'smartquest'), $sapevents);
        // $mform->addGroup($typegroup, 'typegroup', get_string('typegroup', 'smartquest'), ' ', false);
        
        $mform->addElement('select', 'rtype', get_string('rtype', 'smartquest'), $smartquesttypes);
        $mform->addHelpButton('rtype', 'rtype', 'smartquest');

        /*
        $mform->addElement('autocomplete', 'sapevent_id', get_string('sapevent_id', 'smartquest'), $sapevents);
        $mform->addHelpButton('sapevent_id', 'sapevent_id', 'smartquest');
        $mform->disabledIf('sapevent_id', 'rtype', 'neq', '1');
        */
        $mform->addElement('select', 'role_id', get_string('role_id', 'smartquest'), $roletypes, ['data-courseid' => $COURSE->id]);
        $mform->addHelpButton('role_id', 'role_id', 'smartquest');
        $mform->disabledIf('role_id', 'rtype', 'in', [COURSE, STUDEFFECT, ROLEEFFECT, GUIDELINE]);
        
        $roleid = isset($smartquest->role_id) && $smartquest->role_id ? $smartquest->role_id : array_keys($roletypes)[0];
        $users = get_users_in_role($roleid, $COURSE->id);
        $mform->addElement('select', 'user_id', get_string('user_id', 'smartquest'), $users);
        $mform->addHelpButton('user_id', 'user_id', 'smartquest');
        $mform->disabledIf('user_id', 'rtype', 'in', [COURSE, STUDEFFECT, ROLEEFFECT, GUIDELINE]);

        // $roles = $mform->addElement('hierselect', 'roles', 'roles', ['class' => 'custom-select']);
        // $mform->setType('roles', PARAM_INT);
        // $roles->setOptions($roletypes_users);
        
        $mform->addElement('header', 'smartquesthdr', get_string('responseoptions', 'smartquest'));
        $mform->addElement('select', 'qtype', get_string('qtype', 'smartquest'), $smartquestfreq);
        $mform->addHelpButton('qtype', 'qtype', 'smartquest');

        $mform->addElement('hidden', 'cannotchangerespondenttype');
        $mform->setType('cannotchangerespondenttype', PARAM_INT);
        $mform->addElement('select', 'respondenttype', get_string('respondenttype', 'smartquest'), $smartquestrespondents);
        $mform->addHelpButton('respondenttype', 'respondenttype', 'smartquest');
        $mform->disabledIf('respondenttype', 'cannotchangerespondenttype', 'eq', 1);

        $mform->addElement('select', 'resp_view', get_string('responseview', 'smartquest'), $smartquestresponseviewers);
        $mform->addHelpButton('resp_view', 'responseview', 'smartquest');

        $mform->addElement('selectyesno', 'notifications', get_string('notifications', 'smartquest'));
        $mform->addHelpButton('notifications', 'notifications', 'smartquest');

        $options = array('0' => get_string('no'), '1' => get_string('yes'));
        $mform->addElement('select', 'resume', get_string('resume', 'smartquest'), $options);
        $mform->addHelpButton('resume', 'resume', 'smartquest');

        $options = array('0' => get_string('no'), '1' => get_string('yes'));
        $mform->addElement('select', 'navigate', get_string('navigate', 'smartquest'), $options);
        $mform->addHelpButton('navigate', 'navigate', 'smartquest');

        $mform->addElement('select', 'autonum', get_string('autonumbering', 'smartquest'), $autonumbering);
        $mform->addHelpButton('autonum', 'autonumbering', 'smartquest');
        // Default = autonumber both questions and pages.
        $mform->setDefault('autonum', 3);

        // Removed potential scales from list of grades. CONTRIB-3167.
        $grades[0] = get_string('nograde');
        for ($i = 100; $i >= 1; $i--) {
            $grades[$i] = $i;
        }
        $mform->addElement('select', 'grade', get_string('grade', 'smartquest'), $grades);

        if (empty($smartquest->sid)) {
            if (!isset($smartquest->id)) {
                $smartquest->id = 0;
            }

            $mform->addElement('header', 'contenthdr', get_string('contentoptions', 'smartquest'));
            $mform->addHelpButton('contenthdr', 'createcontent', 'smartquest');

            $mform->addElement('radio', 'create', get_string('createnew', 'smartquest'), '', 'new-0');

            // Retrieve existing private smartquests from current course.
            $surveys = smartquest_get_survey_select($COURSE->id, 'private');
            if (!empty($surveys)) {
                $prelabel = get_string('useprivate', 'smartquest');
                foreach ($surveys as $value => $label) {
                    $mform->addElement('radio', 'create', $prelabel, $label, $value);
                    $prelabel = '';
                }
            }
            // Retrieve existing template smartquests from this site.
            $surveys = smartquest_get_survey_select($COURSE->id, 'template', false);
            if (!empty($surveys)) {
                $prelabel = get_string('usetemplate', 'smartquest');
                foreach ($surveys as $value => $label) {
                    $mform->addElement('radio', 'create', $prelabel, $label, $value);
                    $prelabel = '';
                }
            } else {
                $mform->addElement('static', 'usetemplate', get_string('usetemplate', 'smartquest'),
                                '('.get_string('notemplatesurveys', 'smartquest').')');
            }

            // Retrieve existing public smartquests from this site.
            $surveys = smartquest_get_survey_select($COURSE->id, 'public');
            if (!empty($surveys)) {
                $prelabel = get_string('usepublic', 'smartquest');
                foreach ($surveys as $value => $label) {
                    $mform->addElement('radio', 'create', $prelabel, $label, $value);
                    $prelabel = '';
                }
            } else {
                $mform->addElement('static', 'usepublic', get_string('usepublic', 'smartquest'),
                                   '('.get_string('nopublicsurveys', 'smartquest').')');
            }

            $mform->setDefault('create', 'new-0');
        }

        $this->standard_coursemodule_elements();

        // Buttons.
        $this->add_action_buttons();
    }

    public function data_preprocessing(&$defaultvalues) {
        global $DB;
        if (empty($defaultvalues['opendate'])) {
            $defaultvalues['useopendate'] = 0;
        } else {
            $defaultvalues['useopendate'] = 1;
        }
        if (empty($defaultvalues['closedate'])) {
            $defaultvalues['useclosedate'] = 0;
        } else {
            $defaultvalues['useclosedate'] = 1;
        }
        // Prevent smartquest set to "anonymous" to be reverted to "full name".
        $defaultvalues['cannotchangerespondenttype'] = 0;
        if (!empty($defaultvalues['respondenttype']) && $defaultvalues['respondenttype'] == "anonymous") {
            // If this smartquest has responses.
            $numresp = $DB->count_records('smartquest_response',
                            array('survey_id' => $defaultvalues['sid'], 'complete' => 'y'));
            if ($numresp) {
                $defaultvalues['cannotchangerespondenttype'] = 1;
            }
        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }

    public function add_completion_rules() {
        $mform =& $this->_form;
        $mform->addElement('checkbox', 'completionsubmit', '', get_string('completionsubmit', 'smartquest'));
        return array('completionsubmit');
    }

    public function completion_rule_enabled($data) {
        return !empty($data['completionsubmit']);
    }

}
