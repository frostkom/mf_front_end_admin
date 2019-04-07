<?php
/*
Plugin Name: MF Front-End Admin
Plugin URI: 
Description: 
Version: 1.0.6
Licence: GPLv2 or later
Author: Martin Fors
Author URI: https://frostkom.se
Text Domain: lang_fea
Domain Path: /lang

Depends: MF Base
GitHub Plugin URI: 
*/

include_once("include/classes.php");

$obj_fea = new mf_fea();

add_action('cron_base', 'activate_fea', mt_rand(1, 10));

if(is_admin())
{
	register_activation_hook(__FILE__, 'activate_fea');
	register_uninstall_hook(__FILE__, 'uninstall_fea');

	add_action('wp_before_admin_bar_render', array($obj_fea, 'wp_before_admin_bar_render'));

	add_action('admin_init', array($obj_fea, 'settings_fea'), 0);
}

else
{
	add_filter('login_redirect', array($obj_fea, 'login_redirect'), 10, 3);
}

add_filter('init_base_admin', array($obj_fea, 'init_base_admin'));
add_filter('init_base_admin', array($obj_fea, 'init_base_admin_2'), 11);

add_filter('get_page_templates', array($obj_fea, 'get_page_templates'));

load_plugin_textdomain('lang_fea', false, dirname(plugin_basename(__FILE__)).'/lang/');

function activate_fea()
{
	replace_option(array('old' => 'setting_base_front_end_admin', 'new' => 'setting_fea_pages'));
}

function uninstall_fea()
{
	mf_uninstall_plugin(array(
		'options' => array('setting_fea_user_info', 'setting_fea_pages', 'setting_fea_redirect_after_login'),
	));
}