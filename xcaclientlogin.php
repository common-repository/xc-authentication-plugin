<?php
session_start();
//session_set_cookie_params('5000');

require_once 'httplib.php';
require_once ('lib/nusoap.php');

class XcaaasClientLogin {
	function XcaaasClientLogin() {

	}
	function Authenticate($username, $password, $xca_servlet, $xca_service, $xca_redirect1, $xca_company) {
	function strtohex($x) {
	  $s='';
	  foreach(str_split($x) as $c) $s.=sprintf("%02X",ord($c));
	  return($s);
	}
	
	function check_mobile_session($username, $xca_service, $xca_company){
		$client = new nusoap_client($xca_service."?wsdl", 'wsdl');
		$err = $client->getError();
		if ($err) {
		echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}
		$param = array('userName' => $username,'company' => $xca_company);
		$result = $client->call('getUserSuccId',$param);
		return $result;
	}
	
	function check_session_exist($username, $xca_service, $xca_company){

		$client = new nusoap_client($xca_service."?wsdl", 'wsdl');
		$err = $client->getError();
		if ($err) {
		echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}
		$param = array('userName' => $username,'company' => $xca_company);
		$result = $client->call('verifySession',$param);
		return $result;
	}
		$xca_redirect = strtohex($xca_redirect1);

		$_SESSION['log'] = $_POST['log'];
		$_SESSION['pwd'] = $_POST['pwd'];
		$_SESSION['wp-submit'] = $_POST['wp-submit'];
		$_SESSION['rememberme'] = $_POST['rememberme'];
		$_SESSION['redirect_to'] = $_POST['redirect_to'];
		//$_SESSION['redirect_to'] = "securepage.php";
		$_SESSION['testcookie'] = $_POST['testcookie'];

		$_SESSION['company'] = $xca_company;
		$_SESSION['servlet'] = $xca_servlet;
		$_SESSION['service'] = $xca_service;
		$_SESSION['redirect'] = $xca_redirect;
		$_SESSION['redirect1'] = $xca_redirect1;

		$xcausbsuccess = check_session_exist($username, $xca_service, $xca_company);
		$xcamobsuccess = check_mobile_session($username, $xca_service, $xca_company);
		$resultusb = explode(':', $xcausbsuccess, 2);
		$resultmob = explode(':', $xcamobsuccess, 2);

		if($resultusb[0]=="success" || $resultmob[0]=="success")
			return true;
		else {
			header("Location: xca_indent.php");
		}
	}

	function XCA_indent_check($username, $xca_service, $xca_company){
			$client = new nusoap_client($xca_service."?wsdl", 'wsdl');
			$err = $client->getError();
			if ($err) {
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			}
			$param = array('userName' => $username,'company' => $xca_company);
			$result = $client->call('verifyIdentification',$param);
			//echo $result;
			return $result;
		}

	function check_mobile_session($username, $xca_service, $xca_company){
		$client = new nusoap_client($xca_service."?wsdl", 'wsdl');
		$err = $client->getError();
		if ($err) {
		echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		}
		$param = array('userName' => $username,'company' => $xca_company);
		$result = $client->call('getUserSuccId',$param);
		return $result;
	}
}

?>