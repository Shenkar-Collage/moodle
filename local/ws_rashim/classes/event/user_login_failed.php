<?php

namespace local_ws_rashim\event;

defined ( 'MOODLE_INTERNAL' ) || die ();

class user_login_failed extends \core\event\user_login_failed {
	public function get_description() {
		$username = s($this->other['username']);
		$reason = s($this->other['reason']);
		
		return "Login failed for the username '{$username}' for the reason '{$reason}'.";
	}
}

?>