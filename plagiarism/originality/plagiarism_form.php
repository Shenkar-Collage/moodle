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
 * @since       2.0
 * @Author     The Originality Group
 * @package    plagiarism_originality
 * @subpackage plagiarism
 * Last update date: 2017-09-18
 */


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot . '/lib/formslib.php');

class plagiarism_setup_form extends moodleform {
    // Define the form.
    public function definition() {
        global $CFG;

        $mform = & $this->_form;
        $choices = array('No', 'Yes');
        $mform->addElement('html', get_string('originalityexplain', 'plagiarism_originality'));
        $mform->addElement('checkbox', 'originality_use', get_string('useoriginality', 'plagiarism_originality'));

        if ($CFG->debugdeveloper){
            $mform->addElement('text', 'originality_server', get_string('originality_server', 'plagiarism_originality'), array('size'=>'30'));
            $mform->setType('originality_server', PARAM_NOTAGS);
        }

        $mform->addElement('text',
                           'originality_key',
                           get_string('originality_key', 'plagiarism_originality'),
                           array('size' => '30'));
        $mform->setType('originality_key', PARAM_NOTAGS);
        $mform->addHelpButton('originality_key', 'originalitykey', 'plagiarism_originality');
        $mform->addRule('originality_key', null, 'required', null, 'client');

        /*
         Uncomment this when actually want to use this feature of allowing admin to make a student viewing a report an option
         $mform->addElement('checkbox', 'originality_view_report', get_string('originality_view_report', 'plagiarism_originality'));
         */

        $mform->addElement('hidden', 'originality_view_report', '0');
        $mform->setType('originality_view_report', PARAM_NOTAGS);

        $this->add_action_buttons(false);
    }
}


class plagiarism_upgrade_form extends moodleform {
    // Define the form.
    public function definition() {
        global $CFG;

        $mform = & $this->_form;
        $mform->addElement('html', get_string('originality_new_version_available', 'plagiarism_originality'));
        $this->add_action_buttons(false, 'Upgrade Now');
    }
}
