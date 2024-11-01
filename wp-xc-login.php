<?php

/** Make sure that the WordPress bootstrap has run before continuing. */
require( dirname(__FILE__) . '/wp-load.php' );

if ( force_ssl_admin() && !is_ssl() ) {
	if ( 0 === strpos($_SERVER['REQUEST_URI'], 'http') ) {
		wp_redirect(preg_replace('|^http://|', 'https://', $_SERVER['REQUEST_URI']));
		exit();
	} else {
		wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		exit();
	}
}

/**
 * Outputs the header for the login page.
 *
 * @uses do_action() Calls the 'login_head' for outputting HTML in the Log In
 *		header.
 * @uses apply_filters() Calls 'login_headerurl' for the top login link.
 * @uses apply_filters() Calls 'login_headertitle' for the top login title.
 * @uses apply_filters() Calls 'login_message' on the message to display in the
 *		header.
 * @uses $error The error global, which is checked for displaying errors.
 *
 * @param string $title Optional. WordPress Log In Page title to display in
 *		<title/> element.
 * @param string $message Optional. Message to display in header.
 * @param WP_Error $wp_error Optional. WordPress Error Object
 */
function login_header($title = 'Log In', $message = '', $wp_error = '') {
	global $error, $is_iphone, $interim_login, $current_site;

	// Don't index any of these forms
	add_filter( 'pre_option_blog_public', '__return_zero' );
	add_action( 'login_head', 'noindex' );

	if ( empty($wp_error) )
		$wp_error = new WP_Error();

	// Shake it!
	$shake_error_codes = array( 'empty_password', 'empty_email', 'invalid_email', 'invalidcombo', 'empty_username', 'invalid_username', 'incorrect_password' );
	$shake_error_codes = apply_filters( 'shake_error_codes', $shake_error_codes );

	if ( $shake_error_codes && $wp_error->get_error_code() && in_array( $wp_error->get_error_code(), $shake_error_codes ) )
		add_action( 'login_head', 'wp_shake_js', 12 );

	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<title><?php bloginfo('name'); ?> &rsaquo; <?php echo $title; ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<?php
	wp_admin_css( 'login', true );
	wp_admin_css( 'colors-fresh', true );

	if ( $is_iphone ) { ?>
	<meta name="viewport" content="width=320; initial-scale=0.9; maximum-scale=1.0; user-scalable=0;" />
	<style type="text/css" media="screen">
	form { margin-left: 0px; }
	#login { margin-top: 20px; }
	</style>
<?php
	} elseif ( isset($interim_login) && $interim_login ) { ?>
	<style type="text/css" media="all">
	.login #login { margin: 20px auto; }
	</style>
<?php
	}

	do_action( 'login_enqueue_scripts' );
	do_action( 'login_head' ); ?>
</head>
<body class="login">
<?php   if ( !is_multisite() ) { ?>
<div id="login"><h1><a href="<?php echo apply_filters('login_headerurl', 'http://wordpress.org/'); ?>" title="<?php echo apply_filters('login_headertitle', esc_attr__('Powered by WordPress')); ?>"><?php bloginfo('name'); ?></a></h1>
<?php   } else { ?>
<div id="login"><h1><a href="<?php echo apply_filters('login_headerurl', network_home_url() ); ?>" title="<?php echo apply_filters('login_headertitle', esc_attr($current_site->site_name) ); ?>"><span class="hide"><?php bloginfo('name'); ?></span></a></h1>
<?php   }

	$message = apply_filters('login_message', $message);
	if ( !empty( $message ) ) echo $message . "\n";

	// Incase a plugin uses $error rather than the $errors object
	if ( !empty( $error ) ) {
		$wp_error->add('error', $error);
		unset($error);
	}

	if ( $wp_error->get_error_code() ) {
		$errors = '';
		$messages = '';
		foreach ( $wp_error->get_error_codes() as $code ) {
			$severity = $wp_error->get_error_data($code);
			foreach ( $wp_error->get_error_messages($code) as $error ) {
				if ( 'message' == $severity )
					$messages .= '	' . $error . "<br />\n";
				else
					$errors .= '	' . $error . "<br />\n";
			}
		}
		if ( !empty($errors) )
			echo '<div id="login_error">' . apply_filters('login_errors', $errors) . "</div>\n";
		if ( !empty($messages) )
			echo '<p class="message">' . apply_filters('login_messages', $messages) . "</p>\n";
	}
} // End of login_header()

/**
 * Outputs the footer for the login page.
 *
 * @param string $input_id Which input to auto-focus
 */
function login_footer($input_id = '') {
	echo "</div>\n";

	if ( !empty($input_id) ) {
?>
<script type="text/javascript">
try{document.getElementById('<?php echo $input_id; ?>').focus();}catch(e){}
if(typeof wpOnload=='function')wpOnload();
</script>
<?php
	}
?>
<p id="backtoblog"><a href="<?php bloginfo('url'); ?>/" title="<?php esc_attr_e('Are you lost?') ?>"><?php printf(__('&larr; Back to %s'), get_bloginfo('title', 'display' )); ?></a></p>
<?php do_action('login_footer'); ?>
</body>
</html>
<?php
}

function wp_shake_js() {
	global $is_iphone;
	if ( $is_iphone )
		return;
?>
<script type="text/javascript">
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
function s(id,pos){g(id).left=pos+'px';}
function g(id){return document.getElementById(id).style;}
function shake(id,a,d){c=a.shift();s(id,c);if(a.length>0){setTimeout(function(){shake(id,a,d);},d);}else{try{g(id).position='static';wp_attempt_focus();}catch(e){}}}
addLoadEvent(function(){ var p=new Array(15,30,15,0,-15,-30,-15,0);p=p.concat(p.concat(p));var i=document.forms[0].id;g(i).position='relative';shake(i,p,20);});
</script>
<?php
}

/**
 * Handles sending password retrieval email to user.
 *
 * @uses $wpdb WordPress Database object
 *
 * @return bool|WP_Error True: when finish. WP_Error on error
 */
function retrieve_password() {
	global $wpdb, $current_site;

	$errors = new WP_Error();

	if ( empty( $_POST['user_login'] ) && empty( $_POST['user_email'] ) )
		$errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or e-mail address.'));

	if ( strpos($_POST['user_login'], '@') ) {
		$user_data = get_user_by_email(trim($_POST['user_login']));
		if ( empty($user_data) )
			$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.'));
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_userdatabylogin($login);
	}

	do_action('lostpassword_post');

	if ( $errors->get_error_code() )
		return $errors;

	if ( !$user_data ) {
		$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or e-mail.'));
		return $errors;
	}

	// redefining user_login ensures we return the right case in the email
	$user_login = $user_data->user_login;
	$user_email = $user_data->user_email;

	do_action('retreive_password', $user_login);  // Misspelled and deprecated
	do_action('retrieve_password', $user_login);

	$allow = apply_filters('allow_password_reset', true, $user_data->ID);

	if ( ! $allow )
		return new WP_Error('no_password_reset', __('Password reset is not allowed for this user'));
	else if ( is_wp_error($allow) )
		return $allow;

	$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
	if ( empty($key) ) {
		// Generate something random for a key...
		$key = wp_generate_password(20, false);
		do_action('retrieve_password_key', $user_login, $key);
		// Now insert the new md5 key into the db
		$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
	}
	$message = __('Someone requested that the password be reset for the following account:') . "\r\n\r\n";
	$message .= network_site_url() . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
	$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
	$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";

	if ( is_multisite() )
		$blogname = $GLOBALS['current_site']->site_name;
	else
		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$title = sprintf( __('[%s] Password Reset'), $blogname );

	$title = apply_filters('retrieve_password_title', $title);
	$message = apply_filters('retrieve_password_message', $message, $key);

	if ( $message && !wp_mail($user_email, $title, $message) )
		wp_die( __('The e-mail could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...') );

	return true;
}

/**
 * Retrieves a user row based on password reset key and login
 *
 * @uses $wpdb WordPress Database object
 *
 * @param string $key Hash to validate sending user's password
 * @param string $login The user login
 *
 * @return object|WP_Error
 */
function check_password_reset_key($key, $login) {
	global $wpdb;

	$key = preg_replace('/[^a-z0-9]/i', '', $key);

	if ( empty( $key ) || !is_string( $key ) )
		return new WP_Error('invalid_key', __('Invalid key'));

	if ( empty($login) || !is_string($login) )
		return new WP_Error('invalid_key', __('Invalid key'));

	$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login));

	if ( empty( $user ) )
		return new WP_Error('invalid_key', __('Invalid key'));

	return $user;
}

/**
 * Handles resetting the user's password.
 *
 * @uses $wpdb WordPress Database object
 *
 * @param string $key Hash to validate sending user's password
 */
function reset_password($user, $new_pass) {
	do_action('password_reset', $user, $new_pass);

	wp_set_password($new_pass, $user->ID);

	wp_password_change_notification($user);
}

//
// Main
//

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
$errors = new WP_Error();

if ( isset($_GET['key']) )
	$action = 'resetpass';

// validate action so as to default to the login screen
if ( !in_array($action, array('logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register', 'login'), true) && false === has_filter('login_form_' . $action) )
	$action = 'login';

nocache_headers();

header('Content-Type: '.get_bloginfo('html_type').'; charset='.get_bloginfo('charset'));

if ( defined('RELOCATE') ) { // Move flag is set
	if ( isset( $_SERVER['PATH_INFO'] ) && ($_SERVER['PATH_INFO'] != $_SERVER['PHP_SELF']) )
		$_SERVER['PHP_SELF'] = str_replace( $_SERVER['PATH_INFO'], '', $_SERVER['PHP_SELF'] );

	$schema = is_ssl() ? 'https://' : 'http://';
	if ( dirname($schema . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) != get_option('siteurl') )
		update_option('siteurl', dirname($schema . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']) );
}

//Set a cookie now to see if they are supported by the browser.
setcookie(TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN);
if ( SITECOOKIEPATH != COOKIEPATH )
	setcookie(TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN);
	if ( isset( $_REQUEST['redirect_to'] ) ) {
		$redirect_to = $_REQUEST['redirect_to'];
		// Redirect to https if user wants ssl
		if ( $secure_cookie && false !== strpos($redirect_to, 'wp-admin') )
			$redirect_to = preg_replace('|^http://|', 'https://', $redirect_to);
	} else {
		$redirect_to = admin_url();
	}

	$reauth = empty($_REQUEST['reauth']) ? false : true;

	// If the user was redirected to a secure login form from a non-secure admin page, and secure login is required but secure admin is not, then don't use a secure
	// cookie and redirect back to the referring non-secure admin page.  This allows logins to always be POSTed over SSL while allowing the user to choose visiting
	// the admin via http or https.
	if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )
		$secure_cookie = false;

	$user = wp_signon('', $secure_cookie);

	$redirect_to = apply_filters('login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user);

	if ( !is_wp_error($user) && !$reauth ) {
		if ( $interim_login ) {
			$message = '<p class="message">' . __('You have logged in successfully.') . '</p>';
			login_header( '', $message ); ?>
			<script type="text/javascript">setTimeout( function(){window.close()}, 8000);</script>
			<p class="alignright">
			<input type="button" class="button-primary" value="<?php esc_attr_e('Close'); ?>" onclick="window.close()" /></p>
			</div></body></html>
<?php		exit;
		}

		if ( ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) ) {
			// If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
			if ( is_multisite() && !get_active_blog_for_user($user->id) )
				$redirect_to = user_admin_url();
			elseif ( is_multisite() && !$user->has_cap('read') )
				$redirect_to = get_dashboard_url( $user->id );
			elseif ( !$user->has_cap('edit_posts') )
				$redirect_to = admin_url('profile.php');
		}
		wp_safe_redirect($redirect_to);
		exit();
	}

	$errors = $user;
	// Clear errors if loggedout is set.
	if ( !empty($_GET['loggedout']) || $reauth )
		$errors = new WP_Error();

	// If cookies are disabled we can't log in even with a valid user+pass
	if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) )
		$errors->add('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));

	// Clear any stale cookies.
	if ( $reauth )
		wp_clear_auth_cookie();

	login_header(__('Log In'), '', $errors);

	if ( isset($_POST['log']) )
		$user_login = ( 'incorrect_password' == $errors->get_error_code() || 'empty_password' == $errors->get_error_code() ) ? esc_attr(stripslashes($_POST['log'])) : '';
	$rememberme = ! empty( $_POST['rememberme'] );
?>

<form name="loginform" id="loginform" action="<?php echo site_url('wp-xc-login.php', 'login_post') ?>" method="post">
	<p>
		<label><?php _e('Username') ?><br />
		<input type="text" name="log" id="user_login" class="input" value="<?php echo esc_attr($user_login); ?>" size="20" tabindex="10" /></label>
	</p>
	<p>
		<label><?php // _e('Password') ?><br />
		<input type="hidden" name="pwd" id="user_pass" class="input" value="xca_user" size="20" tabindex="20" /></label>
	</p>
<?php do_action('login_form'); ?>
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="<?php esc_attr_e('Log In'); ?>" tabindex="100" />
<?php	if ( $interim_login ) { ?>
		<input type="hidden" name="interim-login" value="1" />
<?php	} else { ?>
		<input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>" />
<?php 	} ?>
		<input type="hidden" name="testcookie" value="1" />
	</p>
</form>
</div>
<?php 
login_footer('user_login');
?>
<script type="text/javascript">
function wp_attempt_focus(){
setTimeout( function(){ try{
<?php if ( $user_login || $interim_login ) { ?>
d = document.getElementById('user_pass');
d.value = '';
<?php } else { ?>
d = document.getElementById('user_login');
<?php if ( 'invalid_username' == $errors->get_error_code() ) { ?>
if( d.value != '' )
d.value = '';
<?php
}
}?>
d.focus();
d.select();
} catch(e){}
}, 200);
}

<?php if ( !$error ) { ?>
wp_attempt_focus();
<?php } ?>
if(typeof wpOnload=='function')wpOnload();
</script>
<?php do_action( 'login_footer' ); ?>
</body>
</html>
<?php

 // end action switch
?>
