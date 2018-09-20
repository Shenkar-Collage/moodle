<?php

/*
* Originality Plagiarism Plugin
* New PHP file starting with  Plugin Version 4.0.0
* List Log files in orginality_logs directory in the moodledata folder
*/

require_once("../../config.php");
require_once($CFG->dirroot. '/course/lib.php');
require_once($CFG->libdir. '/coursecatlib.php');


global $CFG;

$logs_dir = $CFG->dataroot . '/originality_logs';

$list = array();

foreach (new DirectoryIterator($logs_dir) as $fileInfo) {
    if($fileInfo->isDot()) continue;
     $link = $CFG->wwwroot .'/plagiarism/originality/show_log.php?file=' . $fileInfo->getFilename();
     $list[$fileInfo->getMTime()] = "<a href='$link' target='_blank'>".$fileInfo->getFilename() . "</a><br><br />\n";
}

krsort($list);




?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
<h1>Originality Log Files</h1>
<?php
foreach ($list as $filetime=>$file){
    echo $file;
}
?>

</body>
</html>