<?php
/*
Template Name: Front-End Admin
*/

//Only effective if cache is off, so we need an extra check in the API + possibly to invalidate cache on this page
if(!is_user_logged_in())
{
	mf_redirect(wp_login_url()."?redirect_to=".$_SERVER['REQUEST_URI']);
}

$arr_views = apply_filters('init_base_admin', array());

get_header();

	if(have_posts())
	{
		if(!isset($obj_theme))
		{
			$obj_theme = new mf_theme();
		}

		echo "<article>";

			while(have_posts())
			{
				the_post();

				$post_title = $post->post_title;
				$post_content = apply_filters('the_content', $post->post_content);

				$post_pre_content = "";

				$is_heading_visible = $obj_theme->is_heading_visible($post);

				if($is_heading_visible)
				{
					$post_pre_content .= "<h1>".$post_title."</h1>";
				}

				if(is_user_logged_in())
				{
					$setting_fea_user_info = get_option('setting_fea_user_info');

					if(is_array($setting_fea_user_info) && count($setting_fea_user_info) > 0)
					{
						$post_pre_content .= "<section class='logged_in'>
							<p>";

								if(in_array('name', $setting_fea_user_info))
								{
									$user_data = get_userdata(get_current_user_id());
									$display_name = $user_data->display_name; //apply_filters('filter_admin_display_name', )

									$post_pre_content .= "<i class='fa fa-user'></i> ".$display_name;
								}

								if(in_array('role', $setting_fea_user_info))
								{
									$arr_roles = get_roles_for_select(array('add_choose_here' => false, 'use_capability' => false));
									$user_role = get_current_user_role(get_current_user_id());

									$post_pre_content .= " (".$arr_roles[$user_role].")";
								}

								if(in_array('logout', $setting_fea_user_info))
								{
									$post_pre_content .= " - <a href='".wp_logout_url()."'>".__("Log Out", 'lang_fea')."</a>";
								}

							$post_pre_content .= "</p>
						</section>";
					}
				}

				if(count($arr_views) > 0)
				{
					if(get_option('setting_fea_display_menu') != 'no')
					{
						/* Add Custom Menu */
						#################################
						$locations = get_nav_menu_locations();

						if(isset($locations['front_end_admin']))
						{
							$post_url = apply_filters('get_front_end_admin_url', '');

							$post_parent = '';

							$arr_menu = wp_get_nav_menu_items($locations['front_end_admin']);

							foreach($arr_menu as $menu_object)
							{
								$menu_object_title = $menu_object->post_title != '' ? $menu_object->post_title : $menu_object->title;
								$menu_object_url = str_replace($post_url, "", $menu_object->url);
								$menu_object_parent = $menu_object->menu_item_parent; //post_parent

								if(substr($menu_object_url, 0, 7) == "#admin/")
								{
									$arr_menu_object_url_parts = explode("/", $menu_object_url);

									if(isset($arr_views[$arr_menu_object_url_parts[1]]))
									{
										if($menu_object_parent == 0)
										{
											$post_parent = $arr_menu_object_url_parts[1];
										}

										else if($post_parent != '')
										{
											$arr_views[$arr_menu_object_url_parts[1]]['items'][] = array(
												'id' => $arr_menu_object_url_parts[2],
												'name' => $menu_object_title,
											);
										}
									}

									else
									{
										$arr_views[$arr_menu_object_url_parts[1]] = array(
											'name' => $menu_object_title,
											'icon' => "fas fa-arrow-alt-circle-right",
											'items' => array(
												array(
													'id' => $arr_menu_object_url_parts[2],
													'name' => $menu_object_title,
												),
											),
										);

										if($menu_object_parent == 0)
										{
											$post_parent = $arr_menu_object_url_parts[1];
										}
									}
								}

								else
								{
									if(isset($arr_views[$menu_object_url]))
									{
										if($menu_object_parent == 0)
										{
											$post_parent = $menu_object_url;
										}

										else if($post_parent != '')
										{
											$arr_views[$post_parent]['items'][] = array(
												'id' => $menu_object_url,
												'name' => $menu_object_title,
											);
										}
									}

									else
									{
										if($menu_object_parent == 0)
										{
											$arr_views[$menu_object_url] = array(
												'name' => $menu_object_title,
												'icon' => "fas fa-arrow-alt-circle-right",
												'items' => array(
													array(
														'id' => $menu_object_url,
														'name' => $menu_object_title,
													),
												),
											);

											$post_parent = $menu_object_url;
										}

										else if($post_parent != '')
										{
											$arr_views[$post_parent]['items'][] = array(
												'id' => $menu_object_url,
												'name' => $menu_object_title,
											);
										}

										else
										{
											do_log("Nav Menu Item Error: ".var_export($menu_object, true));
										}
									}
								}
							}
						}
						#################################

						$post_pre_content .= "<nav>
							<ul>";

								if($post_content != '')
								{
									$post_pre_content .= "<li>
										<a href=''>
											<i class='fas fa-home'></i>
											<span>".__("Home", 'lang_fea')."</span>
										</a>
									</li>";
								}

								foreach($arr_views as $key => $view)
								{
									$post_pre_content .= "<li>";

										$i = 0;

										$count_temp = count($view['items']);

										foreach($view['items'] as $item)
										{
											$item_url = "";

											if(!isset($item['clickable']) || $item['clickable'] == true || $count_temp == 1)
											{
												if(filter_var($key, FILTER_VALIDATE_URL))
												{
													$item_url = $key;
												}

												else if(filter_var($item['id'], FILTER_VALIDATE_URL))
												{
													$item_url = $item['id'];
												}

												else
												{
													$item_url = "#admin/".str_replace("_", "/", $key)."/".$item['id'];
												}
											}

											$api_url = (isset($view['api_url']) ? $view['api_url'] : '');

											if($i == 0)
											{
												if($item_url != '')
												{
													$post_pre_content .= "<a href='".$item_url."'";

														if($api_url != '')
														{
															$post_pre_content .= " data-api-url='".$api_url."'";
														}

													$post_pre_content .= ">";
												}

												else
												{
													$post_pre_content .= "<span>";
												}

													if(isset($view['icon']) && $view['icon'] != '')
													{
														$post_pre_content .= "<i class='".$view['icon']."'></i>";
													}

													$post_pre_content .= "<span>".$view['name']."</span>";

												if($item_url != '')
												{
													$post_pre_content .= "</a>";
												}

												else
												{
													$post_pre_content .= "</span>";
												}
											}

											else
											{
												if($i == 1)
												{
													$post_pre_content .= "<ul>";
												}

													$post_pre_content .= "<li>
														<a href='".$item_url."'";

															if($api_url != '')
															{
																$post_pre_content .= " data-api-url='".$api_url."'";
															}

														$post_pre_content .= ">
															<span>".$item['name']."</span>
														</a>
													</li>";

												if($i == ($count_temp - 1))
												{
													$post_pre_content .= "</ul>";
												}
											}

											$i++;
										}

									$post_pre_content .= "</li>";
								}

							$post_pre_content .= "</ul>
						</nav>";
					}

					$post_content = "<div class='error hide'><p></p></div>
					<div class='updated hide'><p></p></div>
					<div class='admin_container'>
						<div class='default'>".$post_content."</div>
						<div class='loading hide'><i class='fa fa-spinner fa-spin fa-3x'></i></div>";

						foreach($arr_views as $key => $view)
						{
							foreach($view['items'] as $item)
							{
								@list($id, $rest) = explode("/", $item['id']);

								$post_content .= "<div id='admin_".$key."_".$id."' class='hide'>
									<".($is_heading_visible ? "h2" : "h1").">".$view['name']."</".($is_heading_visible ? "h2" : "h1").">
									<div><i class='fa fa-spinner fa-spin fa-3x'></i></div>
								</div>";
							}
						}

					$post_content .= "</div>";

					$arr_templates_id = array();

					foreach($arr_views as $key => $view)
					{
						if(!isset($view['templates_id']) || !in_array($view['templates_id'], $arr_templates_id))
						{
							if(isset($view['templates']))
							{
								$post_content .= $view['templates'];
							}
						}

						if(isset($view['templates_id']))
						{
							$arr_templates_id[] = $view['templates_id'];
						}
					}
				}

				if(is_active_sidebar('widget_after_heading') && !post_password_required())
				{
					ob_start();

					dynamic_sidebar('widget_after_heading');

					$widget_content = ob_get_clean();

					if($widget_content != '')
					{
						$post_pre_content .= "<div class='aside after_heading'>"
							.$widget_content
						."</div>";
					}
				}

				echo apply_filters('filter_template_admin_content', $post_pre_content
				."<section>"
					.$post_content
				."</section>");
			}

		echo "</article>";
	}

get_footer();