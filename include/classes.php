<?php

class mf_fea
{
	var $meta_prefix = 'mf_fea_';
	var $arr_views  = array();
	var $post_pre_content = "";
	var $post_content = "";

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

	function get_front_end_views_for_select($data)
	{
		$arr_data = $arr_include = array();

		switch($data['type'])
		{
			case 'active':
				$arr_include = get_option('setting_fea_pages', array());
			break;
		}

		$arr_views = apply_filters('init_base_admin', array(), array('include' => 'all'));

		//do_log("FEA views: ".var_export($arr_views, true));

		foreach($arr_views as $key => $arr_view)
		{
			if(!is_array($arr_include) || count($arr_include) == 0 || in_array($key, $arr_include))
			{
				$arr_data[$key] = $arr_view['name'];

				foreach($arr_view['items'] as $arr_item)
				{
					$key_child = 'internal_'.$arr_item['id'];

					if($key_child != $key)
					{
						if(!is_array($arr_include) || count($arr_include) == 0 || in_array($key_child, $arr_include))
						{
							$arr_data[$key_child] = " - ".$arr_item['name'];
						}
					}
				}
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

	function cron_base()
	{
		global $wpdb;

		$obj_cron = new mf_cron();
		$obj_cron->start(__CLASS__);

		if($obj_cron->is_running == false)
		{
			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->postmeta." SET meta_value = %s WHERE meta_key = %s AND meta_value = %s", 'template_admin.php', '_wp_page_template', $this->get_template_path().'template_admin.php'));
		}

		$obj_cron->end();
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
						if(IS_ADMINISTRATOR)
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

			$setting_fea_pages = get_option('setting_fea_pages');

			if(is_array($setting_fea_pages) && count($setting_fea_pages) > 0)
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

		echo show_select(array('data' => $this->get_front_end_views_for_select(array('type' => 'active')), 'name' => $setting_key."[]", 'value' => $option, 'allow_hidden_field' => false));
	}

	function setting_fea_redirect_after_login_callback()
	{
		$setting_key = get_setting_key(__FUNCTION__);
		$option = get_option($setting_key);

		echo show_select(array('data' => get_roles_for_select(array('add_choose_here' => false, 'use_capability' => false)), 'name' => $setting_key."[]", 'value' => $option, 'description' => __("Users with these roles will be redirected after login and to the their front-end profile", 'lang_fea')));
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

	function get_login_redirect($redirect_to, $user_data)
	{
		$setting_fea_redirect_after_login = get_option_or_default('setting_fea_redirect_after_login', array());

		if(isset($user_data->roles) && is_array($user_data->roles) && count(array_intersect($setting_fea_redirect_after_login, $user_data->roles)) > 0)
		{
			$post_url = apply_filters('get_front_end_admin_url', '');

			if($post_url != '')
			{
				$redirect_to = $post_url;
			}
		}

		return $redirect_to;
	}

	function get_page_template($post_id)
	{
		global $wpdb;

		return $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM ".$wpdb->posts." INNER JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id WHERE ID = '%d' AND post_type = %s AND meta_key = %s LIMIT 0, 1", $post_id, 'page', '_wp_page_template'));
	}

	function get_template_path()
	{
		return str_replace(WP_CONTENT_DIR, "", plugin_dir_path(__FILE__))."templates/";
	}

	function display_post_states($post_states, $post)
	{
		$page_template = $this->get_page_template($post->ID);

		switch($page_template)
		{
			case 'template_admin.php':
				$post_states['template_admin'] = __("Front-End Admin", 'lang_fea');
			break;

			/*default:
				$post_states['default'] = $page_template;
			break;*/
		}

		return $post_states;
	}

	function get_template_admin()
	{
		global $post, $obj_theme_core, $obj_theme;

		//Only effective if cache is off, so we need an extra check in the API + possibly to invalidate cache on this page
		if(is_user_logged_in() == false)
		{
			mf_redirect(wp_login_url()."?redirect_to=".$_SERVER['REQUEST_URI']);
		}

		get_header();

			if(IS_ADMINISTRATOR)
			{
				echo "<div rel='".(have_posts() ? "yes" : "no")."'></div>";
			}

			if(have_posts())
			{
				$this->arr_views = apply_filters('init_base_admin', array(), array('init' => true));

				if(!isset($obj_theme_core) && class_exists('mf_theme_core'))
				{
					$obj_theme_core = new mf_theme_core();
				}

				if(!isset($obj_theme) && class_exists('mf_theme'))
				{
					$obj_theme = new mf_theme();
				}

				echo "<article".(IS_ADMINISTRATOR ? " class='template_admin'" : "").">";

					while(have_posts())
					{
						the_post();

						$post_title = $post->post_title;
						$this->post_content = apply_filters('the_content', $post->post_content);

						$this->post_pre_content = "";

						$is_heading_visible = (isset($obj_theme) ? $obj_theme->is_heading_visible($post) : true);

						if($is_heading_visible)
						{
							$this->post_pre_content .= "<h1>".$post_title."</h1>";
						}

						if(is_user_logged_in())
						{
							$setting_fea_user_info = get_option_or_default('setting_fea_user_info', array());

							if(count($setting_fea_user_info) > 0)
							{
								$this->post_pre_content .= "<section class='logged_in'>
									<p>";

										if(in_array('name', $setting_fea_user_info))
										{
											$this->post_pre_content .= "<i class='fa fa-user'></i> ".get_user_info();
										}

										if(in_array('role', $setting_fea_user_info))
										{
											$arr_roles = get_roles_for_select(array('add_choose_here' => false, 'use_capability' => false));
											$user_role = get_current_user_role(get_current_user_id());

											if(isset($arr_roles[$user_role]))
											{
												$this->post_pre_content .= " (".$arr_roles[$user_role].")";
											}

											else
											{
												$this->post_pre_content .= " (".$user_role.")";
											}
										}

										if(in_array('logout', $setting_fea_user_info))
										{
											$this->post_pre_content .= " - <a href='".wp_logout_url()."'>".__("Log Out", 'lang_fea')."</a>";
										}

									$this->post_pre_content .= "</p>
								</section>";
							}
						}

						if(count($this->arr_views) > 0)
						{
							$this->get_menu();

							$this->post_content = "<div class='error hide'><p></p></div>
							<div class='updated hide'><p></p></div>
							<div class='admin_container'>
								<div class='default'>".$this->post_content."</div>
								<div class='loading hide'><i class='fa fa-spinner fa-spin fa-3x'></i></div>";

								foreach($this->arr_views as $key => $view)
								{
									foreach($view['items'] as $item)
									{
										if(!isset($item['is_custom']) || $item['is_custom'] == false)
										{
											@list($id, $rest) = explode("/", $item['id']);

											$this->post_content .= "<div id='admin_".$key."_".$id."' class='admin_container_child hide'>
												<".($is_heading_visible ? "h2" : "h1").">";

													if(isset($item['heading']['name']))
													{
														$this->post_content .= $item['heading']['name'];
													}

													else
													{
														$this->post_content .= $view['name'];
													}

													if(isset($item['heading']['button']['url']) && isset($item['heading']['button']['name']))
													{
														$this->post_content .= "<div".get_form_button_classes().">
															<a href='".$item['heading']['button']['url']."' class='button'>".$item['heading']['button']['name']."</a>
														</div>";
													}

												$this->post_content .= "</".($is_heading_visible ? "h2" : "h1").">
												<div>
													<i class='fa fa-spinner fa-spin fa-3x'></i>
												</div>
											</div>";
										}
									}
								}

							$this->post_content .= "</div>";

							$arr_templates_id = array();

							foreach($this->arr_views as $key => $view)
							{
								if(!isset($view['templates_id']) || !in_array($view['templates_id'], $arr_templates_id))
								{
									if(isset($view['templates']))
									{
										$this->post_content .= $view['templates'];
									}
								}

								if(isset($view['templates_id']))
								{
									$arr_templates_id[] = $view['templates_id'];
								}
							}
						}

						if(is_active_sidebar('widget_after_heading') && isset($obj_theme_core) && $obj_theme_core->is_post_password_protected() == false)
						{
							ob_start();

							dynamic_sidebar('widget_after_heading');

							$widget_content = ob_get_clean();

							if($widget_content != '')
							{
								$this->post_pre_content .= "<div class='aside after_heading'>"
									.$widget_content
								."</div>";
							}
						}

						echo apply_filters('filter_template_admin_content', $this->post_pre_content
						."<section>"
							.$this->post_content
						."</section>");
					}

				echo "</article>";
			}

		get_footer();
	}

	function login_init()
	{
		$action = check_var('action');

		switch($action)
		{
			case 'logout':
				// Do nothing
			break;

			default:
				if(is_user_logged_in())
				{
					$redirect_to = (current_user_can('read') ? admin_url() : home_url());
					$user_data = get_userdata(get_current_user_id());

					$redirect_to = $this->get_login_redirect($redirect_to, $user_data);

					wp_redirect($redirect_to, 302);
					exit;
				}
			break;
		}
	}

	function login_redirect($redirect_to, $request, $user_data)
	{
		// Just in case we have sent this variable along with the URL
		$redirect_to = check_var('redirect_to', 'char', true, $redirect_to);

		if($redirect_to == admin_url())
		{
			$redirect_to = $this->get_login_redirect($redirect_to, $user_data);
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

	function get_menu($data = array())
	{
		if(!isset($data['is_front_end_page'])){		$data['is_front_end_page'] = true;} // Add a check if we are on the front end page instead?

		$setting_fea_display_in_menu = get_option_or_default('setting_fea_display_in_menu', array());

		/* Filter those that are not included via settings */
		#################################
		foreach($this->arr_views as $key => $view)
		{
			if(!in_array($key, $setting_fea_display_in_menu))
			{
				$this->arr_views[$key]['display_in_menu'] = false;
			}
		}
		#################################

		/* Add Custom Menu */
		#################################
		$has_custom_menu = false;

		$locations = get_nav_menu_locations();

		if(isset($locations['front_end_admin']))
		{
			$post_parent = '';

			$arr_menu = wp_get_nav_menu_items($locations['front_end_admin']);

			if(count($arr_menu) > 0)
			{
				global $obj_font_icons;

				if(!isset($obj_font_icons))
				{
					$obj_font_icons = new mf_font_icons();
				}

				$arr_icons = $obj_font_icons->get_array();

				$exclude = array("fa-bar-chart-o");
				$include = array("fa-chart-bar");

				$front_end_admin_url = apply_filters('get_front_end_admin_url', '');

				foreach($arr_menu as $menu_object)
				{
					$menu_object_id = $menu_object->ID;
					$menu_object_title = $menu_object->post_title != '' ? $menu_object->post_title : $menu_object->title;
					$menu_object_url = str_replace($front_end_admin_url, "", $menu_object->url);
					$menu_object_parent = $menu_object->menu_item_parent;

					$menu_object_icon = "fas fa-arrow-alt-circle-right";

					$menu_icons = get_post_meta($menu_object_id, 'menu-icons', true);

					if(isset($menu_icons['icon']) && $menu_icons['icon'] != '')
					{
						$found_icon = false;

						$menu_icon = str_replace($exclude, $include, $menu_icons['icon']);

						foreach($arr_icons as $key => $value)
						{
							if(strpos($value, $menu_icon))
							{
								$menu_object_icon = $value;

								$found_icon = true;

								break;
							}
						}

						if($found_icon == false)
						{
							do_log("No Icon Found: ".var_export($menu_icons, true));
						}
					}

					if(substr($menu_object_url, 0, 7) == "#admin/")
					{
						$arr_menu_object_url_parts = explode("/", $menu_object_url);

						if(isset($this->arr_views[$arr_menu_object_url_parts[1]]))
						{
							if($menu_object_parent == 0)
							{
								$post_parent = $arr_menu_object_url_parts[1];
							}

							else if($post_parent != '')
							{
								$this->arr_views[$arr_menu_object_url_parts[1]]['items'][] = array(
									'id' => $arr_menu_object_url_parts[2],
									'name' => $menu_object_title,
									'is_custom' => true,
								);

								$has_custom_menu = true;
							}
						}

						else
						{
							$this->arr_views[$arr_menu_object_url_parts[1]] = array(
								'name' => $menu_object_title,
								'icon' => $menu_object_icon,
								'items' => array(
									array(
										'id' => $arr_menu_object_url_parts[2],
										'name' => $menu_object_title,
										'is_custom' => true,
									),
								),
							);

							$has_custom_menu = true;

							if($menu_object_parent == 0)
							{
								$post_parent = $arr_menu_object_url_parts[1];
							}
						}
					}

					else
					{
						if(isset($this->arr_views[$menu_object_url]))
						{
							if($menu_object_parent == 0)
							{
								$post_parent = $menu_object_url;
							}

							else if($post_parent != '')
							{
								$this->arr_views[$post_parent]['items'][] = array(
									'id' => $menu_object_url,
									'name' => $menu_object_title,
									'is_custom' => true,
								);

								$has_custom_menu = true;
							}
						}

						else
						{
							if($menu_object_parent == 0)
							{
								$this->arr_views[$menu_object_url] = array(
									'name' => $menu_object_title,
									'icon' => $menu_object_icon,
									'items' => array(
										array(
											'id' => $menu_object_url,
											'name' => $menu_object_title,
											'is_custom' => true,
										),
									),
								);

								$has_custom_menu = true;

								$post_parent = $menu_object_url;
							}

							else if($post_parent != '')
							{
								$this->arr_views[$post_parent]['items'][] = array(
									'id' => $menu_object_url,
									'name' => $menu_object_title,
									'is_custom' => true,
								);

								$has_custom_menu = true;
							}

							else
							{
								do_log("Nav Menu Item Error: ".var_export($menu_object, true));
							}
						}
					}
				}
			}
		}
		#################################

		if(count($setting_fea_display_in_menu) > 0 || $has_custom_menu == true)
		{
			$this->post_pre_content .= "<nav class='fea_menu'>
				<ul>";

					if($this->post_content != '')
					{
						$this->post_pre_content .= "<li>
							<a href=''>
								<i class='fas fa-home'></i>
								<span>".__("Home", 'lang_fea')."</span>
							</a>
						</li>";
					}

					$front_end_admin_url = ($data['is_front_end_page'] ? "" : apply_filters('get_front_end_admin_url', ''));

					foreach($this->arr_views as $key => $view)
					{
						if(!isset($view['display_in_menu']) || $view['display_in_menu'] == true)
						{
							$this->post_pre_content .= "<li>";

								$i = 0;

								$count_temp = $count_temp_displayed = count($view['items']);

								$has_start_ul = false;

								foreach($view['items'] as $arr_item)
								{
									if($i == 0 || !isset($arr_item['display_in_menu']) || $arr_item['display_in_menu'] == true)
									{
										$item_url = "";

										if(!isset($arr_item['clickable']) || $arr_item['clickable'] == true || $count_temp == 1)
										{
											if(filter_var($key, FILTER_VALIDATE_URL))
											{
												$item_url = $key;
											}

											else if(filter_var($arr_item['id'], FILTER_VALIDATE_URL))
											{
												$item_url = $arr_item['id'];
											}

											else if(substr($arr_item['id'], 0, 1) == "/")
											{
												$item_url = $arr_item['id'];
											}

											else
											{
												$item_url = $front_end_admin_url."#admin/".str_replace("_", "/", $key)."/".$arr_item['id'];
											}
										}

										$api_url = (isset($view['api_url']) ? $view['api_url'] : '');

										if($i == 0)
										{
											if($item_url != '')
											{
												$this->post_pre_content .= "<a href='".$item_url."'";

													if($api_url != '')
													{
														$this->post_pre_content .= " data-api-url='".$api_url."'";
													}

												$this->post_pre_content .= ">";
											}

											else
											{
												$this->post_pre_content .= "<span>";
											}

												if(isset($view['icon']) && $view['icon'] != '')
												{
													$this->post_pre_content .= "<i class='".$view['icon']."'></i>";
												}

												$this->post_pre_content .= "<span>".$view['name']."</span>";

											if($item_url != '')
											{
												$this->post_pre_content .= "</a>";
											}

											else
											{
												$this->post_pre_content .= "</span>";
											}

											$i++;
										}

										else if(in_array('internal_'.$arr_item['id'], $setting_fea_display_in_menu))
										{
											if($i == 1)
											{
												$this->post_pre_content .= "<ul>";

												$has_start_ul = true;
											}

											$this->post_pre_content .= "<li class='sub' rel='".$arr_item['id']."'>
												<a href='".$item_url."'";

													if($api_url != '')
													{
														$this->post_pre_content .= " data-api-url='".$api_url."'";
													}

												$this->post_pre_content .= ">
													<span>".$arr_item['name']."</span>
												</a>
											</li>";

											$i++;
										}
									}
								}

								if($has_start_ul == true)
								{
									$this->post_pre_content .= "</ul>";

									$has_start_ul = false;
								}

							$this->post_pre_content .= "</li>";
						}
					}

				$this->post_pre_content .= "</ul>
			</nav>";
		}
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

	function widgets_init()
	{
		register_widget('widget_fea_menu');
	}

	function get_pagination_list($data)
	{
		if(!isset($data['prefix'])){		$data['prefix'] = "";}

		$out = "<form method='post' action='' class='mf_form' data-action='".$data['action']."'>"
			.$data['prefix']
			."<% if(pagination.list_amount > 0)
			{ %>
				<div class='tablenav-pages'>
					<span class='displaying-num'>".sprintf($data['amount_text'], "<%= pagination.list_amount %>")."</span>
					<% if(Object.keys(pagination.pages).length > 1)
					{ %>
						<span".get_form_button_classes().">
							<% if(Object.keys(pagination.pages).length > 6)
							{ %>
								<a href='#".$data['action']."/1' class='button<% if(pagination.current_page <= 2){ %> is_disabled<% } %>' title='".__("First", 'lang_fea')."'><i class='fas fa-angle-double-left'></i></a>
							<% } %>
							<a href='#".$data['action']."/<%= (parseInt(pagination.current_page) - 1) %>' class='button<% if(pagination.current_page <= 1){ %> is_disabled<% } %>' title='".__("Previous", 'lang_fea')."'><i class='fas fa-angle-left'></i></a>
							<span>".sprintf(__("Page %s of %s", 'lang_fea'), show_textfield(array('value' => "<%= pagination.current_page %>", 'xtra' => " data-action='".$data['action']."' data-max='<%= Object.keys(pagination.pages).length %>'")), "<%= Object.keys(pagination.pages).length %>")."</span>
							<a href='#".$data['action']."/<%= (parseInt(pagination.current_page) + 1) %>' class='button<% if(pagination.current_page >= Object.keys(pagination.pages).length){ %> is_disabled<% } %>' title='".__("Next", 'lang_fea')."'><i class='fas fa-angle-right'></i></a>
							<% if(Object.keys(pagination.pages).length > 6)
							{ %>
								<a href='#".$data['action']."/<%= Object.keys(pagination.pages).length %>' class='button<% if(pagination.current_page >= (Object.keys(pagination.pages).length - 1)){ %> is_disabled<% } %>' title='".__("Last", 'lang_fea')."'><i class='fas fa-angle-double-right'></i></a>
							<% } %>
						</span>
					<% } %>
				</div>
			<% } %>
		</form>";

		return $out;
	}

	function init_base_admin($arr_views, $data = array())
	{
		global $wpdb;

		if(!isset($data['init'])){	$data['init'] = false;}

		$templates = "";

		$plugin_base_include_url = plugins_url()."/mf_base/include/";
		$plugin_include_url = plugin_dir_url(__FILE__);
		$plugin_version = get_plugin_version(__FILE__);

		mf_enqueue_style('style_fea', $plugin_include_url."style.php", $plugin_version);

		if($data['init'] == true)
		{
			mf_enqueue_script('underscore');
			mf_enqueue_script('backbone');
			mf_enqueue_script('script_base_plugins', $plugin_base_include_url."backbone/bb.plugins.js", $plugin_version);

			mf_enqueue_script('script_fea_router', $plugin_include_url."backbone/bb.admin.router.js", $plugin_version);
			mf_enqueue_script('script_fea_models', $plugin_include_url."backbone/bb.admin.models.js", array('api_url' => $plugin_include_url), $plugin_version);
			mf_enqueue_script('script_fea_views', $plugin_include_url."backbone/bb.admin.views.js", array('api_url' => $plugin_include_url), $plugin_version);

			$templates .= "<script type='text/template' id='template_admin_profile_edit'>
				<form method='post' action='' class='mf_form' data-api-url='".$plugin_include_url."' data-action='admin/profile/save'>
					<% _.each(fields, function(field)
					{
						switch(field.type)
						{
							case 'date': %>"
								.show_textfield(array('type' => 'date', 'name' => '<%= field.name %>', 'text' => "<%= field.text %>", 'value' => "<%= field.value %>"))
							."<% break;

							case 'email': %>"
								.show_textfield(array('type' => 'email', 'name' => '<%= field.name %>', 'text' => "<%= field.text %>", 'value' => "<%= field.value %>", 'description' => "<%= field.description %>"))
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
									.get_media_library(array('type' => 'image', 'name' => '<%= field.name %>', 'value' => "<%= field.value %>"))
								."</div>
							<% break;

							case 'number': %>"
								.show_textfield(array('type' => 'number', 'name' => '<%= field.name %>', 'text' => "<%= field.text %>", 'value' => "<%= field.value %>"))
							."<% break;

							case 'password': %>"
								.show_password_field(array('name' => '<%= field.name %>', 'text' => "<%= field.text %>", 'placeholder' => __("Enter a New Password Here", 'lang_fea')))
							."<% break;

							case 'select': %>
								<div class='form_select type_<%= field.type %><%= field.class %>'>
									<label for='<%= field.name %>'><%= field.text %></label>
									<select id='<%= field.name %>' name='<%= field.name %><% if(field.multiple == true){ %>[]<% } %>' class='mf_form_field'<% if(field.multiple == true){ %> multiple<% } %>"
										//."<%= field.attributes %>"
										."<% _.each(field.attributes, function(attribute_value, attribute_key)
										{%> <%= attribute_key %>='<%= attribute_value %>'<% }); %>"
									.">
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
								.show_textfield(array('name' => '<%= field.name %>', 'text' => "<%= field.text %>", 'value' => "<%= field.value %>"))
							."<% break;

							case 'textarea': %>"
								.show_textarea(array('name' => '<%= field.name %>', 'text' => "<%= field.text %>", 'value' => "<%= field.value %>"))
							."<% break;

							default: %>
								<strong><%= meta_field.type %></strong>: <%= meta_field.name %><br>
							<% break;
						}
					}); %>
					<div".get_form_button_classes().">"
						.show_button(array('text' => __("Update", 'lang_fea')))
						.input_hidden(array('name' => 'user_id', 'value' => "<%= user_id %>"))
					."</div>
				</form>
			</script>";

			/*if(IS_ADMINISTRATOR)
			{
				$templates .= "<script type='text/template' id='template_admin_registration_create'>
					<form method='post' action='' class='mf_form' data-api-url='".$plugin_include_url."' data-action='admin/registration/save'>
						Test...
						<div".get_form_button_classes().">"
							.show_button(array('text' => __("Submit", 'lang_fea')))
						."</div>
					</form>
				</script>";
			}*/

			if(IS_EDITOR)
			{
				$templates .= "<script type='text/template' id='template_admin_posts_list'>"
					.$this->get_pagination_list(array(
						'action' => 'admin/posts/list',
						'amount_text' => __("%s posts", 'lang_fea'),
					))
					."<table class='widefat striped'>
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
												.(IS_ADMINISTRATOR ? "<a href='".admin_url("post.php?post=<%= posts.post_id %>&action=edit")."'>".__("Edit in Admin", 'lang_fea')."</a>" : "")
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

				<script type='text/template' id='template_admin_posts_edit'>
					<form method='post' action='' class='mf_form' data-api-url='".$plugin_include_url."' data-action='admin/posts/save'>
						<div class='context_normal'>
							<div id='".$this->meta_prefix."information' class='meta_box'>
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
						</div>
						<div class='context_side'>
							<div id='".$this->meta_prefix."actions' class='meta_box'>
								<div".get_form_button_classes().">"
									.show_button(array('text' => __("Save", 'lang_fea')))
									."<a href='#admin/posts/list' class='button'>".__("Cancel", 'lang_fea')."</a>"
									."<% if(post_id > 0)
									{ %>"
										.input_hidden(array('name' => 'post_id', 'value' => "<%= post_id %>"))
									."<% } %>"
								."</div>
							</div>
							<div id='".$this->meta_prefix."settings' class='meta_box'>"
								//."<h2>".__("Settings", 'lang_fea')."</h2>"
								."<div>"
									.show_select(array('data' => $this->get_post_status_for_select(), 'name' => 'post_status', 'text' => __("Status", 'lang_fea'), 'value' => "<%= post_status %>"))
									.show_select(array('data' => get_users_for_select(array('add_choose_here' => false)), 'name' => 'post_author', 'text' => __("Author", 'lang_fea'), 'value' => "<%= post_author %>"))
									.show_textfield(array('name' => 'post_name', 'text' => __("Slug", 'lang_fea'), 'value' => "<%= post_name %>"))
									.show_select(array('data' => get_categories_for_select(array('add_choose_here' => true)), 'name' => 'post_categories', 'text' => __("Category", 'lang_fea'), 'value' => "<%= post_categories %>"))
									.show_select(array('data' => get_yes_no_for_select(array('add_choose_here' => false)), 'name' => 'comment_status', 'text' => __("Allow Comments", 'lang_fea'), 'value' => "<%= comment_status %>"))
								."</div>
							</div>
						</div>
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

		/*if(IS_ADMINISTRATOR)
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
						'heading' => array(
							'button' => array(
								'url' => "#admin/posts/edit",
								'name' => __("Add New", 'lang_fea'),
							),
						),
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

			$post_id = $obj_base->has_page_template(array('template' => 'template_admin.php')); //$this->get_template_path().
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
		$user_data = get_userdata(get_current_user_id());

		$setting_fea_redirect_after_login = get_option_or_default('setting_fea_redirect_after_login', array());

		if(isset($user_data->roles) && is_array($user_data->roles) && count(array_intersect($setting_fea_redirect_after_login, $user_data->roles)) > 0)
		{
			$post_url = apply_filters('get_front_end_admin_url', '');

			if($post_url != '' && in_array('profile', get_option_or_default('setting_fea_pages', array())))
			{
				$url = $post_url."#admin/profile/edit";
			}
		}

		return $url;
	}

	function get_page_templates($templates)
	{
		$templates['template_admin.php'] = __("Front-End Admin", 'lang_fea');

		return $templates;
	}
}

class widget_fea_menu extends WP_Widget
{
	var $obj_fea;
	var $widget_ops;
	var $arr_default = array(
		'menu_heading' => "",
	);

	function __construct()
	{
		$this->obj_fea = new mf_fea();

		$this->widget_ops = array(
			'classname' => 'fea_menu',
			'description' => __("Display front-end admin menu", 'lang_fea'),
		);

		parent::__construct(str_replace("_", "-", $this->widget_ops['classname']).'-widget', __("Front-End Admin Menu", 'lang_fea'), $this->widget_ops);
	}

	function widget($args, $instance)
	{
		extract($args);
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		$this->obj_fea->arr_views = apply_filters('init_base_admin', array());

		$this->obj_fea->get_menu(array('is_front_end_page' => false));

		if($this->obj_fea->post_pre_content != '')
		{
			echo apply_filters('filter_before_widget', $before_widget);

				if($instance['menu_heading'] != '')
				{
					$instance['menu_heading'] = apply_filters('widget_title', $instance['menu_heading'], $instance, $this->id_base);

					echo $before_title
						.$instance['menu_heading']
					.$after_title;
				}

				echo $this->obj_fea->post_pre_content
			.$after_widget;
		}
	}

	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$new_instance = wp_parse_args((array)$new_instance, $this->arr_default);

		$instance['menu_heading'] = sanitize_text_field($new_instance['menu_heading']);

		return $instance;
	}

	function form($instance)
	{
		$instance = wp_parse_args((array)$instance, $this->arr_default);

		echo "<div class='mf_form'>"
			.show_textfield(array('name' => $this->get_field_name('menu_heading'), 'text' => __("Heading", 'lang_fea'), 'value' => $instance['menu_heading'], 'xtra' => " id='".$this->widget_ops['classname']."-title'"))
		."</div>";
	}
}