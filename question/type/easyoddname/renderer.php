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
 * easyoddname question renderer class.
 *
 * @package    qtype
 * @subpackage easyoddname
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for easyoddname questions.
 *
 * @copyright  2014 onwards Carl LeBlond 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_easyoddname_renderer extends qtype_renderer {
    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
        global $CFG, $PAGE;

        $question = $qa->get_question();
        $currentanswer = $qa->get_last_qt_var('answer');
        $inputname = $qa->get_qt_field_name('answer');
        $inputattributes = array(
            'type' => 'text',
            'name' => $inputname,
            'value' => $currentanswer,
            'id' => $inputname,
            'size' => '80%',
        );

        $feedbackimg = '';

        if ($options->correctness) {
            $answer = $question->get_matching_answer(array('answer' => $currentanswer));
            if ($answer) {
                $fraction = $answer->fraction;
            } else {
                $fraction = 0;
            }
            $inputattributes['class'] = $this->feedback_class($fraction);
            $feedbackimg = $this->feedback_image($fraction);
        }

        $questiontext = $question->format_questiontext($qa);
        $placeholder = false;

        if (preg_match('/_____+/', $questiontext, $matches)) {
            $placeholder = $matches[0];
            $inputattributes['size'] = round(strlen($placeholder) * 1.1);
        }

        $result = '';

        $toreplaceid = 'applet'.$qa->get_slot();

        if ($options->readonly) {
            $inputattributes['readonly'] = 'readonly';
            $input = html_writer::empty_tag('input', $inputattributes) . $feedbackimg;
        } else {
            $input = "";
        }

        if ($placeholder) {

            $inputinplace = html_writer::tag('label', get_string('answer'),
                    array('for' => $inputattributes['id'], 'class' => 'accesshide'));
            $inputinplace .= $input;
            $questiontext = substr_replace($questiontext, $inputinplace,
                    strpos($questiontext, $placeholder), strlen($placeholder));

        }

        $result .= html_writer::tag('div', $questiontext, array('class' => 'qtext'));

        if (!$placeholder) {
            $answer = $question->get_correct_response();
            $coranswer = str_replace("|", "", $answer['answer']);
            $response = str_replace("|", "", $qa->get_last_qt_var('answer'));

            $result .= html_writer::start_tag('div', array('class' => 'ablock'));
            $result .= html_writer::tag('label', get_string('answer', 'qtype_shortanswer',
                    html_writer::tag('span', $input, array('class' => 'answer'))),
                    array('for' => $inputattributes['id']));
            $result .= html_writer::end_tag('div');
        }

        if ($qa->get_state() == question_state::$invalid) {
            $lastresponse = $this->get_last_response($qa);
            $result .= html_writer::nonempty_tag('div',
                                                $question->get_validation_error($lastresponse),
                                                array('class' => 'validationerror'));
        }

        if ($options->readonly) {
            $currentanswer = $qa->get_last_qt_var('answer');
        }

        $result .= html_writer::tag('div',
                                    $this->hidden_fields($qa),
                                    array('class' => 'inputcontrol'));

        if (!$options->readonly) {

            $result .= html_writer::start_tag('div', array('id' => 'play'.$qa->get_slot()));
            $result .= html_writer::start_tag('div',
                array('id' => $qa->get_slot().'answerdiv', 'class' => 'answerdiv'.$qa->get_slot()));
            $result .= html_writer::tag('p', get_string('draghere', 'qtype_easyoddname'));
            $result .= html_writer::tag('ul', '', array('class' => 'dropable', 'id' => 'list1'.$qa->get_slot()));

            $trashpixurl = $CFG->wwwroot."/question/type/easyoddname/pix/trash.png";
            $trashhtml = html_writer::empty_tag('img',
                array('id' => 'trashcan', 'class' => 'dropable', 'src' => $trashpixurl, 'alt' => 'trash'));
            $result .= html_writer::tag('ul', $trashhtml, array('id' => 'trash'));
            $result .= html_writer::end_tag('div');  // End answerdiv!

            $result .= html_writer::tag('p', get_string('parenthydrocarbon', 'qtype_easyoddname'));
            $result .= html_writer::start_tag('ul', array('class' => 'dragable', 'id' => 'list2'.$qa->get_slot()));
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
            $result .= html_writer::start_tag('ul', array('class' => 'dragable', 'id' => 'list3'.$qa->get_slot()));
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
            $result .= html_writer::start_tag('ul', array('class' => 'dragable', 'id' => 'list4'.$qa->get_slot()));
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
            $result .= html_writer::start_tag('ul', array('class' => 'dragable', 'id' => 'list5'.$qa->get_slot()));
            $result .= html_writer::tag('li', 'di' , array('class' => 'list2'));
            $result .= html_writer::tag('li', 'tri' , array('class' => 'list2'));
            $result .= html_writer::tag('li', 'tetra' , array('class' => 'list2'));
            $result .= html_writer::tag('li', 'penta' , array('class' => 'list2'));
            $result .= html_writer::tag('li', 'hexa' , array('class' => 'list2'));
            $result .= html_writer::tag('li', 'cyclo' , array('class' => 'list2'));
            $result .= html_writer::end_tag('ul');

            $result .= html_writer::start_tag('ul', array('class' => 'dragable', 'id' => 'list6'.$qa->get_slot()));
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

            $result .= html_writer::start_tag('ul', array('class' => 'dragable', 'id' => 'list7'.$qa->get_slot()));
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
        }

        $this->page->requires->js_init_call('M.qtype_easyoddname.dragndrop', array($qa->get_slot()));
        $this->require_js($qa, $options->readonly, $options->correctness);
        return $result;
    }

    protected function require_js(question_attempt $qa, $readonly, $correctness) {
        global $PAGE;

        $jsmodule = array(
            'name'     => 'qtype_easyoddname',
            'fullpath' => '/question/type/easyoddname/module.js',
            'requires' => array(),
            'strings' => array(
                array('enablejava', 'qtype_easyoddname')
            )
        );
        $topnode = 'div.que.easyoddname#q'.$qa->get_slot();

        if ($correctness) {
            $feedbackimage = $this->feedback_image($this->fraction_for_last_response($qa));
        } else {
            $feedbackimage = '';
        }

        $strippedanswerid = "stripped_answer".$qa->get_slot();
        $PAGE->requires->js_init_call('M.qtype_easyoddname.insert_easyoddname_applet',
                                      array($topnode,
                                            $feedbackimage,
                                            $readonly,
                                            $strippedanswerid,
                                            $qa->get_slot()),
                                            false,
                                            $jsmodule);
    }

    protected function fraction_for_last_response(question_attempt $qa) {
        $question = $qa->get_question();
        $lastresponse = $this->get_last_response($qa);
        $answer = $question->get_matching_answer($lastresponse);
        if ($answer) {
            $fraction = $answer->fraction;
        } else {
            $fraction = 0;
        }
        return $fraction;
    }


    protected function get_last_response(question_attempt $qa) {
        $question = $qa->get_question();
        $responsefields = array_keys($question->get_expected_data());
        $response = array();
        foreach ($responsefields as $responsefield) {
            $response[$responsefield] = $qa->get_last_qt_var($responsefield);
        }
        return $response;
    }

    public function specific_feedback(question_attempt $qa) {
        $question = $qa->get_question();

        $answer = $question->get_matching_answer($this->get_last_response($qa));
        if (!$answer) {
            return '';
        }

        $feedback = '';
        if ($answer->feedback) {
            $feedback .= $question->format_text($answer->feedback, $answer->feedbackformat,
                    $qa, 'question', 'answerfeedback', $answer->id);
        }
        return $feedback;
    }

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();

        $answer = $question->get_matching_answer($question->get_correct_response());

        if (!$answer) {
            return '';
        }

        return get_string('correctansweris', 'qtype_easyoddname', s($answer->answer));
    }

    protected function hidden_fields(question_attempt $qa) {
        $question = $qa->get_question();

        $hiddenfieldshtml = '';
        $inputids = new stdClass();
        $responsefields = array_keys($question->get_expected_data());
        foreach ($responsefields as $responsefield) {
            $hiddenfieldshtml .= $this->hidden_field_for_qt_var($qa, $responsefield);
        }
        return $hiddenfieldshtml;
    }
    protected function hidden_field_for_qt_var(question_attempt $qa, $varname) {
        $value = $qa->get_last_qt_var($varname, '');
        $fieldname = $qa->get_qt_field_name($varname);
        $attributes = array('type' => 'hidden',
                            'id' => str_replace(':', '_', $fieldname),
                            'class' => $varname,
                            'name' => $fieldname,
                            'value' => $value);
        return html_writer::empty_tag('input', $attributes);
    }
}
