<?php

namespace local_ws_rashim\event;

defined ( 'MOODLE_INTERNAL' ) || die ();

class course_section_copied extends \core\event\base {

	protected function init() {
		$this->data ['objecttable'] = 'course_sections';
		$this->data ['crud'] = 'u';
		$this->data ['edulevel'] = self::LEVEL_TEACHING;
	}

	public static function get_name() {
		return get_string ( 'eventcoursesectioncopied', 'local_ws_rashim' );
	}

	public function get_description() {
		return "The user with id '$this->userid' copied the module with id '" . $this->other ['old_moduleid'] . "' from section with id '" . $this->other ['old_sectionid'] . " to module with id '" . $this->other ['new_moduleid'] . "' in section with id '" . $this->other ['new_sectionid'] . "'.";
	}

	public function get_url() {
		return new \moodle_url ( '/course/editsection.php', array (
				'id' => $this->objectid 
		) );
	}
}

?>