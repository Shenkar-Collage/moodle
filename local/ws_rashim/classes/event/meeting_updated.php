<?php

namespace local_ws_rashim\event;

defined ( 'MOODLE_INTERNAL' ) || die ();

class meeting_updated extends \core\event\base {

	protected function init() {
		$this->data ['objecttable'] = 'course_sections';
		$this->data ['crud'] = 'u';
		$this->data ['edulevel'] = self::LEVEL_TEACHING;
	}

	public static function get_name() {
		return get_string ( 'eventcoursesectionupdated', 'local_ws_rashim' );
	}

	public function get_description() {
		return "The user with id '$this->userid' updated the meeting with id '$this->objectid' in course with id '$this->courseid'.";
	}

	public function get_url() {
		return new \moodle_url ( '/course/editsection.php', array (
				'id' => $this->objectid 
		) );
	}
}

?>
