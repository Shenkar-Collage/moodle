<?php

/*
* Originality Plagiarism Plugin
* New PHP file starting with  Plugin Version 3.1.7
* List courses: ID, Course Name, Category, Subcategory, Teacher, Teacher email and download as csv
* Required for remote telemetry on demand
* Last update date: 2017-09-18
*/

require_once("../../config.php");
require_once($CFG->dirroot. '/course/lib.php');
require_once($CFG->libdir. '/coursecatlib.php');


$courses = get_courses();

$fieldnames = array('Course ID', 'Shortname', 'Fullname', 'Date Start', 'Date End', 'Category', 'Parent Category', 'Teachers');

$lines = array();

$lines[0] = $fieldnames;

$i = 1;

foreach($courses as $course)
{
    $courserecord = $DB->get_record('course', array('id' => $course->id), '*', MUST_EXIST);

    $tmpcourse = new course_in_list($courserecord);

    $contacts = $tmpcourse->get_course_contacts();

    $teacherinfo = '';

    foreach($contacts as $userid=>$contact){
        $rolename = $contact['rolename'];
        $name = $contact["user"]->firstname . ' ' . $contact["user"]->lastname;
        $user = $DB->get_record('user', array('id' => $userid));
        $email = $user->email;
        $teacherinfo .= ("$rolename: $name ($email)\n");
    }

    $cat = coursecat::get($course->category);
    $cat2 = coursecat::get($cat->parent);

    $lines[$i] = array($course->id, $course->shortname, $course->fullname, date("F j, Y", $course->startdate), date("F j, Y", $course->enddate), $cat->name, $cat2->name, $teacherinfo);

    $i++;
}

if (isset($_GET['csv']) and $_GET['csv'] == 1){

    $url_data = parse_url($_SERVER['SERVER_NAME']);

    $host = $url_data['path'];

    $filename = $host . '_MoodleCourses_as_of_' . date("Y_m_d", time()) . '.csv';


    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=$filename");

    $output = fopen('php://output', 'w');

    foreach($lines as $line){
        fputcsv($output, $line);
    }
    exit;
}




$output = "<div style='text-align: center;'><h1>Courses</h1>\n";

$output = "<a href='$_SERVER[REQUEST_URI]&csv=1'>Download as csv</a><br /><br />\n";

$headerrow = "<table  id='coursesTable' cellpadding='2'>\n
           <thead>\n
           \n";

$i = 0;

foreach($lines as $line){
    if ($i==0){
        $headerrow .= "<tr>\n";
        foreach($line as $l){
            $headerrow .= "<th>$l</th>";
        }
        $headerrow .= "</tr></thead><tbody>\n";
    }
    else{
        $headerrow .= "<tr>\n";
        foreach($line as $l){
            $headerrow .= "<td>".nl2br($l)."</td>";
        }
        $headerrow .= "</tr>\n";
    }
    $i++;
}

$output .= $headerrow;

$output .= "</tbody></table>\n";


function get_course_id($assignmentid){
    global $DB, $CFG;
    $assignments= $DB->get_recordset_sql("select * from ".$CFG->prefix . "assign where id=$assignmentid");

    return $assignments->current()->course ?$assignments->current()->course : 0;
}



function get_user_name($id){
    global $DB;
    $user =  $orig_key = $DB->get_record('user', array('id'=>$id));
    return $user->firstname . ' ' . $user->lastname;
}





?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css">
    <script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.12.4.js">
    </script>
    <script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js">
    </script>
    <script type="text/javascript" class="init">


        $(document).ready(function() {
            $('#coursesTable').DataTable();
        } );


    </script>
</head>
<body>
<?php
echo $output;
?>

</body>
</html>