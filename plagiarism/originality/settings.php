<?php

/**
 * plagiarism.php - allows the admin to configure plagiarism stuff
 *
 * @package   plagiarism_originality
 * @author    Dan Marsden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Last update date: 2017-09-18
 */

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');
require_once($CFG->dirroot.'/plagiarism/originality/lib.php');
require_once($CFG->dirroot.'/plagiarism/originality/plagiarism_form.php');

require_login();
admin_externalpage_setup('manageplagiarismplugins');

$context = context_system::instance();
require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

require_once('plagiarism_form.php');

echo $OUTPUT->header();
$currenttab = 'originalitysettings';
require_once('tabs.php');

$mform = new plagiarism_setup_form();
$plagiarismplugin = new plagiarism_plugin_originality();

if ($mform->is_cancelled()) {
    redirect('');
}


if (($data = $mform->get_data()) && confirm_sesskey()) {
	if (!isset($data->originality_use)) {
		$data->originality_use = 0;
	}
    if (!isset($data->originality_view_report)) {
        $data->originality_view_report = 0;
    }
    if (!isset($data->originality_allow_mutiple_file_submission)) {
        $data->originality_allow_mutiple_file_submission = 0;
    }
    foreach ($data as $field=>$value) {
       if ($field=='originality_server')   $orig_server = $value;
    }
    /*
     * Version 3.1.7 Check permissions of moodledata files folder.
     * From now on storing the originality files there
     */
    $files_dir = $CFG->dataroot . '/originality';
    if (!file_exists($files_dir)){
        if (!mkdir($files_dir, 0755)){
            log_it("Error creating the originality directory in moodle data folder");
        }
    }else{
        if (0755 !== (fileperms($files_dir) & 0777)){
            chmod($files_dir, 0755);
        }
    }

	foreach ($data as $field=>$value) {
        if ($field=='originality_key'){  //check if key is valid
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "$orig_server/Api/validate/$value");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            $output = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);

            $output = strip_tags($output);

            if ($output=='true') echo $OUTPUT->notification(get_string('savedconfigsuccess', 'plagiarism_originality'), 'notifysuccess');
            else {
                echo $OUTPUT->notification(get_string('settings_key_error', 'plagiarism_originality'));
                log_it("Settings check key error");
            }
        }
            if (($field=='originality_key' && $output=='true') || ($field!='originality_key')){
                if (strpos($field, 'originality')===0) {
                    if ($tiiconfigfield = $DB->get_record('config_plugins', array('name'=>$field, 'plugin'=>'plagiarism'))) {
                        $tiiconfigfield->value = $value;

                        if (! $DB->update_record('config_plugins', $tiiconfigfield)) {
                            print_error("errorupdating");
                        }
                    } else {
                        $tiiconfigfield = new stdClass();
                        $tiiconfigfield->value = $value;
                        $tiiconfigfield->plugin = 'plagiarism';
                        $tiiconfigfield->name = $field;

                        if (! $DB->insert_record('config_plugins', $tiiconfigfield)) {
                            print_error("errorinserting");
                        }
                    }

                    //Clear Database Cache after insert/update plagiarism plugin data
                    cache_helper::purge_stores_used_by_definition('core','databasemeta');
                }//if strpos
        }//if field is originality key
    }//foreach

}


$plagiarismsettings = (array)get_config('plagiarism');

$mform->set_data($plagiarismsettings);

echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

?>