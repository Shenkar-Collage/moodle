<?php

/*
 * Originality Plugin 
 * New file for plugin version 3.1.7 
 * Helper function to display originality reports now saved in moodledata.
 * Last update date: 2017-09-18
 */


global $CFG, $PAGE, $USER;


require_once(dirname(dirname(__FILE__)) . '/../config.php');

require_login();


$data_root = $CFG->dataroot;

$file = $data_root . '/originality/' . $_GET['file'];

header('Content-type: application/pdf');
header("Content-Disposition: inline; filename=$file");
readfile($file);

exit;





?>