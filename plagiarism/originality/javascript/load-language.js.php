<?php

require_once '../../../config.php';
global $CFG;


require_once($CFG->dirroot . '/plagiarism/lib.php');
require_once($CFG->dirroot . '/plagiarism/originality/locallib.php');


$current_language = current_language();

echo "current_language='". $current_language. "';";


?>