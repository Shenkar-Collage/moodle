<?php

namespace local_ws_rashim\event;

defined ( 'MOODLE_INTERNAL' ) || die ();

class session_ended extends \core\event\base {

	protected function init() {
		$this->data ['objecttable'] = 'webservices_sessions';
		$this->data ['crud'] = 'u';
		$this->data ['edulevel'] = self::LEVEL_OTHER;
		$this->context = \context_system::instance ();
	}
	
	public function get_description() {
		return "The user with id '$this->userid' ended a session with id '" . $this->other['sessionid']. "'.";
	}

	public static function get_name() {
		return get_string ( 'eventsessionended', 'local_ws_rashim' );
	}
}

?>