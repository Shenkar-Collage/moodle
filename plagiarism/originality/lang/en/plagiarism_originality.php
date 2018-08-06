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
 * @package   plagiarism_originality
  * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// global $DB,$CFG,$USER;
$string['pluginname'] = 'Originality Plagiarism Checker';
$string['studentdisclosuredefault']  ='All files uploaded will be submitted to the Originality plagiarism detection service';
$string['originalitystudentdisclosure']  ='This submission is genuinely mine, it was written by me and I take full responsibility for its authenticity.<br>This assignment is my work, in exception to where I have acknowledged the use of the works of others.<br>';
$string['studentdisclosure'] = ' Originality Student Disclosure';
$string['studentdisclosure_help'] = 'This text will be displayed to all students on the file upload page.';
$string['originalityexplain'] = 'Settings for the Originality plagiarism plugin';
$string['originality'] = 'Originality plagiarism plugin';
$string['useoriginality'] ='Enable Originality';
$string['savedconfigsuccess'] = 'Plagiarism Settings Saved';
$string['useoriginality'] ='Enable Originality';
$string['originality_help'] = 'Originality is an anti-plagiarism tool. Enabling Originality will set the maximum submission size to 100KB.';
$string['originality_key'] = 'Originality key';
$string['originalitykey'] = 'Originality key';
$string['originalitykey_help'] = 'You need to obtain an Originality ID key to use this plugin ';
$string['originality_server'] = 'Originality Server';
$string['originalityserver'] = 'Originality Server';
$string['originality_server_default_value'] = 'https://www.originality.co.il/restapi/';
$string['originalityserver_help'] = 'Enter originality IP address with http or https';
// $string['originality_api'] = ""$orig_server->value/Api/$orig_key->value/SubmitDocument/"
$string['originality_view_report'] = 'Allow students to view the report';
$string['originalityviewreport_help'] = 'Allow students to view the report';


$string['savedconfigsuccess'] = 'Settings saved successfully';
$string['savedconfigfailed'] = 'The API Key that was entered is wrong, please try again (plugin is disabled)';

$string['agree_checked'] = "I am aware and in full agreement that this assignment may examined by 'Originality Group' in order to detect plagiarism and I accept the <a rel='external' href='https://www.originality.co.il/termsOfUse.html' target='_blank' style='text-decoration:underline'>terms of this examination</a>.";

$string['originality_fileextmsg']="Only files with the following extensions will be permitted: "; //TXT, RTF, DOC, DOCX, PDF";

$string['originality_inprocessmsg'] = "In process";

$string['originality_info'] = "Originality Information";

$string['originality_settings'] = "Originality Settings";

$string['originality_customerservice'] = "Originality Group. Contact at CustomerService@originality.co.il";

$string['settings_key_error'] = "The secret key you entered is invalid. Please enter a valid secret key";

$string['originality_one_type_submission'] = "Plagiarism detection allows either a single file or inline text submission. Choose one of the two, not both.";

$string['originality_unprocessable'] = 'Unprocessable';

//It goes with file_identifier for next version (probably 3.1.8)
$string['originality_allow_multiple_file_submission'] = "Allow multiple file submissions";

?>