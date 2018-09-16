<?php

namespace local_ws_rashim\event;

defined ( 'MOODLE_INTERNAL' ) || die ();

class session_created extends \core\event\base {

	protected function init() {
		$this->data ['objecttable'] = 'webservices_sessions';
		$this->data ['crud'] = 'c';
		$this->data ['edulevel'] = self::LEVEL_OTHER;
		$this->context = \context_system::instance();
	}
	
	public function get_description() {
		return "The user with id '$this->userid' created a session with id '" . $this->other['sessionid']. "'.";
	}

	public static function get_name() {
		return get_string ( 'eventsessioncreated', 'local_ws_rashim' );
	}
}

?>