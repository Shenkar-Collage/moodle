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
 * Defines the editing form for the easyoddname question type.
 *
 * @package    qtype
 * @subpackage easyoddname
 * @copyright  2014 onwards Carl LeBlond 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/shortanswer/edit_shortanswer_form.php');

class qtype_easyoddname_edit_form extends qtype_shortanswer_edit_form {

    protected function definition_inner($mform) {
        global $PAGE, $CFG, $question, $DB;
        $PAGE->requires->js('/question/type/easyoddname/easyoddname_script.js');
        $PAGE->requires->css('/question/type/easyoddname/styles.css');
        if (isset($question->id)) {
                $record = $DB->get_record('question_easyoddname', array('question' => $question->id ));
        }
        $mform->addElement('static', 'answersinstruct',
        get_string('correctanswers', 'qtype_easyoddname'),
        get_string('filloutoneanswer', 'qtype_easyoddname'));
        $mform->closeHeaderBefore('answersinstruct');


        $result = html_writer::start_tag('div', array('id' => 'play'));
        $result .= html_writer::start_tag('div',
            array('id' => 'answerdiv', 'class' => 'answerdiv'));
        $result .= html_writer::tag('p', get_string('draghere', 'qtype_easyoddname'));
        $result .= html_writer::tag('ul', '', array('class' => 'dropable', 'id' => 'list1'));

        $trashpixurl = $CFG->wwwroot."/question/type/easyoddname/pix/trash.png";
        $trashhtml = html_writer::empty_tag('img',
            array('id' => 'trashcan', 'class' => 'dropable', 'src' => $trashpixurl, 'alt' => 'trash'));
        $result .= html_writer::tag('ul', $trashhtml, array('id' => 'trash'));
        $result .= html_writer::end_tag('div');  // End answerdiv!

        $result .= html_writer::tag('p', get_string('parenthydrocarbon', 'qtype_easyoddname'));
        $result .= html_writer::start_tag('ul', array('class' => 'dragable', 'id' => 'list2'));
        $result .= html_writer::tag('li', 'meth' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'eth' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'prop' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'but' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'pent' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'hex' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'hept' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'oct' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'non' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'dec' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'benzene' , array('class' => 'list2'));
        $result .= html_writer::end_tag('ul');

        $result .= html_writer::tag('p', get_string('parentfunctgroup', 'qtype_easyoddname'));
        $result .= html_writer::start_tag('ul', array('class' => 'dragable', 'id' => 'list3'));
        $result .= html_writer::tag('li', 'ane' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'an' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'ene' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'en' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'yne' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'yn' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'ol' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'al' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'oic acid' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'one' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'ate' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'amide' , array('class' => 'list2'));
        $result .= html_writer::end_tag('ul');

        $result .= html_writer::tag('p', get_string('subsgroups', 'qtype_easyoddname'));
        $result .= html_writer::start_tag('ul', array('class' => 'dragable', 'id' => 'list3'));
        $result .= html_writer::tag('li', 'methyl' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'ethyl' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'propyl' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'butyl' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'pentyl' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'hexyl' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'heptyl' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'octyl' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'nonyl' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'decyl' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'fluoro' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'chloro' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'bromo' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'iodo' , array('class' => 'list2'));
        $result .= html_writer::end_tag('ul');

        $result .= html_writer::tag('p', get_string('prefixes', 'qtype_easyoddname'));
        $result .= html_writer::start_tag('ul', array('class' => 'dragable', 'id' => 'list3'));
        $result .= html_writer::tag('li', 'di' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'tri' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'tetra' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'penta' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'hexa' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'cyclo' , array('class' => 'list2'));
        $result .= html_writer::end_tag('ul');

        $result .= html_writer::start_tag('ul', array('class' => 'dragable', 'id' => 'list3'));
        $result .= html_writer::tag('li', 'N' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '1' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '2' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '3' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '4' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '5' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '6' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '7' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '8' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '9' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '10' , array('class' => 'list2'));
        $result .= html_writer::end_tag('ul');

        $result .= html_writer::start_tag('ul', array('class' => 'dragable', 'id' => 'list3'));
        $result .= html_writer::tag('li', 'R' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'S' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'E' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'Z' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'o' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'm' , array('class' => 'list2'));
        $result .= html_writer::tag('li', 'p' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '&nbsp;(&nbsp;' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '&nbsp;)&nbsp;' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '&nbsp;-&nbsp;' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '&nbsp;,&nbsp;' , array('class' => 'list2'));
        $result .= html_writer::tag('li', '&nbsp;&nbsp;' , array('class' => 'list2'));
        $result .= html_writer::end_tag('ul');
        $result .= html_writer::end_tag('div');  // End play div!

//        $temp = file_get_contents('type/easyoddname/dragable.html');
//        $temp = str_replace("slot", "", $temp);
//        $easyoddnamebuildstring = $temp;
        $mform->addElement('html', $result);
//        $mform->addElement('html', $easyoddnamebuildstring);




                        $jsmodule = array(
                            'name'     => 'qtype_easyoddname',
                            'fullpath' => '/question/type/easyoddname/easyoddname_script.js',
                            'requires' => array(),
                            'strings' => array(
                                array('enablejava', 'qtype_easyoddname')
                            )
                        );

        $htmlid = 1;
//        $module = array('name' => 'easyoddname',
//        'fullpath' => '/question/type/easyoddname/module.js', 'requires' => array('yui2-treeview'));

        $url = $CFG->wwwroot . '/question/type/easyoddname/template_update.php?numofstereo=';
        $PAGE->requires->js_init_call('M.qtype_easyoddname.dragndrop', array($url, $htmlid),
                                      true,
                                      $jsmodule);

        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_easyoddname', '{no}'),
                question_bank::fraction_options());
        $this->add_interactive_settings();
        $PAGE->requires->js_init_call('M.qtype_easyoddname.init_getanswerstring', array($CFG->version), true, $jsmodule);
    }

    protected function get_per_answer_fields($mform, $label, $gradeoptions,
            &$repeatedoptions, &$answersoption) {
        $repeated = parent::get_per_answer_fields($mform, $label, $gradeoptions,
        $repeatedoptions, $answersoption);
        $scriptattrs = 'class = id_insert';
        $insertbutton = $mform->createElement('button', 'insert', get_string('insertfromeditor',
        'qtype_easyoddname'), $scriptattrs);

        array_splice($repeated, 2, 0, array($insertbutton));

        return $repeated;
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        return $question;
    }

    public function qtype() {
        return 'easyoddname';
    }
}
