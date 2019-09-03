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
$arr_type = explode("/", $type);

switch($arr_type[0])
{
	case 'admin':
		if(isset($arr_type[1]))
		{
			switch($arr_type[1])
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

					if(isset($arr_type[2]))
					{
						switch($arr_type[2])
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
					}
				break;

				/*case 'registration':
					if(isset($arr_type[2]))
					{
						switch($arr_type[2])
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
					}
				break;*/

				case 'posts':
					if(isset($arr_type[2]))
					{
						switch($arr_type[2])
						{
							case 'list':
								$current_page = isset($arr_type[3]) && is_numeric($arr_type[3]) ? $arr_type[3] : 1;
								$edit_page_per_page = get_the_author_meta_or_default('edit_page_per_page', get_current_user_id(), 20);

								$arr_pages = $arr_list = array();
								$list_amount = 0;

								$query_where = "";

								/*if(1 == 1 || !IS_ADMIN)
								{
									$query_where .= " AND post_author = '".get_current_user_id()."'";
								}*/

								$result = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title, post_status, post_author, post_date, post_modified FROM ".$wpdb->posts." WHERE post_type = %s AND (post_status = %s OR post_status = %s)".$query_where." ORDER BY post_date DESC", 'post', 'publish', 'draft'));

								foreach($result as $r)
								{
									$list_amount++;
									$page_number = ceil($list_amount / $edit_page_per_page);

									if(($list_amount % $edit_page_per_page) == 1)
									{
										$arr_pages[] = $page_number;
									}

									if($current_page == $page_number)
									{
										$user_data = get_userdata($r->post_author);

										$arr_list[] = array(
											'post_id' => $r->ID,
											'post_status' => $r->post_status,
											'post_title' => $r->post_title,
											'post_url' => get_permalink($r->ID),
											'post_author' => $user_data->display_name,
											'post_categories' => $obj_fea->get_post_categories(array('output' => 'html', 'post_id' => $r->ID)),
											'post_date' => format_date($r->post_date),
											'post_modified' => format_date($r->post_modified),
										);
									}
								}

								$json_output['success'] = true;
								$json_output['admin_response'] = array(
									'template' => $arr_type[0]."_".$arr_type[1]."_".$arr_type[2],
									'pagination' => array(
										'list_amount' => $list_amount,
										'current_page' => $current_page,
										'pages' => $arr_pages,
									),
									'list' => $arr_list,
								);
							break;

							case 'edit':
								$post_id = isset($arr_type[3]) ? $arr_type[3] : 0;

								if($post_id > 0)
								{
									$result = $wpdb->get_results($wpdb->prepare("SELECT post_title, post_excerpt, post_content, post_status, post_name, post_author, comment_status FROM ".$wpdb->posts." WHERE post_type = %s AND (post_status = %s OR post_status = %s) AND ID = '%d'", 'post', 'publish', 'draft', $post_id));

									foreach($result as $r)
									{
										$json_output['success'] = true;
										$json_output['admin_response'] = array(
											'template' => $arr_type[0]."_".$arr_type[1]."_".$arr_type[2],
											'post_id' => $post_id,
											'post_title' => $r->post_title,
											'post_excerpt' => $r->post_excerpt,
											'post_content' => $r->post_content,
											'post_status' => $r->post_status,
											'post_name' => $r->post_name,
											'post_categories' => $obj_fea->get_post_categories(array('output' => 'id', 'post_id' => $post_id)),
											'post_author' => $r->post_author,
											'comment_status' => $r->comment_status,
										);
									}
								}

								else
								{
									$json_output['success'] = true;
									$json_output['admin_response'] = array(
										'template' => $arr_type[0]."_".$arr_type[1]."_".$arr_type[2],
										'post_id' => 0,
										'post_title' => "",
										'post_excerpt' => "",
										'post_content' => "",
										'post_status' => 'draft',
										'post_name' => "",
										'post_categories' => array(),
										'post_author' => get_current_user_id(),
										'comment_status' => 'no',
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
								$post_categories = check_var('post_categories', 'int'); //, 'array'
								$post_author = check_var('post_author', 'int');
								$comment_status = check_var('comment_status');

								if(!is_array($post_categories))
								{
									if($post_categories > 0)
									{
										$post_categories = array($post_categories);
									}

									else
									{
										$post_categories = array(get_option('default_category'));
									}
								}

								if(!($post_author > 0))
								{
									$post_author = get_current_user_id();
								}

								$post_data = array(
									'post_title' => $post_title,
									'post_excerpt' => $post_excerpt,
									'post_content' => $post_content,
									'post_status' => $post_status,
									'post_name' => $post_name,
									'post_category' => $post_categories,
									'post_author' => $post_author,
									'comment_status' => ($comment_status == 'yes' ? 'open' : 'closed'),
								);

								$updated = false;

								if($post_id > 0)
								{
									$post_data['ID'] = $post_id;

									if(wp_update_post($post_data) > 0)
									{
										$updated = true;

										$json_output['message'] = __("I have saved the information for you", 'lang_fea');
									}
								}

								else
								{
									$post_data['post_type'] = 'post';

									$post_id = wp_insert_post($post_data);

									if($post_id > 0)
									{
										$updated = true;

										$json_output['message'] = __("I have saved the information for you", 'lang_fea')." (2)";
										$json_output['next_request'] = "admin/posts/edit/".$post_id;
									}
								}

								if($updated == true)
								{
									$json_output['success'] = true;
								}

								else
								{
									if(!isset($json_output['message']) || $json_output['message'] == '')
									{
										$json_output['message'] = __("I could not save the information for you", 'lang_fea');
									}
								}
							break;
						}
					}
				break;
			}
		}
	break;
}

echo json_encode($json_output);