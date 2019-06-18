<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
* Originality Plagiarism Plugin
* New PHP file starting with  Plugin Version 4.0.0
* List Log files in orginality_logs directory in the moodledata folder
*/

// @codingStandardsIgnoreLine
require_once("../../config.php");
require_once($CFG->dirroot. '/course/lib.php');
require_once($CFG->libdir. '/coursecatlib.php');

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

global $CFG;

$logsdir = $CFG->dataroot . '/originality_logs';

$list = array();

foreach (new DirectoryIterator($logsdir) as $fileinfo) {
    if ($fileinfo->isDot()) {
        continue;
    }
    $link = $CFG->wwwroot .'/plagiarism/originality/show_log.php?file=' . $fileinfo->getFilename();
    $list[$fileinfo->getMTime()] = "<a href='$link' target='_blank'>".$fileinfo->getFilename() . "</a><br><br />\n";
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
foreach ($list as $filetime => $file) {
    echo $file;
}
?>

</body>
</html>