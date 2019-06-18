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

// @codingStandardsIgnoreLine
/*
 * Functionality for various requests.
 *
 * Input are GET parameters, clientkey and requesttype.
 *
 * Type1:  Reprocess and delete requests that were never completely processed.
 *
 *  If there are any records left in plagiarism_originality_req then have a page
 *  with the list and buttons to delete and resubmit.
 *
 */
// @codingStandardsIgnoreLine
require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

require_once(dirname(__FILE__) . '/lib.php');

require_once(dirname(__FILE__) . '/version.php');

global $DB, $CFG, $PAGE;

if (!isset($_GET['clientkey'])) {
    $errmsg = "Client key required";
    print_error_page($errmsg);
    exit;
}
$inputclientkey = $_GET['clientkey'];

/*
 * Key Checks
 */

$plagiarismsettings = (array)get_config('plagiarism');

if (!empty($plagiarismsettings['originality_key'])) {
    $clientkey = $plagiarismsettings['originality_key'];
} else {
    log_it("No originality key in database");
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$clientkeyvalid = client_key_valid($inputclientkey);

if (!$clientkeyvalid) {
    echo "Client key invalid";
    log_it("Client key invalid");
    exit;
}

if ($inputclientkey != $clientkey) {
    echo "Client key input does not match saved settings";
    exit;
}

if (isset($_GET['requesttype'])) {
    if ($_GET['requesttype'] == 1) {
        log_it("Successful request made: " . $_SERVER['QUERY_STRING']);
        // @codingStandardsIgnoreLine
        log_it(" POST: " . print_r($_POST, 1));
        require_once('requests_1.php');
    } else if ($_GET['requesttype'] == 2) {
        log_it("Successful request made: " . $_SERVER['QUERY_STRING']);
        require_once('requests_2.php');
    } else if ($_GET['requesttype'] == 3) {
        log_it("Successful request made: " . $_SERVER['QUERY_STRING']);
        require_once('requests_3.php');
    } else {
        $errmsg = "No such request type defined.";
        print_error_page($errmsg);
    }
} else {
    $errmsg = "You must give a request type.";
    print_error_page($errmsg);
}


function print_error_page($error) {
    $requesttype = isset($_GET['requesttype']) ? $_GET['requesttype'] : 'none given';

    log_it("Invalid request: $error, requesttype = $requesttype");
    echo <<<HHH
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
$error
</body>
</html>
HHH;
}



function client_key_valid($key) {

    list($origserver, $origkey) = get_server_and_key();

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $origserver->value . "customers/ping",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "authorization: ".$key,
            "cache-control: no-cache",
        ),
    ));

    $output = curl_exec($curl);

    $outputarray = json_decode($output, true);

    $err = curl_error($curl);

    curl_close($curl);

    if ($outputarray['Pong'] == 'true') {
        return true;
    } else {
        return false;
    }

}


function get_server_and_key() {
    global $DB;

    $origkey = $DB->get_record('config_plugins', array('name' => 'originality_key', 'plugin' => 'plagiarism'));
    $origserver = $DB->get_record('config_plugins', array('name' => 'originality_server', 'plugin' => 'plagiarism'));
    return array($origserver, $origkey);
}
