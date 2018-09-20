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
 * lib.php - Contains Plagiarism plugin specific functions called by Modules.
 *
 * @since      2.0
 * @package    plagiarism_originality
 * @subpackage plagiarism
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @LastUpdateDate: 2017-09-18 
 */

if (!defined('MOODLE_INTERNAL')) {
	die('Direct access to this script is forbidden.'); ///  It must be included from a Moodle page
}

//get global class
global $CFG;
require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/originality/locallib.php');

class plagiarism_plugin_originality extends plagiarism_plugin
{
	/**
     * hook to allow plagiarism specific information to be displayed beside a submission
     * @param array  $linkarraycontains all relevant information for the plugin to generate a link
     * @return string
     *
     */

	public function get_links($linkarray)
    {
		global $DB, $USER, $COURSE, $OUTPUT, $CFG, $PAGE;

		$output = ''; // < Added by openapp, solves "undefined variable"
        $displaynone = '';

        $plagiarismsettings = (array)get_config('plagiarism');
        $admin_allows_student_view_report = $plagiarismsettings['originality_view_report'];
        $select = 'cm = ?';

        if ($originality_use = $DB->get_record_select('plagiarism_originality_conf', $select, array($linkarray['cmid']))) {
            $teacher_allows_student_view_report = $originality_use->student_view_report;
        }else{
            return;
        }
        $userroles = current(get_user_roles($PAGE->context, $USER->id));

        if ($userroles) {
            $isStudent = $userroles->shortname=='student'? true : false;
            // if (user_has_role_assignment($USER->id, 5, $linkarray['cmid'])){ //roleid is 5 when its a student
            if ($isStudent){
                if (!($admin_allows_student_view_report && $teacher_allows_student_view_report))
                    return;
            }
        }

        $current_language = current_language();
       //commenting this out since it wasn't working on moodle340
       // echo "<script> var current_language = '" . $current_language . "';</script>";
       $PAGE->requires->js('/plagiarism/originality/javascript/load-language.js.php');

        if (isset($_GET['action'])){
            if ($_GET['action']=='grading') $PAGE->requires->js('/plagiarism/originality/javascript/jquery-3.1.1.min.js');
            if ($_GET['action']=='grading') $PAGE->requires->js('/plagiarism/originality/javascript/inter-submissions.js'.'?v='.time());
        }

		//$userid, $file, $cmid, $course, $module
		$cmid = $linkarray['cmid'];
		$userid = $linkarray['userid'];
		$select = 'id = ' . $cmid;
		$ins = $DB->get_record_select('course_modules', $select,null,'*',IGNORE_MULTIPLE);

//		$select = 'assignment = ' . $ins->instance . ' AND userid =' . $userid;
		// < Changed by openapp, userid condition should be done only when isset
		$select = 'assignment = ' . $ins->instance; 
		if(isset($userid)) {
			$select .= ' AND userid =' . $userid;
		}
		// >
		$resp = $DB->get_record_select('plagiarism_originality_resp', $select,null,'*',IGNORE_MULTIPLE);

		if ($resp) {

			$grade = $resp->grade;

            if ($grade > 950)  {
                $grade = get_string("originality_unprocessable", "plagiarism_originality");
            }else{
                $grade  = $grade . '%';
            }

            if ($_GET['action']=='grading')  $displaynone = "style='display:none;'";

            /*
             * Version 3.1.7 Check permissions of moodledata files folder.
             * From now on storing the originality files there
			 * updated on 2017-09-18
             */

            $files_dir = $CFG->dataroot . '/originality/';

            $path = $CFG->wwwroot .'/plagiarism/originality/show_report.php?file=' . $ins->instance . '/' . $resp->file;

            $respfile = $resp->file;
            $respfile_array = explode('.', $respfile);
			$extension = end($respfile_array);
			$icon = $OUTPUT->pix_url(file_extension_icon(".$extension"))->out();
			$image = html_writer::empty_tag('img', array('src' => $icon));
			$output = '<div class="plagiarismreport" dir="rtl" ' . $displaynone . '>';
			$output .= /*'Originality status: '.*/ $grade.'&nbsp;&nbsp;';
			if(file_exists($files_dir.$ins->instance."/". $resp->file)) $output .= '<a href="' . $path . '" target="_blank" style="text-decoration:underline">'.$image.'</a>' .
			'</div><input type="hidden" id="assNum" value="'. $ins->instance.'">';
		} else {
            $req = $DB->get_record_select('plagiarism_originality_req', $select,null,'*',IGNORE_MULTIPLE);

            if ($req){
                $output = '<div class="plagiarismreport" dir="rtl" ' . $displaynone . '>';;
                $output .= get_string("originality_inprocessmsg", "plagiarism_originality");
                $output .= '</div>';
            }
		}

		$plagiarismsettings = (array)get_config('plagiarism');
		$select = 'cm = ?';

		if (!empty($plagiarismsettings['originality_use'])) 
		{
			if (!$originality_use = $DB->get_records_select('plagiarism_originality_conf', $select, array($cmid))) 
			{
				return;
			}
		} else 
			{
				return;
			}
		return $output;
	}
	// creates a compressed zip file DISABLED, not required by customers
	static function create_zip($files = array(), $destination = '', $overwrite = false)
	{
	}

	/* hook to save plagiarism specific settings on a module settings page
	* @param object $data - data from an mform submission.
	*/

	public function save_form_elements($data)
	{
		global $DB;
		$plagiarismsettings = (array)get_config('plagiarism');


		if (!empty($plagiarismsettings['originality_use'])) { //this is the admin setting
            $select = 'cm = ?';

            if ($originality_use = $DB->get_record_select('plagiarism_originality_conf', $select, array($data->coursemodule))) { //this is the lecturer setting
                $DB->delete_records_select('plagiarism_originality_conf', $select, array($data->coursemodule));
            }
			if (isset($data->originality_use)) {
				if ($data->originality_use != 0) {
					$newelement = new stdClass();
					$newelement->cm = $data->coursemodule;
                    $newelement->student_view_report  = $data->student_view_report;
					$DB->insert_record('plagiarism_originality_conf', $newelement);
				} /* else {
					$select = 'cm = ?';

					if ($originality_use = $DB->get_record_select('plagiarism_originality_conf', $select, array($data->coursemodule))) {
						$DB->delete_records_select('plagiarism_originality_conf', $select, array($data->coursemodule));
					}
				}*/
			}
		}
	}

	/**
     * hook to add plagiarism specific settings to a module settings page
     * @param object $mform  - Moodle form
     * @param object $context - current context
     */

	//Added by openapp - param $modulename
	public function get_form_elements_module($mform, $context, $modulename='plagiarism')
	{
		global $DB;
        global $PAGE;
        global $CFG;

    

        if ($PAGE->pagetype != 'mod-assign-mod') return;  //only show settings for assignments

       /*
        * Version 3.1.7: Only set default of max upload size for new assignments, not edit, as it may have previously been set.
        * Only set the default if the higher level default of a new assignment is higher than 100kb, which is value 102400
        */

        //Actually comment this out, decided to do on the client side when someone chooses to use originality
/*
        $default_is_above_100kb = (intval($CFG->portfolio_moderate_filesize_threshold) > 102400);

        $add = optional_param('add', '', PARAM_TEXT);
        if ($add && $default_is_above_100kb){
            $mform->getElement('assignsubmission_file_maxsizebytes');
            $mform->setDefault('assignsubmission_file_maxsizebytes', '102400');
        }
*/
        //check whether on add assignment to set use originality to yes as the default, depends on the particular college setting on the server
        $default_use_originality = $this->default_assignment_settings_use_originality();
        $add = optional_param('add', '', PARAM_TEXT);
        if ($add){
            if ($default_use_originality){ //if there is no record its the first time and set depending on the status of the college
                $mform->setDefault('originality_use', 1);
            }
        }

        /*
         * Check if this particular college is set to have the default to on for this school
         */

		$plagiarismsettings = (array)get_config('plagiarism');
        $admin_allows_student_view_report = $plagiarismsettings['originality_view_report'];
      //  $originality_allow_multiple_file_submission = $plagiarismsettings['originality_allow_mutiple_file_submission'];

        /*
         * If there is a global setting by the administrator from the originality settings page that allows multiple file submission,
         * then don't load the javascript that checks for multiple files.
         * It goes with file_identifier for next version (probably 3.1.8)
         */
       // if (!$originality_allow_multiple_file_submission){
            $PAGE->requires->js('/plagiarism/originality/javascript/jquery-3.1.1.min.js');
            $PAGE->requires->js('/plagiarism/originality/javascript/inter-assignment.js?v=10');
            $mform->addElement('hidden', 'originality_one_type_submission_error', get_string("originality_one_type_submission", "plagiarism_originality"));
            $mform->setType('originality_one_type_submission_error', PARAM_TEXT);
      //  }

		if (!empty($plagiarismsettings['originality_use'])) {
			$cmid = optional_param('update', 0, PARAM_INT); //there doesn't seem to be a way to obtain the current cm a better way - $this->_cm is not available here.
			$ynoptions = array(0 => get_string('no'), 1 => get_string('yes'));
			$mform->addElement('header', 'originalitydesc', get_string('originality', 'plagiarism_originality'));
			$mform->addHelpButton('originalitydesc', 'originality', 'plagiarism_originality');
			$mform->addElement('select', 'originality_use', get_string("useoriginality", "plagiarism_originality"), $ynoptions);
            if ($admin_allows_student_view_report) {
                $mform->addElement('select', 'student_view_report', get_string("originality_view_report", "plagiarism_originality"), $ynoptions);
            }
            else{
                $mform->addElement('hidden', 'student_view_report', '0');
            }
            $mform->setType('student_view_report', PARAM_NOTAGS);
			$select = 'cm = ?';

			if ($originality_use = $DB->get_record_select('plagiarism_originality_conf', $select, array($cmid))) { //if there is a record at all, it means originality was enabled
				$mform->setDefault('originality_use', 1);
                if ($admin_allows_student_view_report) {
                    $mform->setDefault('student_view_report', $originality_use->student_view_report);
                }
			}
		}
	}

	/**
     * hook to allow a disclosure to be printed notifying users what will happen with their submission
     * @param int $cmid - course module id
     * @return string
     */

	public function print_disclosure($cmid)
	{
		global $OUTPUT, $PAGE, $DB;

        if ($PAGE->pagetype == 'mod-forum-post') return;

		$plagiarismsettings = (array)get_config('plagiarism');
		$select = 'cm = ?';

		if (!empty($plagiarismsettings['originality_use'])) {
			if (!$originality_use = $DB->get_records_select('plagiarism_originality_conf', $select, array($cmid))) 
			{
				return;
			}
		}

		$str= $OUTPUT->box_start('generalbox boxaligncenter', 'intro-originality'); // < 2016-01-01 Changed id of element from 'intro'

		$formatoptions = new stdClass;
		$formatoptions->noclean = true;
		$path = core_component::get_plugin_directory("mod", "originality");

		$PAGE->requires->js('/plagiarism/originality/javascript/jquery-3.1.1.min.js');
		$PAGE->requires->js('/plagiarism/originality/javascript/inter.js?v=10');
		$str.= format_text(get_string("originalitystudentdisclosure", "plagiarism_originality"), FORMAT_MOODLE, $formatoptions);

		//  "I agree supports English and Hebrew
		$str.= "<div style='margin-top:10px'> <input  style='vertical-align: middle; margin-bottom: 4px; margin-right: 5px;'
        id='iagree' name='iagree' type='checkbox'/>". "<label for='iagree' >".get_string('agree_checked', 'plagiarism_originality')."</label>" ."</div>";

        $orig_server = $DB->get_record('config_plugins', array('name'=>'originality_server', 'plugin'=>'plagiarism'));

        //$server = str_replace('restapi', '', $orig_server->value);
        //$server = rtrim($server, '/');

        //$allowed_exts = trim(file_get_contents($server ."/fileext.txt"));

        $allowed_exts = "txt,rtf,doc,docx,pdf";

        $file_ext_err = get_string("originality_fileextmsg", 'plagiarism_originality') . $allowed_exts;

        $str.= <<<HHH
        <div id='fileext_err' style='background-color:yellow;margin-bottom:10px;padding:5px;font-weight:bold;display:none;'>
        $file_ext_err
        </div>
        <span id='file_exts' style='display:none;'>$allowed_exts</span>
HHH;

        $str.= $OUTPUT->box_end();
        return $str;
	}


    /**
     * hook to allow status of submitted files to be updated - called on grading/report pages.
     *
     * @param object $course - full Course object
     * @param object $cm - full cm object
     */

	public function update_status($course, $cm)
	{
		//debugging("called");
		//called at top of submissions/grading pages - allows printing of admin style links or updating status
	}

	/**
     * called by admin/cron.php
     *
     */

	public function cron()
	{
		//do any scheduled task stuff
	}
//}


    private function _get_server_and_key(){
        global $DB,$CFG,$USER;
        $orig_key = $DB->get_record('config_plugins', array('name'=>'originality_key', 'plugin'=>'plagiarism'));
        $orig_server = $DB->get_record('config_plugins', array('name'=>'originality_server', 'plugin'=>'plagiarism'));
        return array($orig_server, $orig_key);
    }


    private function _get_params_for_file_submission($eventdata){
        global $DB,$CFG,$USER;
        $courseNum = $eventdata['courseid'];
        $cmid = $eventdata['contextinstanceid']; //in events2 api, I think this is the course module id (Yael)
        $courseID = $eventdata['courseid'];
        $userID = $eventdata['userid'];
        $inst = "0";
        $lectID = "0";
        $courseCategory = null;
        $courseName = null;
        $senderIP = get_client_ip();
      //  $courseCategory = $DB->get_field_sql("SELECT name FROM {course_categories} WHERE id = (SELECT category FROM {course} WHERE id = $courseID)");
      //  $courseName = $DB->get_field('course','fullname',array('id'=>$courseID));
        $facultyCode = 100;
        $facultyName = 'FacultyName';
        $deptCode = 200;
        $deptName = 'DepartmentName';
        $courseCategory = 'CourseCategory';
        $courseName = 'CourseName';
      //  if (is_null($courseCategory)) $courseCategory = 'CourseCategory';
      //  if (is_null($courseName)) $courseName = 'CourseName';

        $check_file = '1'; //indicator whether to check file for plagiarism, for now default is 1
        $reserve2 = 'Reserve1';
        $groupsize = 1; // In the future set to # of group members submitting the work together

        if ($USER->idnumber)  $idnumber = '~' . $USER->idnumber;
        else $idnumber=$USER->id;

        //due to a problem with using the hebrew letter aleph in urls sent to the server, we are using constants for the names and for various other fields.

       // $firstname = str_replace(' ', '-', $USER->firstname);
       // $lastname = str_replace(' ', '-', $USER->lastname);

       // $firstname = str_replace('א', '_', $firstname);
       // $lastname = str_replace('א', '_', $lastname);

        $firstname = 'fname';
        $lastname = 'lname';

        $groupmembers = urlencode(str_replace(' ', '-', $firstname).'~'.str_replace(' ', '-', $lastname) . $idnumber);

        //$groupmembers = 'firstname~lastname';

//				$fileName = rawurlencode($fileName);
//				$courseCategory = rawurlencode($courseCategory);
//				$courseName = rawurlencode($courseName);
//				$groupmembers = rawurlencode($groupmembers);


        if (!isset($eventdata['assignNum'])){
            if($records = $DB->get_records_menu('course_modules', array('id' => $cmid), '', 'course,instance')) {
                if(isset($records[$courseNum]) && !empty($records[$courseNum])) {
                    $assignNum = $records[$courseNum];
                }
            }
        }else{
            $assignNum = $eventdata['assignNum'];
        }


        return array($courseNum, $cmid, $courseID, $userID, $inst, $lectID, $courseCategory, $courseName, $senderIP, $facultyCode, $facultyName, $deptCode, $deptName, $check_file, $reserve2, $groupsize, $groupmembers, $idnumber, $assignNum);

    }

    /*
     * Moodle for next version (3.1.8 probably) adding unique file identifier to each file submitted within an assignment submission so that we can use both online text and file within a submission
     * Note that since submissions can be both files and online text and the online text callback is separate, find a way to get sequential file identifier numbers accessible to both.
     * Perhaps check last request file identifier in the requests table and use the next free one.
     */
    private function _do_curl_request($orig_server, $orig_key, $content, $fileName, $courseNum, $cmid, $courseID, $userID, $inst, $lectID, $courseCategory, $courseName, $senderIP, $facultyCode, $facultyName, $deptCode, $deptName, $check_file, $reserve2, $groupsize, $groupmembers, $idnumber, $assignNum){ //, $file_identifier){
        global $DB,$CFG,$USER;
        $url = "$orig_server->value/Api/$orig_key->value/SubmitDocument/$fileName/$senderIP/$facultyCode/$facultyName/$deptCode/$deptName/$courseCategory/$courseNum/$courseName/$assignNum/$userID/$lectID/$check_file/$reserve2/$groupsize/$groupmembers"; // /$file_identifier";
        //$url = "http://moodle29test/yael.php/SubmitDocument/$fileName/$senderIP/$facultyCode/$facultyName/$deptCode/$deptName/$courseCategory/$courseNum/$courseName/$assignNum/$userID/$lectID/$reserve1/$reserve2/$groupsize/$groupmembers";

        $allowedUrlLength = 4096; //So its accepted by the server. IIS: The default value for maxUrl is 4096 bytes

        if (strlen($url) > $allowedUrlLength){
            $deptName = mb_substr($deptName, 0, 1, 'UTF8'); //get first character of deptName
            log_it("Url too long, changing Department Name to ". $deptName);
            $url = "$orig_server->value/Api/$orig_key->value/SubmitDocument/$fileName/$senderIP/$facultyCode/$facultyName/$deptCode/$deptName/$courseCategory/$courseNum/$courseName/$assignNum/$userID/$lectID/$check_file/$reserve2/$groupsize/$groupmembers"; // /$file_identifier";

            if (strlen($url)>$allowedUrlLength){
                $courseCategory = mb_substr($courseCategory, 0, 1, 'UTF8'); //get first character of deptName
                log_it("Url too long, changing Course Category to ". $courseCategory);
                $url = "$orig_server->value/Api/$orig_key->value/SubmitDocument/$fileName/$senderIP/$facultyCode/$facultyName/$deptCode/$deptName/$courseCategory/$courseNum/$courseName/$assignNum/$userID/$lectID/$check_file/$reserve2/$groupsize/$groupmembers"; // /$file_identifier";

                if (strlen($url) > $allowedUrlLength){
                    $courseName = mb_substr($courseName, 0, 1, 'UTF8'); //get first character of deptName
                    log_it("Url too long, changing Course Name to ". $courseName);
                    $url = "$orig_server->value/Api/$orig_key->value/SubmitDocument/$fileName/$senderIP/$facultyCode/$facultyName/$deptCode/$deptName/$courseCategory/$courseNum/$courseName/$assignNum/$userID/$lectID/$check_file/$reserve2/$groupsize/$groupmembers"; // /$file_identifier";
                }
            }
        }


        log_it("Uploading file: $url");

        $url = str_replace(' ','-',$url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE,1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $curl_error = false;

        if(curl_error($ch))
        {
            log_it("Curl Error: " . curl_error($ch));
            $curl_error = true;
        }else{
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200:
                    break;
                default:
                    log_it('Unexpected HTTP code: '. $http_code . "\n");
                    $curl_error = true;
            }
        }

        curl_close($ch);
        log_it("Uploading file curl output: " . strip_tags($output));
        if (!$curl_error) return true;
        else return false;

    }

    private function _update_request_and_response_db_records($assignNum, $userID, $fileName){ //, $file_identifier){
        global $DB,$CFG,$USER;
        $newelement = new stdClass();
        $newelement->assignment = $assignNum;
        $newelement->userid= $userID;
        $newelement->file = $fileName;
     //   $newelement->file_identifer = $file_identifier;

        $DB->delete_records('plagiarism_originality_resp',array("assignment"=>$assignNum,'userid'=> $userID)); //, "file_identifier"=>$file_identifier)); //delete any previous originality scores.
        $DB->delete_records('plagiarism_originality_req',array("assignment"=>$assignNum,'userid'=> $userID)); //, "file_identifier"=>$file_identifier)); //delete any previous requests.

        $DB->insert_record('plagiarism_originality_req', $newelement);
    }



function originality_event_file_uploaded($eventdata)
{
    //error_log("in file upload");
	$result = true;
    global $DB,$CFG,$USER;

	$plagiarismvalues = $DB->get_records_menu('plagiarism_originality_conf', array('cm' =>  $eventdata['contextinstanceid']), '', 'id,cm');

	if (empty($plagiarismvalues)) {
		return $result;
	} else {
        list($orig_server, $orig_key) = $this->_get_server_and_key();
		$modulecontext = context_module::instance( $eventdata['contextinstanceid']);
		$fs = get_file_storage();

        /*
         * Version 3.1.7, adding a file identifier so more than one file can be submitted, for example an assignment that allows both online text and file uploads
         */
       // $file_identifier = 0;

		if ($files = $fs->get_area_files($modulecontext->id, 'assignsubmission_file', 'submission_files', $eventdata['objectid'])) {
			foreach ($files as $file) {
				if ($file->get_filename() === '.') 
				{
					continue;
				}

             //   $file_identifier++;

                list($courseNum, $cmid, $courseID, $userID, $inst, $lectID, $courseCategory, $courseName, $senderIP, $facultyCode, $facultyName, $deptCode, $deptName, $check_file, $reserve2, $groupsize, $groupmembers, $idnumber, $assignNum) = $this->_get_params_for_file_submission($eventdata);

				$fileName = trim($file->get_filename());

                $fileName = preg_replace('!\s+!', '_', $fileName);
                $fileName = preg_replace("/[^א-תa-zA-Z0-9_\.]/", '', $fileName);
                $fileName = str_replace('א', '_', $fileName);

                $courseName = preg_replace('!\s+!', '_', $courseName);
                $courseName = preg_replace("/[^א-תa-zA-Z0-9_\.]/", '', $courseName);
              //  $courseName = str_replace('א', '_', $courseName);

                $deptName = preg_replace('!\s+!', '_', $deptName);
                $deptName = preg_replace("/[^א-תa-zA-Z0-9_\.]/", '', $deptName);
              //  $deptName = str_replace('א', '_', $deptName);

                $facultyName = preg_replace('!\s+!', '_', $facultyName);
                $facultyName = preg_replace("/[^א-תa-zA-Z0-9_\.]/", '', $facultyName);
              //  $facultyName = str_replace('א', '_', $facultyName);

                $courseCategory = preg_replace('!\s+!', '_', $courseCategory);
                $courseCategory = preg_replace("/[^א-תa-zA-Z0-9_\.]/", '', $courseCategory);
              //  $courseCategory = str_replace('א', '_', $courseCategory);

                $info = pathinfo($fileName);
                $fileName =  basename($fileName,'.'.$info['extension']);

                if (strlen($fileName) > 30)  $fileName = substr($fileName, 0, 30);
                $fileName = $fileName . '.' . $info['extension'];

                $fileName = urlencode($this->base64_url_encode($fileName));

/*
                $deptName = urlencode($this->base64_url_encode($deptName));
                $courseName = urlencode($this->base64_url_encode($courseName));
                $courseCategory = urlencode($this->base64_url_encode($courseCategory));
                $groupmembers =  urlencode($this->base64_url_encode($groupmembers));
                $facultyName = urlencode($this->base64_url_encode($facultyName));
*/

                $content = $file->get_content();

				//================================== PARAMS ADDED BY ORIGINALITY LTD =========================================
				if(!empty($orig_server->value) && !empty($orig_key->value)) {

                    $upload_result = $this->_do_curl_request($orig_server, $orig_key, $content, $fileName, $courseNum, $cmid, $courseID, $userID, $inst, $lectID, $courseCategory, $courseName, $senderIP, $facultyCode, $facultyName, $deptCode, $deptName, $check_file, $reserve2, $groupsize, $groupmembers, $idnumber, $assignNum); //, $file_identifier);

                    if ($upload_result) $this->_update_request_and_response_db_records($assignNum, $userID, $fileName); //, $file_identifier);
                    else $this->notify_customer_service_failed_file_upload($userID, $assignNum);

				}
			}
		}
	}
	return $result;
}

function notify_customer_service_failed_file_upload($userID, $assignNum){

    $assignmentName = $this->get_assignment_name($assignNum);
    $userName = $this->get_user_name($userID);

    $to      = 'customerservice@originality.co.il';
    $from = 'notify@'.ltrim($_SERVER['HTTP_HOST'], 'www.');
    $subject = 'Originality: Failed File Upload';
    $message = 'File upload failed from client domain: ' . $_SERVER['HTTP_HOST']. " for user $userID:$userName and assignment $assignNum:$assignmentName";
    $headers = "From: $from" . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    mail($to, $subject, $message, $headers);
}

function get_assignment_name($id){
    global $DB;
    $assignment =  $orig_key = $DB->get_record('assign', array('id'=>$id));
    return $assignment->name;
}

function get_user_name($id){
    global $DB;
    $user =  $orig_key = $DB->get_record('user', array('id'=>$id));
    return $user->firstname . ' ' . $user->lastname;
}

function file_extension($filename){
	$path_info = pathinfo($filename);
	return $path_info['extension'];
}

function base64_url_encode($input) {
	return strtr(base64_encode($input), '+/=', '-_,');
}

function originality_event_files_done($eventdata)
{
	global $DB;
	$result = true;
}

    /*
     *   if ($showcontent) { // If we should be handling in-line text.
                    $submission = $DB->get_record('assignsubmission_onlinetext', array('submission' => $eventdata['objectid']));
                    if (!empty($submission) && str_word_count($submission->onlinetext) > $wordcount) {
                        $content = trim(format_text($submission->onlinetext, $submission->onlineformat,
                            array('context' => $modulecontext)));
                        $file = urkund_create_temp_file($cmid, $eventdata['courseid'], $userid, $content);
                        urkund_queue_file($cmid, $userid, $file);
                    }
                }


function urkund_create_temp_file($cmid, $courseid, $userid, $filecontent) {
    global $CFG;
    if (!check_dir_exists($CFG->tempdir."/urkund", true, true)) {
        mkdir($CFG->tempdir."/urkund", 0700);
    }
    $filename = "content-" . $courseid . "-" . $cmid . "-" . $userid ."-". random_string(8).".htm";
    $filepath = $CFG->tempdir."/urkund/" . $filename;
    $fd = fopen($filepath, 'wb');   // Create if not exist, write binary.

    // Write html and body tags as it seems that Urkund doesn't works well without them.
    $content = plagiarism_urkund_format_temp_content($filecontent);

    fwrite($fd, $content);
    fclose($fd);

    return $filepath;
}

// Helper function used to add extra html around file contents.
function plagiarism_urkund_format_temp_content($content) {
    return '<html>' .
           '<head>' .
           '<meta charset="UTF-8">' .
           '</head>' .
           '<body>' .
           $content .
           '</body></html>';

}
*/


    function create_temp_file($cmid, $courseid, $userid, $filecontent) {
        global $CFG;
        if (!check_dir_exists($CFG->tempdir."/originality", true, true)) {
            mkdir($CFG->tempdir."/originality", 0700);
        }
        $filename = "content-" . $courseid . "-" . $cmid . "-" . $userid ."-". random_string(8).".htm";
        $filepath = $CFG->tempdir."/originality/" . $filename;
        $fd = fopen($filepath, 'wb');   // Create if not exist, write binary.

        $content = $filecontent;

        fwrite($fd, $content);
        fclose($fd);

        return $filepath;
    }




 function originality_event_onlinetext_submitted($eventdata){

     //error_log("in file upload");
     $result = true;
     global $DB,$CFG,$USER;

     $plagiarismvalues = $DB->get_records_menu('plagiarism_originality_conf', array('cm' =>  $eventdata['contextinstanceid']), '', 'id,cm');

     if (empty($plagiarismvalues)) {
         return $result;
     } else {
         list($orig_server, $orig_key) = $this->_get_server_and_key();

         list($courseNum, $cmid, $courseID, $userID, $inst, $lectID, $courseCategory, $courseName, $senderIP, $facultyCode, $facultyName, $deptCode, $deptName, $check_file, $reserve2, $groupsize, $groupmembers, $idnumber, $assignNum) = $this->_get_params_for_file_submission($eventdata);

         $fileName = 'onlinetext-'.$userID.'.txt';
         $content = strip_tags($eventdata['other']['content']);

      //   $file_identifier = 1;

                 //================================== PARAMS ADDED BY ORIGINALITY LTD =========================================
                 if(!empty($orig_server->value) && !empty($orig_key->value)) {

                     $this->_do_curl_request($orig_server, $orig_key, $content, $fileName, $courseNum, $cmid, $courseID, $userID, $inst, $lectID, $courseCategory, $courseName, $senderIP, $facultyCode, $facultyName, $deptCode, $deptName, $check_file, $reserve2, $groupsize, $groupmembers, $idnumber, $assignNum); //, $file_identifier);

                     $this->_update_request_and_response_db_records($assignNum, $userID, $fileName); //, $file_identifier);

                 }//if have orig server value
     }

}


    /*
     * Check the default setting for the school for whether to have the lecturer definitions for the course have
     * originality on as the default
     */
    private function default_assignment_settings_use_originality(){
        global $DB,$CFG,$USER;

        list($orig_server, $orig_key) = $this->_get_server_and_key();

        $url = "$orig_server->value/Api/$orig_key->value/status";

        $response = file_get_contents($url);

        $values = json_decode($response);

        if (isset($values->SendByDefault) && $values->SendByDefault==true)  return true;

        else return false;

    }


function originality_event_mod_created($eventdata)
{
	$result = true;
	return $result;
}

function originality_event_mod_updated($eventdata)
{
	$result = true;
	return $result;
}

function originality_event_mod_deleted($eventdata)
{
	$result = true;
	return $result;
}


}
?>