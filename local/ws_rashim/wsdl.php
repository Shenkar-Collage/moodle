<?php

// the wsdl uses double instead of long because of a bug in the mysql/mssql connection layer when using bigint!!!
// it is completly safe to convert the values to long with no data loss
header ( "Content-Type: text/xml; charset=UTF-8" );

echo file_get_contents ( 'wsdl.xml' );

?>



