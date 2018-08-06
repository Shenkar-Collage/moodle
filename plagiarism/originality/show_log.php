<?php

/*
 * Originality Plugin 
 * New file for plugin version 4.0.0
 * Helper function to display originality logs now saved in moodledata
 * Last update date: 2017-10-09
 */


global $CFG, $PAGE, $USER;


require_once(dirname(dirname(__FILE__)) . '/../config.php');

$data_root = $CFG->dataroot;

$file = $data_root . '/originality_logs/' . $_GET['file'];

header('Content-type: text/plain');
header("Content-Disposition: inline; filename=$file");
readfile($file);

exit;





?>