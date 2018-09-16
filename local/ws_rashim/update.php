<?php
require_once ("../../config.php");
require_once ('../../admin/tool/assignmentupgrade/locallib.php');

global $DB;

$PAGE->set_url ( '/local/ws_rashim/update.php' );
$PAGE->set_context ( context_system::instance () );
$PAGE->set_pagelayout ( 'admin' );
$PAGE->set_cacheable ( false );
$PAGE->set_heading ( $SITE->fullname );
$PAGE->set_title ( $SITE->fullname . ': ' . get_string ( 'pluginname', 'local_ws_rashim' ) );

echo $OUTPUT->header ();
echo $OUTPUT->heading ( get_string ( 'pluginname', 'local_ws_rashim' ) );

$dbman = $DB->get_manager ();

$rows = $DB->get_records ( 'matalot', array (
		'moodle_type' => 'assignment' 
) );

if ($dbman->table_exists ( 'assignment' ) && count ( $rows ) > 0) {
	$mod = $DB->get_field ( 'modules', 'id', array (
			'name' => 'assignment' 
	), MUST_EXIST );
	
	echo $OUTPUT->heading ( get_string ( 'updateassignment', 'local_ws_rashim' ) );
	echo $OUTPUT->box_start ( 'generalbox' );
	
	foreach ( $rows as $row ) {
		$course = $DB->get_record ( 'course', array (
				'id' => $row->course_id 
		) );
		
		$assignment = $DB->get_record ( 'assignment', array (
				'id' => $row->moodle_id 
		) );
		
		$module_old = $DB->get_record ( 'course_modules', array (
				'course' => $row->course_id,
				'module' => $mod,
				'instance' => $row->moodle_id 
		) );
		
		$section_old = $DB->get_record ( 'course_sections', array (
				'course' => $row->course_id,
				'id' => $module_old->section 
		) );
		
		if (! $course || ! $assignment || ! $module_old || ! $section_old) {
			echo '<p>' . get_string ( 'assignment', 'local_ws_rashim' ) . " '$assignment->name', " . get_string ( 'course', 'local_ws_rashim' ) . " '$course->fullname'... X" . '</p>';
			
			$DB->delete_records ( 'matalot', array (
					'id' => $row->id 
			) );
			
			continue;
		}
		
		echo '<p>' . get_string ( 'assignment', 'local_ws_rashim' ) . " '$assignment->name', " . get_string ( 'course', 'local_ws_rashim' ) . " '$course->fullname'... ";
		
		$rv = tool_assignmentupgrade_upgrade_assignment ( $row->moodle_id );
		
		$section_new = $DB->get_record ( 'course_sections', array (
				'id' => $section_old->id 
		), '*', MUST_EXIST );
		
		$old = explode ( ',', $section_old->sequence );
		$new = explode ( ',', $section_new->sequence );
		
		$diff = array_diff ( $new, $old );
		
		$sec = current ( $diff );
		
		$module_new = $DB->get_record ( 'course_modules', array (
				'id' => $sec 
		), '*', MUST_EXIST );
		
		$row->moodle_type = 'assign';
		$row->moodle_id = $module_new->instance;
		
		$DB->update_record ( 'matalot', $row );
		
		echo get_string ( 'updated', 'local_ws_rashim' ) . '</p>';
	}
	
	echo $OUTPUT->box_end ();
} else {
	echo $OUTPUT->heading ( get_string ( 'noassignments', 'local_ws_rashim' ) );
}

echo $OUTPUT->footer ();

?>