<?php

namespace local_ws_rashim\event;

defined ( 'MOODLE_INTERNAL' ) || die ();

class tikyesod_shl_changed extends \core\event\base {

	protected function init() {
		$this->data ['objecttable'] = 'meetings';
		$this->data ['crud'] = 'u';
		$this->data ['edulevel'] = self::LEVEL_OTHER;
	}
	
	public static function get_name() {
		return get_string ( 'eventcoursemoved', 'local_ws_rashim' );
	}

	public function get_description() {
		return "The user with id '$this->userid' moved the course with id '$this->courseid' from SHL '$this->other['old_shl']' to SHL '$this->other['new_shl']'.";
	}

	public function get_url() {
		return new \moodle_url ( '/course/edit.php', array (
				'id' => $this->objectid 
		) );
	}
}

?>