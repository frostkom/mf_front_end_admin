<?php

if(!defined('ABSPATH'))
{
	header('Content-Type: application/json');

	$folder = str_replace("/wp-content/plugins/mf_front_end_admin/include/api", "/", dirname(__FILE__));

	require_once($folder."wp-load.php");
}

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
						);
					break;

					case 'save':
						$updated = false;

						foreach($arr_fields as $key => $value)
						{
							if(isset($value['name']))
							{
								$user_meta = check_var($value['name']);

								if($user_meta != '' || isset($value['required']) && $value['required'] == false)
								{
									switch($arr_fields[$key]['type'])
									{
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
							$json_output['message'] = __("I could not update the information for you", 'lang_fea');
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
		}
	break;
}

echo json_encode($json_output);