<?php

	// Import Parents and Students
	use Box\Spout\Reader\ReaderFactory;
	use Box\Spout\Common\Type;
	

	
	$parents_file  = 'parents.xlsx';
	$students_file = 'children.xlsx';
	
	if (file_exists($parents_file) || file_exists($students_file)){
		
		include_once('../../../../wp-load.php');
		include_once('Spout/Autoloader/autoload.php');
		
		
		if (!current_user_can('manage_options')){
			die("You must be admin for this operation.\n");
		}
		
		
		// Load and prepare Centres info
		$rows = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}hope_centres`");
		
		if (!count($rows)){
			die("Table {$wpdb->prefix}hope_centres is empty! (Student Reports plugin)\n");
		}

		$sr_centres = array();
		$mailing_list_ids = array();
		foreach($rows as $row){
			$sr_centres[$row->id] = $row;
			$mailing_list_ids []=  $row->mailing_list_id;
		}
		
		$mailing_list_ids = array_unique($mailing_list_ids);
		$mailing_list_ids = implode(',', $mailing_list_ids);
		
	
		// Import Parents
		if (file_exists($parents_file)){
			$reader = ReaderFactory::create(Type::XLSX);
			$reader->open($parents_file);

			foreach($reader->getSheetIterator() as $sheet){
				$first_row = true;
				foreach($sheet->getRowIterator() as $row){
					// Skip first row
					if ($first_row){
						$first_row = false;
						continue;
					}
					
					// Skip without email
					$user_email = trim($row[4]);
					if (strpos($user_email, '@') === FALSE){
						continue;
					}
					
											
					$user_id = (int)$wpdb->get_var($wpdb->prepare("SELECT `ID` FROM `{$wpdb->users}` WHERE `user_email`=%s LIMIT 1", $user_email));
					
					
					// Create
					if ($user_id < 1){
						// Username
						/*
						$user_name = $row[5] . $row[11];
						$user_name = preg_replace('/[^a-z-0-9]/i', '', $user_name);
						$user_name = strtolower($user_name);
						*/					
						$user_name = $user_email;
						
						// Create
						$random_password = wp_generate_password(8, false);
						$user_id         = wp_create_user($user_name, $random_password, $user_email);
					}
					

					if (!$user_id){
						echo "Error. Can't create/update user!<br />\n";
						print_r($row);
						die();
					}
					
					
					// Update user info
					$userdata = array(
						'ID' => $user_id,
						'first_name' => trim($row[5]),
						'last_name' => trim($row[11])
					);
					
					wp_update_user($userdata);
					
					
					// Custom fields					
					update_user_meta($user_id, 'sr_accountname', trim($row[0]));
					update_user_meta($user_id, 'sr_accounts', trim($row[1]));
					update_user_meta($user_id, 'sr_collectcode', trim($row[2]));
					update_user_meta($user_id, 'sr_contactid', trim($row[3]));
					update_user_meta($user_id, 'sr_haddress', trim($row[6]));
					update_user_meta($user_id, 'sr_hpcode', trim($row[7]));
					update_user_meta($user_id, 'sr_hphone', trim($row[8]));
					update_user_meta($user_id, 'sr_hstate', trim($row[9]));
					update_user_meta($user_id, 'sr_hsuburb', trim($row[10]));
					update_user_meta($user_id, 'sr_mangroupname', trim($row[12]));
					update_user_meta($user_id, 'sr_mobile', trim($row[13]));
					update_user_meta($user_id, 'sr_notes', trim($row[14]));
					update_user_meta($user_id, 'sr_occupation', trim($row[15]));
					update_user_meta($user_id, 'sr_organisation', trim($row[16]));
					update_user_meta($user_id, 'sr_parentid', trim($row[17]));
					update_user_meta($user_id, 'sr_relationship', trim($row[18]));
					update_user_meta($user_id, 'sr_rollname', trim($row[19]));
					update_user_meta($user_id, 'sr_silentph', trim($row[20]));
					update_user_meta($user_id, 'sr_title', trim($row[21]));
					update_user_meta($user_id, 'sr_waddress', trim($row[22]));
					update_user_meta($user_id, 'sr_wpcode', trim($row[23]));
					update_user_meta($user_id, 'sr_wphone', trim($row[24]));
					update_user_meta($user_id, 'sr_wstate', trim($row[25]));
					update_user_meta($user_id, 'sr_wsuburb', trim($row[26]));
					update_user_meta($user_id, 'sr__key_id_parent', trim($row[27]));
					
					$centre = trim($row[28]);
					$centre = strtolower($centre);
					$centre = preg_replace('/ +/', '_', $centre);
					
					switch($centre){
						case 'patterson_lakes':
							$centre = '1';
						break;
						
						case 'frankston':
							$centre = '2';
						break;

						case 'carrum_downs':
							$centre = '3';
						break;					
						
						default:
							$centre = '';
						break;
					}
					
					update_user_meta($user_id, 'sr_centre', $centre);

					
					// MailPoet integration
					// Add/update user to his centre mailling list
					$list_id = $sr_centres[$centre]->mailing_list_id;
					if ($list_id > 0){
						// Get MailPoet user_id
						$mp_user_id = $wpdb->get_var("SELECT `user_id` FROM `{$wpdb->prefix}wysija_user` WHERE `wpuser_id`='{$user_id}'");
						if ($mp_user_id > 0){
							// Unlink from old Centre List
							$wpdb->query("DELETE FROM `{$wpdb->prefix}wysija_user_list` WHERE `user_id`='{$mp_user_id}' AND `list_id` IN ({$mailing_list_ids})");
														
							$t = time();
							$wpdb->query("INSERT INTO `{$wpdb->prefix}wysija_user_list` VALUES('{$list_id}', '{$mp_user_id}', '{$t}', '0')");
						}
					}
				}
				
				break;
			}
					
			$reader->close();
			
		}		
		
		
		
		// Import Students
		if (file_exists($students_file)){
			
			// Create new uesr role
			add_role('student', 'Student');
			
			
			$reader = ReaderFactory::create(Type::XLSX);
			$reader->open($students_file);

			foreach($reader->getSheetIterator() as $sheet){
				$first_row = true;
				foreach($sheet->getRowIterator() as $row){
					// Skip first row
					if ($first_row){
						$first_row = false;
						continue;
					}
					
					// Check child_id
					$child_id = trim($row[7]);
					if (!$child_id){
						continue;
					}
					
					// Check parent
					$parent_id  = 0;
					$parent_key = trim($row[8]);
					if ($parent_key){
						$parent_id = (int)$wpdb->get_var("SELECT `user_id` FROM {$wpdb->usermeta} WHERE `meta_key`='sr_parentid' AND `meta_value`='{$parent_key}' LIMIT 1");						
					}

					if (!$parent_id){
						continue;
					}
					
					
					// Generate email
					$user_email = 'student-' . $child_id . '@example.com';
					
											
					$user_id = (int)$wpdb->get_var($wpdb->prepare("SELECT `ID` FROM `{$wpdb->users}` WHERE `user_email`=%s LIMIT 1", $user_email));					
					
					// Create
					if ($user_id < 1){
						$user_name = $user_email;
						
						// Create
						$random_password = wp_generate_password(8, false);
						$user_id         = wp_create_user($user_name, $random_password, $user_email);
					}
					
					
					if (!$user_id){
						echo "Error. Can't create/update user!<br />\n";
						print_r($row);
						die();
					}
					
					
					// Update user info
					$userdata = array(
						'ID' => $user_id,
						'first_name' => trim($row[0]),
						'last_name' => trim($row[2]),
						'role' => 'student'
					);
					
					wp_update_user($userdata);
					
					
					// Custom fields
					update_user_meta($user_id, 'sr_is_child', 1);
					update_user_meta($user_id, 'sr_parent_id', $parent_id);					
					update_user_meta($user_id, 'sr_childmiddle', trim($row[1]));
					update_user_meta($user_id, 'sr_gender', trim($row[3]));
					update_user_meta($user_id, 'sr_dob', trim($row[4]));
					
					$centre = trim($row[5]);
					$centre = strtolower($centre);
					
					switch($centre){
						case 'hope_plakes':
							$centre = '1';
						break;
						
						case 'hope_frankston':
							$centre = '2';
						break;

						case 'hope_c_downs':
							$centre = '3';
						break;					
						
						default:
							$centre = '';
						break;
					}
					
					update_user_meta($user_id, 'sr_centre', $centre);
					
					update_user_meta($user_id, 'sr_centrecode', trim($row[6]));
					update_user_meta($user_id, 'sr_children_key_parent_id', $parent_key);
					update_user_meta($user_id, 'sr__key_id_child', $child_id);
				}
				
				break;
			}
					
			$reader->close();
			
		}
	}

	die('Import completed!');