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
 * Strings for component 'qtype_easyoddname', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    qtype
 * @subpackage easyoddname
 * @copyright  2014 onwards Carl LeBlond 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['parenthydrocarbon'] = "Parent Hydrocarbon Chain";
$string['parentfunctgroup'] = "Parent Functional Group";
$string['subsgroups'] = "Substituents/Groups";
$string['prefixes'] = "Prefixes";
$string['draghere'] = "<strong>Drag and Drop Groups into this Box</strong>";

$string['easyoddname'] = "Drag and Drop Chem Nomenclature";
$string['caseconformtrue'] = 'True';
$string['caseconformfalse'] = 'False';
$string['caseconformimportant'] = '<b>Conformation Important</b><br/>Is the conformation important?  If "True" then a specific conformation must be draw.  (e.g. Draw butane in its gauche conformation)';


$string['staggered'] = 'Staggered';
$string['eclipsed'] = 'Eclipsed';
$string['casestagoreclip'] = '<b>Staggered/Eclipsed</b><br/>Do you want a Staggered or Eclipsed template problem?';


$string['caseorienttrue'] = 'True';
$string['caseorientfalse'] = 'False';
$string['caseorientimportant'] = '<b>Perspective Important?:</b><br/>Is the perspective important?  If "True" then the student must draw the molecule from a certain perspective! (e.g Draw 2-methylbutane looking up the C2-C3 bond?';



$string['addmoreanswerblanks'] = 'Blanks for {no} More Answers';
$string['answermustbegiven'] = 'You must enter an answer if there is a grade or feedback.';
$string['answerno'] = 'Answer {$a}';
$string['pluginname'] = 'Drag and Drop Naming';
$string['pluginname_help'] = 'Draw a Fischer projection below.  Choose whether you want to display eclipsed or staggered.  Then choose whether the conformation or the view perspective is important in grading.  You can ask questions like;<br><ul>Draw (r)-2-butanol looking up the C2-C3 bond?</ul>';
$string['pluginname_link'] = 'question/type/easyoddname';
$string['pluginnameadding'] = 'Adding a Drag and Drop Naming question';
$string['pluginnameediting'] = 'Editing a Drag and Drop Naming question';
$string['pluginnamesummary'] = 'Student must construct names of molecules by dragging and dropping parent and substituents into answer field.';
$string['easyoddname_options'] = 'Marvin Applet options';
$string['enablejava'] = 'Tried but failed to load javascript. You have not got a JAVA runtime environment working in your browser. You will need one to attempt this question.';
$string['enablejavaandjavascript'] = 'Loading  editor.... If this message does not get replaced by the editor then you have not got javascript working in your browser.';
$string['configeasyoddnameoptions'] = '';
$string['filloutoneanswer'] = '<b><ul>
<li>Insert your question text and a structure above.</li>
<li>Drag and drop groups below into the green box to form the name of your structure.</li>
<li>Press the "Insert from editor" button to insert the code into the answer box.</li>
</ul></b>';
$string['filloutanswers'] = 'Make sure you choose the correct options above then drag your groups on the Fischer projection template.  Press the "Insert from editor" buttons to insert the answer into the answer boxes';
$string['insertfromeditor'] = 'Insert from editor';
$string['javaneeded'] = 'To use this page you need a Java-enabled browser. Download the latest Java plug-in from {$a}.';
$string['instructions'] = '';
$string['answer'] = 'Answer: {$a}';
$string['youranswer'] = 'Your answer: {$a}';
$string['correctansweris'] = 'The correct answer is: {$a}.';
$string['correctanswers'] = '<b>Instructions</b>';
$string['notenoughanswers'] = 'This type of question requires at least {$a} answers';
$string['pleaseenterananswer'] = 'Please enter an answer.';
$string['easyoddnameeditor'] = 'EasyOChem Fischer Projection Editor';
$string['author'] = 'By Carl LeBlond';
$string['insert'] = 'Insert from editor';
