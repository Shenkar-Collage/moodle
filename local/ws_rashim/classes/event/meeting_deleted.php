<?php

namespace local_ws_rashim\event;

defined ( 'MOODLE_INTERNAL' ) || die ();

class meeting_deleted extends \core\event\base {

	protected function init() {
		$this->data ['objecttable'] = 'course_sections';
		$this->data ['crud'] = 'd';
		$this->data ['edulevel'] = self::LEVEL_TEACHING;
	}

	public static function get_name() {
		return get_string ( 'eventcoursesectiondeleted', 'local_ws_rashim' );
	}

	public function get_description() {
		return "The user with id '$this->userid' deleted the meeting with id '$this->objectid' in course with id '$this->courseid'.";
	}
}

?>
