<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Message form for invitations (using Moodle formslib)
 *
 * @package    mod_scheduler
 * @copyright  2016 Henning Bostelmann and others (see README.txt)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class smartquest_message_form extends moodleform {

    protected $smartquest;

    protected function definition() {
        global $PAGE, $DB, $CFG, $USER, $_SESSION;
        $PAGE->requires->js('/mod/smartquest/javascript/script.js');

        $msg = optional_param('msg', null, PARAM_INT);
        $cmid = optional_param('id', null, PARAM_INT);
        if (isset($cmid)) {
            $_SESSION['cmid'] = $cmid;
        } else {
            $cmid = $_SESSION['cmid'];
        }
        $mform = $this->_form;
        $courseid = $DB->get_field('course_modules', 'course', array('id' => $cmid));
        $users = array();

        $context = context_course::instance($courseid);
        $users = get_enrolled_users($context);

        $usersarray = array();
        // Fix not show suspendeds  sara.

	foreach ($users as $userid=> $user) {
	    $enrolsql = $DB->get_records_sql("SELECT id FROM {enrol} WHERE courseid = " .$courseid);
	    $output = array_map(function ($object) {return $object->id;} , $enrolsql);
	    $enroll = implode (',' , $output);
//echo $enroll;die;
	    $statussql = $DB->get_record_sql("SELECT status FROM {user_enrolments} WHERE userid = " . $userid . " and enrolid in (" .$enroll." ) limit 1");
		if($statussql->status == 0 ) {
			$usersarray[$userid] = $user->firstname. " " . $user->lastname;
		}
	}
	 
	// End fix sara.
	//foreach ($users as $userid => $user) {
          //  $usersarray[$userid] = $user->firstname . " ". $user->lastname ;
       // }

        $options = array('multiple' => true);
        $mform->addElement('autocomplete', 'users', get_string('messagerecipients', 'smartquest'), $usersarray, $options);
        $mform->addRule('users', null, 'required');

        $mform->addElement('text', 'subject', get_string('messagesubject', 'smartquest'), array('size' => '60'));
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', null, 'required');
        $mform->setDefault('subject',  ' {usertoname}' .  get_string('subjectoutlooklink', 'smartquest') . $USER->firstname .' '. $USER->lastname );

        $bodyedit = $mform->addElement('editor', 'body', get_string('messagebody', 'smartquest'),
                                       array('rows' => 15, 'columns' => 60), array('collapsed' => true));
        $mform->setType('body', PARAM_RAW); // Must be PARAM_RAW for rich text editor content.

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('sendmessage', 'smartquest'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

        $mform->addElement('hidden', 'msg', $msg);
        $mform->setType('msg', PARAM_RAW);
        $mform->addElement('hidden', 'userto', $user->firstname);
        $mform->setType('userto', PARAM_RAW);
        $mform->addElement('hidden', 'userfrom',  $USER->firstname . ' ' . $USER->lastname);
        $mform->setType('userfrom', PARAM_RAW);

        $PAGE->set_url( $CFG->wwwroot.'/mod/smartquest/complete.php?id=' .'1980' );
    }

    function definition_after_data() {
        global $CFG, $COURSE , $DB;

        $mform =&$this->_form;

        $introtext = $mform->getElementValue('body');
        $userfrom = $mform->getElementValue('userfrom');
        $userto = $mform->getElementValue('userto');
        //<sari
        $rid = $DB->get_field_sql('SELECT r.id 
                                   FROM {smartquest} s
                                   join {smartquest_response} r on s.id = r.survey_id
                                   order by r.id desc 
                                   limit 1');
        //sari>
        $link = $CFG->wwwroot.'/mod/smartquest/myreport.php?id='. $this->_customdata['cmid'] . '&instance='.  $this->_customdata['instanceid'] .'&user='. $this->_customdata['emailto'] . '&byresponse=0&action=vresp
                                                          &byresponse=1&individaualresponse=1&rid=' . $rid;
        $hyperlink = '<a href="' . $link . ' "> לכניסה לאימון, לחצו כאן</a>';
        $prebody = get_string('bodyoutlooklink', 'smartquest');
        $endbody = get_string('endbodyoutlooklink', 'smartquest');
       // $introtext ['text'] = get_string('hello' ,  'smartquest') . ' {usertoname}' . $prebody . $hyperlink . $endbody . $userfrom;
        $introtext ['text'] = '<div style="font-family:Arial;text-align:right">' . get_string('hello' , 'smartquest') . ' {usertoname}' . '</br>' . $prebody . '</br>' . $hyperlink . '</br>'. $endbody . '</br>' . $userfrom . '</div>'; 
        $mform->getElement('body')->setValue($introtext);
    }

    /**
     * Form validation
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }

}
