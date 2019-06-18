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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

function get_client_ip() {
    $ipaddress = '';

    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    } else if (isset($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    } else {
        $ipaddress = 0;
    }
    return $ipaddress;
}


function log_it($str='') {

    global $CFG;

    require($CFG->dirroot . '/plagiarism/originality/version.php');

    $logsdir = $CFG->dataroot . '/originality_logs';

    if (!file_exists($logsdir)) {
        if (!mkdir($logsdir, 0755)) {
            notify_customer_service_did_not_create_logs_dir();
        }
    } else {
        if (0755 !== (fileperms($logsdir) & 0777)) {
            chmod($logsdir, 0755);
        }
    }

    /*
     * For plugin version 4.0.0 don't display the client key in the log file
     */

    $plagiarismsettings = (array)get_config('plagiarism');

    if (!empty($plagiarismsettings['originality_key'])) {
        $clientkey = $plagiarismsettings['originality_key'];
        $str = str_replace($clientkey, str_repeat('X', strlen($clientkey)), $str);
    }

    $logfile = 'originality_' . date('Y-m-d')  . '.log';

    $str = date('Y-m-d H:i:s', time() )  . " release: " . $plugin->release .
           "  " .basename($_SERVER['PHP_SELF']) .": " . $str. "\n";
    file_put_contents($logsdir."/$logfile", $str, FILE_APPEND);

}



function notify_customer_service_did_not_create_logs_dir() {

    $to = 'customerservice@originality.co.il';
    $from = 'notify@'.ltrim($_SERVER['HTTP_HOST'], 'www.');
    $subject = 'Originality: Failed to create logs directory';
    $message = 'Failed to create logs directory for client domain ' . $_SERVER['HTTP_HOST'];
    $headers = "From: $from" . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    mail($to, $subject, $message, $headers);
}



function get_latest_version_number($origserver, $origkey){
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $origserver->value."plugins/versions",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "authorization: ".$origkey->value,
            "cache-control: no-cache",
        ),
    ));

    $output = curl_exec($curl);

    $outputarray = json_decode($output, true);

    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        log_it("cURL Error:" . $err);
    } else {
        return $outputarray['version'];
    }
}