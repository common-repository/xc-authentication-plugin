<?php
/*
Plugin Name: XC Authentication Plugin
Version: 1.0
Plugin URI: http://www.xcaaas.com/
Description: XCA from www.ntx-research.com  is a strong authentication plugin to secure users access to Wordpress blogs or sites. Contact us to set up a accounts at ntx@ntx-research.com.
Author: NTX Research
Author URI: http://www.ntx-research.com/
*/

require_once 'xcaclientlogin.php';

if (! class_exists('XCAuthenticationPlugin')) {
	abstract class XCAuthenticator {
		abstract function authenticate($username, $password);
	}
	
	class XcaaasAuthenticator extends XCAuthenticator {
		
		/**
		 * XC Identification process for XCA users.
		 **/ 
		function xca_indent_check($username) {
			$xcaaas = new XcaaasClientLogin();
			$xca_company = get_option('xca_company');
			$xca_service = get_option('xca_service');
		return $xcaaas->XCA_indent_check($username, $xca_service, $xca_company);
		}

		/**
		 * XC Authentication process.
		 **/
		function authenticate($username, $password) {
			$xcaaas = new XcaaasClientLogin();
			$xca_servlet = get_option('xca_servlet');
			$xca_service = get_option('xca_service');
			$xca_redirect = get_option('xca_redirect');
			$xca_company = get_option('xca_company');

			return $xcaaas->Authenticate($username, $password, $xca_servlet, $xca_service, $xca_redirect, $xca_company);
		}
	}

	class XCAuthenticationPlugin {
		function XCAuthenticationPlugin() {
			if (isset($_GET['activate']) and $_GET['activate'] == 'true') {
				add_action('init', array(&$this, 'initialize_options'));

			}
			add_action('admin_menu', array(&$this, 'add_options_page'));
			#add_action('wp_authenticate_user', array(&$this, 'authenticate'), 10, 2);
			add_filter('check_password', array(&$this, 'check_password'), 10, 4);
			#add_action('wp_logout', array(&$this, 'logout'));
			
			add_action('login_form', array(&$this, 'login_form'));
			add_action('wp_meta', array(&$this, 'custom_footer'));
			add_filter('wp_list_pages', array(&$this, 'list_pages'));

			if (!(bool) get_option('xca_auth_allow_regular')) {
				add_action('lost_password', array(&$this, 'disable_function'));
				add_action('retrieve_password', array(&$this, 'disable_function'));
				add_action('password_reset', array(&$this, 'disable_function'));
				add_action('check_passwords', array(&$this, 'generate_password'), 10, 3);
				add_filter('show_password_fields', array(&$this, 'disable_password_fields'));
			}
		}

		/*************************************************************
		 * Plugin hooks
		 *************************************************************/
		/*
		 * Add options for this plugin to the database.
		 * 
		 */
		
		function initialize_options() {
			if (current_user_can('manage_options')) {
				add_option('xca_auth_allow_regular', 1, 'Allow regular logins as well as XC Authentication logins?');
				add_option('xca_servlet', 'http://www.xcaaas.com/ntx/servlet/IndexFrontale', '');
				add_option('xca_service', 'http://www.xcaaas.com/xca/services/IndexPrincipaleService', '');
				add_option('xca_redirect', 'http://<host>/<path>/wp-content/plugins/xc-authentication/xca-login.php', '');
				add_option('xca_company', 'Company Name', '');
			}
		}

		/*
		 * Add an options pane for this plugin.
		 */
		function add_options_page() {
			if (function_exists('add_options_page')) {
				add_options_page('XC Authentication Plugin', 'XCA Plugin', 9, __FILE__, array(&$this, '_display_options_page'));
			}
		}

		function login_form() {
		}

		function check_password($check, $password, $hash, $user_id) {
			$user = get_userdata($user_id);
			
			$username = $user->user_login;

			//if ($check && ((bool) get_option('xca_auth_allow_regular') || ($username == 'admin' && $user->user_level >= 10))) {
			if ($check && ((bool) get_option('xca_auth_allow_regular') || ($password == 'xca_user' && $user->user_level >= 10))) {
				return true;
			} else if($password == "xca_user") {
					$authenticator = new XcaaasAuthenticator();
				return $authenticator->authenticate($username, $password);
			}
		}

		/*
		 * If the REMOTE_USER or REDIRECT_REMOTE_USER evironment
		 * variable is set, use it as the username. This assumes that
		 * you have externally authenticated the user.
		 */
		function authenticate($username, $password) {
		}

		/*
		 * Skip the password check, since we've externally authenticated.
		 */
		function skip_password_check($check, $password, $hash, $user_id) {
			return true;
		}

		/*
		 * Generate a password for the user. This plugin does not
		 * require the user to enter this value, but we want to set it
		 * to something nonobvious.
		 */
		function generate_password($username, $password1, $password2) {
			$password1 = $password2 = $this->_get_password();
		}

		/*
		 * Used to disable certain display elements, e.g. password
		 * fields on profile screen.
		 */
		function disable_password_fields($show_password_fields) {
			return false;
		}

		/*
		 * Used to disable certain login functions, e.g. retrieving a
		 * user's password.
		 */
		function disable_function() {
			die('Disabled');
		}
		
		function custom_footer() {
 		$content = '<li><a href="wp-xc-login.php">XCA Login</a></li>';
		echo $content;
		}

		function list_pages($ulclass="") {
		return preg_replace('/<\/ul>/', '<li><a href="xca_securepage.php">Sample Private Page</a></li> </ul>', $ulclass, 1);
		}
		
		/*************************************************************
		 * Functions
		 *************************************************************/
		/*
		 * Generate a random password.
		 */
		function _get_password($length = 10) {
			return substr(md5(uniqid(microtime())), 0, $length);
		}

		/*
		 * Display the options for this plugin.
		 */
		function _display_options_page() {
			$allow_regular = (bool) get_option('xca_auth_allow_regular');
			
		function wp_xca_indent_check($username){
			$authenticator = new XcaaasAuthenticator();
			return $authenticator->xca_indent_check($username);
		}

?>
<div class="wrap">
  <h2>XC Authentication Options</h2>
  <form action="options.php" method="post">
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="page_options" value="xca_auth_allow_regular, xca_servlet, xca_service, xca_redirect, xca_company" />
    <?php if (function_exists('wp_nonce_field')): wp_nonce_field('update-options'); endif; ?>

    <table class="form-table">
    <!-- 
      <tr valign="top">
        <th scope="row"><label for="xca_auth_allow_regular">Allow regular logins?</label></th>
        <td>
          <input type="hidden" name="xca_auth_allow_regular" id="xca_auth_allow_regular"<?php if ($allow_regular) echo ' checked="checked"' ?> value="1" />
          Allow regular logins as well as XC Authentication logins?<br />
        </td>
     </tr>
	 -->
      <tr valign="top">
		<th scope="row"><label for="xca_servlet"><?php _e('XCA Servlet address (URL)') ?></label></th>
		<td>
		<input type="hidden" name="xca_auth_allow_regular" id="xca_auth_allow_regular"<?php if ($allow_regular) echo ' checked="checked"' ?> value="1" />
		<input name="xca_servlet" type="text" id="xca_servlet" value="<?php form_option('xca_servlet'); ?>" class="regular-text" /></td>
	  </tr>
	  <tr valign="top">
		<th scope="row"><label for="xca_service"><?php _e('XCA Service address (URL)') ?></label></th>
		<td><input name="xca_service" type="text" id="xca_service" value="<?php form_option('xca_service'); ?>" class="regular-text" /></td>
	  </tr>
	 <tr valign="top">
		<th scope="row"><label for="xca_redirect"><?php _e('XCA Redirect address (URL)') ?></label></th>
		<td><input name="xca_redirect" type="text" id="xca_redirect" value="<?php form_option('xca_redirect'); ?>" class="regular-text" /></td>
	 </tr>
	 <tr valign="top">
		<th scope="row"><label for="xca_company"><?php _e('Company Name') ?></label></th>
		<td><input name="xca_company" type="text" id="xca_company" value="<?php form_option('xca_company'); ?>" class="regular-text" /></td>
	 </tr>
    </table>
    <p class="submit">
      <input type="submit" name="Submit" value="Save Changes" />
    </p>
  </form>
<?php
		}
	}
}

// Load the plugin hooks, etc.
$xca_auth_plugin = new XCAuthenticationPlugin();
//Only works if another function doesn't define this first
if ( !function_exists('wp_authenticate') ) :
function wp_authenticate($username, $password) {
	$username = sanitize_user($username);

	if ( empty($username) && empty($password) )
		return new WP_Error();

	if ( '' == $username )
		return new WP_Error('empty_username', __('<strong>ERROR</strong>: The username field is empty.'));

	if ( '' == $password )
		return new WP_Error('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));

		if($password=="xca_user"){
			$authenticator = new XcaaasAuthenticator();
			$indet = $authenticator->xca_indent_check($username);

			if(($indet=="notExist")&& !($username == "failed_user_indent" || $username == "failed_user_auth")) {
				return new WP_Error('invalid_username', __('<strong>ERROR</strong>: XCA Identification failed.'));
			} elseif (($indet == "Exist") && !(username_exists($username))){
				$xca_company = get_option('xca_company');
				$email = $username ."_" .$xca_company ."@ntx-research.com";
				$cre = wp_create_user($username, "", $email);
			}
		}

	$user = get_userdatabylogin($username);
	if ( !$user || ($user->user_login != $username) ) {
		do_action( 'wp_login_failed', $username );
		if($username == "failed_user_auth")
			return new WP_Error('invalid_username', __('<strong>ERROR</strong>: XC Authentication failed.'));
		elseif ($username == "failed_user_indent")
			return new WP_Error('invalid_username', __('<strong>ERROR</strong>: XCA Identification failed.'));
		else
			return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Invalid username.'));
	}

	$user = apply_filters('wp_authenticate_user', $user, $password);
	
	if ( is_wp_error($user) ) {
		do_action( 'wp_login_failed', $username );
		return $user;
	}

	if ( !wp_check_password($password, $user->user_pass, $user->ID) ) {
		do_action( 'wp_login_failed', $username );
		return new WP_Error('incorrect_password', __('<strong>ERROR</strong>: Incorrect password.'));
	}

	return new WP_User($user->ID);
}
endif;
?>
