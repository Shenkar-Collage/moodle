<?php

namespace local_ws_rashim\event;

defined ( 'MOODLE_INTERNAL' ) || die ();

class user_profile_field_missing extends \core\event\user_updated {

	public static function get_name() {
		return get_string ( 'eventprofilefieldmissing', 'local_ws_rashim' );
	}

	public function get_description() {
		return "The user with id '$this->relateduserid' has no profile field by name '{$this->other['field']}'.";
	}
}

?>