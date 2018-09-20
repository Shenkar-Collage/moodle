<?php

/**
 * tabs.php - tabs used in admin interface.
 *
 * @package   plagiarism_originality
 * @original author    Dan Marsden
 * Updated by the Originality Group
 * Last update date: 2017-09-18
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}

$strplagiarism = get_string('originality_settings', 'plagiarism_originality');
$strplagiarisminfo = get_string('originality_info', 'plagiarism_originality');

$tabs = array();
$tabs[] = new tabobject('originalitysettings', 'settings.php', $strplagiarism, $strplagiarism, false);
$tabs[] = new tabobject('originalityinfo', 'originality_info.php', $strplagiarisminfo, $strplagiarisminfo, false);
print_tabs(array($tabs), $currenttab);

?>