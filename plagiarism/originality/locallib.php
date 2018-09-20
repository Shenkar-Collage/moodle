<?php


function get_client_ip() {

	$ipaddress = '';

	if (isset($_SERVER['HTTP_CLIENT_IP']))

	$ipaddress = $_SERVER['HTTP_CLIENT_IP'];

	else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))

	$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];

	else if(isset($_SERVER['HTTP_X_FORWARDED']))

	$ipaddress = $_SERVER['HTTP_X_FORWARDED'];

	else if(isset($_SERVER['HTTP_FORWARDED_FOR']))

	$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];

	else if(isset($_SERVER['HTTP_FORWARDED']))

	$ipaddress = $_SERVER['HTTP_FORWARDED'];

	else if(isset($_SERVER['REMOTE_ADDR']))

	$ipaddress = $_SERVER['REMOTE_ADDR'];

	else

	$ipaddress = 0;



	return $ipaddress;

}


function log_it($str=''){

    global $CFG;

    require($CFG->dirroot . '/plagiarism/originality/version.php');

    $logs_dir = $CFG->dataroot . '/originality_logs';

    if (!file_exists($logs_dir)){
        if (!mkdir($logs_dir, 0755)){
           notify_customer_service_did_not_create_logs_dir();
        }
    }else{
        if (0755 !== (fileperms($logs_dir) & 0777)){
            chmod($logs_dir, 0755);
        }
    }

    /*
     * For plugin version 4.0.0 don't display the client key in the log file
     */

    $plagiarismsettings = (array)get_config('plagiarism');

    if (!empty($plagiarismsettings['originality_key'])){
        $client_key = $plagiarismsettings['originality_key'];
        $str = str_replace($client_key, str_repeat('X', strlen($client_key)), $str);
    }


    $log_file = 'originality_' . date('Y-m-d')  . '.log';

    $str = date('Y-m-d H:i:s', time() )  . " release: " . $plugin->release . "  " .basename($_SERVER['PHP_SELF']) .": " . $str. "\n";
    file_put_contents($logs_dir."/$log_file", $str, FILE_APPEND);

}



function notify_customer_service_did_not_create_logs_dir(){

    $to      = 'customerservice@originality.co.il';
    $from = 'notify@'.ltrim($_SERVER['HTTP_HOST'], 'www.');
    $subject = 'Originality: Failed to create logs directory';
    $message = 'Failed to create logs directory for client domain ' . $_SERVER['HTTP_HOST'];
    $headers = "From: $from" . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    mail($to, $subject, $message, $headers);
}


?>