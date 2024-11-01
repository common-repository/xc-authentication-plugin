<?php
/*
Plugin Name: XCA Widgets
Plugin URI: http://www.ntx-research.com/
Description: XCA from www.ntx-research.com  is a strong authentication plugin to secure users access to Wordpress blogs or sites. Contact us to set up a accounts at ntx@ntx-research.com.
Author: NTX Research
Version: 1.0
Author URI: http://www.ntx-research.com/
*/
 
function xca_login_wid()
{
  echo "<h3>XCA Login</h3>";
}
 
function widget_xcaLogin($args) {
  extract($args);
  echo $before_widget;
  xca_login_wid();
  echo $before_title;?><ul><li><a href="wp-xc-login.php">XCA Login</a></li><li><a href="./privatePages/securepage.php">Zone privee</a></li></ul><?php echo $after_title;
  echo $after_widget;
}
 
function widget_xcaLogin_init()
{
  register_sidebar_widget(__('XCA Login'), 'widget_xcaLogin');
}
add_action("plugins_loaded", "widget_xcaLogin_init");
?>
