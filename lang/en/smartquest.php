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
 * Strings for component 'smartquest', language 'en', branch 'MOODLE_24_STABLE'
 *
 * @package    mod
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['action'] = 'Action';
$string['activityoverview'] = 'You have smartquests that are due';
$string['additionalinfo'] = 'Additional Info';
$string['additionalinfo_help'] = 'Text to be displayed at the top of the first page of this smartquest. (i.e. instructions, background info, etc.)';
$string['addnewquestion'] = 'Adding {$a} question';
$string['addquestions'] = 'Add questions';
$string['addselqtype'] = 'Add selected question type';
$string['alignment'] = 'Radio buttons Alignment';
$string['alignment_help'] = 'Select buttons alignment: vertical (default) or horizontal.';
$string['alignment_link'] = 'mod/smartquest/questions#Radio_Buttons';
$string['all'] = 'All';
$string['alreadyfilled'] = 'You have already filled out this smartquest for us{$a}. Thank you.';
$string['andaveragevalues'] = 'and average values';
$string['anonymous'] = 'Anonymous';
$string['answergiven'] = 'This answer given';
$string['answernotgiven'] = 'This answer not given';
$string['answerquestions'] = 'Answer the questions...';
$string['attempted'] = 'This smartquest has been submitted.';
$string['attemptstillinprogress'] = 'In progress. Saved on:';
$string['autonumbering'] = 'Auto numbering';
$string['autonumbering_help'] = 'Automatic numbering of questions and pages. You might want to disable automatic numbering
 for smartquests with conditional branching.';
$string['autonumberno'] = 'Do not number questions or pages';
$string['autonumberquestions'] = 'Auto number questions';
$string['autonumberpages'] = 'Auto number pages';
$string['autonumberpagesandquestions'] = 'Auto number pages and questions';
$string['average'] = 'Average';
$string['averagerank'] = 'Average rank';
$string['averageposition'] = 'Average position';
$string['bodytext'] = 'Body text';
$string['boxesnbexact'] = 'exactly {$a} box(es).';
$string['boxesnbmax'] = 'a maximum of {$a} box(es).';
$string['boxesnbmin'] = 'a minimum of {$a} box(es).';
$string['boxesnbreq'] = 'For this question you must tick ';
$string['by'] = ' by ';
$string['missingname'] = 'Question {$a} cannot be used in this feedback section because it does not have a name.';
$string['missingrequired'] = 'Question {$a} cannot be used in this feedback section because it is not required.';
$string['missingnameandrequired'] = 'Question {$a} cannot be used in this feedback section because it does not have a name and it is not required.';
$string['cannotviewpublicresponses'] = 'You cannot view responses to this public smartquest.';
$string['chart:bipolar'] = 'Bipolar bars';
$string['chart:hbar'] = 'Horizontal bars';
$string['chart:radar'] = 'Radar';
$string['chart:rose'] = 'Rose';
$string['chart:type'] = 'Chart type';
$string['chart:type_help'] = 'Select the Chart type you want to use for this feedback';
$string['chart:vprogress'] = 'Vertical Progress bar';
$string['checkallradiobuttons'] = 'Please check <strong>{$a}</strong> radio buttons!';
$string['checkboxes'] = 'Check Boxes';
$string['checkboxes_help'] = 'Enter one option per line for the user to select one or multiple answers from. ';
$string['checkboxes_link'] = 'mod/smartquest/questions#Check_Boxes';
$string['checkbreaksadded'] = 'New Page Break(s) inserted at position(s):';
$string['checkbreaksok'] = 'All the required Page breaks are present!';
$string['checkbreaksremoved'] = 'Removed {$a} extra Page break(s).';
$string['checknotstarted'] = 'Select not started';
$string['checkstarted'] = 'Select started';
$string['clicktoswitch'] = '(click to switch)';
$string['closed'] = 'The smartquest was closed on {$a}. Thanks.';
$string['closedate'] = 'Use Close Date';
$string['closeson'] = 'Smsrtquest closes on {$a}';
$string['closedate_help'] = 'You can specify a date to close the smartquest here.
 Check the check box, and select the date and time you want.
 Users will not be able to fill out the smartquest after that date. If this is not selected, it will never be closed.';
$string['completionsubmit'] = 'Student must submit this smartquest to complete it';
$string['condition'] = 'Condition';
$string['confalts'] = '- OR - <br />Confirmation page';
$string['configusergraph'] = 'Display charts for "Personality Test" feedback';
$string['configusergraphlong'] = 'Use the <a href="http://www.rgraph.net/">Rgraph</a> library to display "Personality Test" feedback charts.';
$string['configmaxsections'] = 'Maximum feedback sections';
$string['confirmdelallresp'] = 'Are you sure you want to delete ALL the responses in this smartquest?';
$string['confirmdelchildren'] = 'If you delete this question, its child(ren) question(s) will also be deleted:';
$string['confirmdelgroupresp'] = 'Are you sure you want to delete ALL the responses of {$a}?';
$string['confirmdelquestion'] = 'Are you sure you want to delete the question at position {$a}?';
$string['confirmdelquestionresps'] = 'This will also delete the {$a} response(s) already given to that question.';
$string['confirmdelresp'] = 'Are you sure you want to delete the response by&nbsp;{$a}&nbsp;?';
$string['confpage'] = 'Heading text';
$string['confpage_help'] = 'Heading (in bold) and body text for the "Confirmation" page displayed after a user completes this smartquest.
 (URL, if present, takes precedence over confirmation text.) If you leave this field empty, a default message will be displayed upon smartquest completion (Thank you for completing this Smsrtquest).';
$string['confpagedesc'] = 'Heading (in bold) and body text for the &quot;Confirmation&quot;
 page displayed after a user completes this smartquest. (URL, if present, takes precedence over confirmation text.)';
$string['contentoptions'] = 'Content options';
$string['couldnotdelresp'] = 'Could not delete response ';
$string['couldnotcreatenewsurvey'] = 'Could not create a new survey!';
$string['createcontent'] = 'Define New Content';
$string['createcontent_help'] = 'Select one of the radio button options. \'Create new\' is the default.';
$string['createcontent_link'] = 'mod/smartquest/mod#Content_Options';
$string['createnew'] = 'Create new';
$string['date'] = 'Date';
$string['date_help'] = 'Use this question type if you expect the response to be a correctly formatted date.';
$string['date_link'] = 'mod/smartquest/questions#Date';
$string['dateformatting'] = 'Use the day/month/year format, e.g. for March 14th, 1945:&nbsp; <strong>14/3/1945</strong>';
$string['deleteallresponses'] = 'Delete ALL Responses';
$string['deletecurrentquestion'] = 'Delete question {$a}';
$string['deletedallgroupresp'] = 'Deleted ALL Responses in group {$a}';
$string['deletedallresp'] = 'Smsrtquest responses deleted';
$string['deletedisabled'] = 'This item cannot be deleted';
$string['deletedresp'] = 'Deleted Response';
$string['deleteresp'] = 'Delete this Response';
$string['deletingresp'] = 'Deleting Response';
$string['dependencies'] = 'Dependencies';
$string['dependquestion'] = 'Parent Question';
$string['dependquestion_help'] = 'You can select a parent question and a choice option for this question. A child question will only be displayed
                to the student if its parent question and parent choice have been previously selected.';
$string['dependquestion_link'] = 'mod/smartquest/questions#Parent_Question';
$string['directwarnings'] = 'Direct dependencies to this question will be removed. This will affect:';
$string['displaymethod'] = 'Display method not defined for question.';
$string['download'] = 'Download';
$string['downloadtextformat'] = 'Download in text format';
$string['downloadtextformat_help'] = 'This feature enables you to save all the responses of a smartquest to a text file (EXCEL).
 This file can then be imported into a spreadsheet (e.g. MS Excel or Open Office Calc) or a statistical package for further processing the data.';
$string['downloadtextformat_link'] = 'mod/smartquest/report#Download_in_text_format';
$string['dropdown'] = 'Dropdown Box';
$string['dropdown_help'] = 'There is no real advantage to using the Dropdown Box over using the Radio Buttons
 except perhaps for longish lists of options, to save screen space.';
$string['dropdown_link'] = 'mod/smartquest/questions#Dropdown_Box';
$string['edit'] = 'Edit';
$string['editingsmartquest'] = 'Editing Smsrtquest Settings';
$string['editquestion'] = 'Editing {$a} question';
$string['email'] = 'Email';
$string['errnewname'] = 'Sorry, name already in use. Pick a new name.';
$string['erroropening'] = 'Error opening smartquest.';
$string['errortable'] = 'Error system table corrupt.';
$string['essaybox'] = 'Essay Box';
$string['essaybox_help'] = 'This question will display a plain text box with x Textarea columns (or area width) and y Textarea rows (number of text lines).

If you leave both x and y to their default 0 value (or if you set it to 0), then moodle\'s HTML editor will be displayed
 with standard height and width (if available in the course/user context & user profile). ';
$string['event_all_responses_deleted'] = 'All Responses deleted';
$string['event_all_responses_saved_as_text'] = 'All Responses saved as text';
$string['event_all_responses_viewed'] = 'All Responses report viewed';
$string['event_individual_responses_viewed'] = 'Individual Responses report viewed';
$string['event_previewed'] = 'Smsrtquest previewed';
$string['event_non_respondents_viewed'] = 'Non-respondents viewed';
$string['event_question_created'] = 'Question created';
$string['event_question_deleted'] = 'Question deleted';
$string['event_response_deleted'] = 'Individual Response deleted';
$string['event_resumed'] = 'Attempt resumed';
$string['event_saved'] = 'Responses saved';
$string['event_submitted'] = 'Responses submitted';
$string['feedback'] = 'Feedback';
$string['feedback_help'] = 'Feedback Help';
$string['feedback_link'] = 'mod/smartquest/personality_test#Editing_Smsrtquest_Feedback_Messages';
$string['feedbackaddmorefeedbacks'] = 'Add {no} more feedback fields';
$string['feedbackbysection'] = 'Sections Feedback';
$string['feedbackeditingglobal'] = 'Editing Smsrtquest Global Feedback';
$string['feedbackeditingsections'] = 'Editing Smsrtquest Feedback Sections';
$string['feedbackeditingmessages'] = 'Editing Smsrtquest Feedback Messages';
$string['feedbackeditmessages'] = 'Save Sections settings and edit Feedback Messages';
$string['feedbackeditsections'] = 'Save settings and edit Feedback Sections';
$string['feedbackerrorboundaryformat'] = 'Feedback score boundaries must be either a percentage or a number. The value you entered in boundary {$a} is not recognised.';
$string['feedbackerrorboundaryoutofrange'] = 'Feedback score boundaries must be between 0% and 100%. The value you entered in boundary {$a} is out of range.';
$string['feedbackerrorjunkinboundary'] = 'You must fill in the feedback score boundary boxes without leaving any gaps.';
$string['feedbackerrorjunkinfeedback'] = 'You must fill in the feedback boxes without leaving any gaps.';
$string['feedbackerrororder'] = 'Feedback score boundaries must be in order, highest first. The value you entered in boundary {$a} is out of sequence.';
$string['feedbackglobal'] = 'Global Feedback';
$string['feedbackglobalmessages'] = 'Global Feedback messages';
$string['feedbackglobalheading'] = 'Global Feedback heading';
$string['feedbackheading'] = 'Feedback heading';
$string['feedbackheading_help'] = 'In the feedback heading field you can use 2 variables: $scorepercent and $oppositescorepercent.';
$string['feedbackmessages'] = 'Feedback messages for section {$a}';
$string['feedbacknextsection'] = 'Next section {$a}';
$string['feedbacknone'] = 'No Feedback messages';
$string['feedbacknotes'] = 'Feedback notes';
$string['feedbacknotes_help'] = 'Text entered here will be displayed to the respondents at the end of their Feedback Report';
$string['feedbackoptions'] = 'Feedback options';
$string['feedbackoptions_help'] = 'Feedback options are available if your smartquest contains the following question types and question settings:
Radio buttons; Dropdown box or Rate. Those questions must be set as Required, their Question Name field must NOT be empty and the Possible answers choices must contain a value.';
$string['feedbackoptions_link'] = 'mod/smartquest/personality_test';
$string['feedbackremovequestionfromsection'] = 'This question is part of feedback section [{$a}]';
$string['feedbackremovesection'] = 'Removing this question will completely remove feedback section [{$a}]';
$string['feedbackreport'] = 'Feedback Report';
$string['feedbackscore'] = 'Feedback Score';
$string['feedbackscores'] = 'Display Scores';
$string['feedbackscores_help'] = 'Display the table of feedback scores';
$string['feedbackscoreboundary'] = 'Feedback Score boundary';
$string['feedbacksectionlabel'] = 'Label';
$string['feedbacksectionlabel_help'] = 'This label will be used in the charts/diagrams. Please keep it as short as possible!';
$string['feedbacksectionheading'] = 'Feedback heading for section {$a}';
$string['feedbacksectionheadingmissing'] = 'You must enter a Heading for this Feedback section!';
$string['feedbacksectionheadingtext'] = 'Heading';
$string['feedbackhdr'] = 'Feedbacks';
$string['feedbacksection'] = 'Section';
$string['feedbacksections'] = '{$a} Feedback sections';
$string['feedbacksectionsselect'] = 'Sections';
$string['feedbacksectionsselect_help'] = 'Place your questions into those Sections';
$string['feedbacksectionsselect_link'] = 'mod/smartquest/personality_test#Editing_Smsrtquest_Feedback_Sections_2';
$string['feedbacksettingssaved'] = 'Feedback settings saved';
$string['feedbacktype'] = 'Feedback type';
$string['field'] = 'Question {$a}';
$string['fieldlength'] = 'Input box length';
$string['fieldlength_help'] = 'For the **Text Box** question type, enter the **Input Box length** and the **Maximum text length** of text to be entered by
respondent.

Default values are 20 characters for the Input Box width and 25 characters for the maximum length of text entered.';
$string['finished'] = 'You have answered all the questions in this smartquest!';
$string['firstrespondent'] = 'First Respondent';
$string['formateditor'] = 'HTML editor';
$string['formatplain'] = 'Plain text';
$string['grade'] = 'Submission grade';
$string['gradesdeleted'] = 'Smsrtquest grades deleted';
$string['headingtext'] = 'Heading text';
$string['horizontal'] = 'Horizontal';
$string['id'] = 'ID';
$string['includechoicecodes'] = 'Include choice codes';
$string['includechoicetext'] = 'Include choice text';
$string['incorrectcourseid'] = 'Course ID is incorrect';
$string['incorrectmodule'] = 'Course Module ID was incorrect';
$string['incorrectsmartquest'] = 'Smsrtquest is incorrect';
$string['invalidresponse'] = 'Invalid response specified.';
$string['invalidresponserecord'] = 'Invalid response record specified.';
$string['invalidsurveyid'] = 'Invalid smartquest ID.';
$string['indirectwarnings'] = 'This list shows the indirect dependent questions and the remaining dependencies for direct dependent questions:';
$string['kindofratescale'] = 'Type of rate scale';
$string['kindofratescale_help'] = 'Right-click on the More Help link below.';
$string['kindofratescale_link'] = 'mod/smartquest/questions#Type_of_rate_scale';
$string['lastrespondent'] = 'Last Respondent';
$string['length'] = 'Length';
$string['managequestions'] = 'Manage questions';
$string['managequestions_help'] = 'In the Manage questions section of the Edit Questions page, you can conduct a number of operations on a Smsrtquest\'s questions.';
$string['managequestions_link'] = 'mod/smartquest/questions#Manage_questions';
$string['mandatory'] = 'Mandatory - All these dependencies must be fulfilled.';
$string['maxdigitsallowed'] = 'Max. digits allowed';
$string['maxdigitsallowed_help'] = 'Use **Max. digits allowed** to set a limit to the number of characters entered for a Numeric question. Note that the
decimal point also counts as one character!';
$string['maxdigitsallowed_link'] = 'mod/smartquest/questions#Numeric';
$string['maxforcedresponses'] = 'Max. forced responses';
$string['maxforcedresponses_help'] = 'Use these parameters to force respondent to tick a minimum of **Min.** boxes and a maximum of **Max.** check boxes. To
force an exact number of check boxes to be ticked, set **Min.** and **Max.** to the same value. If only a min or a max value is desired, just leave the other
value to its default **0** value. If you set **Min.** or **Max.** to values other than their default **0** value, a warning message will be displayed if
respondent does not comply with your requirements. Obviously you should make any requirements clear to the respondent either in the general instructions of
your Smsrtquest or in the text of relevant questions.';
$string['maxtextlength'] = 'Max. text length';
$string['maxtextlength_help'] = 'For the Text Box question type, enter the Input Box length and the Maximum text length of text to be entered by respondent.
Default values are 20 characters for the Input Box width and 25 characters for the maximum length of text entered.';
$string['messageprovider:message'] = 'Smsrtquest reminder';
$string['messageprovider:notification'] = 'Smsrtquest submission';
$string['minforcedresponses'] = 'Min. forced responses';
$string['minforcedresponses_help'] = 'Use these parameters to force respondent to tick a minimum of **Min.** boxes and a maximum of **Max.** check boxes. To
force an exact number of check boxes to be ticked, set **Min.** and **Max.** to the same value. If only a min or a max value is desired, just leave the other
value to its default **0** value. If you set **Min.** or **Max.** to values other than their default **0** value, a warning message will be displayed if
respondent does not comply with your requirements. Obviously you should make any requirements clear to the respondent either in the general instructions of
your Smsrtquest or in the text of relevant questions.';
$string['misconfigured'] = 'Course is misconfigured';
$string['missingquestion'] = 'Please answer Required question ';
$string['missingquestions'] = 'Please answer Required questions: ';
$string['modulename'] = 'Smsrtquest';
$string['modulename_help'] = 'The smartquest module allows you to construct surveys using a variety of question types, for the purpose of gathering data from users.';
$string['modulenameplural'] = 'Smsrtquests';
$string['movedisabled'] = 'This item cannot be moved';
$string['myresponses'] = 'All your responses';
$string['myresponsetitle'] = 'Your {$a} response(s)';
$string['myresults'] = 'Your Results';
$string['name'] = 'Name';
$string['navigate'] = 'Allow branching questions';
$string['navigate_help'] = 'Enable Yes/No and Radio Buttons questions to have Child questions dependent on their choices in your smartquest.';
$string['navigate_link'] = 'mod/smartquest/conditional_branching';
$string['next'] = 'Next';
$string['nextpage'] = 'Next Page';
$string['nlines'] = '{$a} lines';
$string['noanswer'] = 'No answer';
$string['noattempts'] = 'No attempts have been made on this smartquest';
$string['nodata'] = 'No data posted.';
$string['noduplicates'] = 'No duplicate choices';
$string['noduplicateschoiceserror'] = 'You must enter at least 2 Possible answers for the "No duplicate choices" option!';
$string['notenoughscaleitems'] = 'You must enter a minimum value of 2 scale items!';
$string['noneinuse'] = 'This smartquest does not contain any questions.';
$string['non_respondents'] = 'Users who have not yet submitted their responses to this smartquest';
$string['non_respondentsstudeffect'] = 'Users who have not yet filled out smartquest';
$string['nopublicsurveys'] = 'No public smartquests.';
$string['noresponsedata'] = 'No responses for this question.';
$string['noresponses'] = 'No responses';
$string['normal'] = 'Normal';
$string['notanumber'] = '<strong>{$a}</strong> is not an accepted number format.';
$string['notapplicable'] = 'Not relevant';
$string['notapplicablecolumn'] = 'not relevant column';
$string['explainnotapplicable'] = 'Explain';
$string['notavail'] = 'This smartquest is no longer available. Ask your teacher to delete it.';
$string['noteligible'] = 'You are not eligible to take this smartquest.';
$string['notemplatesurveys'] = 'No template smartquests.';
$string['notifications'] = 'Send submission notifications';
$string['notifications_help'] = 'Notify roles with the "mod/smartquest:submissionnotification" capability when a submission is made.';
$string['notifications_link'] = 'mod/smartquest/mod#Submission_Notifications';
$string['notopen'] = 'This smartquest will not open until {$a}.';
$string['notrequired'] = 'Response is not required';
$string['notset'] = 'not set';
$string['not_started'] = 'not started';
$string['nousersselected'] = 'No users selected';
$string['num'] = '#';
$string['numattemptsmade'] = '{$a} attempts made on this smartquest';
$string['numberfloat'] = 'The number you entered <strong>{$a->number}</strong> has been reformatted/rounded with <strong>{$a->precision}</strong> decimal place(s).';
$string['numberofdecimaldigits'] = 'Nb of decimal digits';
$string['numberofdecimaldigits_help'] = 'Use **Nb of decimal digits** to specify the format of the Average value counted and displayed at the Smsrtquest Report page.';
$string['numberofdecimaldigits_link'] = 'mod/smartquest/questions#Numeric';
$string['numberscaleitems'] = 'Nb of scale items';
$string['numberscaleitems_help'] = 'Nb of scale items is the *number of items* to be used in your rate scale. You would normally use a value of 3 to 5. Default value: **5**.';
$string['numeric'] = 'Numeric';
$string['numeric_help'] = 'Use this question type if you expect the response to be a correctly formatted number.';
$string['of'] = 'of';
$string['opendate'] = 'Use Open Date';
$string['opendate_help'] = 'You can specify a date to open the smartquest here. Check the check box, and select the date and time you want.
 Users will not be able to fill out the smartquest before that date. If this is not selected, it will be open immediately.';
$string['option'] = 'option {$a}';
$string['optional'] = 'Optional - At least one of this dependencies has to be fulfilled.';
$string['optionalname'] = 'Question Name';
$string['optionalname_help'] = 'The Question Name is only used when you export responses to CSV/Excel format.
 If you never export to CSV, then you needn\'t worry about Question names at all.
 If you plan to regularly export your smartquest data to CSV, then you have a choice of two options for question naming. ';
$string['optionalname_link'] = 'mod/smartquest/questions#Question_Name';
$string['or'] = '- OR -';
$string['order_ascending'] = 'Ascending order';
$string['order_default'] = 'View Default order';
$string['order_descending'] = 'Descending order';
$string['orderresponses'] = 'Order Responses';
$string['orderresponses_help'] = 'When displaying All Responses you can order the choices by number of responses (the Average column) for the following
 4 types of questions.

* single choices radio button
* single choices dropdown list
* multiple choices (check boxes)
* rate questions (including Likert scales).

When you arrive on the All Responses page, by default all responses are ordered in the order that the smartquest creator entered the question choices.
 You can choose to order them by ascending or descending order.';
$string['orderresponses_link'] = 'mod/smartquest/report#Order_Responses';
$string['osgood'] = 'Osgood';
$string['other'] = 'Other:';
$string['otherempty'] = 'If you tick this choice you must enter some text in the text box!';
$string['overviewnumresplog'] = 'responses';
$string['overviewnumresplog1'] = 'response';
$string['overviewnumrespvw'] = 'responses';
$string['overviewnumrespvw1'] = 'response';
$string['owner'] = 'Owner';
$string['page'] = 'Page';
$string['pageof'] = 'Page {$a->page} of {$a->totpages}';
$string['parent'] = 'Parent';
$string['participant'] = 'Participant';
$string['pleasecomplete'] = 'Please complete this choice.';
$string['pluginadministration'] = 'Smsrtquest administration';
$string['pluginname'] = 'Smsrtquest';
$string['position'] = 'position';
$string['possibleanswers'] = 'Possible answers';
$string['posteddata'] = 'Reached page with posted data:';
$string['preview_label'] = 'Preview';
$string['preview_smartquest'] = 'Smsrtquest Preview';
$string['previewingalert'] = 'In preview state';
$string['previewing'] = ' Previewing Smsrtquest ';
$string['previous'] = 'Previous';
$string['previouspage'] = 'Previous Page';
$string['print'] = 'Print this Response';
$string['printblank'] = 'Print Blank';
$string['printblanktooltip'] = 'Opens printer-friendly window with blank Smsrtquest';
$string['printtooltip'] = 'Opens printer-friendly window with current Response';
$string['private'] = 'Private';
$string['public'] = 'Public';
$string['publiccopy'] = 'Copy:';
$string['publicoriginal'] = 'Original:';
$string['qtype'] = 'Type';
$string['qtype_help'] = 'Select whether users will be allowed to respond once, daily, weekly, monthly or an unlimited number of times (many).';
$string['qtypedaily'] = 'respond daily';
$string['qtypemonthly'] = 'respond monthly';
$string['qtypeonce'] = 'respond once';
$string['qtypeunlimited'] = 'respond many';
$string['qtypeweekly'] = 'respond weekly';
$string['smartquest:addinstance'] = 'Add a new smartquest';
$string['smartquest:copysurveys'] = 'Copy template and private smartquests';
$string['smartquest:createpublic'] = 'Create public smartquests';
$string['smartquest:createtemplates'] = 'Create template smartquests';
$string['smartquest:deleteresponses'] = 'Delete any response';
$string['smartquest:downloadresponses'] = 'Download responses in an EXCEL file';
$string['smartquest:editquestions'] = 'Create and edit smartquest questions';
$string['smartquest:manage'] = 'Create and edit smartquests';
$string['smartquest:message'] = 'Send message to non-respondents';
$string['smartquest:preview'] = 'Preview smartquests';
$string['smartquest:printblank'] = 'Print blank smartquest';
$string['smartquest:readallresponseanytime'] = 'Read all responses any time';
$string['smartquest:readallresponses'] = 'Read response summaries, subject to open times';
$string['smartquest:readownresponses'] = 'Read own responses';
$string['smartquest:submissionnotification'] = 'Receive notification for each submission';
$string['smartquest:submit'] = 'Complete and submit a smartquest';
$string['smartquest:view'] = 'View a smartquest';
$string['smartquest:viewsingleresponse'] = 'View complete individual responses';
$string['smartquest:canbesurveyed'] = 'Roles can be surveyed';
$string['smartquest:canbestudsurveyed'] = 'Stud roles';
$string['smartquest:createsapeventsmartquest'] = 'Create SAP event smartquest';
$string['smartquest:createcoursesmartquest'] = 'Create course smartquest';
$string['smartquest:createroleeffectsmartquest'] = 'Create effect smartquest';
$string['smartquest:createstudeffectsmartquest'] = 'Create stud effect smartquest';
$string['smartquest:createroletypesmartquest'] = 'Create role type smartquest';
$string['smartquest:createguidelinesmartquest'] = 'Create guideline smartquest';
$string['smartquest:viewsapeventsmartquest'] = 'View SAP event smartquest';
$string['smartquest:viewcoursesmartquest'] = 'View course smartquest';
$string['smartquest:viewroleeffectsmartquest'] = 'View effect smartquest';
$string['smartquest:viewstudeffectsmartquest'] = 'View stud effect smartquest';
$string['smartquest:viewroletypesmartquest'] = 'View role type smartquest';
$string['smartquest:viewguidelinesmartquest'] = 'View guideline smartquest';
$string['smartquestadministration'] = 'Smsrtquest Administration';
$string['smartquestcloses'] = 'Smsrtquest Closes';
$string['smartquestopens'] = 'Smsrtquest Opens';
$string['smartquestreport'] = 'Smsrtquest Report';
$string['questionnum'] = 'Question #';
$string['questions'] = 'Questions';
$string['questionsinsection'] = 'Questions in this section:';
$string['questiontypes'] = 'Question types';
$string['questiontypes_help'] = 'See the Moodle Documentation below';
$string['questiontypes_link'] = 'mod/smartquest/questions#Question_Types';
$string['radiobuttons'] = 'Radio Buttons';
$string['radiobuttons_help'] = 'In this question type, the respondent must select one out of the choices offered.';
$string['radiobuttons_link'] = 'mod/smartquest/questions#Radio_Buttons';
$string['rank'] = 'Rank';
$string['ratescale'] = 'Rate (scale 1..5)';
$string['ratescale_help'] = 'See the Moodle Documentation below';
$string['ratescale_link'] = 'mod/smartquest/questions#Rate_.28scale_1..5.29';
$string['realm'] = 'Smsrtquest Type';
$string['realm_help'] = '* **There are  three types of smartquests:**
 * Private - belongs to the course it is defined in only.
 * Template - can be copied and edited.
 * Public - can be shared among courses.';
$string['realm_link'] = 'mod/smartquest/qsettings#Smsrtquest_Type';
$string['redirecturl'] = 'The URL to which a user is redirected after completing this smartquest.';
$string['remove'] = 'Delete';
$string['removenotinuse'] = 'This smartquest used to depend on a Public smartquest which has been deleted.
It can no longer be used and should be deleted.';
$string['required'] = 'Response is required';
$string['required_help'] = 'If you select ***Yes***, response to this question will be required, i.e.
the respondent will not be able to submit the smartquest
until this question has been answered.';
$string['required_link'] = 'mod/smartquest/questions#Response_Required';
$string['requiredparameter'] = 'A required parameter was missing.';
$string['reset'] = 'Reset';
$string['removeallsmartquestattempts'] = 'Delete all smartquest responses';
$string['respeligiblerepl'] = '(replaced by role overrides)';
$string['respondent'] = 'Respondent';
$string['respondenteligibleall'] = 'all';
$string['respondenteligiblestudents'] = 'students only';
$string['respondenteligibleteachers'] = 'teachers only';
$string['respondents'] = 'Respondents';
$string['respondenttype'] = 'Respondent Type';
$string['respondenttype_help'] = 'You can display your users\' full names with each response by setting this to "fullname".
You can hide your users\' identities from the responses by setting this to "anonymous".';
$string['respondenttype_link'] = 'mod/smartquest/mod#Respondent_Type';
$string['respondenttypeanonymous'] = 'anonymous';
$string['respondenttypefullname'] = 'fullname';
$string['response'] = 'Response';
$string['responsefieldlines'] = 'Input box size';
$string['responseformat'] = 'Response format';
$string['responseoptions'] = 'Response options';
$string['responses'] = 'Responses';
$string['responseview'] = 'Students can view ALL responses';
$string['responseview_help'] = 'You can specify who can see the responses of all respondents to submitted smartquests (general statistics tables).';
$string['responseview_link'] = 'mod/smartquest/mod#Response_viewing';
$string['responseviewstudentsalways'] = 'Always';
$string['responseviewstudentsnever'] = 'Never';
$string['responseviewstudentswhenanswered'] = 'After answering the smartquest';
$string['responseviewstudentswhenclosed'] = 'After the smartquest is closed';
$string['restrictedtoteacher'] = 'These functions are restricted to editing teachers only!';
$string['resume'] = 'Save/Resume answers';
$string['resume_help'] = 'Setting this option allows users to save their answers to a smartquest before submitting them.
 Users can leave the smartquest unfinished and resume from the save point at a later date.';
$string['resume_link'] = 'mod/smartquest/mod#Save/Resume_answers';
$string['resumesurvey'] = 'Resume smartquest';
$string['return'] = 'Return';
$string['save'] = 'Save';
$string['saveasnew'] = 'Save as New Question';
$string['savedbutnotsubmitted'] = 'This smartquest has been saved but not yet submitted.';
$string['savedprogress'] = 'Your progress has been saved.  You may return at any time to complete this smartquest.';
$string['saveeditedquestion'] = 'Save question {$a}';
$string['savesettings'] = 'Save settings';
$string['search:activity'] = 'Smsrtquest - activity information';
$string['search:question'] = 'Smsrtquest - questions';
$string['section'] = 'Description';
$string['sectionbreak'] = '----- Page Break -----';
$string['sectionbreak_help'] = '----- Page Break -----';
$string['sectionsnotset'] = 'You must select at least ONE question per section!<br />Section(s) not selected: {$a}';
$string['sectiontext'] = 'Label';
$string['sectiontext_help'] = 'This is not a question but a (short) text which will be displayed to introduce a series of questions.';
$string['selecttheme'] = 'Select a theme (css) to use with this smartquest.';
$string['send'] = 'Send';
$string['sendemail'] = 'Send email';
$string['send_message'] = 'Send message to selected users';
$string['send_message_to'] = 'Send message to:';
$string['sendemail_help'] = 'Sends a copy of each submission to the specified address or addresses.
You can provide more than one address by separating them with commas.
Leave blank for no email backup.';
$string['set'] = 'set';
$string['settings'] = 'Settings';
$string['settingssaved'] = 'Settings saved';
$string['show_nonrespondents'] = 'Non-respondents';
$string['started'] = 'started';
$string['strfdate'] = '%d/%m/%Y';
$string['strfdateformatcsv'] = 'd/m/Y H:i:s';
$string['submissionnotificationhtmlanon'] = 'There is a new <a href="{$a->submissionurl}">submission</a> to the "{$a->name}"
 smartquest.';
$string['submissionnotificationhtmluser'] = 'There is a new <a href="{$a->submissionurl}">submission</a> to the "{$a->name}"
 smartquest from "<a href="{$a->profileurl}">{$a->username}</a>".';
$string['submissionnotificationsubject'] = 'New smartquest submission';
$string['submissionnotificationtextanon'] = 'There is a new submission ({$a->submissionurl}) to the "{$a->name}" smartquest.';
$string['submissionnotificationtextuser'] = 'There is a new submission ({$a->submissionurl}) to the "{$a->name}" smartquest from "{$a->username}" ({$a->profileurl}).';
$string['submitoptions'] = 'Submission options';
$string['submitpreview'] = 'Submit preview';
$string['submitpreviewcorrect'] = 'This submission would be accepted as correctly filled in.';
$string['submitsurvey'] = 'Submit smartquest';
$string['submitted'] = 'Submitted on:';
$string['subtitle'] = 'Subtitle';
$string['subtitle_help'] = 'Subtitle of this smartquest. Appears below the title on the first page only.';
$string['subject'] = 'Subject';
$string['summary'] = 'Summary';
$string['surveynotexists'] = 'smartquest does not exist.';
$string['surveyowner'] = 'You must be a smartquest owner to perform this operation.';
$string['surveyresponse'] = 'Response from smartquest';
$string['template'] = 'Template';
$string['templatenotviewable'] = 'Template smartquests are not viewable.';
$string['text'] = 'Question Text';
$string['textareacolumns'] = 'Textarea columns';
$string['textareacolumns_help'] = 'This question will display a plain text box with **x**
 *Textarea columns* (or area *width*) and **y** *Textarea rows* (number of text
*lines*).
If you leave both x and y to their default **0** value (or if you set it to **0**),
 then HTML editor will be displayed with standard height and width (if
available in the course/user context &amp; user profile).';
$string['textarearows'] = 'Textarea rows';
$string['textbox'] = 'Text Box';
$string['textbox_help'] = 'For the Text Box question type,
enter the Input Box length and the Maximum text length of text to be entered by respondent.
Default values are 20 characters for the Input Box width and 25 characters for the maximum length of text entered.';
$string['textdownloadoptions'] = 'Options for text download (EXCEL)';
$string['thank_head'] = 'Thank you for completing this Smsrtquest.';
$string['theme'] = 'Theme';
$string['thismonth'] = 'this month';
$string['thisresponse'] = 'This response';
$string['thisweek'] = 'this week';
$string['title'] = 'Title';
$string['title_help'] = 'Title of this smartquest, which will appear at the top of every page. By default Title is set to the smartquest Name, but you can edit it as you like.';
$string['today'] = 'today';
$string['total'] = 'Total';
$string['type'] = 'Question Type';
$string['undefinedquestiontype'] = 'Undefined question type!';
$string['unknown'] = 'Unknown';
$string['unknownaction'] = 'Unknown smartquest action specified...';
$string['url'] = 'Confirmation URL';
$string['url_help'] = 'The URL to which a user is redirected after completing this smartquest.';
$string['useprivate'] = 'Copy existing';
$string['usepublic'] = 'Use public';
$string['usetemplate'] = 'Use template';
$string['vertical'] = 'Vertical';
$string['view'] = 'View';
$string['viewallresponses'] = 'View All Responses';
$string['viewallresponses_help'] = 'If the smartquest is set to **Group Mode**:
             *Visible groups*, or is set to *Separate groups* and the current user
 has the *moodle/site:accessallgroups* capability (in the current context), and groups have been defined in the current course,
 then the user has access to a dropdown list of groups.
  This dropdown list enables the user to "filter" the smartquest responses by groups.
 If the setting is **Group Mode**: *Separate groups*, then users who do not have the *moodle/site:accessallgroups* capability
 (usually students, or non-editing teachers, etc.) will only be able to view the responses of the group(s) they belong to.';
$string['viewallresponses_link'] = 'Viewing_Smsrtquest_responses#Group_filtering';
$string['viewbyresponse'] = 'List of responses';
$string['viewindividualresponse'] = 'Individual responses';
$string['viewindividualresponse_help'] = 'Click on the respondents\' names in the list below to view their individual responses.';
$string['viewresponses'] = 'All responses ({$a})';
$string['viewyourresponses'] = 'Your responses- view {$a}';
$string['warning'] = 'Warning, error encountered.';
$string['wronganswers'] = 'There is something wrong with your answers (see below)';
$string['wrongdateformat'] = 'The date entered: <strong>{$a}</strong> does not correspond to the format shown in the example.';
$string['wrongdaterange'] = 'ERROR! The year must be set in the 1902 to 2037 range.';
$string['wrongformat'] = 'There is something wrong with your answer to question:&nbsp;';
$string['wrongformats'] = 'There is something wrong with your answer to questions:&nbsp;';
$string['yesno'] = 'Yes/No';
$string['yesno_help'] = 'Simple Yes/No question.';
$string['yourresponse'] = 'Your response';
$string['yourresponses'] = 'Your responses';
$string['crontask'] = 'Smsrtquest cleanup job';
$string['haschilds'] = 'to this questions has childrens';
$string['anonymoustemplate'] = 'Anonymous template';
$string['relevantratescale'] = 'Rate (scale 1..5 or not relevant)';
$string['relevantratescale_help'] = 'See the Moodle Documentation below';
$string['kindofrelevantratescale'] = 'Type of rate scale';
$string['kindofrelevantratescale_help'] = 'Right-click on the More Help link below.';
$string['sapevent'] = 'SAP event';
$string['roletype'] = 'Role';
$string['course'] = 'Course';
$string['studeffect'] = 'Stud effect';
$string['roleeffect'] = 'Effect';
$string['guideline'] = 'Guide Line';
$string['smartquesthtype'] = 'Smartquesth type';
$string['rtype'] = 'Smartquesth type';
$string['rtype_help'] = 'The smartquest type shoul be selected from the following options';
$string['sapevent_id'] = 'SAP event';
$string['sapevent_id_help'] = 'SAP event';
$string['user_id'] = 'Name of user';
$string['user_id_help'] = 'Name of user with selected role type';
$string['role_id'] = 'Role type';
$string['role_id_help'] = 'Role type';
$string['rtypecourse'] = 'Smartquest of course';
$string['rtyperoleeffect'] = 'Smartquest effect';
$string['rtypesapevent'] = 'Smartquest of SAP event: <b><u>{$a->name}</u></b>';
$string['rtypesapeventwithrole'] = 'Smartquest of SAP event: <b><u>{$a->name}</u></b> to <b>
            <u>{$a->userfullname}</u></b> ({$a->rolename})';
$string['rtyperole'] = 'Smartquest of role: <b><u>{$a->rolename}</u></b> name: <b><u>{$a->userfullname}</u></b>';
$string['rtypestudeffect'] = 'Smartquest of Stud Effect';
$string['rtypeguideline'] = 'Guide Line';
$string['chooseuser'] = 'Choose User';
$string['rtypestudeffectwithuser'] = 'Smartquesth about stud: <b><u>{$a->firstname} {$a->lastname}</u></b>';
$string['rtypeguidelinewithuser'] = 'Guide line about stud: <b><u>{$a->firstname} {$a->lastname}</u></b>';
$string['choose'] = 'Choose';
$string['aboutuser'] = 'About <b><u>{$a->firstname} {$a->lastname}</u></b>';
$string['aboutusercsv'] = 'About User';
$string['checkrelallradiobuttons'] = 'You need to explain why not relevant';
$string['notcompletedefinition'] = 'This smartquesth setting is not yet complete.';
$string['notrelevant'] = 'Not relevant';
$string['tonext'] = 'To next stud';
$string['toreport'] = 'To response report';
$string['allstudeffectcomplete'] = 'All stud effect complete';
$string['numberid'] = 'id number';
$string['sentsmartqoest'] = 'Share smartQuest';
$string['bodyoutlooktext'] = 'This smartQuest shared with you, you can view it in the attached link.';
$string['edit_questions'] = 'Edit questions';
$string['phone'] = 'phone';
$string['workerid'] = 'worker id number';
$string['brigate'] = 'brigate';
$string['wing'] = 'wing';
$string['userdepartment'] = 'department';
$string['stuff'] = 'stuff';
$string['manager'] = 'manager';

$string['messagesubject'] = 'someone share it with you';
$string['messagerecipients'] = 'recipients';
$string['messagesubject'] = 'subject';
$string['messagebody'] = 'body';
$string['subjectoutlooklink'] = 'someone share this smartquest with you';   
$string['copytomyself'] = 'copy to myself?';   
$string['sendmessage'] = 'sendmessage';     
$string['bodyoutlooklink'] = 'someone share this smartquest with you';
$string['endbodyoutlooklink'] = "see U in the next meating";
$string['hello'] = 'hi' ;
$string['pleaseselect'] = 'please select ' ;
 
