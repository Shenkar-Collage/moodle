<?php

/*
 * Functionality for various requests.
 *
 * Input are GET parameters, clientkey and requesttype.
 *
 * Type1:  Reprocess and delete requests that were never completely processed.
 *
 *        If there are any records left in plagiarism_originality_req then have a page with the list and buttons to delete and resubmit.
 *
 */
require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

require_once(dirname(__FILE__) . '/lib.php');

function base64_url_decode($input) {
    return base64_decode(strtr($input, '-_,', '+/='));
}

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

//require_login();

global $DB, $CFG, $PAGE;

/***********************************************************************
 * INPUT
 * *********************************************************************
 */


if (!isset($_GET['clientkey'])){
    $errmsg =  "Client key required";
    print_error_page($errmsg);
    exit;
}
$input_clientkey = $_GET['clientkey'];


/***********************************************************************
 * KEY CHECKS
 * *********************************************************************
 */


$plagiarismsettings = (array)get_config('plagiarism');

if (!empty($plagiarismsettings['originality_key'])){
    $client_key = $plagiarismsettings['originality_key'];
    //   $output.= '<h4>Client key:'. $client_key.'</h4>';
}else{
    log_it("No originality key in database");
    header('HTTP/1.1 403 Forbidden');
    exit;
}

$client_key_valid = client_key_valid($input_clientkey);

if (!$client_key_valid){
    echo "Client key invalid";
    log_it("Client key invalid");
    exit;
}

if ($input_clientkey != $client_key){
    echo "Client key input does not match saved settings";
    exit;
}

if (isset($_GET['requesttype'])){
    if ($_GET['requesttype']==1)  {
        log_it("Successful request made: " . $_SERVER['QUERY_STRING']);
        require_once 'requests_1.php';
    }
    elseif ($_GET['requesttype'] == 2 )  {
        log_it("Successful request made: " . $_SERVER['QUERY_STRING']);
        require_once 'requests_2.php';
    }
    elseif ($_GET['requesttype'] == 3 )  {
        log_it("Successful request made: " . $_SERVER['QUERY_STRING']);
        require_once 'requests_3.php';
    }
    else{
        $errmsg = "No such request type defined.";
        print_error_page($errmsg);
    }
}else{
    $errmsg = "You must give a request type.";
    print_error_page($errmsg);
}


function print_error_page($error){
    $requesttype = isset($_GET['requesttype']) ? $_GET['requesttype']: 'none given';

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



function client_key_valid($key){

    list($orig_server, $orig_key) = get_server_and_key();

    $orig_server = $orig_server->value;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$orig_server/Api/validate/$key");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    $output = strip_tags($output);

    if ($output=='true') {
        return true;
    }
    else {
        return false;
    }
}


function get_server_and_key(){
    global $DB;
    $orig_key = $DB->get_record('config_plugins', array('name'=>'originality_key', 'plugin'=>'plagiarism'));
    $orig_server = $DB->get_record('config_plugins', array('name'=>'originality_server', 'plugin'=>'plagiarism'));
    return array($orig_server, $orig_key);
}


?>