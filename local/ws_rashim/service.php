<?php
require_once ("../../config.php");

if ($_SERVER ["REQUEST_METHOD"] == "POST") {
	require_once ("lib.php");
	
	$server = new SoapServer ( 'wsdl.xml' );
	
	$server->setClass ( "server" );
	$server->handle ();
} else {
	header ( "Location: ../../local/ws_rashim/wsdl.php" );
	exit ();
}

?>
