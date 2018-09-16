<?php

class RashimAuth {

    const login = 'MICHAPI';
    const password = 'WSM3API';
    const michlolPath = 'WsM3Api/MichlolApi.asmx?WSDL';

    private $client;

    public function __construct($url) {
        $oldlevel = error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
        $this->client = new SoapClient($url.self::michlolPath,
            array(
                'exceptions' => true,
                'trace' => true,
                'soap_version' => SOAP_1_2,
                'encoding' => 'UTF-8',
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
                'cache_wsdl' => WSDL_CACHE_NONE
            ));
        error_reporting($oldlevel);
    }

    public function user_login($username, $password) {
        global $CFG;
        /*if (!isset($CFG->passwordsaltmain))  {
            $CFG->passwordsaltmain = '';
        }
        $salt = md5($password.$CFG->passwordsaltmain);
        //file_put_contents($CFG->dataroot.'/login.log',"\nusername: $username\tpassword: $password\tfinal password: $salt\n", FILE_APPEND);
        if ($user = get_record('user', 'username', $username, 'password', md5($password.$CFG->passwordsaltmain)))   {
            return true;
        }*/
        if ($this->auth($this->studentXML($username, $password),$username)) {
            return true;
        } else if ($this->auth($this->teacherXML($username, $password),$username)) {
            return true;
        }

        return false;
    }

    public function auth($xml,$username) {

        $param = array(
            'P_RequestParams' => array(
                'RequestID' => 111,
                'InputData' => $xml
            ),
            'Authenticator' => array(
                'UserName' => self::login,
                'Password' => self::password,
            ));

        $result = $this->client->ProcessRequest($param);
//if($username = 300931581){print_r($param); print_r($result);die;}
        if (empty($result->ProcessRequestResult->OutputData)) {
            return false;
        }

        $xml_result = new SimpleXMLElement($result->ProcessRequestResult->OutputData);

        if (($xml_result->RECORD->LOGIN_RESULT == 10) && (!strcmp($xml_result->RECORD->LOGIN_USERNAMENAME,$username))){
	  //        if ($xml_result->RECORD->LOGIN_RESULT == 10) {
            return true;
        } else {
            return false;
        }
    }

    public function studentXML($username, $password) {
        return <<<EOXML
<?xml version="1.0" encoding="utf-8" ?>
<PARAMS>
	<PM_ZHT></PM_ZHT>
	<PM_USERNAME></PM_USERNAME>
	<PM_PASSWORD></PM_PASSWORD>
	<PT_ZHT>$username</PT_ZHT>
	<PT_SECRETCODE>$password</PT_SECRETCODE>
	<PT_USERNAME></PT_USERNAME>
	<PT_INTERNETPASSWORD></PT_INTERNETPASSWORD>
	<PT_PASSWORD></PT_PASSWORD>
</PARAMS>
EOXML;
    }


    public function teacherXML($username, $password) {
        return <<<EOXML
<?xml version="1.0" encoding="utf-8" ?>
<PARAMS>
        <PM_ZHT>$username</PM_ZHT>
        <PM_USERNAME></PM_USERNAME>
        <PM_PASSWORD>$password</PM_PASSWORD>
        <PT_ZHT></PT_ZHT>
        <PT_SECRETCODE></PT_SECRETCODE>
        <PT_USERNAME></PT_USERNAME>
        <PT_INTERNETPASSWORD></PT_INTERNETPASSWORD>
        <PT_PASSWORD></PT_PASSWORD>
</PARAMS>
EOXML;
    }

}
