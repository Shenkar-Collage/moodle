<?php

/**
 * plagiarism.php - allows the admin to configure plagiarism stuff
 *
 * @package   plagiarism_originality
 * @original author    Dan Marsden
 * updates by the Originality Group
 * Last update date: 2017-09-18
 */

//Initialize $plugin object if it hasn't been already
$plugin = (isset($plugin) ? $plugin : new stdClass());

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');
require_once($CFG->dirroot.'/plagiarism/originality/lib.php');
require_once($CFG->dirroot.'/plagiarism/originality/plagiarism_form.php');
require_once($CFG->dirroot.'/plagiarism/originality/version.php');

require_login();
admin_externalpage_setup('manageplagiarismplugins');

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");


echo $OUTPUT->header();
$currenttab = 'originalityinfo';
require_once('tabs.php');

include_once 'version.php'; // plugin version

echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
echo "<div>";
echo "<div style='margin-bottom:10px;'><span style='font-weight:bold;'>Release:</span> ".$plugin->release . "</div>";
echo "<div><span style='font-weight:bold;'>Customer Service:</span> " . get_string("originality_customerservice", 'plagiarism_originality')."</div>";
echo "</div>";
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

?>