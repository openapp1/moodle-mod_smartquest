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
 * @package mod_smartquest
 * @copyright  2016 Mike Churchward (mike.churchward@poetgroup.org)
 * @author     Mike Churchward
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_smartquest;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class settings_form extends \moodleform {

    public function definition() {
        global $smartquest, $smartquestrealms;

        $mform    =& $this->_form;

        $mform->addElement('header', 'contenthdr', get_string('contentoptions', 'smartquest'));

        $capabilities = smartquest_load_capabilities($smartquest->cm->id);
        if (!$capabilities->createtemplates) {
            unset($smartquestrealms['template']);
        }
        if (!$capabilities->createpublic) {
            unset($smartquestrealms['public']);
        }
        if (isset($smartquestrealms['public']) || isset($smartquestrealms['template'])) {
            $mform->addElement('select', 'realm', get_string('realm', 'smartquest'), $smartquestrealms);
            $mform->setDefault('realm', $smartquest->survey->realm);
            $mform->addHelpButton('realm', 'realm', 'smartquest');
        } else {
            $mform->addElement('hidden', 'realm', 'private');
        }
        $mform->setType('realm', PARAM_RAW);

        //$mform->addElement('checkbox', 'anonymoustemplate', get_string('anonymoustemplate', 'smartquest'));
        //$mform->disabledIf('anonymoustemplate', 'realm', 'in', ['private','public']);        

        $mform->addElement('text', 'title', get_string('title', 'smartquest'), array('size' => '60'));
        $mform->setDefault('title', $smartquest->survey->title);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addHelpButton('title', 'title', 'smartquest');

        $mform->addElement('text', 'subtitle', get_string('subtitle', 'smartquest'), array('size' => '60'));
        $mform->setDefault('subtitle', $smartquest->survey->subtitle);
        $mform->setType('subtitle', PARAM_TEXT);
        $mform->addHelpButton('subtitle', 'subtitle', 'smartquest');

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'trusttext' => true);
        $mform->addElement('editor', 'info', get_string('additionalinfo', 'smartquest'), null, $editoroptions);
        $mform->setDefault('info', $smartquest->survey->info);
        $mform->setType('info', PARAM_RAW);
        $mform->addHelpButton('info', 'additionalinfo', 'smartquest');

        $mform->addElement('header', 'submithdr', get_string('submitoptions', 'smartquest'));

        $mform->addElement('text', 'thanks_page', get_string('url', 'smartquest'), array('size' => '60'));
        $mform->setType('thanks_page', PARAM_TEXT);
        $mform->setDefault('thanks_page', $smartquest->survey->thanks_page);
        $mform->addHelpButton('thanks_page', 'url', 'smartquest');

        $mform->addElement('static', 'confmes', get_string('confalts', 'smartquest'));
        $mform->addHelpButton('confmes', 'confpage', 'smartquest');

        $mform->addElement('text', 'thank_head', get_string('headingtext', 'smartquest'), array('size' => '30'));
        $mform->setType('thank_head', PARAM_TEXT);
        $mform->setDefault('thank_head', $smartquest->survey->thank_head);

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'trusttext' => true);
        $mform->addElement('editor', 'thank_body', get_string('bodytext', 'smartquest'), null, $editoroptions);
        $mform->setType('thank_body', PARAM_RAW);
        $mform->setDefault('thank_body', $smartquest->survey->thank_body);

        $mform->addElement('text', 'email', get_string('email', 'smartquest'), array('size' => '75'));
        $mform->setType('email', PARAM_TEXT);
        $mform->setDefault('email', $smartquest->survey->email);
        $mform->addHelpButton('email', 'sendemail', 'smartquest');

        $defaultsections = get_config('smartquest', 'maxsections');

        // We cannot have more sections than available (required) questions with a choice value.
        $nbquestions = 0;
        foreach ($smartquest->questions as $question) {
            $qtype = $question->type_id;
            $qname = $question->name;
            $required = $question->required;
            // Question types accepted for feedback; QUESRATE ok except noduplicates.
            if (($qtype == QUESRADIO || $qtype == QUESDROP || ($qtype == QUESRATE && $question->precise != 2) || ($qtype == QUESRELEVANTRATE && $question->precise != 2))
                            && $required == 'y' && $qname != '') {
                foreach ($question->choices as $choice) {
                    if (isset($choice->value) && $choice->value != null && $choice->value != 'NULL') {
                        $nbquestions ++;
                        break;
                    }
                }
            }
            if ($qtype == QUESYESNO && $required == 'y' && $qname != '') {
                $nbquestions ++;
            }
        }

        // Smsrtquest Feedback Sections and Messages.
        if ($nbquestions != 0) {
            $maxsections = min ($nbquestions, $defaultsections);
            $feedbackoptions = array();
            $feedbackoptions[0] = get_string('feedbacknone', 'smartquest');
            $mform->addElement('header', 'submithdr', get_string('feedbackoptions', 'smartquest'));
            $feedbackoptions[1] = get_string('feedbackglobal', 'smartquest');
            for ($i = 2; $i <= $maxsections; ++$i) {
                $feedbackoptions[$i] = get_string('feedbacksections', 'smartquest', $i);
            }
            $mform->addElement('select', 'feedbacksections', get_string('feedbackoptions', 'smartquest'), $feedbackoptions);
            $mform->setDefault('feedbacksections', $smartquest->survey->feedbacksections);
            $mform->addHelpButton('feedbacksections', 'feedbackoptions', 'smartquest');

            $options = array('0' => get_string('no'), '1' => get_string('yes'));
            $mform->addElement('select', 'feedbackscores', get_string('feedbackscores', 'smartquest'), $options);
            $mform->addHelpButton('feedbackscores', 'feedbackscores', 'smartquest');

            // Is the RGraph library enabled at level site?
            $usergraph = get_config('smartquest', 'usergraph');
            if ($usergraph) {
                $chartgroup = array();
                $charttypes = array (null => get_string('none'),
                        'bipolar' => get_string('chart:bipolar', 'smartquest'),
                        'vprogress' => get_string('chart:vprogress', 'smartquest'));
                $chartgroup[] = $mform->createElement('select', 'chart_type_global',
                        get_string('chart:type', 'smartquest').' ('.
                                get_string('feedbackglobal', 'smartquest').')', $charttypes);
                if ($smartquest->survey->feedbacksections == 1) {
                    $mform->setDefault('chart_type_global', $smartquest->survey->chart_type);
                }
                $mform->disabledIf('chart_type_global', 'feedbacksections', 'eq', 0);
                $mform->disabledIf('chart_type_global', 'feedbacksections', 'neq', 1);

                $charttypes = array (null => get_string('none'),
                        'bipolar' => get_string('chart:bipolar', 'smartquest'),
                        'hbar' => get_string('chart:hbar', 'smartquest'),
                        'rose' => get_string('chart:rose', 'smartquest'));
                $chartgroup[] = $mform->createElement('select', 'chart_type_two_sections',
                        get_string('chart:type', 'smartquest').' ('.
                                get_string('feedbackbysection', 'smartquest').')', $charttypes);
                if ($smartquest->survey->feedbacksections > 1) {
                    $mform->setDefault('chart_type_two_sections', $smartquest->survey->chart_type);
                }
                $mform->disabledIf('chart_type_two_sections', 'feedbacksections', 'neq', 2);

                $charttypes = array (null => get_string('none'),
                        'bipolar' => get_string('chart:bipolar', 'smartquest'),
                        'hbar' => get_string('chart:hbar', 'smartquest'),
                        'radar' => get_string('chart:radar', 'smartquest'),
                        'rose' => get_string('chart:rose', 'smartquest'));
                $chartgroup[] = $mform->createElement('select', 'chart_type_sections',
                        get_string('chart:type', 'smartquest').' ('.
                                get_string('feedbackbysection', 'smartquest').')', $charttypes);
                if ($smartquest->survey->feedbacksections > 1) {
                    $mform->setDefault('chart_type_sections', $smartquest->survey->chart_type);
                }
                $mform->disabledIf('chart_type_sections', 'feedbacksections', 'eq', 0);
                $mform->disabledIf('chart_type_sections', 'feedbacksections', 'eq', 1);
                $mform->disabledIf('chart_type_sections', 'feedbacksections', 'eq', 2);

                $mform->addGroup($chartgroup, 'chartgroup',
                        get_string('chart:type', 'smartquest'), null, false);
                $mform->addHelpButton('chartgroup', 'chart:type', 'smartquest');
            }
            $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'trusttext' => true);
            $mform->addElement('editor', 'feedbacknotes', get_string('feedbacknotes', 'smartquest'), null, $editoroptions);
            $mform->setType('feedbacknotes', PARAM_RAW);
            $mform->setDefault('feedbacknotes', $smartquest->survey->feedbacknotes);
            $mform->addHelpButton('feedbacknotes', 'feedbacknotes', 'smartquest');

            $mform->addElement('submit', 'feedbackeditbutton', get_string('feedbackeditsections', 'smartquest'));
            $mform->disabledIf('feedbackeditbutton', 'feedbacksections', 'eq', 0);
        }

        // Hidden fields.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'sid', 0);
        $mform->setType('sid', PARAM_INT);
        $mform->addElement('hidden', 'name', '');
        $mform->setType('name', PARAM_TEXT);
        $mform->addElement('hidden', 'courseid', '');
        $mform->setType('courseid', PARAM_RAW);

        // Buttons.

        $submitlabel = get_string('savechangesanddisplay');
        $submit2label = get_string('savechangesandreturntocourse');
        $mform = $this->_form;

        // Elements in a row need a group.
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', $submit2label);
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->setType('buttonar', PARAM_RAW);
        $mform->closeHeaderBefore('buttonar');

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
