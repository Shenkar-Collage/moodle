<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

global $DB;

if ($hassiteconfig) {
	$settings = new admin_settingpage ( 'local_ws_rashim', get_string ( 'pluginname', 'local_ws_rashim' ) );
	
	$ADMIN->add ( 'localplugins', $settings );
	
	$auths = get_plugin_list ( 'auth' );
	$auth_options = array ();
	
	foreach ( $auths as $auth => $unused ) {
		if (is_enabled_auth ( $auth )) {
			$auth_options [$auth] = get_string ( 'pluginname', "auth_{$auth}" );
		}
	}
	
	$settings->add ( new admin_setting_heading ( 'local_ws_rashim/version', '<h6>' . get_string ( 'version', 'local_ws_rashim' ) . $this->release . ' (' . $this->versiondisk . ')</h6>', '' ) );
	
	$settings->add ( new admin_setting_configselect ( 'local_ws_rashim/michlolauth', get_string ( 'michlolauth', 'local_ws_rashim' ), null, 'manual', $auth_options ) );
	
	$settings->add ( new admin_setting_configselect ( 'local_ws_rashim/michlol_course_visible', get_string ( 'michlol_course_visible', 'local_ws_rashim' ), null, 0, [ 
			get_string ( 'hide' ),
			get_string ( 'show' ) 
	] ) );
	
	$settings->add ( new admin_setting_configtext ( 'local_ws_rashim/def_city', get_string ( 'def_city', 'local_ws_rashim' ), null, 'ירושלים' ) );
	
	$settings->add ( new admin_settings_country_select ( 'local_ws_rashim/def_country', get_string ( 'def_country', 'local_ws_rashim' ), null, 'IL' ) );
	
	$settings->add ( new admin_setting_heading ( 'local_ws_rashim/michlol_api', '<h2>' . get_string ( 'michlol_api', 'local_ws_rashim' ) . '</h2>', '' ) );
	
	$settings->add ( new admin_setting_configtext ( 'local_ws_rashim/api_url', get_string ( 'michlolurl', 'local_ws_rashim' ), NULL, NULL ) );
	
	$settings->add ( new admin_setting_configtext ( 'local_ws_rashim/api_user', get_string ( 'michloluser', 'local_ws_rashim' ), NULL, NULL ) );
	
	$settings->add ( new admin_setting_configpasswordunmask ( 'local_ws_rashim/api_psw', get_string ( 'michlolpsw', 'local_ws_rashim' ), NULL, NULL ) );

	$settings->add ( new admin_setting_heading ( 'local_ws_rashim/michlol_login_header', '<h2>' . get_string ( 'michlol_login_header', 'local_ws_rashim' ) . '</h2>', '' ) );
	
	$settings->add ( new admin_setting_configcheckbox ( 'local_ws_rashim/michlol_login', get_string ( 'michlol_login', 'local_ws_rashim' ), null, 0 ) );
	
	$settings->add ( new admin_setting_heading ( 'local_ws_rashim/michlol_obsolate', '<h2>' . get_string ( 'michlol_obsolate', 'local_ws_rashim' ) . '</h2>', '' ) );
	
	$settings->add ( new admin_setting_configcheckbox ( 'local_ws_rashim/michlol_useid', get_string ( 'michlol_useid', 'local_ws_rashim' ), null, 0 ) );
	
	$dbman = $DB->get_manager ();
	
	if ($dbman->table_exists ( 'assignment' )) {
		if ($rows = $DB->get_records ( 'matalot', array (
				'moodle_type' => 'assignment' 
		) )) {
			$ADMIN->add ( 'localplugins', new admin_externalpage ( 'local_ws_rashim_update', get_string ( 'updateassignment', 'local_ws_rashim' ), new moodle_url ( '/local/ws_rashim/update.php' ) ) );
		}
	}
}

?>
