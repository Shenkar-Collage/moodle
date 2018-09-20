<?php
/**
 * @since       2.0
 * @Author     The Originality Group
 * @package    plagiarism_originality
 * @subpackage plagiarism
 * Last update date: 2017-09-18
 */

require_once($CFG->dirroot . '/lib/formslib.php');

class plagiarism_setup_form extends moodleform
{
	/// Define the form
	function definition()
	{
		global $CFG;

		$mform =& $this->_form;
		$choices = array('No', 'Yes');
		$mform->addElement('html', get_string('originalityexplain', 'plagiarism_originality'));
		$mform->addElement('checkbox', 'originality_use', get_string('useoriginality', 'plagiarism_originality'));

		$mform->addElement('text', 'originality_key', get_string('originality_key', 'plagiarism_originality'), array('size'=>'30'));
        $mform->setType('originality_key', PARAM_NOTAGS);
		$mform->addHelpButton('originality_key', 'originalitykey', 'plagiarism_originality');
		$mform->addRule('originality_key', null, 'required', null, 'client');

		$mform->addElement('text', 'originality_server', get_string('originality_server', 'plagiarism_originality'), array('size'=>'30'));
        $mform->setType('originality_server', PARAM_NOTAGS);
        $mform->setDefault('originality_server', get_string('originality_server_default_value', 'plagiarism_originality'));
		$mform->addHelpButton('originality_server', 'originalityserver', 'plagiarism_originality');
		$mform->addRule('originality_server', null, 'required', null, 'client');

        /*
         * This setting is for whether on the edit assignment screen a teacher can choose both file and online text submissions, or the interface
         * would only allow choosing one type of submission.
         * It goes with file_identifier for next version (probably 3.1.8)
         */
        //$mform->addElement('checkbox', 'originality_allow_mutiple_file_submission', get_string('originality_allow_multiple_file_submission', 'plagiarism_originality'));

        //uncomment this when actually want to use this feature of allowing admin to make a student viewing a report an option
//        $mform->addElement('checkbox', 'originality_view_report', get_string('originality_view_report', 'plagiarism_originality'));

        $mform->addElement('hidden', 'originality_view_report', '0');
        $mform->setType('originality_view_report', PARAM_NOTAGS);

		$this->add_action_buttons(false);
	}
}

//PARAM_NOTAGS setType

?>