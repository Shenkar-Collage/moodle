<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

require_once (__DIR__ . '../../../course/lib.php');
require_once (__DIR__ . '../../../course/format/lib.php');
require_once (__DIR__ . '../../../course/modlib.php');
require_once (__DIR__ . '../../../user/lib.php');
require_once (__DIR__ . '../../../user/profile/lib.php');
require_once (__DIR__ . '../../../group/lib.php');
require_once (__DIR__ . '../../../mod/assign/lib.php');
require_once (__DIR__ . '../../../mod/quiz/lib.php');
require_once (__DIR__ . '../../../mod/url/lib.php');
require_once (__DIR__ . '../../../lib/coursecatlib.php');
require_once (__DIR__ . '../../../lib/moodlelib.php');

class error_msg {
	static $E5001 = 'Could not get validated client session.';
	// static $E5002 = '';
	// static $E5003 = '';
	static $E5004 = 'Failed to close session.';
	static $E5005 = 'Could not find session.';
	static $E5006 = 'Access is denied.';
	static $E5007 = 'Missing required fields.';
	static $E5008 = 'Invalid username and / or password.';
	static $E5009 = 'User cannot open more than one sessions.';
	static $E5010 = 'Could not add user.';
	static $E5011 = 'User does not exists.';
	// static $E5012 = '';
	// static $E5013 = '';
	static $E5014 = 'Course does not exists.';
	// static $E5015 = '';
	// static $E5016 = '';
	static $E5017 = 'Parent category does not exists.';
	static $E5018 = 'Category does not exists.';
	static $E5019 = 'Could not delete course.';
	static $E5020 = 'Could not add category.';
	// static $E5021 = '';
	// static $E5022 = '';
	// static $E5023 = '';
	// static $E5024 = '';
	// static $E5025 = '';
	static $E5026 = 'Could not add/update syllabus.';
	static $E5027 = 'Could not create group.';
	static $E5028 = 'Could not create grouping.';
	static $E5029 = 'Could not add group to grouping.';
	static $E5030 = 'Could not add exam module.';
	// static $E5031 = '';
	static $E5032 = 'Exam does not exists.';
	static $E5033 = 'Module does not exists.';
	// static $E5034 = '';
	static $E5035 = 'Could not create course section.';
	static $E5036 = 'Course section does not exists.';
	// static $E5037 = '';
	// static $E5038 = '';
	// static $E5039 = '';
	// static $E5040 = '';
	static $E5041 = 'Meeting does not exists.';
	static $E5042 = 'Error reading XML.';
	static $E5043 = 'Role does not exists.';
	static $E5044 = 'Can not add user to group.';
}

class server {
	protected $DB;
	protected $CFG;
	protected $config;
	protected $local;
	protected $admin;
	protected $sessiontimeout = 1800;

	public function __construct($local = false) {
		global $DB;
		global $CFG;

		$this->DB = $DB;
		$this->CFG = $CFG;
		$this->config = get_config ( 'local_ws_rashim' );
		$this->local = $local;
	}

	public function server($local = false) {
		self::__construct ( $local );
	}

	protected function error($err, $msg = '') {
		if ($msg == '') {
			$errno = 'E' . $err;
			$msg = error_msg::$$errno;
		}

		if ($this->local) {
			$loc_err->err = $err;
			$loc_err->msg = $msg;

			return $loc_err;
		} else {
			throw new SoapFault ( 'Server', "{$err}~{$msg}" );
		}
	}

	protected function valid_login($session_key, $admin_name, $admin_psw) {
		return $this->admin_login ( $admin_name, $admin_psw );
	}

	protected function admin_login($admin_name, $admin_psw) {
		$conditions = array (
				'username' => $admin_name
		);
		if (! $admin = $this->DB->get_record ( 'user', $conditions )) {
			return $this->error ( 5008 );
		}

		// we do not use this function in the first place
		// because the functon creates the user if does not exists!
		$admin = authenticate_user_login ( $admin_name, $admin_psw );

		complete_user_login ( $admin );

		if (($admin === false) || ($admin && $admin->id == 0)) {
			return $this->error ( 5008 );
		} else {
			if (! is_siteadmin ( $admin->id )) {
				return $this->error ( 5006 );
			} else {
				$event = \local_ws_rashim\event\user_loggedin::create ( array (
						'userid' => $admin->id,
						'objectid' => $admin->id,
						'other' => array (
								'username' => $admin->username
						)
				) );

				$event->trigger ();

				$this->admin = $admin;

				return true;
			}
		}
	}

	protected function get_course_condition($course_id) {
		if (isset ( $this->config->michlol_useid ) && $this->config->michlol_useid) {
			return (array (
					'id' => $course_id
			));
		} else {
			return (array (
					'idnumber' => $course_id
			));
		}
	}

	protected function user_unenroll($course, $user) {
		$auth = 'manual';

		$manualenrol = enrol_get_plugin ( $auth );
		$enrolinstance = $this->DB->get_record ( 'enrol', array (
				'courseid' => $course,
				'status' => ENROL_INSTANCE_ENABLED,
				'enrol' => $auth
		), '*', MUST_EXIST );
		$manualenrol->unenrol_user ( $enrolinstance, $user );

		$event = \local_ws_rashim\event\user_enrolment_deleted::create ( array (
				'userid' => $this->admin->id,
				'courseid' => $course,
				'context' => context_course::instance ( $course ),
				'relateduserid' => $user,
				'objectid' => $user,
				'other' => array (
						'userenrolment' => '',
						'enrol' => $auth
				)
		) );

		$event->trigger ();
	}

	protected function user_enroll($course, $user, $role, $role_start = 0, $role_end = 0) {
		$auth = 'manual';

        if ($role_start  == NULL)
            $role_start = time();

        if ($role_end  == NULL)
            $role_end = 0;

		$manualenrol = enrol_get_plugin ( $auth );
		$enrolinstance = $this->DB->get_record ( 'enrol', array (
				'courseid' => $course,
				'status' => ENROL_INSTANCE_ENABLED,
				'enrol' => $auth
		), '*', MUST_EXIST );
		$manualenrol->enrol_user ( $enrolinstance, $user, $role, $role_start, $role_end );

		$event = \local_ws_rashim\event\user_enrolment_created::create ( array (
				'userid' => $this->admin->id,
				'courseid' => $course,
				'relateduserid' => $user,
				'objectid' => $user,
				'context' => context_course::instance ( $course ),
				'other' => array (
						'enrol' => $auth
				)
		) );

		$event->trigger ();
	}

	protected function add_syllabus_url($course, $url) {
		$conditions = array (
				'course' => $course->id,
				'name' => 'סילבוס'
		);
		if (! $syl = $this->DB->get_record ( 'url', $conditions )) {
			$syl = new stdClass ();
			$syl->course = $course->id;
			$syl->name = 'סילבוס';
			$syl->intro = 'קישור לסילבוס במכלול';
			$syl->introformat = 0;
			$syl->externalurl = $url;
			$syl->display = 0;
			$syl->timemodified = time ();

			$syl->modulename = 'url';
			$syl->module = $this->DB->get_field ( 'modules', 'id', array (
					'name' => 'url'
			), MUST_EXIST );
			$syl->section = 0;
			$syl->visible = true;

			$module = add_moduleinfo ( $syl, $course );
			$syl->id = $module->instance;

			if (empty ( $syl->id )) {
				return $this->error ( 5026 );
			}

			$event = \local_ws_rashim\event\course_module_created::create ( array (
					'userid' => $this->admin->id,
					'context' => context_course::instance ( $course->id ),
					'objectid' => $module->id,
					'other' => array (
							'modulename' => 'url',
							'instanceid' => $module->instance,
							'name' => $module->name
					)
			) );

			$event->trigger ();
		} else {
			$conditions = array (
					'course' => $course->id,
					'instance' => $syl->id
			);
			$cm = $this->DB->get_record ( 'course_modules', $conditions );

			if (empty ( $url )) {
				course_delete_module ( $cm->id );

				$event = \local_ws_rashim\event\course_module_deleted::create ( array (
						'userid' => $this->admin->id,
						'courseid' => $cm->course,
						'context' => context_course::instance ( $cm->course ),
						'objectid' => $cm->id,
						'other' => array (
								'modulename' => 'url',
								'instanceid' => $cm->instance
						)
				) );

				$event->trigger ();
			} else {
				$cm->modname = 'url';

				$syl->modulename = 'url';
				$syl->coursemodule = $cm->id;
				$syl->externalurl = $url;
				$syl->introeditor ['text'] = 'קישור לסילבוס במכלול';
				$syl->introeditor ['format'] = 0;
				$syl->visible = true;

				update_moduleinfo ( $cm, $syl, $course );

				$event = \local_ws_rashim\event\course_module_updated::create ( array (
						'userid' => $this->admin->id,
						'context' => context_course::instance ( $cm->course ),
						'objectid' => $cm->id,
						'other' => array (
								'modulename' => 'url',
								'instanceid' => $cm->instance,
								'name' => $cm->name
						)
				) );

				$event->trigger ();
			}
		}
	}

	protected function handle_course_section($course_id, $section_num, $section_name = null, $visible = 1) {
		course_create_sections_if_missing ( $course_id, $section_num );

		$conditions = array (
				'course' => $course_id,
				'section' => $section_num
		);
		$section = $this->DB->get_record ( 'course_sections', $conditions );

		if (empty ( $section->id )) {
			return $this->error ( 5035 );
		}

		// pre 3.1 hack
		if (function_exists ( 'course_update_section' )) {
			$new = array (
					'name' => $section_name,
					'visible' => $visible
			);

			course_update_section ( $course_id, $section, $new );
		} else {
			$section->name = $section_name;
			$section->visible = $visible;

			$this->DB->update_record ( 'course_sections', $section );
			rebuild_course_cache ( $course_id, true );
		}

		$event = \local_ws_rashim\event\course_section_updated::create ( array (
				'userid' => $this->admin->id,
				'courseid' => $course_id,
				'context' => context_course::instance ( $course_id ),
				'objectid' => $section->id,
				'other' => array (
						'sectionnum' => $section->section
				)
		) );

		$event->trigger ();

		return ($section->id);
	}

	protected function user_extra($user, $extra) {
		$save = false;

		$arr1 = explode ( ';', $extra );

		$profile = profile_user_record ( null, false );

		foreach ( $arr1 as $key1 => $value1 ) {
			$arr2 = explode ( '=', $value1 );

			$field = $arr2 [0];
			$value = $arr2 [1];

			if (! empty ( $field )) {
				if (property_exists ( $profile, $field )) {
					$user->{'profile_field_' . $field} = $value;

					$save = true;
				} else {
					$event = \local_ws_rashim\event\user_profile_field_missing::create ( array (
							'userid' => $this->admin->id,
							'objectid' => $user->id,
							'relateduserid' => $user->id,
							'context' => context_user::instance ( $user->id ),
							'other' => array (
									'field' => $field
							)
					) );

					$event->trigger ();
				}
			}
		}

		if ($save) {
			profile_save_data ( $user );
		}
	}

	protected function update_assignment($course, $bhn, $bhn_shm, $moodle_type, $start, $end) {
		if ($moodle_type == 'quiz') {
			$bhn->timeopen = $start == 0 ? 0 : $start;
			$bhn->timeclose = $end == 0 ? 0 : $end;

			$bhn->quizpassword = '';
		} else if ($moodle_type == 'assign') {
			if ($bhn->name == $bhn->description) {
				$bhn->description = $bhn_shm;
			}

			$bhn->duedate = $start == 0 ? 0 : $start;
			$bhn->cutoffdate = $end == 0 ? 0 : $end;

			$plugins = $this->DB->get_records ( 'assign_plugin_config', array (
					'assignment' => $bhn->id
			) );

			foreach ( $plugins as $key => $value ) {
				if (isset ( $value->value )) {
					if ($value->plugin == 'file') {
						if ($value->name == 'maxfilesubmissions')
							$value->name = 'maxfiles';
						if ($value->name == 'maxsubmissionsizebytes')
							$value->name = 'maxsizebytes';
					}

					$bhn->{$value->subtype . '_' . $value->plugin . '_' . $value->name } = $value->value;
				}
			}
		}

		$conditions = array (
				'course' => $course->id,
				'instance' => $bhn->id
		);
		$cm = $this->DB->get_record ( 'course_modules', $conditions );

		$cm->modname = $moodle_type;

		$bhn->name = $bhn_shm;

		$bhn->modulename = $moodle_type;
		$bhn->coursemodule = $cm->id;
		$bhn->introeditor ['text'] = $bhn->intro;
		$bhn->introeditor ['format'] = 0;
		$bhn->visible = $cm->visible;

		$rv = update_moduleinfo ( $cm, $bhn, $course );
		$moduleinfo = $rv [1];

		$event = \local_ws_rashim\event\course_module_updated::create ( array (
				'userid' => $this->admin->id,
				'courseid' => $course->id,
				'context' => context_course::instance ( $course->id ),
				'objectid' => $moduleinfo->id,
				'other' => array (
						'modulename' => $bhn->modulename,
						'instanceid' => $moduleinfo->instance,
						'name' => $bhn->name
				)
		) );

		$event->trigger ();

		return $moduleinfo;
	}

	protected function add_assignment($course_id, $section_num, $bhn_shm, $michlol_krs, $michlol_sms, $michlol_sid, $moodle_type, $start, $end) {
		// quiz - b
		// assignment/online - m
		// assignment/offline - t
		// assignment/upload - k
		$course = $this->DB->get_record ( 'course', array (
				'id' => $course_id
		) );

		$conditions = array (
				'course_id' => $course_id,
				'michlol_krs_bhn_krs' => $michlol_krs,
				'michlol_krs_bhn_sms' => $michlol_sms,
				'michlol_krs_bhn_sid' => $michlol_sid
		);
		if (! $matala = $this->DB->get_record ( 'matalot', $conditions )) {
			$bhn = new stdClass ();

			if ($moodle_type == 'b') {
				$bhn->modulename = 'quiz';

				$bhn->timeopen = $start == 0 ? 0 : $start;
				$bhn->timeclose = $end == 0 ? 0 : $end;

				$bhn->quizpassword = '';
				$bhn->preferredbehaviour = 'deferredfeedback';
				$bhn->shuffleanswers = true;
			} else if (($moodle_type == 'm') || ($moodle_type == 't') || ($moodle_type == 'k')) {
				$bhn->modulename = 'assign';

				$bhn->duedate = $start == 0 ? 0 : $start;
				$bhn->cutoffdate = $end == 0 ? 0 : $end;

				if ($moodle_type == 'm') {
					$bhn->assignsubmission_onlinetext_enabled = true;
				}

				if ($moodle_type == 'k') {
					$bhn->assignsubmission_file_maxfiles = 3;
					$bhn->assignsubmission_file_enabled = true;
				}

				$bhn->submissiondrafts = 0;
				$bhn->requiresubmissionstatement = 0;
				$bhn->sendnotifications = 0;
				$bhn->sendlatenotifications = 0;
				$bhn->allowsubmissionsfromdate = 0;
				$bhn->teamsubmission = 0;
				$bhn->requireallteammemberssubmit = 0;
				$bhn->blindmarking = 0;
				$bhn->markingworkflow = 0;
			}

			$bhn->course = $course_id;
			$bhn->name = $bhn_shm;
			$bhn->intro = 'מטלה נוצרה ממכלול';
			$bhn->introformat = 0;

			$bhn->module = $this->DB->get_field ( 'modules', 'id', array (
					'name' => $bhn->modulename
			), MUST_EXIST );
			;
			$bhn->visible = 0;
			$bhn->section = $section_num;
			$bhn->grade = 100;

			$moduleinfo = add_moduleinfo ( $bhn, $course );

			if (empty ( $moduleinfo->instance )) {
				return $this->error ( 5030 );
			}

			$matala = new stdClass ();
			$matala->course_id = $course_id;
			$matala->michlol_krs_bhn_krs = $michlol_krs;
			$matala->michlol_krs_bhn_sms = $michlol_sms;
			$matala->michlol_krs_bhn_sid = $michlol_sid;
			$matala->moodle_type = $bhn->modulename;
			$matala->moodle_id = $moduleinfo->instance;

			$this->DB->insert_record ( 'matalot', $matala );

			$event = \local_ws_rashim\event\course_module_created::create ( array (
					'userid' => $this->admin->id,
					'courseid' => $matala->course_id,
					'context' => context_course::instance ( $matala->course_id ),
					'objectid' => $moduleinfo->id,
					'other' => array (
							'modulename' => $bhn->modulename,
							'instanceid' => $moduleinfo->instance,
							'name' => $bhn->name
					)
			) );

			$event->trigger ();

			return $moduleinfo;
		} else {
			$conditions = array (
					'id' => $matala->moodle_id
			);
			if ($bhn = $this->DB->get_record ( $matala->moodle_type, $conditions )) {
				return $this->update_assignment ( $course, $bhn, $bhn_shm, $matala->moodle_type, $start, $end );
			}
		}
	}

	protected function add_assignment_link($course_id, $section_num, $bhn_shm, $michlol_krs, $michlol_sms, $michlol_sid, $moodle_type, $start, $end) {
		// quiz - b
		// assignment/online - m
		// assignment/offline - t
		// assignment/upload - k
		$course = $this->DB->get_record ( 'course', array (
				'id' => $course_id
		) );

		if ($moodle_type == 'b') {
			$type = 'quiz';
		} else if (($moodle_type == 'm') || ($moodle_type == 't') || ($moodle_type == 'k')) {
			$type = 'assign';
		}

		$conditions = array (
				'course_id' => $course_id,
				'michlol_krs_bhn_krs' => $michlol_krs,
				'michlol_krs_bhn_sms' => $michlol_sms,
				'michlol_krs_bhn_sid' => $michlol_sid
		);
		if (! $matala = $this->DB->get_record ( 'matalot', $conditions )) {
			$conditions = array (
					'course' => $course_id,
					'section' => $section_num
			);
			if (! $section = $this->DB->get_record ( 'course_sections', $conditions )) {
				return $this->error ( 5036 );
			}

			$conditions = array (
					'course' => $course_id,
					'section' => $section->id,
					'module' => $this->DB->get_field ( 'modules', 'id', array (
							'name' => $type
					), MUST_EXIST )
			);
			if (! $module = $this->DB->get_record ( 'course_modules', $conditions )) {
				return $this->error ( 5033 );
			}

			$conditions = array (
					'course' => $course_id,
					'id' => $module->instance
			);
			$bhn = $this->DB->get_record ( $type, $conditions );

			if (empty ( $bhn->id )) {
				return $this->error ( 5030 );
			}

			$this->update_assignment ( $course, $bhn, $bhn_shm, $type, $start, $end );

			$matala->course_id = $course_id;
			$matala->michlol_krs_bhn_krs = $michlol_krs;
			$matala->michlol_krs_bhn_sms = $michlol_sms;
			$matala->michlol_krs_bhn_sid = $michlol_sid;
			$matala->moodle_type = $type;
			$matala->moodle_id = $bhn->id;

			$this->DB->insert_record ( 'matalot', $matala );
		} else {
			$conditions = array (
					'id' => $matala->moodle_id
			);
			$bhn = $this->DB->get_record ( $matala->moodle_type, $conditions );

			if (empty ( $bhn->id )) {
				return $this->error ( 5030 );
			}

			$this->update_assignment ( $course, $bhn, $bhn_shm, $matala->moodle_type, $start, $end );
		}
	}

	protected function xml2meetings($course_id, $xml) {
		$section_num = 1;

		foreach ( $xml->MEETINGS->children () as $meeting ) {
			if (! isset ( $meeting->WEEK )) {
				$meeting->WEEK = - 1;
			}

			if (! isset ( $meeting->DAY )) {
				$meeting->DAY = - 1;
			}

			if (! isset ( $meeting->MEETING_DATE )) {
				$meeting->MEETING_DATE = - 1;
			}

			$conditions = array (
					'snl' => ( string ) $xml->DATA->SNL,
					'shl' => ( integer ) $xml->DATA->SHL,
					'hit' => ( integer ) $xml->DATA->MIS,
					'krs' => ( integer ) $meeting->MIS,
					'mfgs' => ( integer ) $meeting->SID
			);
			if (! $meeting_old = $this->DB->get_record ( 'meetings', $conditions )) {
				$section = $this->handle_course_section ( $course_id, $section_num, ( string ) $meeting->SHM );

				// write record to the help table anable sorting
				$meeting_new->snl = ( string ) $xml->DATA->SNL;
				$meeting_new->shl = ( integer ) $xml->DATA->SHL;
				$meeting_new->hit = ( integer ) $xml->DATA->MIS;
				$meeting_new->krs = ( integer ) $meeting->MIS;
				$meeting_new->mfgs = ( integer ) $meeting->SID;

				$meeting_new->course_id = $course_id;
				$meeting_new->section_num = $section_num;

				$meeting_new->subject = ( string ) $meeting->SUB;
				$meeting_new->week = ( integer ) $meeting->WEEK;
				$meeting_new->day = ( integer ) $meeting->DAY;
				$meeting_new->meeting_date = ( integer ) $meeting->MEETING_DATE;
				$meeting_new->hour_begin = ( integer ) $meeting->BEGIN;
				$meeting_new->hour_end = ( integer ) $meeting->END;

				$this->DB->insert_record ( 'meetings', $meeting_new );

				$section_num ++;

				$event = \local_ws_rashim\event\meeting_created::create ( array (
						'userid' => $this->admin->id,
						'objectid' => $section,
						'courseid' => $meeting_new->course_id,
						'context' => context_course::instance ( $meeting_new->course_id ),
						'other' => array (
								'sectionnum' => $meeting_new->section_num
						)
				) );

				$event->trigger ();
			} else {
				$meeting_old->subject = ( string ) $meeting->SUB;
				$meeting_old->week = ( integer ) $meeting->WEEK;
				$meeting_old->day = ( integer ) $meeting->DAY;
				$meeting_old->meeting_date = ( integer ) $meeting->MEETING_DATE;
				$meeting_old->hour_begin = ( integer ) $meeting->BEGIN;
				$meeting_old->hour_end = ( integer ) $meeting->END;

				$this->DB->update_record ( 'meetings', $meeting_old );

				$section_conditions = array (
						'course' => $meeting_old->course_id,
						'section' => $meeting_old->section_num
				);

				if (! $section = $this->DB->get_record ( 'course_sections', $section_conditions )) {
					return $this->error ( 5036 );
				}

				$this->handle_course_section ( $meeting_old->course_id, $meeting_old->section_num, ( string ) $meeting->SHM );

				$event = \local_ws_rashim\event\meeting_updated::create ( array (
						'userid' => $this->admin->id,
						'objectid' => $section->id,
						'courseid' => $meeting_old->course_id,
						'context' => context_course::instance ( $meeting_old->course_id ),
						'other' => array (
								'sectionnum' => $meeting_old->section_num
						)
				) );

				$event->trigger ();

				$section_num = $meeting_old->section_num + 1;
			}
		}

		course_get_format ( $course_id )->update_course_format_options ( array (
				'numsections' => $section_num - 1
		) );
	}

	protected function xml2assignments($course_id, $xml) {
		foreach ( $xml->ASSIGNMENTS->children () as $assignment ) {
			if (( integer ) $assignment->ORG_KRS != - 1) {
				$this->xml2assignments_modula ( $course_id, $assignment );
			} else {
				$conditions = array (
						'snl' => ( string ) $xml->DATA->SNL,
						'shl' => ( integer ) $xml->DATA->SHL,
						'hit' => ( integer ) $xml->DATA->MIS,
						'krs' => ( integer ) $assignment->MIS,
						'mfgs' => ( integer ) $assignment->SID
				);
				if (! $meeting = $this->DB->get_record ( 'meetings', $conditions )) {
					return $this->error ( 5041 );
				}

				if ($meeting->meeting_date == - 1) {
					$this->add_assignment ( $course_id, $meeting->section_num, ( string ) $assignment->BHN_SHM, ( integer ) $assignment->BHN_KRS, ( string ) $assignment->BHN_SMS, ( integer ) $assignment->BHN_SID, ( string ) $assignment->BHN_MOODLETYPE, 0, 0 );
				} else {
					$this->add_assignment ( $course_id, $meeting->section_num, ( string ) $assignment->BHN_SHM, ( integer ) $assignment->BHN_KRS, ( string ) $assignment->BHN_SMS, ( integer ) $assignment->BHN_SID, ( string ) $assignment->BHN_MOODLETYPE, $meeting->meeting_date + $meeting->hour_begin, $meeting->meeting_date + $meeting->hour_end );
				}
			}
		}
	}

	protected function xml2assignments_link($course_id, $xml) {
		foreach ( $xml->ASSIGNMENTS->children () as $assignment ) {
			if (( integer ) $assignment->ORG_KRS != - 1) {
				$this->xml2assignments_modula ( $course_id, $assignment );
			} else {
				$conditions = array (
						'snl' => ( string ) $xml->DATA->SNL,
						'shl' => ( integer ) $xml->DATA->SHL,
						'hit' => ( integer ) $xml->DATA->MIS,
						'krs' => ( integer ) $assignment->MIS,
						'mfgs' => ( integer ) $assignment->SID
				);
				if (! $meeting = $this->DB->get_record ( 'meetings', $conditions )) {
					return $this->error ( 5041 );
				}

				if ($meeting->meeting_date == - 1) {
					$this->add_assignment_link ( $course_id, $meeting->section_num, ( string ) $assignment->BHN_SHM, ( integer ) $assignment->BHN_KRS, ( string ) $assignment->BHN_SMS, ( integer ) $assignment->BHN_SID, ( string ) $assignment->BHN_MOODLETYPE, 0, 0 );
				} else {
					$this->add_assignment_link ( $course_id, $meeting->section_num, ( string ) $assignment->BHN_SHM, ( integer ) $assignment->BHN_KRS, ( string ) $assignment->BHN_SMS, ( integer ) $assignment->BHN_SID, ( string ) $assignment->BHN_MOODLETYPE, $meeting->meeting_date + $meeting->hour_begin, $meeting->meeting_date + $meeting->hour_end );
				}
			}
		}
	}

	protected function xml2assignments_modula($course_id, $assignment) {
		$conditions = array (
				'krs' => ( integer ) $assignment->MIS,
				'mfgs' => ( integer ) $assignment->SID,
				'course_id' => $course_id
		);
		if (! $mfgs = $this->DB->get_record ( 'meetings', $conditions )) {
			return $this->error ( 5041 );
		} else {
			$conditions = array (
					'course' => $course_id,
					'section' => $mfgs->section_num
			);
			if (! $dest_sec = $this->DB->get_record ( 'course_sections', $conditions )) {
				return $this->error ( 5036 );
			} else {
				$conditions = array (
						'michlol_krs_bhn_krs' => ( integer ) $assignment->ORG_KRS,
						'michlol_krs_bhn_sms' => ( string ) $assignment->ORG_SMS,
						'michlol_krs_bhn_sid' => ( integer ) $assignment->ORG_SID
				);
				if (! $bhn = $this->DB->get_record ( 'matalot', $conditions )) {
					return $this->error ( 5032 );
				} else {
					$conditions = array (
							'course' => $bhn->course_id,
							'instance' => $bhn->moodle_id
					);
					if (! $module = $this->DB->get_record ( 'course_modules', $conditions )) {
						return $this->error ( 5033 );
					} else {
						$this->copy_section ( $module->section, $dest_sec->id );

						$conditions = array (
								'course' => $course_id,
								'section' => $dest_sec->id
						);
						if (! $module = $this->DB->get_record ( 'course_modules', $conditions )) {
							return $this->error ( 5033 );
						} else {
							$this->add_assignment_link ( $course_id, $mfgs->section_num, ( string ) $assignment->BHN_SHM, ( integer ) $assignment->BHN_KRS, ( string ) $assignment->BHN_SMS, ( integer ) $assignment->BHN_SID, ( string ) $assignment->BHN_MOODLETYPE );
						}
					}
				}
			}
		}
	}

	protected function add_user($user_id, $user_name, $user_psw, $user_firstname, $user_lastname, $user_email, $user_phone1, $user_phone2, $user_address, $user_lang, $user_extra) {
		$conditions = array (
				'idnumber' => $user_id
		);
		if (! $user = $this->DB->get_record ( 'user', $conditions )) {
			if (! isset ( $user_name ) || ! isset ( $user_psw ) || ! isset ( $user_id ) || ! isset ( $user_firstname ) || ! isset ( $user_lastname )) {
				return $this->error ( 5007 );
			}

			$user->confirmed = true;
			$user->mnethostid = $this->CFG->mnet_localhost_id;

			if (isset ( $user_lang )) {
				$user->lang = $user_lang;
			}

			$user->password = $user_psw;

			$user->idnumber = $user_id;
			$user->username = $user_name;
			$user->firstname = $user_firstname;
			$user->lastname = $user_lastname;
			$user->email = isset ( $user_email ) ? $user_email : '';
			$user->phone1 = isset ( $user_phone1 ) ? $user_phone1 : '';
			$user->phone2 = isset ( $user_phone2 ) ? $user_phone2 : '';
			$user->address = $user_address;

			if (isset ( $this->config->michlolauth )) {
				$user->auth = $this->config->michlolauth;
			}

			if (isset ( $this->config->def_city )) {
				$user->city = $this->config->def_city;
			}

			if (isset ( $this->config->def_country )) {
				$user->country = $this->config->def_country;
			}

			$user->id = user_create_user ( $user );

			if (empty ( $user->id )) {
				return $this->error ( 5010 );
			}

			$event = \local_ws_rashim\event\user_created::create ( array (
					'userid' => $this->admin->id,
					'objectid' => $user->id,
					'context' => context_user::instance ( $user->id ),
					'relateduserid' => $user->id
			) );

			$event->trigger ();
		} else {
			if (! isset ( $user_id )) {
				return $this->error ( 5007 );
			}

			$user->password = $user_psw;

			$user->username = $user_name;
			$user->firstname = $user_firstname;
			$user->lastname = $user_lastname;
			$user->email = isset ( $user_email ) ? $user_email : '';
			$user->phone1 = isset ( $user_phone1 ) ? $user_phone1 : '';
			$user->phone2 = isset ( $user_phone2 ) ? $user_phone2 : '';
			$user->address = $user_address;

			user_update_user ( $user );

			$event = \local_ws_rashim\event\user_updated::create ( array (
					'userid' => $this->admin->id,
					'objectid' => $user->id,
					'context' => context_user::instance ( $user->id ),
					'relateduserid' => $user->id
			) );

			$event->trigger ();
		}

		$this->user_extra ( $user, $user_extra );

		return $user;
	}

	protected function category_add($category_parent, $category_code, $category_name) {
		$category->visible = true;
		$category->timemodified = time ();
		$category->name = $category_name;
		$category->parent = $category_parent;
		$category->idnumber = $category_code;

		$coursecat = coursecat::create ( $category );

		$category->id = $coursecat->id;

		if (empty ( $category->id )) {
			return $this->error ( 5020 );
		} else {
			$event = \local_ws_rashim\event\category_created::create ( array (
					'userid' => $this->admin->id,
					'objectid' => $category->id
			) );

			$event->trigger ();

			return $category->id;
		}
	}

	protected function category_tree($category_snlcode, $category_snlname, $category_shlcode, $category_shlname, $category_mslcode, $category_mslname) {
		$conditions = array (
				'idnumber' => $category_snlcode
		);
		if (! ($category_snl = $this->DB->get_record ( 'course_categories', $conditions ))) {
			$conditions = array (
					'name' => $category_snlname
			);
			$category_snl = $this->DB->get_record ( 'course_categories', $conditions );
		}

		if ($category_snl != null) {
			$conditions = array (
					'idnumber' => $category_shlcode
			);
			if (! ($category_shl = $this->DB->get_record ( 'course_categories', $conditions ))) {
				$conditions = array (
						'name' => $category_shlname,
						'parent' => $category_snl->id
				);
				$category_shl = $this->DB->get_record ( 'course_categories', $conditions );
			}

			if ($category_shl != null) {
				$conditions = array (
						'idnumber' => $category_mslcode
				);
				if (! ($category_msl = $this->DB->get_record ( 'course_categories', $conditions ))) {
					$conditions = array (
							'name' => $category_mslname,
							'parent' => $category_shl->id
					);
					$category_msl = $this->DB->get_record ( 'course_categories', $conditions );
				}
			}
		}

		if ($category_msl == null) {
			if ($category_shl == null) {
				if ($category_snl == null) {
					$category_snl->id = $this->category_add ( 0, $category_snlcode, $category_snlname );
				}

				$category_shl->id = $this->category_add ( $category_snl->id, $category_shlcode, $category_shlname );
			}

			if ($category_mslcode != - 1) {
				$category_msl->id = $this->category_add ( $category_shl->id, $category_mslcode, $category_mslname );
			}
		}

		if ($category_mslcode != - 1) {
			return $category_msl->id;
		} else {
			return $category_shl->id;
		}
	}

	protected function copy_course_section($src, $dest) {
		$conditions = array (
				'course_id' => $src
		);
		$tikyesod = $this->DB->get_records ( 'meetings', $conditions, 'krs, mfgs' );

		$conditions = array (
				'course_id' => $dest
		);
		$machzor = $this->DB->get_records ( 'meetings', $conditions, 'krs, mfgs' );

		foreach ( $tikyesod as $t_mfgs ) {
			foreach ( $machzor as $m_mfgs ) {
				if (($m_mfgs->krs == $t_mfgs->krs) && ($m_mfgs->mfgs == $t_mfgs->mfgs)) {
					$conditions = array (
							'course' => $t_mfgs->course_id,
							'section' => $t_mfgs->section_num
					);
					$src_sec = $this->DB->get_record ( 'course_sections', $conditions );

					$conditions = array (
							'course' => $m_mfgs->course_id,
							'section' => $m_mfgs->section_num
					);
					$dest_sec = $this->DB->get_record ( 'course_sections', $conditions );

					$this->copy_section ( $src_sec->id, $dest_sec->id );
				}
			}
		}
	}

	protected function copy_course_section_tik($xml) {
		foreach ( $xml->MEETINGS->children () as $meeting ) {
			$conditions = array (
					'snl' => '9999',
					'shl' => ( integer ) $xml->DATA->SHL,
					'hit' => ( integer ) $xml->DATA->PREV_VERSION,
					'krs' => ( integer ) $meeting->PREV_VERSION,
					'mfgs' => ( integer ) $meeting->SID
			);
			if ($src = $this->DB->get_record ( 'meetings', $conditions )) {
				$conditions = array (
						'snl' => '9999',
						'shl' => ( integer ) $xml->DATA->SHL,
						'hit' => ( integer ) $xml->DATA->MIS,
						'krs' => ( integer ) $meeting->MIS,
						'mfgs' => ( integer ) $meeting->SID
				);
				if ($dest = $this->DB->get_record ( 'meetings', $conditions )) {
					$conditions = array (
							'course' => $src->course_id,
							'section' => $src->section_num
					);
					$src_sec = $this->DB->get_record ( 'course_sections', $conditions );

					$conditions = array (
							'course' => $dest->course_id,
							'section' => $dest->section_num
					);
					$dest_sec = $this->DB->get_record ( 'course_sections', $conditions );

					$this->copy_section ( $src_sec->id, $dest_sec->id );
				}
			}
		}
	}

	protected function copy_section($src, $dest) {
		$conditions = array (
				'id' => $src
		);
		if ($section_src = $this->DB->get_record ( 'course_sections', $conditions )) {
			$conditions = array (
					'id' => $dest
			);
			if ($section_dest = $this->DB->get_record ( 'course_sections', $conditions )) {
				$conditions = array (
						'id' => $section_src->course
				);
				$course_src = $this->DB->get_record ( 'course', $conditions );

				$conditions = array (
						'id' => $section_dest->course
				);
				$course_dest = $this->DB->get_record ( 'course', $conditions );

				$conditions = array (
						'section' => $src
				);
				if ($modules = $this->DB->get_records ( 'course_modules', $conditions )) {
					foreach ( $modules as $module ) {
						$module->modname = $this->DB->get_field ( 'modules', 'name', array (
								'id' => $module->module
						), MUST_EXIST );

						$module_new = duplicate_module ( $course_dest, $module );

						delete_mod_from_section ( $module_new->id, $src );

						course_add_cm_to_section ( $course_dest->id, $module_new->id, $section_dest->section );

						$event = \local_ws_rashim\event\course_section_copied::create ( array (
								'userid' => $this->admin->id,
								'courseid' => $section_dest->course,
								'objectid' => $section_dest->id,
								'context' => context_course::instance ( $section_dest->course ),
								'other' => array (
										'sectionnum' => $section_dest->section,
										'old_moduleid' => $module->id,
										'old_sectionid' => $src,
										'new_moduleid' => $module_new->id,
										'new_sectionid' => $dest
								)
						) );

						$event->trigger ();
					}
				}
			}
		}
	}

	public function session_login($admin_name, $admin_psw) {
		return md5 ( ( string ) time () ^ ( string ) random_string ( 10 ) );
	}

	public function session_logout($session_key) {
		return true;
	}

	public function course_add($admin_name, $admin_psw, $session_key, $course_id, $course_psw, $course_name, $course_shortname, $course_sylurl, $category_code, $category_snlcode, $category_snlname, $category_shlcode, $category_shlname, $category_mslcode, $category_mslname) {
		if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
			if (! isset ( $category_code )) {
				$category_code = $this->category_tree ( $category_snlcode, $category_snlname, $category_shlcode, $category_shlname, $category_mslcode, $category_mslname );
			}

			$conditions = $this->get_course_condition ( $course_id );
			if (! $course = $this->DB->get_record ( 'course', $conditions )) {
				if (isset ( $this->config->michlol_useid ) && $this->config->michlol_useid) {
					return $this->error ( 5014 );
				}

				if (! isset ( $course_name ) || ! isset ( $course_id )) {
					return $this->error ( 5007 );
				}

				$course->idnumber = $course_id;
				$course->fullname = $course_name;
				$course->shortname = $course_shortname;
				$course->category = $category_code;
				$course->startdate = time ();

				$course->password = md5 ( $course_psw );

				$courseconfig = get_config ( 'moodlecourse' );
				foreach ( $courseconfig as $key => $value ) {
					$course->$key = $value;
				}

				if (isset ( $this->config->michlol_course_visible )) {
					$course->visible = ( int ) $this->config->michlol_course_visible;
				}

				$course = create_course ( $course );

				$event = \local_ws_rashim\event\course_created::create ( array (
						'userid' => $this->admin->id,
						'courseid' => $course->id,
						'context' => context_course::instance ( $course->id ),
						'objectid' => $course->id,
						'other' => array (
								'fullname' => $course->fullname
						)
				) );

				$event->trigger ();
			} else {
				if (! isset ( $course_id )) {
					return $this->error ( 5007 );
				}

				$course->idnumber = $course_id;
				$course->fullname = $course_name;
				$course->shortname = $course_shortname;
				$course->category = $category_code;

				$course->password = md5 ( $course_psw );

				update_course ( $course );

				$event = \local_ws_rashim\event\course_updated::create ( array (
						'userid' => $this->admin->id,
						'courseid' => $course->id,
						'context' => context_course::instance ( $course->id ),
						'objectid' => $course->id
				) );

				$event->trigger ();
			}

			$this->add_syllabus_url ( $course, $course_sylurl );

			return true;
		}
	}

	public function course_delete($admin_name, $admin_psw, $course_id, $nodelete) {
		if ($this->admin_login ( $admin_name, $admin_psw )) {
			if (empty ( $course_id )) {
				return $this->error ( 5007 );
			}

			$conditions = $this->get_course_condition ( $course_id );
			if ($course = $this->DB->get_record ( 'course', $conditions )) {
				$context = context_course::instance ( $course->id );

				if ($nodelete) {
					$course->visible = 0;

					update_course ( $course );
				} else {
					if (! delete_course ( $course, false )) {
						return $this->error ( 5019 );
					}
				}
			}

			$event = \local_ws_rashim\event\course_deleted::create ( array (
					'userid' => $this->admin->id,
					'courseid' => $course->id,
					'context' => $context,
					'objectid' => $course->id,
					'other' => array (
							'fullname' => $course->fullname,
							'nodelete' => $nodelete
					)
			) );

			$event->trigger ();

			return true;
		}
	}

	public function user_add($admin_name, $admin_psw, $session_key, $user_id, $user_name, $user_psw, $user_firstname, $user_lastname, $user_email, $user_phone1, $user_phone2, $user_address, $user_lang, $user_extra, $course_id, $course_role, $group_id, $group_name, $role_start, $role_end) {
		if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
			$user = $this->add_user ( $user_id, $user_name, $user_psw, $user_firstname, $user_lastname, $user_email, $user_phone1, $user_phone2, $user_address, $user_lang, $user_extra );

			if (isset ( $course_id )) {
				$conditions = $this->get_course_condition ( $course_id );
				if (! $course = $this->DB->get_record ( 'course', $conditions )) {
					return $this->error ( 5014 );
				}

				if (isset ( $course_role )) {
					$conditions = array (
							'shortname' => $course_role
					);
					if (! $role = $this->DB->get_record ( 'role', $conditions )) {
						return $this->error ( 5043 );
					}

					$this->user_enroll ( $course->id, $user->id, $role->id, $role_start, $role_end );
				}

				if (isset ( $group_id ) && isset ( $group_name )) {
					$conditions = array (
							'courseid' => $course->id,
							'enrolmentkey' => $group_id
					);
					if (! $group = $this->DB->get_record ( 'groups', $conditions )) {
						$group = new stdClass ();
						$group->courseid = $course->id;
						$group->name = $group_name;
						$group->enrolmentkey = $group_id;

						if (! $group->id = groups_create_group ( $group )) {
							return $this->error ( 5027 );
						}
					}

					$conditions = array (
							'courseid' => $course->id,
							'idnumber' => $group_id
					);
					if (! $grouping = $this->DB->get_record ( 'groupings', $conditions )) {
						$conditions = array (
								'courseid' => $course->id,
								'name' => $group_name
						);
						if (! $grouping = $this->DB->get_record ( 'groupings', $conditions )) {
							$grouping = new stdClass ();
							$grouping->courseid = $course->id;
							$grouping->name = $group_name;
							$grouping->idnumber = $group_id;
							$grouping->description = 'נוצר ממכלול';

							if (! $grouping->id = groups_create_grouping ( $grouping )) {
								return $this->error ( 5028 );
							}
						}
					}

					if (! groups_assign_grouping ( $grouping->id, $group->id )) {
						return $this->error ( 5029 );
					}

					if (! groups_add_member ( $group->id, $user->id )) {
						return $this->error ( 5044 );
					}
				}
			}

			return true;
		}
	}

	public function user_remove($admin_name, $admin_psw, $user_id, $course_id, $course_role) {
		if ($this->admin_login ( $admin_name, $admin_psw )) {
			$conditions = array (
					'idnumber' => $user_id
			);
			if (! $user = $this->DB->get_record ( 'user', $conditions )) {
				return $this->error ( 5011 );
			}

			$conditions = $this->get_course_condition ( $course_id );
			if (! $course = $this->DB->get_record ( 'course', $conditions )) {
				return $this->error ( 5014 );
			}

			$this->user_unenroll ( $course->id, $user->id );

			return true;
		}
	}

	public function bhn_add($admin_name, $admin_psw, $session_key, $course_id, $bhn_shm, $michlol_krs, $michlol_sms, $michlol_sid, $moodle_type) {
		if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
			if (! isset ( $course_id ) || ! isset ( $bhn_shm ) || ! isset ( $michlol_krs ) || ! isset ( $michlol_sms ) || ! isset ( $michlol_sid ) || ! isset ( $moodle_type )) {
				return $this->error ( 5007 );
			}

			$conditions = $this->get_course_condition ( $course_id );
			if ($course = $this->DB->get_record ( 'course', $conditions )) {
				$modinfo = $this->add_assignment ( $course->id, 0, $bhn_shm, $michlol_krs, $michlol_sms, $michlol_sid, $moodle_type, 0, 0 );
			} else {
				return $this->error ( 5014 );
			}

			return true;
		}
	}

	public function bhn_delete($admin_name, $admin_psw, $michlol_krs, $michlol_sms, $michlol_sid) {
		if ($this->admin_login ( $admin_name, $admin_psw )) {
			if (! isset ( $michlol_krs ) || ! isset ( $michlol_sms ) || ! isset ( $michlol_sid )) {
				return $this->error ( 5007 );
			}

			$conditions = array (
					'michlol_krs_bhn_krs' => $michlol_krs,
					'michlol_krs_bhn_sms' => $michlol_sms,
					'michlol_krs_bhn_sid' => $michlol_sid
			);
			if ($matala = $this->DB->get_record ( 'matalot', $conditions )) {

				$conditions = array (
						'course' => $matala->course_id,
						'instance' => $matala->moodle_id
				);
				if ($module = $this->DB->get_record ( 'course_modules', $conditions )) {
					course_delete_module ( $module->id );
				}

				$conditions = array (
						'id' => $matala->id
				);
				$this->DB->delete_records ( 'matalot', $conditions );
			} else {
				return $this->error ( 5032 );
			}

			$modname = $this->DB->get_field ( 'modules', 'name', array (
					'id' => $module->module
			), MUST_EXIST );

			$event = \local_ws_rashim\event\course_module_deleted::create ( array (
					'userid' => $this->admin->id,
					'courseid' => $course->id,
					'context' => context_course::instance ( $matala->course_id ),
					'objectid' => $module->id,
					'other' => array (
							'modulename' => $modname,
							'instanceid' => $module->instance
					)
			) );

			$event->trigger ();

			return true;
		}
	}

	public function tikyesod_add($admin_name, $admin_psw, $session_key, $xml) {
		if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
			if (! isset ( $xml )) {
				return $this->error ( 5007 );
			}

			$xml = new SimpleXMLElement ( $xml );

			if ($xml) {
				$conditions = array (
						'idnumber' => "{$xml->DATA->SNL}_{$xml->DATA->SHL}_{$xml->DATA->MIS}"
				);
				if ($course = $this->DB->get_record ( 'course', $conditions )) {
					$course->fullname = ( string ) $xml->DATA->SHM;
					$course->shortname = ( string ) $xml->DATA->SHM_UNIQUE;

					update_course ( $course );

					$event = \local_ws_rashim\event\course_updated::create ( array (
							'userid' => $this->admin->id,
							'courseid' => $course->id,
							'context' => context_course::instance ( $course->id ),
							'objectid' => $course->id
					) );

					$event->trigger ();
				} else {
					$course = new stdClass ();
					$course->fullname = ( string ) $xml->DATA->SHM;
					$course->shortname = ( string ) $xml->DATA->SHM_UNIQUE;
					$course->idnumber = "{$xml->DATA->SNL}_{$xml->DATA->SHL}_{$xml->DATA->MIS}";
					$course->category = $this->DB->get_field_sql ( 'SELECT MIN(id) FROM {course_categories}', null, MUST_EXIST );

					$courseconfig = get_config ( 'moodlecourse' );
					foreach ( $courseconfig as $key => $value ) {
						$course->$key = $value;
					}

					if (isset ( $this->config->michlol_course_visible )) {
						$course->visible = ( int ) $this->config->michlol_course_visible;
					}

					$course = create_course ( $course );

					$event = \local_ws_rashim\event\course_created::create ( array (
							'userid' => $this->admin->id,
							'courseid' => $course->id,
							'context' => context_course::instance ( $course->id ),
							'objectid' => $course->id,
							'other' => array (
									'fullname' => $course->fullname
							)
					) );

					$event->trigger ();
				}

				$this->xml2meetings ( $course->id, $xml );

				if (( integer ) $xml->DATA->PREV_VERSION != - 1) {
					$this->copy_course_section_tik ( $xml );

					$this->xml2assignments_link ( $course->id, $xml );
				} else {
					$this->xml2assignments ( $course->id, $xml );
				}
			} else {
				return $this->error ( 5042 );
			}

			return true;
		}
	}

	public function tikyesod_delete($admin_name, $admin_psw, $shl, $hit) {
		if ($this->admin_login ( $admin_name, $admin_psw )) {
			if (! isset ( $shl ) || ! isset ( $hit )) {
				return $this->error ( 5007 );
			}

			$conditions = array (
					'snl' => '9999',
					'shl' => $shl,
					'hit' => $hit
			);
			if ($tikyesod = $this->DB->get_record ( 'meetings', $conditions )) {
				$conditions = array (
						'id' => $tikyesod->course_id
				);
				if ($course = $this->DB->get_record ( 'course', $conditions )) {
					$course->visible = 0;

					update_course ( $course );
				} else {
					return $this->error ( 5014 );
				}
			} else {
				return $this->error ( 5041 );
			}

			$event = \local_ws_rashim\event\course_deleted::create ( array (
					'userid' => $this->admin->id,
					'courseid' => $course->id,
					'context' => context_course::instance ( $course->id ),
					'objectid' => $course->id,
					'other' => array (
							'nodelete' => true,
							'fullname' => $course->fullname
					)
			) );

			$event->trigger ();

			return true;
		}
	}

	public function machzor_add($admin_name, $admin_psw, $session_key, $xml) {
		if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
			if (! isset ( $xml )) {
				return $this->error ( 5007 );
			}

			$xml = new SimpleXMLElement ( $xml );

			if ($xml) {
				$conditions = array (
						'idnumber' => "{$xml->DATA->SNL}_{$xml->DATA->SHL}_{$xml->DATA->MIS}"
				);
				if ($course = $this->DB->get_record ( 'course', $conditions )) {
					$course->fullname = ( string ) $xml->DATA->SHM;
					$course->shortname = ( string ) $xml->DATA->SHM_UNIQUE;

					update_course ( $course );

					$event = \local_ws_rashim\event\course_updated::create ( array (
							'userid' => $this->admin->id,
							'courseid' => $course->id,
							'context' => context_course::instance ( $course->id ),
							'objectid' => $course->id,
							'other' => array (
									'fullname' => $course->fullname
							)
					) );

					$event->trigger ();
				} else {
					$course = new stdClass ();
					$course->fullname = ( string ) $xml->DATA->SHM;
					$course->shortname = ( string ) $xml->DATA->SHM_UNIQUE;
					$course->idnumber = "{$xml->DATA->SNL}_{$xml->DATA->SHL}_{$xml->DATA->MIS}";
					$course->category = 1;

					$courseconfig = get_config ( 'moodlecourse' );
					foreach ( $courseconfig as $key => $value ) {
						$course->$key = $value;
					}

					if (isset ( $this->config->michlol_course_visible )) {
						$course->visible = ( int ) $this->config->michlol_course_visible;
					}

					$course = create_course ( $course );

					$event = \local_ws_rashim\event\course_created::create ( array (
							'userid' => $this->admin->id,
							'courseid' => $course->id,
							'context' => context_course::instance ( $course->id ),
							'objectid' => $course->id,
							'other' => array (
									'fullname' => $course->fullname
							)
					) );

					$event->trigger ();
				}

				$this->xml2meetings ( $course->id, $xml );

				$conditions = array (
						'snl' => '9999',
						'shl' => ( integer ) $xml->DATA->SHL,
						'hit' => ( integer ) $xml->DATA->TAVNIT
				);
				if ($tikyesod = $this->DB->get_record ( 'meetings', $conditions )) {
					$this->copy_course_section ( $tikyesod->course_id, $course->id );
				} else {
					return $this->error ( 5041 );
				}

				$this->xml2assignments_link ( $course->id, $xml );
			} else {
				return $this->error ( 5042 );
			}

			return true;
		}
	}

	public function machzor_delete($admin_name, $admin_psw, $snl, $shl, $hit) {
		if ($this->admin_login ( $admin_name, $admin_psw )) {
			if (! isset ( $snl ) || ! isset ( $shl ) || ! isset ( $hit )) {
				return $this->error ( 5007 );
			}

			$conditions = array (
					'snl' => $snl,
					'shl' => $shl,
					'hit' => $hit
			);
			if ($machzor = $this->DB->get_record ( 'meetings', $conditions )) {
				$conditions = array (
						'id' => $machzor->course_id
				);
				if ($course = $this->DB->get_record ( 'course', $conditions )) {
					$course->visible = 0;

					update_course ( $course );
				} else {
					return $this->error ( 5014 );
				}
			} else {
				return $this->error ( 5041 );
			}

			$event = \local_ws_rashim\event\course_deleted::create ( array (
					'userid' => $this->admin->id,
					'courseid' => $course->id,
					'context' => context_course::instance ( $course->id ),
					'objectid' => $course->id,
					'other' => array (
							'nodelete' => true,
							'fullname' => $course->fullname
					)
			) );

			$event->trigger ();

			return true;
		}
	}

	public function machzor_user_add($admin_name, $admin_psw, $session_key, $user_id, $user_name, $user_psw, $user_firstname, $user_lastname, $user_email, $user_phone1, $user_phone2, $user_address, $user_lang, $user_extra, $snl, $shl, $hit, $course_role) {
		if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
			$user = $this->add_user ( $user_id, $user_name, $user_psw, $user_firstname, $user_lastname, $user_email, $user_phone1, $user_phone2, $user_address, $user_lang, $user_extra );

			$conditions = array (
					'snl' => $snl,
					'shl' => $shl,
					'hit' => $hit
			);
			if (! $meeting = $this->DB->get_record ( 'meetings', $conditions )) {
				return $this->error ( 5041 );
			}

			$conditions = array (
					'shortname' => $course_role
			);
			if (! $role = $this->DB->get_record ( 'role', $conditions )) {
				return $this->error ( 5044 );
			}

			$this->user_enroll ( $meeting->course_id, $user->id, $role->id );

			return true;
		}
	}

	public function machzor_user_remove($admin_name, $admin_psw, $user_id, $snl, $shl, $hit, $course_role) {
		if ($this->admin_login ( $admin_name, $admin_psw )) {
			$conditions = array (
					'idnumber' => $user_id
			);
			if (! $user = $this->DB->get_record ( 'user', $conditions )) {
				return $this->error ( 5011 );
			}

			$conditions = array (
					'snl' => $snl,
					'shl' => $shl,
					'hit' => $hit
			);
			if (! $meeting = $this->DB->get_record ( 'meetings', $conditions )) {
				return $this->error ( 5041 );
			}

			$this->user_unenroll ( $meeting->course_id, $user->id );

			return true;
		}
	}

	public function machzormfgs_upd($admin_name, $admin_psw, $session_key, $xml) {
		if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
			if (! isset ( $xml )) {
				return $this->error ( 5007 );
			}

			$section_num = 0;

			$xml = new SimpleXMLElement ( $xml );

			if ($xml) {
				if (! isset ( $xml->MEETING->WEEK )) {
					$xml->MEETING->WEEK = - 1;
				}

				if (! isset ( $xml->MEETING->DAY )) {
					$xml->MEETING->DAY = - 1;
				}

				if (! isset ( $xml->MEETING->MEETING_DATE )) {
					$xml->MEETING->MEETING_DATE = - 1;
				}

				$conditions = array (
						'snl' => ( string ) $xml->MEETING->SNL,
						'shl' => ( string ) $xml->MEETING->SHL,
						'hit' => ( string ) $xml->MEETING->HIT,
						'krs' => ( string ) $xml->MEETING->MIS,
						'mfgs' => ( string ) $xml->MEETING->SID
				);
				if (! $meeting = $this->DB->get_record ( 'meetings', $conditions )) {
					$mbase = new stdClass ();

					$conditions = array (
							'snl' => ( string ) $xml->MEETING->SNL,
							'shl' => ( string ) $xml->MEETING->SHL,
							'hit' => ( string ) $xml->MEETING->HIT
					);
					if (! $meeting_base = $this->DB->get_records ( 'meetings', $conditions, 'section_num DESC' )) {
						$conditions = array (
								'idnumber' => "{$xml->DATA->SNL}_{$xml->DATA->SHL}_{$xml->DATA->MIS}"
						);
						if (! $coursebase = $this->DB->get_record ( 'course', $conditions )) {
							return $this->error ( 5014 );
						}

						$mbase->course_id = $coursebase->id;
						$mbase->section_num = 0;
						$mbase->snl = ( string ) $xml->MEETING->SNL;
						$mbase->shl = ( integer ) $xml->MEETING->SHL;
						$mbase->hit = ( integer ) $xml->MEETING->HIT;
					} else {
						$index = array_shift ( array_keys ( $meeting_base ) );

						$mbase->course_id = $meeting_base [$index]->course_id;
						$mbase->section_num = $meeting_base [$index]->section_num;
						$mbase->snl = $meeting_base [$index]->snl;
						$mbase->shl = $meeting_base [$index]->shl;
						$mbase->hit = $meeting_base [$index]->hit;
					}

					$section = $this->handle_course_section ( $mbase->course_id, $mbase->section_num + 1, ( string ) $xml->MEETING->SHM );

					$meeting_new->snl = $mbase->snl;
					$meeting_new->shl = $mbase->shl;
					$meeting_new->hit = $mbase->hit;
					$meeting_new->krs = ( integer ) $xml->MEETING->MIS;
					$meeting_new->mfgs = ( integer ) $xml->MEETING->SID;

					$meeting_new->course_id = $mbase->course_id;
					$meeting_new->section_num = $mbase->section_num + 1;

					$meeting_new->subject = ( string ) $xml->MEETING->SUB;

					$meeting_new->week = ( integer ) $xml->MEETING->WEEK;
					$meeting_new->day = ( integer ) $xml->MEETING->DAY;

					$meeting_new->meeting_date = ( integer ) $xml->MEETING->MEETING_DATE;

					$meeting_new->hour_begin = ( integer ) $xml->MEETING->BEGIN;
					$meeting_new->hour_end = ( integer ) $xml->MEETING->END;

					$this->DB->insert_record ( 'meetings', $meeting_new );

					$course_id = $meeting_new->course_id;
					$section_num = $meeting_new->section_num;

					$event = \local_ws_rashim\event\meeting_created::create ( array (
							'userid' => $this->admin->id,
							'objectid' => $section,
							'courseid' => $meeting_new->course_id,
							'context' => context_course::instance ( $meeting_new->course_id ),
							'other' => array (
									'sectionnum' => $meeting_new->section_num
							)
					) );

					$event->trigger ();
				} else {
					$meeting->week = ( integer ) $xml->MEETING->WEEK;
					$meeting->day = ( integer ) $xml->MEETING->DAY;

					$meeting->meeting_date = ( string ) $xml->MEETING->MEETING_DATE;

					$meeting->hour_begin = ( integer ) $xml->MEETING->BEGIN;
					$meeting->hour_end = ( integer ) $xml->MEETING->END;

					$meeting->subject = ( string ) $xml->MEETING->SUB;

					$this->DB->update_record ( 'meetings', $meeting );

					$this->handle_course_section ( $meeting->course_id, $meeting->section_num, ( string ) $xml->MEETING->SHM );

					$course_id = $meeting->course_id;
					$section_num = $meeting->section_num;

					$event = \local_ws_rashim\event\meeting_updated::create ( array (
							'userid' => $this->admin->id,
							'objectid' => $section->id,
							'courseid' => $meeting->course_id,
							'context' => context_course::instance ( $meeting->course_id ),
							'other' => array (
									'sectionnum' => $meeting->section_num
							)
					) );

					$event->trigger ();
				}

				course_get_format ( $course_id )->update_course_format_options ( array (
						'numsections' => $section_num
				) );

				$this->xml2assignments ( $course_id, $xml );
			} else {
				return $this->error ( 5042 );
			}

			return true;
		}
	}

	public function machzormfgs_del($admin_name, $admin_psw, $session_key, $xml) {
		if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
			if (! isset ( $xml )) {
				return $this->error ( 5007 );
			}

			$xml = new SimpleXMLElement ( $xml );

			if ($xml) {
				$conditions = array (
						'snl' => ( string ) $xml->MEETING->SNL,
						'shl' => ( string ) $xml->MEETING->SHL,
						'hit' => ( string ) $xml->MEETING->HIT,
						'krs' => ( string ) $xml->MEETING->MIS,
						'mfgs' => ( string ) $xml->MEETING->SID
				);
				if (! $meeting = $this->DB->get_record ( 'meetings', $conditions )) {
					return $this->error ( 5041 );
				} else {
					$section_conditions = array (
							'course' => $meeting->course_id,
							'section' => $meeting->section_num
					);
					if ($section = $this->DB->get_record ( 'course_sections', $section_conditions )) {
						if (( string ) $xml->MEETING->SNL == '9999') {
							course_delete_section ( $section->course, $section );
						} else {
							$this->handle_course_section ( $section->course, $section->section, $section->name, 0 );
						}
					}

					if (( string ) $xml->MEETING->SNL == '9999') {
						$this->DB->delete_records ( 'meetings', $conditions );
					}
				}
			} else {
				return $this->error ( 5042 );
			}

			$event = \local_ws_rashim\event\meeting_deleted::create ( array (
					'userid' => $this->admin->id,
					'objectid' => $section->id,
					'courseid' => $section->course,
					'context' => context_course::instance ( $section->course ),
					'other' => array (
							'sectionnum' => $section->section,
							'sectionname' => $section->name
					)
			) );

			$event->trigger ();

			return true;
		}
	}

	public function tikyesod_shl_change($admin_name, $admin_psw, $session_key, $xml) {
		if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
			if (! isset ( $xml )) {
				return $this->error ( 5007 );
			}

			$xml = new SimpleXMLElement ( $xml );

			if ($xml) {
				$sql_krs = 'UPDATE {meetings} SET shl = ?, krs = ? WHERE shl = ? AND krs = ?';

				foreach ( $xml->KRS_LIST->children () as $krs ) {
					$this->DB->execute ( $sql_krs, array (
							( integer ) $krs->NEW_SHL,
							( integer ) $krs->NEW_MIS,
							( integer ) $krs->OLD_SHL,
							( integer ) $krs->OLD_MIS
					) );
				}

				$sql_hit = 'UPDATE {meetings} SET shl = ? WHERE snl = ? AND shl = ? AND hit = ?';

				foreach ( $xml->HIT_LIST->children () as $hit ) {
					$this->DB->execute ( $sql_hit, array (
							( integer ) $hit->NEW_SHL,
							( integer ) $hit->SNL,
							( integer ) $hit->OLD_SHL,
							( integer ) $hit->MIS
					) );

					$conditions = array (
							'idnumber' => ( string ) $hit->OLD_KEY
					);
					if ($course = $this->DB->get_record ( 'course', $conditions )) {
						return $this->error ( 5014 );

						$course->idnumber = ( string ) $hit->NEW_KEY;

						update_course ( $course );

						$event = \local_ws_rashim\event\tikyesod_shl_changed::create ( array (
								'userid' => $this->admin->id,
								'objectid' => $course->id,
								'courseid' => $course->id,
								'context' => context_course::instance ( $course->id ),
								'other' => array (
										'old_shl' => ( integer ) $hit->OLD_SHL,
										'new_shl' => ( integer ) $hit->NEW_SHL
								)
						) );

						$event->trigger ();
					}
				}
			} else {
				return $this->error ( 5042 );
			}

			return true;
		}
	}

	public function course_update_key($admin_name, $admin_psw, $course_old_id, $course_new_id, $course_shortname) {
		if ($this->admin_login ( $admin_name, $admin_psw )) {
			$conditions = array (
					'idnumber' => $course_old_id
			);
			if (! $course = $this->DB->get_record ( 'course', $conditions )) {
				return $this->error ( 5014 );
			}

			$course->idnumber = $course_new_id;
			$course->shortname = $course_shortname;

			update_course ( $course );

			$event = \local_ws_rashim\event\course_idnumber_updated::create ( array (
					'userid' => $this->admin->id,
					'objectid' => $course->id,
					'courseid' => $course->id,
					'context' => context_course::instance ( $course->id ),
					'other' => array (
							'old_idnumber' => $course_old_id,
							'new_idnumber' => $course_new_id
					)
			) );

			$event->trigger ();

			return true;
		}
	}

	public function manage_ktree($admin_name, $admin_psw, $session_key, $category_id, $parent_id, $category_name, $user_id, $role, $add) {
		if ($this->valid_login ( $session_key, $admin_name, $admin_psw )) {
			$conditions = array (
					'idnumber' => $category_id
			);
			$category = $this->DB->get_record ( 'course_categories', $conditions );

			if (! empty ( $parent_id )) {
				$conditions = array (
						'idnumber' => $parent_id
				);
				$parent = $this->DB->get_record ( 'course_categories', $conditions );

				if (empty ( $parent->id )) {
					return $this->error ( 5017 );
				}

				$parent_id = $parent->id;
			}

			if (empty ( $category->id )) {
				if ($add) {
					$cartegory_id = $this->category_add ( $parent_id, $category_id, $category_name );
				} else {
					return $this->error ( 5018 );
				}
			} else {
				$category_id = $category->id;

				if (empty ( $user_id )) {
					$cat = coursecat::get ( $category_id );

					$category->name = ! empty ( $category_name ) ? $category_name : $category->name;
					$category->parent = ! empty ( $parent_id ) ? $parent_id : null;
					$cat->update ( $category );
				}
			}

			if (! empty ( $user_id )) {
				$conditions = array (
						'idnumber' => $user_id
				);
				$user = $this->DB->get_record ( 'user', $conditions );

				if (empty ( $user->id )) {
					return $this->error ( 5011 );
				}

				$conditions = array (
						'shortname' => $role
				);

				$roleid = $this->DB->get_field ( 'role', 'id', $conditions, MUST_EXIST );
				if (empty ( $roleid )) {
					return $this->error ( 5043 );
				}

				if ($add) {
					role_assign ( $roleid, $user->id, context_coursecat::instance ( $category_id ) );
				} else {
					role_unassign ( $roleid, $user->id, context_coursecat::instance ( $category_id )->id );
				}
			}
		}

		return true;
	}
}

?>
