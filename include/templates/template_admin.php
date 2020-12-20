<?php
/*
Template Name: Front-End Admin
*/

//Only effective if cache is off, so we need an extra check in the API + possibly to invalidate cache on this page
if(!is_user_logged_in())
{
	mf_redirect(wp_login_url()."?redirect_to=".$_SERVER['REQUEST_URI']);
}

get_header();

	if(have_posts())
	{
		$obj_fea = new mf_fea();
		$obj_fea->arr_views = apply_filters('init_base_admin', array(), array('init' => true));

		if(!isset($obj_theme))
		{
			$obj_theme = new mf_theme();
		}

		echo "<article".(IS_ADMIN ? " class='template_admin'" : "").">";

			while(have_posts())
			{
				the_post();

				$post_title = $post->post_title;
				$obj_fea->post_content = apply_filters('the_content', $post->post_content);

				$obj_fea->post_pre_content = "";

				$is_heading_visible = $obj_theme->is_heading_visible($post);

				if($is_heading_visible)
				{
					$obj_fea->post_pre_content .= "<h1>".$post_title."</h1>";
				}

				if(is_user_logged_in())
				{
					$setting_fea_user_info = get_option_or_default('setting_fea_user_info', array());

					if(count($setting_fea_user_info) > 0)
					{
						$obj_fea->post_pre_content .= "<section class='logged_in'>
							<p>";

								if(in_array('name', $setting_fea_user_info))
								{
									$obj_fea->post_pre_content .= "<i class='fa fa-user'></i> ".get_user_info();
								}

								if(in_array('role', $setting_fea_user_info))
								{
									$arr_roles = get_roles_for_select(array('add_choose_here' => false, 'use_capability' => false));
									$user_role = get_current_user_role(get_current_user_id());

									if(isset($arr_roles[$user_role]))
									{
										$obj_fea->post_pre_content .= " (".$arr_roles[$user_role].")";
									}

									else
									{
										$obj_fea->post_pre_content .= " (".$user_role.")";
									}
								}

								if(in_array('logout', $setting_fea_user_info))
								{
									$obj_fea->post_pre_content .= " - <a href='".wp_logout_url()."'>".__("Log Out", 'lang_fea')."</a>";
								}

							$obj_fea->post_pre_content .= "</p>
						</section>";
					}
				}

				if(count($obj_fea->arr_views) > 0)
				{
					$obj_fea->get_menu();

					$obj_fea->post_content = "<div class='error hide'><p></p></div>
					<div class='updated hide'><p></p></div>
					<div class='admin_container'>
						<div class='default'>".$obj_fea->post_content."</div>
						<div class='loading hide'><i class='fa fa-spinner fa-spin fa-3x'></i></div>";

						foreach($obj_fea->arr_views as $key => $view)
						{
							foreach($view['items'] as $item)
							{
								if(!isset($item['is_custom']) || $item['is_custom'] == false)
								{
									@list($id, $rest) = explode("/", $item['id']);

									$obj_fea->post_content .= "<div id='admin_".$key."_".$id."' class='admin_container_child hide'>
										<".($is_heading_visible ? "h2" : "h1").">";

											if(isset($item['heading']['name']))
											{
												$obj_fea->post_content .= $item['heading']['name'];
											}

											else
											{
												$obj_fea->post_content .= $view['name'];
											}

											if(isset($item['heading']['button']['url']) && isset($item['heading']['button']['name']))
											{
												$obj_fea->post_content .= "<div class='form_button'>
													<a href='".$item['heading']['button']['url']."' class='button'>".$item['heading']['button']['name']."</a>
												</div>";
											}

										$obj_fea->post_content .= "</".($is_heading_visible ? "h2" : "h1").">
										<div>
											<i class='fa fa-spinner fa-spin fa-3x'></i>
										</div>
									</div>";
								}
							}
						}

					$obj_fea->post_content .= "</div>";

					$arr_templates_id = array();

					foreach($obj_fea->arr_views as $key => $view)
					{
						if(!isset($view['templates_id']) || !in_array($view['templates_id'], $arr_templates_id))
						{
							if(isset($view['templates']))
							{
								$obj_fea->post_content .= $view['templates'];
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
						$obj_fea->post_pre_content .= "<div class='aside after_heading'>"
							.$widget_content
						."</div>";
					}
				}

				echo apply_filters('filter_template_admin_content', $obj_fea->post_pre_content
				."<section>"
					.$obj_fea->post_content
				."</section>");
			}

		echo "</article>";
	}

get_footer();