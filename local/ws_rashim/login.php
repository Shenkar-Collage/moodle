<html>
<head>
<title></title>
</head>
<body>
<?php

function print_err($target, $error, $module = null) {
	echo $target->box_start ( 'loginpanel' );
	echo '<span class="error">' . get_string ( $error, $module ) . '</span>';
	echo $target->box_end ();
}

function log_error($target, $username, $course, $reason) {
	$event = \local_ws_rashim\event\user_login_failed::create ( array (
			'userid' => - 1,
			'other' => array (
					'username' => $username,
					'reason' => $reason 
			) 
	) );
	
	$event->trigger ();
	
	echo $target->box_start ( 'loginpanel' );
	echo '<br /><span>Details: ' . $reason . ' (' . $username;
	if ($course != '') {
		echo ', ' . $course;
	}
	echo ')</span>';
	echo $target->box_end ();
}

global $CFG;
global $DB;

require_once ("../../config.php");
require_once ('lib.php');

$config = get_config ( 'local_ws_rashim' );

$user = $_POST ['username'];
$psw = $_POST ['password'];
$course = $_POST ['course'];

$mfgs_krs = $_POST ['mfgs_krs'];
$mfgs_sid = $_POST ['mfgs_sid'];

$resource_type = $_POST ['resource_type'];
$resource_id = $_POST ['resource_id'];

$PAGE->https_required ();
$PAGE->set_url ( "$CFG->httpswwwroot/login/index.php" );
$PAGE->set_context ( context_system::instance () );
$PAGE->set_pagelayout ( 'login' );
$PAGE->navbar->ignore_active ();
$PAGE->navbar->add ( get_string ( "loginsite" ) );
$PAGE->set_heading ( get_site ()->fullname );

$conditions = array (
		"username" => $user 
);
if (! $login_user = $DB->get_record ( 'user', $conditions )) {
	echo $OUTPUT->header ();
	print_err ( $OUTPUT, 'invalidlogin' );
	log_error ( $OUTPUT, $user, null, 'user not found' );
	echo $OUTPUT->footer ();
	die ();
}

if (isset ( $config->michlol_login ) && $config->michlol_login) {
	if ($course != '') {
		redirect ( "$CFG->httpswwwroot/course/view.php?idnumber={$course}" );
	} else {
		redirect ( "$CFG->httpswwwroot/login/index.php" );
	}
} else {
	// we do not use this function in the first place
	// because the functon creates the user if does not exists!
	$login_user = authenticate_user_login ( $user, $psw );
	
	if (($login_user === false) || ($login_user && $login_user->id == 0)) {
		echo $OUTPUT->header ();
		print_err ( $OUTPUT, 'invalidlogin' );
		log_error ( $OUTPUT, $user, null, 'unable to authenticate' );
		echo $OUTPUT->footer ();
		die ();
	} else {
		complete_user_login ( $login_user );
		
		$url = "$CFG->httpswwwroot/login/index.php";
		
		if ($course != '') {
			if (isset ( $config->michlol_useid ) && $config->michlol_useid) {
				$conditions = array (
						"id" => $course 
				);
			} else {
				$conditions = array (
						"idnumber" => $course 
				);
			}
			
			if (isset ( $resource_type ) && ! empty ( $resource_type )) {
				$url = "$CFG->httpswwwroot/mod/{$resource_type}/view.php?id={$resource_id}";
			} else {
				if (! $courseget = $DB->get_record ( 'course', $conditions )) {
					echo $OUTPUT->header ();
					print_err ( $OUTPUT, 'invalidlogin' );
					log_error ( $OUTPUT, $user, $course, 'course not found' );
					echo $OUTPUT->footer ();
					die ();
				} else {
					$conditions = array (
							"krs" => $mfgs_krs,
							"mfgs" => $mfgs_sid,
							"course_id" => $courseget->id 
					);
					
					if (! $meetings = $DB->get_record ( 'meetings', $conditions )) {
						$url = "$CFG->httpswwwroot/course/view.php?id={$courseget->id}";
					} else {
						$url = "$CFG->httpswwwroot/course/view.php?id={$courseget->id}&section={$meetings->section_num}";
					}
				}
			}
		}
	}
}

echo $OUTPUT->header ();
?>
	<form id="formLogin" name="formLogin" method="post"
		action="<?php echo $url; ?>">
		<input name="username" type="hidden" value="<?php echo $user; ?>" /> <input
			name="password" type="hidden" value="<?php echo $psw; ?>" />
	</form>
	<script type="text/javascript">
		window.document.forms["formLogin"].submit();
	</script>
<?php
echo $OUTPUT->footer ();
?>
</body>
</html>
</body>
</html>