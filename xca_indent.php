<?php 
require_once 'wp-content/plugins/xc-authentication/xcaclientlogin.php';

	$numOfTry = "1";
if(isset($_POST['formSubmit'])){
		$username = $_SESSION['log'];
		$xca_company = $_SESSION['company'];
		$xca_servlet = $_SESSION['servlet'];
		$xca_service = $_SESSION['service'];
		$xca_redirect = $_SESSION['redirect'];
		$xca_redirect1 = $_SESSION['redirect1'];
	
	if ($_POST['formSubmit']=="Send"){
		$pass = hash('sha256', $_POST['password']);
		header("Location: $xca_servlet?user=$username@$xca_company&password=$pass&u=$xca_redirect&f=$xca_redirect&isMdp=Y");
	} elseif ($_POST['formSubmit']=="Badge"){
		header("Location: $xca_servlet?user=$username@$xca_company&u=$xca_redirect&f=$xca_redirect");
	} elseif ($_POST['formSubmit']=="Mobile"){
			$numOfTry = $_POST['count'];
			if($numOfTry < 4){
				$numOfTry = $numOfTry + 1;
				$msg = "Please, authenticate yourself with your Mobile first, before clicking on the [Auth by Mobile] button.";
				$xcaaas = new XcaaasClientLogin();
				$xca_ses = $xcaaas->check_mobile_session($username, $xca_service, $xca_company);
				
				$result = explode(':',$xca_ses,2);
					if ($result[0]=="success"){
					header("Location: $xca_redirect1?user=$username&company=$xca_company");
					}
			} else {
				header("Location: $xca_redirect1?error=5");
			}
	}
}
?>
<html>
<head>
<link rel='stylesheet' id='login-css'  href='./wp-admin/css/wp-admin.css' type='text/css' media='all' />
<!-- <link rel='stylesheet' id='colors-fresh-css'  href='./wp-admin/css/colors-fresh.css' type='text/css' media='all' />
	 -->
<link rel='stylesheet' id='colors-fresh-css'  href='./wp-admin/css/colors-fresh.css' type='text/css' media='all' />
<link rel='stylesheet' id='ntx_strong_authentication'  href='./wp-content/plugins/xc-authentication/css/ntx-strong-authentication.css' type='text/css' media='all' />
</head>
<body>
<div id="ntx-xca-select" >
<p><br /><br /><h1 class="side" style="text-align:center;color:#0A246A"><img class="right" src="./img/identite.jpg" alt="" title="Image title" />Zone privee / Restricted Access</h1>
<p style="text-align:center">Merci de vous authentifier pour acceder a notre zone privee. / Please authenticate yourself to be able to access our private area.</p>
	<br /><br />
	<form id="xca_form"  name="xca_form" method="post" action="xca_indent.php"  style='margin-left:25%;margin-right:25%'>
	<center>
		<p><label for="identifiant" style="font-size:20px;color:#0A246A""> Disk file / USB key  :</label></p>
		<input type="hidden" name="formSubmit" value="Badge"/>
		<input type="Submit" value="Authentication by File or USB Key" />
	</center>
	</form>
	<br /><br />
	<form id="xca_form" name="xca_form" method="post" action="xca_indent.php" style='margin-left:25%;margin-right:25%'>
	<center>
		<p><label for="identifiant" style="font-size:20px;color:#0A246A">Smartphone - Tablet - Mobile :</label></p>
		<input type="hidden" name="formSubmit" value="Mobile" />
		<input type="hidden" name="count" value="<?php echo $numOfTry; ?>" />
		<input type="Submit" value="Authentication by Mobile" />
	</center>
	</form>
	<br />
	<center>
	<p style="color:green;"><?php echo @$msg; ?></p>
	</center>
	<!-- <hr class="clear-contentunit" />
	 -->
	</div>
</body>
</html>
