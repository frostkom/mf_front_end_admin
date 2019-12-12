<?php

class mf_fea
{
	function __construct()
	{
		$this->meta_prefix = "mf_fea_";
	}

	function get_user_info_for_select()
	{
		$arr_data = array(
			'name' => __("Name", 'lang_fea'),
			'role' => __("Role", 'lang_fea'),
			'logout' => __("Log Out", 'lang_fea'),
		);

		return $arr_data;
	}

	function get_front_end_views_for_select($data)
	{
		$arr_data = $arr_include = array();

		switch($data['type'])
		{
			case 'active':
				$arr_include = get_option('setting_fea_pages');
			break;
		}

		$arr_views = apply_filters('init_base_admin', array(), array('include' => 'all'));

		foreach($arr_views as $key => $view)
		{
			if(count($arr_include) == 0 || in_array($key, $arr_include))
			{
				$arr_data[$key] = $view['name'];
			}
		}

		return $arr_data;
	}

	function get_post_status_for_select()
	{
		$arr_data = array();

		$arr_data['publish'] = __("Publish", 'lang_fea');
		$arr_data['draft'] = __("Draft", 'lang_fea');

		return $arr_data;
	}

	function get_post_categories($data)
	{
		if(!isset($data['output'])){			$data['output'] = '';}

		$arr_categories = get_the_category($data['post_id']);

		if(is_array($arr_categories) && count($arr_categories) > 0)
		{
			switch($data['output'])
			{
				case 'id':
					$out = array();

					foreach($arr_categories as $category)
					{
						$out[] = $category->term_taxonomy_id;
					}
				break;

				case 'html':
					$out = "";

					//$category_base_url = get_site_url()."/category/";

					foreach($arr_categories as $category)
					{
						$out .= ($out != '' ? ", " : "").$category->name; //"<a href='".$category_base_url.$category->slug."'>".
					}
				break;

				default:
					do_log("get_post_categories: No output type (".$data['output'].")");
				break;
			}
		}

		return $out;
	}

	function wp_before_admin_bar_render()
	{
		global $wp_admin_bar;

		$setting_fea_display_in_menu = get_option_or_default('setting_fea_display_in_menu', array());

		if(count($setting_fea_display_in_menu) > 0)
		{
			$post_id = apply_filters('get_front_end_admin_id', 0);

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
	}

	function settings_fea()
	{
		$options_area = __FUNCTION__;

		add_settings_section($options_area, "",	array($this, $options_area."_callback"), BASE_OPTIONS_PAGE);

		if(apply_filters('get_front_end_admin_id', 0) > 0)
		{
			$arr_settings = array(
				'setting_fea_user_info' => __("User Info", 'lang_fea'),
				'setting_fea_pages' => __("Pages", 'lang_fea'),
			);

			if(count(get_option('setting_fea_pages')) > 0)
			{
				$arr_settings['setting_fea_display_in_menu'] = __("Display in Menu", 'lang_fea');
			}

			$arr_settings['setting_fea_redirect_after_login'] = __("Redirect After Login", 'lang_fea');
			$arr_settings['setting_fea_content_width'] = __("Content Width", 'lang_fea');
		}

		else
		{
			$arr_settings = array(
				'setting_fea_get_started' => __("Get Started", 'lang_fea'),
			);
		}

		show_settings_fields(array('area' => $options_area, 'object' => $this, 'settings' => $arr_settings));
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
		$option = get_option_or_default($setting_key, array());

		echo show_select(array('data' => $this->get_front_end_views_for_select(array('type' => 'all')), 'name' => $setting_key."[]", 'value' => $option));
	}

	function setting_fea_display_in_menu_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option_or_default($setting_key, array());

		echo show_select(array('data' => $this->get_front_end_views_for_select(array('type' => 'active')), 'name' => $setting_key."[]", 'value' => $option));
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

	function setting_fea_get_started_callback()
	{
		echo "<em>".sprintf(__("To use this functionality you have to %sAdd a new page%s and set %s as template", 'lang_fea'), "<a href='".admin_url("post-new.php?post_type=page")."'>", "</a>", __("Front-End Admin", 'lang_fea'))."</em>";
	}

	function login_redirect($redirect_to, $request, $user)
	{
		$admin_url = admin_url();

		// Just in case we have sent this variable along with the URL
		$redirect_to = check_var('redirect_to', 'char', true, $redirect_to);

		if($redirect_to == $admin_url)
		{
			$setting_fea_redirect_after_login = get_option_or_default('setting_fea_redirect_after_login', array());

			if(isset($user->roles) && is_array($user->roles) && count(array_intersect($setting_fea_redirect_after_login, $user->roles)) > 0)
			{
				$post_url = apply_filters('get_front_end_admin_url', '');

				if($post_url != '')
				{
					$redirect_to = $post_url;
				}
			}
		}

		return $redirect_to;
	}

	function wp_nav_menu_objects($sorted_menu_items, $args)
	{
		$front_end_admin_url = apply_filters('get_front_end_admin_url', '');
		$arr_views = apply_filters('init_base_admin', array());

		foreach($sorted_menu_items as $item_key => $item_value)
		{
			if(substr($item_value->url, 0, strlen($front_end_admin_url)) == $front_end_admin_url && strpos($item_value->url, "#") !== 0)
			{
				list($rest, $post_hash) = explode("#", $item_value->url);

				$arr_hash = explode("/", $post_hash);

				$is_allowed = false;

				foreach($arr_views as $view_key => $view_value)
				{
					if($arr_hash[1] == $view_key)
					{
						foreach($view_value['items'] as $view_item_key => $view_item_value)
						{
							if($arr_hash[2] == $view_item_value['id'])
							{
								$is_allowed = true;
							}
						}
					}
				}

				if($is_allowed == false)
				{
					unset($sorted_menu_items[$item_key]);
				}
			}
		}

		return $sorted_menu_items;
	}

	function get_footer()
	{
		global $obj_base;

		if(!isset($obj_base))
		{
			$obj_base = new mf_base();
		}

		echo $obj_base->get_templates(array('lost_connection', 'loading'));
	}

	function after_setup_theme()
	{
		register_nav_menus(array(
			'front_end_admin' => __("Front-End Admin", 'lang_fea'),
		));
	}

	function init_base_admin($arr_views)
	{
		global $wpdb;

		$templates = "";

		$plugin_base_include_url = plugins_url()."/mf_base/include/";
		$plugin_include_url = plugin_dir_url(__FILE__);
		$plugin_version = get_plugin_version(__FILE__);

		if(!is_admin())
		{
			mf_enqueue_style('style_fea', $plugin_include_url."style.php", $plugin_version);

			mf_enqueue_script('underscore');
			mf_enqueue_script('backbone');
			mf_enqueue_script('script_base_plugins', $plugin_base_include_url."backbone/bb.plugins.js", $plugin_version);

			mf_enqueue_script('script_fea_router', $plugin_include_url."bb.admin.router.js", $plugin_version);
			mf_enqueue_script('script_fea_models', $plugin_include_url."bb.admin.models.js", array('api_url' => $plugin_include_url), $plugin_version);
			mf_enqueue_script('script_fea_views', $plugin_include_url."bb.admin.views.js", array('api_url' => $plugin_include_url), $plugin_version);

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
									.get_media_library(array('type' => 'image', 'name' => "<%= field.name %>", 'value' => "<%= field.value %>"))
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

							case 'textarea': %>"
								.show_textarea(array('name' => "<%= field.name %>", 'text' => "<%= field.text %>", 'value' => "<%= field.value %>"))
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

			if(IS_EDITOR)
			{
				$templates .= "<script type='text/template' id='template_admin_posts_list'>
					<% if(pagination.list_amount > 0)
					{ %>
						<form method='post' action='' class='mf_form' data-action='admin/posts/list'>
							<div class='tablenav-pages'>
								<span class='displaying-num'>".sprintf(__("%s posts", 'lang_fea'), "<%= pagination.list_amount %>")."</span>
								<% if(Object.keys(pagination.pages).length > 1)
								{ %>
									<span class='pagination-links form_button'>
										<a href='#admin/posts/list/<%= (parseInt(pagination.current_page) - 1) %>' class='button<% if(pagination.current_page <= 1){ %> is_disabled<% } %>' title='".__("Previous", 'lang_fea')."'>&laquo;</a>
										<span>".sprintf(__("Page %s of %s", 'lang_fea'), "<%= pagination.current_page %>", "<%= Object.keys(pagination.pages).length %>")."</span>
										<a href='#admin/posts/list/<%= (parseInt(pagination.current_page) + 1) %>' class='button<% if(pagination.current_page >= Object.keys(pagination.pages).length){ %> is_disabled<% } %>' title='".__("Next", 'lang_fea')."'>&raquo;</a>
									</span>
								<% } %>
							</div>
						</form>
					<% } %>
					<table class='widefat striped'>
						<thead>
							<tr>
								<th>".__("Name", 'lang_fea')."</th>
								<th>".__("Author", 'lang_fea')."</th>
								<th>".__("Category", 'lang_fea')."</th>
								<th>".__("Updated", 'lang_fea')."</th>
							</tr>
						</thead>
						<tbody>
							<% if(list.length > 0)
							{
								_.each(list, function(posts)
								{ %>
									<tr id='posts_<%= posts.post_id %>'<% if(posts.post_status == 'draft'){ %> class='inactive'<% } %>>
										<td>
											<%= posts.post_title %>
											<% if(posts.post_status == 'draft')
											{ %>
												 (".__("Draft").")
											<% } %>
											<div class='row-actions'>"
												."<a href='#admin/posts/edit/<%= posts.post_id %>'>".__("Edit", 'lang_fea')."</a>"
												.(IS_ADMIN ? "<a href='".admin_url("post.php?post=<%= posts.post_id %>&action=edit")."'>".__("Edit in Admin", 'lang_fea')."</a>" : "")
												."<a href='<%= posts.post_url %>'>".__("View", 'lang_fea')."</a>"
											."</div>
										</td>
										<td><%= posts.post_author %></td>
										<td><%= posts.post_categories %></td>
										<td>
											<%= posts.post_date %>
											<% if(posts.post_modified != posts.post_date)
											{ %>
												<div class='row-actions'>
													".__("Updated", 'lang_fea').": <%= posts.post_modified %>
												</div>
											<% } %>
										</td>
									</tr>
								<% });
							}

							else
							{ %>
								<tr><td colspan='4'>".__("There is nothing to show", 'lang_fea')."</td></tr>
							<% } %>
						</tbody>
					</table>
				</script>

				<script type='text/template' id='template_admin_posts_list_message'>
					<p>".__("You have not added anything yet", 'lang_fea')."</p>
				</script>

				<script type='text/template' id='template_admin_posts_edit'>
					<form method='post' action='' class='mf_form' data-api-url='".$plugin_include_url."' data-action='admin/posts/save'>
						<div id='".$this->meta_prefix."information' class='meta_box context_normal'>
							<h2>".__("Information", 'lang_fea')."</h2>
							<div>"
								.show_textfield(array('name' => 'post_title', 'text' => __("Title", 'lang_fea'), 'value' => "<%= post_title %>", 'required' => true))
								.show_textarea(array('name' => 'post_excerpt', 'text' => __("Excerpt", 'lang_fea'), 'value' => "<%= post_excerpt %>"))
								.show_textarea(array('name' => 'post_content', 'text' => __("Content", 'lang_fea'), 'value' => "<%= post_content %>", 'required' => true))
								/*.show_wp_editor(array('name' => 'post_content', 'value' => "<%= post_content %>",
									//'class' => "hide_media_button hide_tabs",
									'mini_toolbar' => true,
									'editor_height' => 400,
									//'statusbar' => false,
								))*/
							."</div>
						</div>
						<div id='".$this->meta_prefix."settings' class='meta_box context_side'>
							<h2>".__("Settings", 'lang_fea')."</h2>
							<div>"
								.show_select(array('data' => $this->get_post_status_for_select(), 'name' => 'post_status', 'text' => __("Status", 'lang_fea'), 'value' => "<%= post_status %>"))
								.show_select(array('data' => get_users_for_select(array('add_choose_here' => false)), 'name' => 'post_author', 'text' => __("Author", 'lang_fea'), 'value' => "<%= post_author %>"))
								.show_textfield(array('name' => 'post_name', 'text' => __("Slug", 'lang_fea'), 'value' => "<%= post_name %>"))
								.show_select(array('data' => get_categories_for_select(array('add_choose_here' => true)), 'name' => 'post_categories', 'text' => __("Category", 'lang_fea'), 'value' => "<%= post_categories %>"))
								.show_select(array('data' => get_yes_no_for_select(array('add_choose_here' => false)), 'name' => 'comment_status', 'text' => __("Allow Comments", 'lang_fea'), 'value' => "<%= comment_status %>"))
							."</div>
						</div>
						<div class='form_button'>"
							.show_button(array('text' => __("Save", 'lang_fea')))
							.input_hidden(array('name' => 'post_id', 'value' => "<%= post_id %>"))
						."</div>
					</form>
				</script>";
			}
		}

		$arr_views['profile'] = array(
			'name' => __("Profile", 'lang_fea'),
			'icon' => "far fa-address-card",
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

		if(IS_EDITOR)
		{
			$arr_views['posts'] = array(
				'name' => __("Posts", 'lang_fea'),
				'icon' => "fas fa-edit",
				'items' => array(
					array(
						'id' => 'list',
						'name' => __("List", 'lang_fea'),
					),
					array(
						'id' => 'edit',
						'name' => __("Add New", 'lang_fea'),
					),
				),
				'templates' => $templates,
				'api_url' => $plugin_include_url,
			);
		}

		return $arr_views;
	}

	function init_base_admin_2($arr_views)
	{
		if(!is_admin())
		{
			$plugin_base_include_url = plugins_url()."/mf_base/include/";
			$plugin_version = get_plugin_version(__FILE__);

			mf_enqueue_script('script_base_init', $plugin_base_include_url."backbone/bb.init.js", $plugin_version);

			$setting_fea_pages = get_option_or_default('setting_fea_pages', array());

			if(is_array($setting_fea_pages))
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

	function get_front_end_admin_id($post_id)
	{
		global $obj_base;

		if(!($post_id > 0))
		{
			if(!isset($obj_base))
			{
				$obj_base = new mf_base();
			}

			$post_id = $obj_base->has_page_template(array('template' => "/plugins/mf_front_end_admin/include/templates/template_admin.php"));
		}

		return $post_id;
	}

	function get_front_end_admin_url($post_url)
	{
		if($post_url == '')
		{
			$post_id = apply_filters('get_front_end_admin_id', 0);

			if($post_id > 0)
			{
				$post_url = get_permalink($post_id);
			}
		}

		return $post_url;
	}

	function edit_profile_url($url)
	{
		$post_url = apply_filters('get_front_end_admin_url', '');

		if($post_url != '' && in_array('profile', get_option_or_default('setting_fea_pages', array())))
		{
			$url = $post_url."#admin/profile/edit";
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