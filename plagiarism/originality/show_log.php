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
 * Originality Plugin
 * New file for plugin version 4.0.0
 * Helper function to display originality logs now saved in moodledata
 * Last update date: 2017-10-09
 */

global $CFG, $PAGE, $USER;

// @codingStandardsIgnoreLine
require_once(dirname(dirname(__FILE__)) . '/../config.php');

$dataroot = $CFG->dataroot;

$file = $dataroot . '/originality_logs/' . $_GET['file'];

header('Content-type: text/plain');
header("Content-Disposition: inline; filename=$file");
readfile($file);

exit;
