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

/**
 * tabs.php - tabs used in admin interface.
 *
 * @package   plagiarism_originality
 * @original author    Dan Marsden
 * Updated by the Originality Group
 * Last update date: 2017-09-18
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

$plugin = (isset($plugin) ? $plugin : new stdClass());

require_once($CFG->dirroot.'/plagiarism/originality/version.php');

$strplagiarism = get_string('originality_settings', 'plagiarism_originality');
$strplagiarisminfo = get_string('originality_info', 'plagiarism_originality');
$strplagiarismupgrade = get_string('originality_upgrade', 'plagiarism_originality');

$origkey = $DB->get_record('config_plugins', array('name' => 'originality_key', 'plugin' => 'plagiarism'));
$origserver = $DB->get_record('config_plugins', array('name' => 'originality_server', 'plugin' => 'plagiarism'));

$tabs = array();
$tabs[] = new tabobject('originalitysettings', 'settings.php', $strplagiarism, $strplagiarism, false);
$tabs[] = new tabobject('originalityinfo', 'originality_info.php', $strplagiarisminfo, $strplagiarisminfo, false);

// Check if the newest version is a higher version than what is installed.

$currentversion = $plugin->release;

$versionavailable = get_latest_version_number($origserver, $origkey);   $versionavailable = '4.0.6';


print_tabs(array($tabs), $currenttab);

