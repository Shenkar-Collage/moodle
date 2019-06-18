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
 * Author: The Originality Group
 * Input: Assignment ID, User ID, Report File, Grade
 * In the future a File ID wil be added, to support multi-file submission per assignment
 * Stores the originality result in the database and in Moodledata
 * Last update date: 2017-09-18
 */

// @codingStandardsIgnoreLine
require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

global $DB, $CFG;


// Verify credentials.

$secrettoken = $_POST['clientKey'];

$plagiarismsettings = (array)get_config('plagiarism');

if (!empty($plagiarismsettings['originality_key'])) {
    $clientkey = $plagiarismsettings['originality_key'];
}

if ($clientkey) {
    if ($clientkey != $secrettoken) {
        log_it("Secret token does not match database client key.");
        header('HTTP/1.1 403 Forbidden');
        exit;
    }
} else {
    log_it("No originality key in database");
    header('HTTP/1.1 403 Forbidden');
    exit;
}

if (1) {
    $assid = $_POST["assignmentID"];
    $userid = $_POST["userID"];
    $grade = round($_POST["grade"]);
    $fileIdentifier = $_POST['docSequence'];

    $fileidentifierexists = $DB->get_record_select('plagiarism_originality_req', 'file_identifier = ? and userid = ? and assignment = ?', array($fileIdentifier, $userid, $assid));

    // If there is no request record for the unique file identifier, it means it was deleted b.c. the student already resubmitted. So just disregard the report.
    if (!$fileidentifierexists) {
        log_it("Report received: Assignment ID: $assid,  User ID: $userid, Grade: $grade, File Identifier: $fileIdentifier");
        log_it("There is no matching request for this report, so exiting the script");
        exit;
    }

    $moodlefileid = $fileidentifierexists->moodle_file_id;

    $filename = $assid . "_" . $userid . "_" . $moodlefileid . "_" . $_POST["fileName"];

    log_it("Report received: Assignment ID: $assid,  User ID: $userid, Grade: $grade, File: $filename, File Identifier: $fileIdentifier");

    $content = base64_decode($_POST["content"]);

    /*
     * Version 3.1.7 Check permissions of moodledata files folder.
     * From now on storing the originality files there
     */
    $filesdir = $CFG->dataroot . '/originality';
    if (!file_exists($filesdir)) {
        if (!mkdir($filesdir, 0755)) {
            log_it("Error creating the originality directory in moodle data folder");
        }
    } else {
        if (0755 !== (fileperms($filesdir) & 0777)) {
            chmod($filesdir, 0755);
        }
    }

    if (!is_dir($filesdir.'/'.$assid)) {
        if (!mkdir($filesdir.'/'.$assid, 0755)) {
            log_it("Error creating the $assid subdirectory in moodle data folder originality directory");
        }
    }

    if (strlen($content) > 0) {
        file_put_contents($filesdir.'/'.$assid."/".$filename, $content);
    }

    chmod($filesdir.'/'.$assid."/".$filename, 0644);

    $newelement = new stdClass();
    $newelement->assignment = $assid;
    $newelement->userid = $userid;
    $newelement->grade = $grade;
    $newelement->file = $filename;
    $newelement->file_identifier = $fileIdentifier;
    $newelement->moodle_file_id = $moodlefileid;
    /*
     * Moodle for next version (3.1.8 probably) adding unique file identifier to each file submitted
     * within an assignment submission so that we can use both online text and file within a submission
     */
   // ...$newelement->fileidentifier = $fileIdentifier;.

    // Delete before insert.
    $DB->delete_records('plagiarism_originality_resp',
                         array("assignment" => $assid, 'userid' => $userid, "file_identifier"=>$fileIdentifier));
    $DB->delete_records('plagiarism_originality_req',
                         array("assignment" => $assid, 'userid' => $userid, "file_identifier"=>$fileIdentifier));
    $DB->insert_record('plagiarism_originality_resp', $newelement);
}

