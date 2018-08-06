<?php

/*
 * Originality Plagiarism Plugin
 * Reprocess and delete requests that were never completely processed.
 * If there are any records left in plagiarism_originality_req then have a page with the list and buttons to delete and resubmit.
 * Last update date: 2017-09-18
 *
 */

/*
Moodle 3.1.7 new file

Simply moved previous functionality from 3.1.6 to this file so now requests.php includes different files based on request.
*/

if (isset($_GET['delete'])){
    $requests = $DB->get_recordset_sql("select * from ".$CFG->prefix . "plagiarism_originality_req where id=".$_GET['delete']);
    if ($requests->current()){
        $userid = $requests->current()->userid;
        $assignmentid = $requests->current()->assignment;

        log_it("Deleting request record id=".$_GET['delete'] . " for assignment=$assignmentid and user=$userid");
        $DB->delete_records('plagiarism_originality_req',array("id"=>$_GET['delete'])); //delete any previous requests.
    }

}

if (isset($_GET['resubmit'])){
    $requests = $DB->get_recordset_sql("select * from ".$CFG->prefix . "plagiarism_originality_req where id=".$_GET['resubmit']);

    if ($requests->current()){
        $userid = $requests->current()->userid;
        $assignmentid = $requests->current()->assignment;

        log_it("Resubmitting request record id=".$_GET['resubmit']. " for assignment=$assignmentid and user=$userid");

        $courseid = get_course_id($assignmentid);


        $submissionid = get_submission_id($assignmentid, $userid);

        $lib = new plagiarism_plugin_originality();

        list($orig_server, $orig_key) = get_server_and_key();


        //https://docs.moodle.org/dev/Course_module
        $course = $DB->get_record('course', array('id' => $courseid));
        $info = get_fast_modinfo($course);
      //  print_object($info);

        $list = get_array_of_activities($courseid);
    // to see the structure
    //var_dump($list);exit;
        foreach($list as $k=>$v){
            if ($v->mod=='assign' && $v->id==$assignmentid) $cm=$v->cm;
        }


        $eventdata = array();
        $eventdata['contextinstanceid'] = $cm;  //the only thing this is used for is assignment number and I am passing that in directly.
        $eventdata['objectid'] = $submissionid[0];
        $eventdata['courseid']=$courseid;
        $eventdata['userid']=$userid;
        $eventdata['assignNum'] = $assignmentid;

        $USER = new stdClass();
        $USER->idnumber = $userid;

        $lib->originality_event_file_uploaded($eventdata);

    }
}

/***********************************************************************
 * PREPARE OUTPUT
 * *********************************************************************
 */


$output = "<div style='text-align: center;'><h1>Originality Requests that were not processed</h1>\n";

$output = "<a href='requests.php?clientkey=$input_clientkey&requesttype=1'>Refresh list</a>";

$requests = $DB->get_recordset_sql("select * from ".$CFG->prefix . "plagiarism_originality_req");

$headerrow = "<table  id='requestsTable' cellpadding='2'>\n
           <thead>\n
           <tr>\n
           <th>Assignment ID (Name)</th>\n
           <th>User ID (Name)</th>\n
           <th>Date Last Submitted</th>\n
           <th>Days Elapsed</th>\n
           <th>Delete</th>\n
           <th>Resubmit</th>\n
           </tr>\n
           </thead>\n
           <tbody>\n
";

$count=0;
$rows='';

foreach($requests as $req)
{
    list($date_modified, $days_elapsed) = get_assignment_info($req->assignment, $req->userid);
    $reqid = $req->id;
    $delete_button = "<a href='requests.php?clientkey=$input_clientkey&requesttype=1&delete=$reqid'>Delete</a>\n";
    $resubmit_button = "<a href='requests.php?clientkey=$input_clientkey&requesttype=1&resubmit=$reqid'>Resubmit Assignment</a>\n";
    $rows.= "<tr>\n".
    '<td>'.$req->assignment . ' ('.get_assignment_name($req->assignment).")</td>\n".
    '<td>'.$req->userid . ' ('.get_user_name($req->userid).")</td>\n".
    '<td>'.$date_modified."</td>\n".
    '<td>'.$days_elapsed."</td>\n".
    '<td>'.$delete_button."</td>\n".
    '<td>'.$resubmit_button."</td>\n".
    '</tr>';
    $count++;

}

$rows .= "</tbody>\n";

$output .= "<div>Records found: $count</div>" . $headerrow . $rows;

$output .= "</table>\n";

/*
 *
 * $cm = get_coursemodule_from_instance('assignment', $assignmentid, $courseid);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
echo "The context is: $context->id";

$lib = new plagiarism_plugin_originality();





 */



function get_course_id($assignmentid){
    global $DB, $CFG;
    $assignments= $DB->get_recordset_sql("select * from ".$CFG->prefix . "assign where id=$assignmentid");

    return $assignments->current()->course ?$assignments->current()->course : 0;
}

function get_assignment_info($assignment, $userid){
    global $DB,$CFG;
    $submissions = $DB->get_recordset_sql("select *,DATE_FORMAT(FROM_UNIXTIME(timemodified), '%e %b %Y') AS 'date_formatted', floor(((UNIX_TIMESTAMP()-timemodified) / 86400)) AS `time_diff` from ".$CFG->prefix . "assign_submission where assignment=$assignment and userid=$userid");

    return array($submissions->current()->date_formatted, $submissions->current()->time_diff);

}

function get_submission_id($assignmentid, $userid){
    global $DB,$CFG;
    $submissions = $DB->get_recordset_sql("select id from ".$CFG->prefix . "assign_submission where assignment=$assignmentid and userid=$userid");

    return array($submissions->current()->id);
}

function get_assignment_name($id){
    global $DB;
    $assignment =  $orig_key = $DB->get_record('assign', array('id'=>$id));
    return $assignment->name;
}

function get_user_name($id){
    global $DB;
    $user =  $orig_key = $DB->get_record('user', array('id'=>$id));
    return $user->firstname . ' ' . $user->lastname;
}


function originality_event_file_uploaded($eventdata)
{
    //error_log("in file upload");
    $result = true;
    global $DB,$CFG,$USER;

    $plagiarismvalues = $DB->get_records_menu('plagiarism_originality_conf', array('cm' =>  $eventdata['contextinstanceid']), '', 'id,cm');

    if (empty($plagiarismvalues)) {
        return $result;
    } else {
        list($orig_server, $orig_key) = $this->_get_server_and_key();
        $modulecontext = context_module::instance( $eventdata['contextinstanceid']);
        $fs = get_file_storage();

        if ($files = $fs->get_area_files($modulecontext->id, 'assignsubmission_file', 'submission_files', $eventdata['objectid'])) {
            foreach ($files as $file) {
                if ($file->get_filename() === '.')
                {
                    continue;
                }


                list($courseNum, $cmid, $courseID, $userID, $inst, $lectID, $courseCategory, $courseName, $senderIP, $facultyCode, $facultyName, $deptCode, $deptName, $check_file, $reserve2, $groupsize, $groupmembers, $idnumber, $assignNum) = $this->_get_params_for_file_submission($eventdata);

                $fileName = trim($file->get_filename());


                $fileName = preg_replace('!\s+!', '_', $fileName);
                $fileName = preg_replace("/[^א-תa-zA-Z0-9_\.]/", '', $fileName);
                $fileName = str_replace('א', '_', $fileName);

                $courseName = preg_replace('!\s+!', '_', $courseName);
                $courseName = preg_replace("/[^א-תa-zA-Z0-9_\.]/", '', $courseName);
                //  $courseName = str_replace('א', '_', $courseName);

                $deptName = preg_replace('!\s+!', '_', $deptName);
                $deptName = preg_replace("/[^א-תa-zA-Z0-9_\.]/", '', $deptName);
                //  $deptName = str_replace('א', '_', $deptName);

                $facultyName = preg_replace('!\s+!', '_', $facultyName);
                $facultyName = preg_replace("/[^א-תa-zA-Z0-9_\.]/", '', $facultyName);
                //  $facultyName = str_replace('א', '_', $facultyName);

                $courseCategory = preg_replace('!\s+!', '_', $courseCategory);
                $courseCategory = preg_replace("/[^א-תa-zA-Z0-9_\.]/", '', $courseCategory);
                //  $courseCategory = str_replace('א', '_', $courseCategory);

                $info = pathinfo($fileName);
                $fileName =  basename($fileName,'.'.$info['extension']);

                if (strlen($fileName) > 30)  $fileName = substr($fileName, 0, 30);
                $fileName = $fileName . '.' . $info['extension'];

                $fileName = urlencode($this->base64_url_encode($fileName));

                /*
                                $deptName = urlencode($this->base64_url_encode($deptName));
                                $courseName = urlencode($this->base64_url_encode($courseName));
                                $courseCategory = urlencode($this->base64_url_encode($courseCategory));
                                $groupmembers =  urlencode($this->base64_url_encode($groupmembers));
                                $facultyName = urlencode($this->base64_url_encode($facultyName));
                */

                $content = $file->get_content();

                //================================== PARAMS ADDED BY ORIGINALITY LTD =========================================
                if(!empty($orig_server->value) && !empty($orig_key->value)) {

                    $upload_result = $this->_do_curl_request($orig_server, $orig_key, $content, $fileName, $courseNum, $cmid, $courseID, $userID, $inst, $lectID, $courseCategory, $courseName, $senderIP, $facultyCode, $facultyName, $deptCode, $deptName, $check_file, $reserve2, $groupsize, $groupmembers, $idnumber, $assignNum);

                    if ($upload_result) $this->_update_request_and_response_db_records($assignNum, $userID, $fileName);
                    else $this->notify_customer_service_failed_file_upload();

                }
            }
        }
    }
    return $result;
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
            $('#requestsTable').DataTable();
        } );


    </script>
</head>
<body>
<?php
echo $output;
?>

</body>
</html>