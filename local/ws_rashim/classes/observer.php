<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

function grade_update_112($updated_grade) {
	return <<<EOXML
<?xml version="1.0" encoding="utf-8" ?>
<PARAMS>
    <ZHT>$updated_grade->user_idnumber</ZHT>
    <BHN_KRS>$updated_grade->michlol_krs_bhn_krs</BHN_KRS>
    <BHN_SMS>$updated_grade->michlol_krs_bhn_sms</BHN_SMS>
    <BHN_SID>$updated_grade->michlol_krs_bhn_sid</BHN_SID>
    <ZIN>$updated_grade->finalgrade</ZIN>
</PARAMS>
EOXML;
}

function grade_update_113($updated_grade) {
	return <<<EOXML
<?xml version="1.0" encoding="utf-8" ?>
<PARAMS>
	<ZHT>$updated_grade->user_idnumber</ZHT>
	<TASKID>$updated_grade->course_idnumber</TASKID>
	<ZIN>$updated_grade->finalgrade</ZIN>
</PARAMS>
EOXML;
}

class local_ws_rashim_observer {

	protected static function send_grade($course_id, $mod, $mod_id, $user_idnumber, $course_idnumber, $finalgrade) {
		global $DB;
		
		$config = get_config ( 'local_ws_rashim' );
		
		if (empty ( $config->api_url )) {
			return;
		}
		
		$send112 = true;
		
		$client = new SoapClient ( $config->api_url . '/MichlolApi.asmx?WSDL', array (
				'exceptions' => true,
				'trace' => true,
				'soap_version' => SOAP_1_2,
				'encoding' => 'UTF-8',
				'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
				'cache_wsdl' => WSDL_CACHE_NONE 
		) );
		
		$updated_grade = $DB->get_record ( 'matalot', array (
				'course_id' => $course_id,
				'moodle_type' => $mod,
				'moodle_id' => $mod_id 
		) );
		
		if (! $updated_grade) {
			$updated_grade = new stdClass ();
			
			$send112 = false;
		}
		
		$updated_grade->finalgrade = $finalgrade;
		$updated_grade->user_idnumber = $user_idnumber;
		$updated_grade->course_idnumber = $course_idnumber;
		
		if ($send112) {
			// call 112
			$param = array (
					'P_RequestParams' => array (
							'RequestID' => 112,
							'InputData' => grade_update_112 ( $updated_grade ) 
					),
					'Authenticator' => array (
							'UserName' => $config->api_user,
							'Password' => $config->api_psw 
					) 
			);
		} else {
			// call 113
			$param = array (
					'P_RequestParams' => array (
							'RequestID' => 113,
							'InputData' => grade_update_113 ( $updated_grade ) 
					),
					'Authenticator' => array (
							'UserName' => $config->api_user,
							'Password' => $config->api_psw 
					) 
			);
		}
		
		$result = $client->ProcessRequest ( $param );
	}

	public static function grades_export_handler(\core\event\user_graded $eventdata) {
		global $DB;
		
		$grade = $eventdata->get_grade ();
		$grade_item = $grade->grade_item;
		
		$user = $DB->get_record ( 'user', array (
				'id' => $grade->userid 
		) );
		
		$course = $DB->get_record ( 'course', array (
				'id' => $grade_item->courseid 
		) );
		
		local_ws_rashim_observer::send_grade ( $course->id, $grade_item->itemmodule, $grade_item->iteminstance, $user->idnumber, $course->idnumber, $grade->finalgrade );
		
		return true;
	}

	public static function course_deleted_handler(\core\event\course_deleted $eventdata) {
		global $DB;
		
		if (! isset ( $eventdata->other ['nodelete'] ) || $eventdata->other ['nodelete'] == 0) {
			$conditions = array (
					"course_id" => $eventdata->courseid 
			);
			$DB->delete_records ( 'matalot', $conditions );
			$DB->delete_records ( 'meetings', $conditions );
		}
		
		return true;
	}

	public static function course_module_deleted_handler(\core\event\course_module_deleted $eventdata) {
		global $DB;
		
		$conditions = array (
				"course_id" => $eventdata->courseid,
				"moodle_type" => $eventdata->other ['modulename'],
				"moodle_id" => $eventdata->other ['instanceid'] 
		);
		$DB->delete_records ( 'matalot', $conditions );
		
		return true;
	}
}

?>