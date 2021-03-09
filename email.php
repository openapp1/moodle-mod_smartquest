<?php

require_once("../../config.php");
//require_once($CFG->libdir . '/completionlib.php');
//require_once($CFG->dirroot.'/mod/smartquest/smartquest.class.php');
require_once($CFG->dirroot.'/mod/smartquest/email_form.php');

$id = optional_param('id', null, PARAM_INT);    // Course Module ID.
$msg = optional_param('msg', null, PARAM_INT);    // Course Module ID.
$emailto = optional_param('emailto', null, PARAM_INT);    // Course Module ID.

$PAGE->set_pagelayout('course');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('email', 'moodle'));
$PAGE->set_title(get_string('email', 'moodle'));
$url = new moodle_url($CFG->wwwroot.'/mod/smartquest/email.php');
$PAGE->set_url($url);

$PAGE->navbar->add(get_string('pluginname', 'mod_smartquest'), new moodle_url($url));
$PAGE->navbar->add(get_string('email', 'moodle'));
$flag = 0;

if(isset($msg)) {

    global $USER;

    $mform = new smartquest_message_form(null,  array('cmid' => $id, 'instanceid' => $msg ,'emailto' => $emailto));

    if ($mform->is_cancelled()) {
        $req = $_REQUEST['body']['text'];
	$i = strstr($req , 'id');
	$id = substr($i , 0 , strpos($i, '&'));
	$message = "message canclled";
        $url = $CFG->wwwroot. '/mod/smartquest/complete.php?' . $id;
	redirect( $url , $message, 5, 'red');
    } else if ($fromform = $mform->get_data()) {
	$req = $_REQUEST['body']['text'];
	$i = strstr($req ,'id');
        $id = substr($i , 0 , strpos($i, '&'));
        if (isset($fromform->users)) {
            $users = $fromform->users;
            $subject = $fromform->subject;
            $msgbody = $fromform->body;
            $userfrom = $USER->email;
            foreach ($users as $user) {
                $userto = $DB->get_record('user', array('id' => $user));
                $usertoname = $userto->firstname;
                $my_msgbody = str_replace('{usertoname}', $usertoname, $msgbody['text']);
                $my_subject = str_replace('{usertoname}', $usertoname, $subject);
                email_to_user($userto, $userfrom, $my_subject, $my_msgbody,$my_msgbody);
                $flag = 1;
            }
        } else {
            $message = "there was a problem... the message didn't send. please check that the email addresses are correct";
            $url = $CFG->wwwroot. '/mod/smartquest/complete.php?' . $id;
            redirect( $url , $message, 5, 'red');
        }
    } else {
        echo $OUTPUT->header();
        $mform->display();
    }
}
if ($flag == 1) {
    $message = "message sent to: ". count($users) . ' users';
    redirect($CFG->wwwroot. '/mod/smartquest/complete.php?' .$id, $message, 5);
}

 echo $OUTPUT->footer();
