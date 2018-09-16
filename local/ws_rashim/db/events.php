<?php
defined ( 'MOODLE_INTERNAL' ) || die ();

$observers = array (
		array (
				'eventname' => '\core\event\user_graded',
				'callback' => 'local_ws_rashim_observer::grades_export_handler' 
		),
		array (
				'eventname' => '\core\event\course_deleted',
				'callback' => 'local_ws_rashim_observer::course_deleted_handler' 
		),
		array (
				'eventname' => '\core\event\course_module_deleted',
				'callback' => 'local_ws_rashim_observer::course_module_deleted_handler'
		)
);

?>
