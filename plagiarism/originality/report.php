<?php
/**
 * Author: The Originality Group
 * Input: Assignment ID, User ID, Report File, Grade
 * In the future a File ID wil be added, to support multi-file submission per assignment
 * Stores the originality result in the database and in Moodledata
 * Last update date: 2017-09-18
 */


function base64_url_decode($input) {
	return base64_decode(strtr($input, '-_,', '+/='));
}

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

global $DB, $CFG;


//verify credentials

$secret_token = $_POST['Reserve-1'];

//$resp = $DB->get_record_select('config _plugins', "name='originality_key'",null,'*',IGNORE_MULTIPLE);


$plagiarismsettings = (array)get_config('plagiarism');

if (!empty($plagiarismsettings['originality_key']))
    $client_key = $plagiarismsettings['originality_key'];

if ($client_key){
  if($client_key != $secret_token){
     log_it("Secret token does not match database client key.");
     header('HTTP/1.1 403 Forbidden');
     exit;
   }
}else{
    log_it("No originality key in database");
    header('HTTP/1.1 403 Forbidden');
    exit;
}


if (1) {
	$assID = $_POST["assignmentID"];
	$userID = $_POST["userID"];
	$grade = round($_POST["grade"]);
	$fileName = $assID."_".$userID."_".$_POST["fileName"];
    /*
     * Moodle for next version (3.1.8 probably) adding unique file identifier to each file submitted within an assignment submission so that we can use both online text and file within a submission
     */
    //$fileIdentifier = $_POST['fileIdentifier'];

    log_it("Report received: Assignment ID: $assID,  User ID: $userID, Grade: $grade, File: $fileName"); //, File Identifier: $fileIdentifier");

    //	$content = base64_url_decode(base64_decode($_POST["content"]));
    $content = base64_decode($_POST["content"]);

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

    if (!is_dir($files_dir.'/'.$assID)) {
        if (!mkdir($files_dir.'/'.$assID, 0755)){
            log_it("Error creating the $assID subdirectory in moodle data folder originality directory");
        }
    }

	if (strlen($content) > 0) file_put_contents($files_dir.'/'.$assID."/".$fileName, $content);

    chmod($files_dir.'/'.$assID."/".$fileName, 0644);

	$newelement = new stdClass();
	$newelement->assignment = $assID;
	$newelement->userid= $userID;
	$newelement->grade = $grade;
	$newelement->file = $fileName;
    /*
 * Moodle for next version (3.1.8 probably) adding unique file identifier to each file submitted within an assignment submission so that we can use both online text and file within a submission
 */
   // $newelement->fileidentifier = $fileIdentifier;

	// delete before insert

	$DB->delete_records('plagiarism_originality_resp',array("assignment"=>$assID,'userid'=> $userID)); //, "file_identifier"=>$fileIdentifier));
    $DB->delete_records('plagiarism_originality_req',array("assignment"=>$assID,'userid'=> $userID)); //, "file_identifier"=>$fileIdentifier));

	$DB->insert_record('plagiarism_originality_resp', $newelement);

}
?>