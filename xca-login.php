<?php
session_start();
$user = @$_GET['user'];
$errorxca = @$_GET['error'];
?>
<html>
<head>
<script type="text/javascript">
function xcaSubmit(){ 
document.xcaForm.submit();
}
</script>
<title>XC Authentication Redirect</title>
</head>
<body onload="return xcaSubmit()">
<form name="xcaForm" action='../../../wp-xc-login.php' method='POST'>
<?php
//If returs error
if($errorxca){
	if($errorxca == "5"){
?>
	<input type='hidden' name='log' value='failed_user_auth'>
	<input type='hidden' name='pwd' value='xca'>
<?php } else { //else default error
?>
	<input type='hidden' name='log' value='failed_user_indent'>
	<input type='hidden' name='pwd' value='xca'>
<?php
	}
}

if($user){ //If success returns
		unset($_SESSION['company']);
		unset($_SESSION['servlet']);
		unset($_SESSION['service']);
		unset($_SESSION['redirect']);
		unset($_SESSION['redirect1']);
	foreach ($_SESSION as $a => $b){
	print("<input type='hidden' name='".$a."' value='".$b."'>\n");
	}
}
session_destroy();
?>
</form>
</body>
</html>