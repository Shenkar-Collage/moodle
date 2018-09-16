<?php

namespace local_ws_rashim\event;

defined ( 'MOODLE_INTERNAL' ) || die ();

class category_created extends \core\event\base {

	protected function init() {
		$this->data ['objecttable'] = 'course_categories';
		$this->data ['crud'] = 'c';
		$this->data ['edulevel'] = self::LEVEL_OTHER;
		$this->context = \context_system::instance ();
	}

	public static function get_name() {
		return get_string ( 'eventcategorycreated', 'local_ws_rashim' );
	}

	public function get_url() {
		return new \moodle_url ( '/course/management.php', array (
				'categoryid' => $this->objectid 
		) );
	}

	public function get_description() {
		return "The user with id '$this->userid' created the category with id '$this->objectid'.";
	}
}

?>