<?php

namespace local_ws_rashim\event;

defined ( 'MOODLE_INTERNAL' ) || die ();

class course_deleted extends \core\event\course_deleted {

	public function get_description() {
		return "The user with id '$this->userid' " . ($this->other ['nodelete'] ? 'hid' : 'deleted') . " the course with id '$this->courseid'.";
	}
}

?>