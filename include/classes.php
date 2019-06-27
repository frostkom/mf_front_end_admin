<?php

class mf_fea
{
	function __construct(){}

	function get_user_info_for_select()
	{
		$arr_data = array(
			'name' => __("Name", 'lang_fea'),
			'role' => __("Role", 'lang_fea'),
			'logout' => __("Log Out", 'lang_fea'),
		);

		return $arr_data;
	}

	function get_front_end_views_for_select()
	{
		$arr_data = array();

		$arr_views = apply_filters('init_base_admin', array());

		foreach($arr_views as $key => $view)
		{
			$arr_data[$key] = $view['name'];
		}

		return $arr_data;
	}

	function wp_before_admin_bar_render()
	{
		global $wp_admin_bar;

		$obj_base = new mf_base();
		$post_id = $obj_base->has_page_template(array('template' => "/plugins/mf_front_end_admin/include/templates/template_admin.php"));

		if($post_id > 0)
		{
			$post_status = get_post_status($post_id);

			$color = $title = "";

			switch($post_status)
			{
				case 'publish':
					$color = "color_green";
				break;

				case 'draft':
					if(IS_ADMIN)
					{
						$color = "color_yellow";
						$title = __("Not Published", 'lang_fea');
					}
				break;
			}

			if($color != '')
			{
				$wp_admin_bar->add_node(array(
					'id' => 'front-end',
					'title' => "<a href='".get_permalink($post_id)."' class='".$color."'".($title != '' ? " title='".$title."'" : '').">".get_post_title($post_id)."</a>",
				));
			}
		}
	}

	function settings_fea()
	{
		$obj_base = new mf_base();

		if($obj_base->has_page_template(array('template' => "/plugins/mf_front_end_admin/include/templates/template_admin.php")) > 0)
		{
			$options_area = __FUNCTION__;

			add_settings_section($options_area, "",	array($this, $options_area."_callback"), BASE_OPTIONS_PAGE);

			$arr_settings = array(
				'setting_fea_user_info' => __("User Info", 'lang_fea'),
				'setting_fea_pages' => __("Pages", 'lang_fea'),
				'setting_fea_redirect_after_login' => __("Redirect After Login", 'lang_fea'),
				'setting_fea_content_width' => __("Content Width", 'lang_fea'),
			);

			show_settings_fields(array('area' => $options_area, 'object' => $this, 'settings' => $arr_settings));
		}
	}

	function settings_fea_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);

		echo settings_header($setting_key, __("Front-End Admin", 'lang_fea'));
	}

	function setting_fea_user_info_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key, array('name', 'role', 'logout'));

		echo show_select(array('data' => $this->get_user_info_for_select(), 'name' => $setting_key."[]", 'value' => $option));
	}

	function setting_fea_pages_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		echo show_select(array('data' => $this->get_front_end_views_for_select(), 'name' => $setting_key."[]", 'value' => $option));
	}

	function setting_fea_redirect_after_login_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		echo show_select(array('data' => get_roles_for_select(array('add_choose_here' => false, 'use_capability' => false)), 'name' => $setting_key."[]", 'value' => $option, 'description' => __("Users with these roles will be redirected after login", 'lang_fea')));
	}

	function setting_fea_content_width_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		echo show_textfield(array('type' => 'number', 'name' => $setting_key, 'value' => $option, 'suffix' => "px"));
	}

	function login_redirect($redirect_to, $request, $user)
	{
		$admin_url = admin_url();

		// Just in case we have sent this var along with the URL
		$redirect_to = check_var('redirect_to', 'char', true, $redirect_to);

		if($redirect_to == $admin_url)
		{
			$setting_fea_redirect_after_login = get_option_or_default('setting_fea_redirect_after_login', array());

			if(isset($user->roles) && is_array($user->roles) && count(array_intersect($setting_fea_redirect_after_login, $user->roles)) > 0)
			{
				$obj_base = new mf_base();
				$post_id = $obj_base->has_page_template(array('template' => "/plugins/mf_front_end_admin/include/templates/template_admin.php"));

				if($post_id > 0)
				{
					$redirect_to = get_permalink($post_id);
				}
			}
		}

		return $redirect_to;
	}

	function get_footer()
	{
		$obj_base = new mf_base();
		echo $obj_base->get_templates(array('lost_connection', 'loading'));
	}

	function init_base_admin($arr_views)
	{
		$templates = "";

		$plugin_base_include_url = plugins_url()."/mf_base/include/";
		$plugin_include_url = plugin_dir_url(__FILE__);
		$plugin_version = get_plugin_version(__FILE__);

		if(!is_admin())
		{
			mf_enqueue_style('style_base_admin', $plugin_include_url."style.php", $plugin_version);

			mf_enqueue_script('underscore');
			mf_enqueue_script('backbone');
			mf_enqueue_script('script_base_plugins', $plugin_base_include_url."backbone/bb.plugins.js", $plugin_version);

			mf_enqueue_script('script_fea_router', $plugin_include_url."bb.admin.router.js", $plugin_version);
			mf_enqueue_script('script_fea_models', $plugin_include_url."bb.admin.models.js", array('api_url' => $plugin_include_url), $plugin_version);
			mf_enqueue_script('script_fea_views', $plugin_include_url."bb.admin.views.js", $plugin_version);

			$templates .= "<script type='text/template' id='template_admin_profile_edit'>
				<form method='post' action='' class='mf_form' data-api-url='".$plugin_include_url."' data-action='admin/profile/save'>
					<% _.each(fields, function(field)
					{
						switch(field.type)
						{
							case 'date': %>"
								.show_textfield(array('type' => 'date', 'name' => "<%= field.name %>", 'text' => "<%= field.text %>", 'value' => "<%= field.value %>"))
							."<% break;

							case 'email': %>"
								.show_textfield(array('type' => 'email', 'name' => "<%= field.name %>", 'text' => "<%= field.text %>", 'value' => "<%= field.value %>", 'description' => "<%= field.description %>"))
							."<% break;

							case 'flex_start': %>
								<div class='flex_flow'>
							<% break;

							case 'flex_end': %>
								</div>
							<% break;

							case 'media_image': %>
								<div>
									<label for='<%= field.name %>'><%= field.text %></label>"
									.get_media_library(array('name' => "<%= field.name %>", 'value' => "<%= field.value %>", 'type' => 'image'))
								."</div>
							<% break;

							case 'number': %>"
								.show_textfield(array('type' => 'number', 'name' => "<%= field.name %>", 'text' => "<%= field.text %>", 'value' => "<%= field.value %>"))
							."<% break;

							case 'password': %>"
								.show_password_field(array('name' => "<%= field.name %>", 'text' => "<%= field.text %>", 'placeholder' => __("Enter a New Password Here", 'lang_fea')))
								/*."<div class='form_button'>
									<label><%= field.text %></label>
									<a href='".admin_url("profile.php")."' class='button'>".__("Change Password", 'lang_fea')."</a></div>
								</div>"*/
							."<% break;

							case 'select': %>
								<div class='form_select type_<%= field.type %><%= field.class %>'>
									<label for='<%= field.name %>'><%= field.text %></label>
									<select id='<%= field.name %>' name='<%= field.name %><% if(field.multiple == true){ %>[]<% } %>'<% if(field.multiple == true){ %> multiple<% } %><%= field.attributes %>>
										<% _.each(field.options, function(option)
										{%>
											<% if(option.key.toString().substr(0, 9) == 'opt_start')
											{ %>
												<optgroup label='<%= option.value %>' rel='<%= option.key %>'>
											<% }

											else if(option.key.toString().substr(0, 7) == 'opt_end')
											{ %>
												</optgroup>
											<% }

											else
											{ %>
												<option value='<%= option.key %>'<% if(option.key == field.value || field.multiple == true && field.value.indexOf(option.key.toString()) !== -1){%> selected<%} %>><%= option.value %></option>
											<% } %>
										<% }); %>
									</select>
								</div>
							<% break;

							case 'text': %>"
								.show_textfield(array('name' => "<%= field.name %>", 'text' => "<%= field.text %>", 'value' => "<%= field.value %>"))
							."<% break;

							default: %>
								<strong><%= meta_field.type %></strong>: <%= meta_field.name %><br>
							<% break;
						}
					}); %>
					<div class='form_button'>"
						.show_button(array('text' => __("Update", 'lang_fea')))
						.input_hidden(array('name' => 'user_id', 'value' => "<%= user_id %>"))
					."</div>
				</form>
			</script>";

			/*if(IS_ADMIN)
			{
				$templates .= "<script type='text/template' id='template_admin_registration_create'>
					<form method='post' action='' class='mf_form' data-api-url='".$plugin_include_url."' data-action='admin/registration/save'>
						Test...
						<div class='form_button'>"
							.show_button(array('text' => __("Submit", 'lang_fea')))
						."</div>
					</form>
				</script>";
			}*/
		}

		$arr_views['profile'] = array(
			'name' => __("Profile", 'lang_fea'),
			'icon' => "far fa-user-circle",
			'items' => array(
				array(
					'id' => 'edit',
					'name' => __("Edit Profile", 'lang_fea'),
				),
			),
			'templates' => $templates,
			'api_url' => $plugin_include_url,
		);

		/*if(IS_ADMIN)
		{
			$arr_views['registration'] = array(
				'name' => __("Registration", 'lang_fea'),
				'icon' => "fas fa-user-plus",
				'items' => array(
					array(
						'id' => 'create',
						'name' => __("Register User", 'lang_fea'),
					),
				),
				'templates' => $templates,
				'api_url' => $plugin_include_url,
			);
		}*/

		return $arr_views;
	}

	function init_base_admin_2($arr_views)
	{
		if(!is_admin())
		{
			$plugin_base_include_url = plugins_url()."/mf_base/include/";
			//$plugin_include_url = plugin_dir_url(__FILE__);
			$plugin_version = get_plugin_version(__FILE__);

			mf_enqueue_script('script_base_init', $plugin_base_include_url."backbone/bb.init.js", $plugin_version);

			$setting_fea_pages = get_option_or_default('setting_fea_pages', array());

			if(is_array($setting_fea_pages)) // && count($setting_fea_pages) > 0
			{
				foreach($arr_views as $key => $view)
				{
					if(!in_array($key, $setting_fea_pages))
					{
						unset($arr_views[$key]);
					}
				}
			}
		}

		return $arr_views;
	}

	function edit_profile_url($url) //, $user_id, $scheme
	{
		$obj_base = new mf_base();
		$post_id = $obj_base->has_page_template(array('template' => "/plugins/mf_front_end_admin/include/templates/template_admin.php"));

		if($post_id > 0 && in_array('profile', get_option('setting_fea_pages', array())))
		{
			$url = get_permalink($post_id)."#admin/profile/edit";
		}

		return $url;
	}

	function get_page_templates($templates)
	{
		$templates_path = str_replace(WP_CONTENT_DIR, "", plugin_dir_path(__FILE__))."templates/";

		$templates[$templates_path.'template_admin.php'] = __("Front-End Admin", 'lang_fea');

		return $templates;
	}
}