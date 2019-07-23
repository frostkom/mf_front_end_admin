<?php

if(!defined('ABSPATH'))
{
	header('Content-Type: application/json');

	$folder = str_replace("/wp-content/plugins/mf_front_end_admin/include/api", "/", dirname(__FILE__));

	require_once($folder."wp-load.php");
}

$obj_fea = new mf_fea();

$json_output = array(
	'success' => false,
);

$type = check_var('type', 'char');
$arr_input = explode("/", $type);

$type_action = $arr_input[0];
$type_action_type = isset($arr_input[1]) ? $arr_input[1] : '';
$type_class = isset($arr_input[2]) ? $arr_input[2] : '';

switch($type_action)
{
	case 'admin':
		switch($type_action_type)
		{
			case 'profile':
				$user_id = get_current_user_id();

				$arr_fields = array();

				$arr_fields[] = array('type' => 'flex_start');
					$arr_fields[] = array('type' => 'text', 'name' => 'first_name', 'text' => __("First Name", 'lang_fea'), 'required' => true);
					$arr_fields[] = array('type' => 'text', 'name' => 'last_name', 'text' => __("Last Name", 'lang_fea'), 'required' => true);
				$arr_fields[] = array('type' => 'flex_end');
				$arr_fields[] = array('type' => 'flex_start');
					$arr_fields[] = array('type' => 'email', 'name' => 'email', 'text' => __("E-mail", 'lang_fea'), 'required' => true);
					$arr_fields[] = array('type' => 'password', 'name' => 'password', 'text' => __("Password"));
				$arr_fields[] = array('type' => 'flex_end');

				$arr_fields = apply_filters('filter_profile_fields', $arr_fields);

				switch($type_class)
				{
					case 'edit':
						foreach($arr_fields as $key => $value)
						{
							if(isset($value['name']))
							{
								if(!isset($arr_fields[$key]['class'])){			$arr_fields[$key]['class'] = "";}
								if(!isset($arr_fields[$key]['attributes'])){	$arr_fields[$key]['attributes'] = "";}
								if(!isset($arr_fields[$key]['required'])){		$arr_fields[$key]['required'] = false;}

								$arr_fields[$key]['value'] = get_the_author_meta($value['name'], $user_id);

								switch($arr_fields[$key]['type'])
								{
									case 'email':
										$new_email = get_user_meta($user_id, '_new_email', true);
										$user_data = get_userdata($user_id);

										if($new_email && $new_email['newemail'] != $user_data->user_email)
										{
											$arr_fields[$key]['description'] = " ".sprintf(__("There is a pending change of your email to %s.", 'lang_fea'), $new_email['newemail'])
											." <a href='".esc_url(wp_nonce_url(self_admin_url("profile.php?dismiss=".$user_id."_new_email"), 'dismiss-'.$user_id.'_new_email'))."'>".__("Cancel", 'lang_fea')."</a>";
										}

										else
										{
											$arr_fields[$key]['description'] = sprintf(__("If you change this we will send you an email at your new address to confirm it. %sThe new address will not become active until confirmed.%s", 'lang_fea'), "<strong>", "</strong>");
										}
									break;

									case 'select':
										// Otherwise options might end up in the "wrong" order on the site
										#######################
										$arr_data_temp = array();

										foreach($arr_fields[$key]['options'] as $option_key => $option_value)
										{
											$arr_data_temp[] = array(
												'key' => $option_key,
												'value' => $option_value,
											);
										}

										$arr_fields[$key]['options'] = $arr_data_temp;
										#######################

										if(!isset($arr_fields[$key]['multiple']))
										{
											$arr_fields[$key]['multiple'] = false;
										}

										if($arr_fields[$key]['multiple'] == true)
										{
											$arr_fields[$key]['class'] .= " form_select_multiple";
											$arr_fields[$key]['attributes'] .= " size='".get_select_size(array('count' => count($arr_fields[$key]['options'])))."'";
										}
									break;
								}
							}
						}

						$json_output['admin_response'] = array(
							'template' => str_replace("/", "_", $type),
							'container' => str_replace("/", "_", $type),
							'fields' => $arr_fields,
							'user_id' => $user_id,
						);
					break;

					case 'save':
						$updated = false;

						foreach($arr_fields as $key => $value)
						{
							if(isset($value['name']))
							{
								$user_meta = check_var($value['name']);

								if($user_meta != '' || !isset($value['required']) || $value['required'] == false)
								{
									switch($arr_fields[$key]['type'])
									{
										case 'email':
											if($user_meta != '')
											{
												$success = send_confirmation_on_profile_email();

												if(isset($errors) && is_wp_error($errors))
												{
													foreach($errors->errors as $error)
													{
														$json_output['message'] = $error[0];
													}
												}

												else
												{
													$updated = true;
												}
											}
										break;

										case 'password':
											if($user_meta != '')
											{
												wp_set_password($user_meta, $user_id);

												$updated = true;
											}
										break;

										default:
											$meta_id = update_user_meta($user_id, $value['name'], $user_meta);

											if($meta_id > 0)
											{
												$updated = true;
											}
										break;
									}
								}
							}
						}

						if($updated == true)
						{
							$json_output['success'] = true;
							$json_output['message'] = __("I have saved the information for you", 'lang_fea');
						}

						else
						{
							if(!isset($json_output['message']) || $json_output['message'] == '')
							{
								$json_output['message'] = __("I could not update the information for you", 'lang_fea');
							}
						}
					break;
				}
			break;

			/*case 'registration':
				switch($type_class)
				{
					case 'create':
						$json_output['admin_response'] = array(
							'template' => str_replace("/", "_", $type),
							'container' => str_replace("/", "_", $type),
						);
					break;

					case 'save':
						$updated = false;

						if($updated == true)
						{
							$json_output['success'] = true;
							$json_output['message'] = __("I have updated the information for you", 'lang_fea');
						}

						else
						{
							$json_output['message'] = __("I could not update the information for you", 'lang_fea');
						}
					break;
				}
			break;*/

			case 'posts':
				switch($type_class)
				{
					case 'list':
						$arr_list = array();

						$query_where = "";

						/*if(1 == 1 || !IS_ADMIN)
						{
							$query_where .= " AND post_author = '".get_current_user_id()."'";
						}*/

						$result = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title, post_status, post_author, post_modified FROM ".$wpdb->posts." WHERE post_type = %s AND (post_status = %s OR post_status = %s)".$query_where." ORDER BY post_modified DESC", 'post', 'publish', 'draft'));

						foreach($result as $r)
						{
							$user_data = get_userdata($r->post_author);

							//$arr_categories = wp_get_post_categories($r->ID);

							$categories = "";

							$arr_categories = get_the_category($r->ID);

							if(is_array($arr_categories) && count($arr_categories) > 0)
							{
								//$category_base_url = get_site_url()."/category/";

								foreach($arr_categories as $category)
								{
									$categories .= ($categories != '' ? ", " : "").$category->name; //"<a href='".$category_base_url.$category->slug."'>".
								}
							}

							$arr_list[] = array(
								'post_id' => $r->ID,
								'post_title' => $r->post_title.($r->post_status == 'draft' ? " (".__("Draft").")" : ""),
								'post_url' => get_permalink($r->ID),
								'post_author' => $user_data->display_name,
								'categories' => $categories,
								'post_modified' => format_date($r->post_modified),
							);
						}

						$json_output['success'] = true;
						$json_output['admin_response'] = array(
							'template' => str_replace("/", "_", $type),
							'list' => $arr_list,
						);
					break;

					case 'edit':
						$post_id = isset($arr_input[3]) ? $arr_input[3] : 0;

						$result = $wpdb->get_results($wpdb->prepare("SELECT post_title, post_excerpt, post_content, post_status, post_name FROM ".$wpdb->posts." WHERE post_type = %s AND (post_status = %s OR post_status = %s) AND ID = '%d'", 'post', 'publish', 'draft', $post_id));

						foreach($result as $r)
						{
							$json_output['success'] = true;
							$json_output['admin_response'] = array(
								'template' => str_replace(array("/", "_".$post_id), array("_", ""), $type),
								'post_id' => $post_id,
								'post_title' => $r->post_title,
								'post_excerpt' => $r->post_excerpt,
								'post_content' => $r->post_content,
								'post_status' => $r->post_status,
								'post_name' => $r->post_name,
								'post_categories' => $obj_fea->get_post_categories(array('post_id' => $post_id)),
							);
						}
					break;

					case 'save':
						$post_id = check_var('post_id', 'int');
						$post_title = check_var('post_title');
						$post_excerpt = check_var('post_excerpt');
						$post_content = check_var('post_content');
						$post_status = check_var('post_status');
						$post_name = check_var('post_name');
						$post_categories = check_var('post_categories', 'array');

						$updated = false;

						$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->posts." SET post_title = %s, post_excerpt = %s, post_content = %s, post_status = %s, post_name = %s WHERE ID = '%d'", $post_title, $post_excerpt, $post_content, $post_status, $post_name, $post_id));

						if($wpdb->rows_affected > 0)
						{
							$updated = true;
						}

						//Update post_categories

						if($updated == true)
						{
							$json_output['success'] = true;
							$json_output['message'] = __("I have saved the information for you", 'lang_fea');
						}

						else
						{
							if(!isset($json_output['message']) || $json_output['message'] == '')
							{
								$json_output['message'] = __("I could not update the information for you", 'lang_fea');
							}
						}
					break;
				}
			break;
		}
	break;
}

echo json_encode($json_output);