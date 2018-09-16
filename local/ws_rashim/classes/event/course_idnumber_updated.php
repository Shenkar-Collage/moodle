<?php

namespace local_ws_rashim\event;

defined ( 'MOODLE_INTERNAL' ) || die ();

class course_idnumber_updated extends \core\event\course_updated {

	public function get_description() {
		return "The user with id '$this->userid' updated the idnumber of the course with id '$this->courseid' from '" . $this->other['old_idnumber'] . "' to '" . $this->other['new_idnumber'] . "'.";
	}
}

?>