<?php
/*
Plugin Name: MF Front-End Admin
Plugin URI: https://github.com/frostkom/mf_front_end_admin
Description:
Version: 1.6.1
Licence: GPLv2 or later
Author: Martin Fors
Author URI: https://martinfors.se
Text Domain: lang_fea
Domain Path: /lang

Depends: MF Base
GitHub Plugin URI: frostkom/mf_front_end_admin
*/

if(!function_exists('is_plugin_active') || function_exists('is_plugin_active') && is_plugin_active("mf_base/index.php"))
{
	include_once("include/classes.php");

	$obj_fea = new mf_fea();

	add_action('cron_base', 'activate_fea', mt_rand(1, 10));

	if(is_admin())
	{
		register_activation_hook(__FILE__, 'activate_fea');
		register_uninstall_hook(__FILE__, 'uninstall_fea');

		add_action('wp_before_admin_bar_render', array($obj_fea, 'wp_before_admin_bar_render'));

		add_action('admin_init', array($obj_fea, 'settings_fea'), 0);

		add_filter('display_post_states', array($obj_fea, 'display_post_states'), 10, 2);
	}

	else
	{
		add_action('login_init', array($obj_fea, 'login_init'));
		add_filter('login_redirect', array($obj_fea, 'login_redirect'), 10, 3);

		add_filter('wp_nav_menu_objects', array($obj_fea, 'wp_nav_menu_objects'), 10, 2);

		add_action('wp_footer', array($obj_fea, 'get_footer'), 0);
	}

	add_action('after_setup_theme', array($obj_fea, 'after_setup_theme'));

	add_action('widgets_init', array($obj_fea, 'widgets_init'));

	add_filter('init_base_admin', array($obj_fea, 'init_base_admin'), 9, 2);
	add_filter('init_base_admin', array($obj_fea, 'init_base_admin_2'), 11);

	add_filter('get_front_end_admin_id', array($obj_fea, 'get_front_end_admin_id'));
	add_filter('get_front_end_admin_url', array($obj_fea, 'get_front_end_admin_url'));

	add_filter('edit_profile_url', array($obj_fea, 'edit_profile_url'));

	add_filter('get_page_templates', array($obj_fea, 'get_page_templates'));

	load_plugin_textdomain('lang_fea', false, dirname(plugin_basename(__FILE__))."/lang/");

	function activate_fea()
	{
		mf_uninstall_plugin(array(
			'options' => array('setting_fea_display_menu'),
		));
	}

	function uninstall_fea()
	{
		mf_uninstall_plugin(array(
			'options' => array('setting_fea_user_info', 'setting_fea_pages', 'setting_fea_display_in_menu', 'setting_fea_redirect_after_login', 'setting_fea_content_width', 'setting_fea_get_started'),
		));
	}
}