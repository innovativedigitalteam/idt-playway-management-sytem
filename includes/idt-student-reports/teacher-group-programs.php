<?php

include_once "includes/CommonFront.php";

function createTeacherGroupPostType() {
	$labels = array(
		'name'               => 'Group Programs (Teacher Initiated)',
		'singular_name'      => 'Group Program (Teacher Initiated)',
		'add_new'            => 'Add New Group Program (Teacher Initiated)',
		'add_new_item'       => 'Add New Group Program (Teacher Initiated)',
		'edit'               => 'Edit Group Program (Teacher Initiated)',
		'edit_item'          => 'Edit Group Program (Teacher Initiated)',
		'new_item'           => 'New Group Program (Teacher Initiated)',
		'view'               => 'View Group Program (Teacher Initiated)',
		'view_item'          => 'View Group Program (Teacher Initiated)',
		'search_items'       => 'Search Group Programs (Teacher Initiated)',
		'not_found'          => 'Nothing found',
		'not_found_in_trash' => 'Nothing found in Trash',
		'parent_item_colon'  => '',
		'all_items'          => 'All Group Programs (Teacher Initiated)',
	);

	$args = array(
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'hierarchical'        => false,
		'rewrite'             => array( 'slug' => 'tgroups', 'with_front' => 'true' ),
		'query_var'           => true,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-welcome-widgets-menus',
		'supports'            => array( '' ),
		'capability_type'     => 'tgroups',
		'capabilities'        => array(
			'publish_posts'       => 'publish_reports',
			'edit_posts'          => 'edit_reports',
			'edit_others_posts'   => 'edit_others_reports',
			'delete_post'         => 'delete_reports',
			'delete_posts'        => 'delete_reports',
			'delete_others_posts' => 'delete_others_reports',
			'read_private_posts'  => 'read_private_reports',
			'edit_post'           => 'edit_reports',
			'read_post'           => 'read_reports',
		)
	);

	register_post_type( 'tgroups', $args );
//    flush_rewrite_rules();
}

add_action( 'init', 'createTeacherGroupPostType' );


//-----------------------------
//Add Group form to post edit screen
function teacherGroupsAddMetaBoxes() {
	add_meta_box(
		'teacher_group_custom_metabox',       // $id
		'Group (Teacher Initiated)',          // $title
		'teacherGroupCustomMetabox',          // $callback
		'tgroups',                            // $pageС
		'normal',                             // $context
		'high'                                // $priority
	);
}

add_action( 'add_meta_boxes', 'teacherGroupsAddMetaBoxes' );

// Add new role for Report Editor
function teacher_groups_add_roles_on_plugin_activation() {
	add_role( 'tgroup_editor', 'Teacher Initiated Group Editor' );

	// Add access to reports to other roles
	$roles = array( 'tgroup_editor', 'editor', 'administrator' );

	foreach ( $roles as $the_role ) {
		$role = get_role( $the_role );

		$role->add_cap( 'read' );
		$role->add_cap( 'publish_tgroups' );
		$role->add_cap( 'edit_tgroups' );
		$role->add_cap( 'edit_others_tgroups' );
		$role->add_cap( 'delete_tgroups' );
		$role->add_cap( 'delete_others_tgroups' );
		$role->add_cap( 'read_private_tgroups' );
		$role->add_cap( 'read_tgroups' );
	}
}

register_activation_hook( __FILE__, 'teacher_groups_add_roles_on_plugin_activation' );


function teacherGroupCustomMetabox() {
	global $post, $sr_centres, $sr_images_url;

	wp_nonce_field( basename( __FILE__ ), 'tgroup_nonce' );

	// Load Report data
	$postmeta = get_post_meta( $post->ID );
	//$studentId = $postMeta['sr_student_id'];
	$sr_data = $postmeta['sr_data'][0];

	if ( $sr_data ) {
		$sr_data = unserialize( $sr_data );
	} else {
		$sr_data = array();
	}

	$sdt = (object) [
		'title'     => $postmeta['teacher-program-title'][0],
		'centre'    => $postmeta['sr_centre'][0],
		'startdate' => $sr_data['teacher-program-start-date'],
		'enddate'   => $sr_data['teacher-program-end-date'],
		'photos'    => $sr_data['photos']
	];
	?>

    <div class="teacher-program-summary">
        <table>
            <tr>
                <td><label for="teacher-program-title">Program Title</label></td>
                <td><input name="teacher-program-title" id="teacher-program-title" value="<?php echo $sdt->title; ?>"
                           required>
                </td>
            </tr>
            <tr>
                <td><label for="teacher-program-start-date">Start Date</label></td>
                <td><input class="my-datepicker" name="teacher-program-start-date" id="teacher-program-start-date"
                           value="<?php echo $sdt->startdate; ?>"></td>
            </tr>
            <tr>
                <td><label for="teacher-program-end-date">End Date</label></td>
                <td><input class="my-datepicker" name="teacher-program-end-date" id="teacher-program-end-date"
                           value="<?php echo $sdt->enddate; ?>"></td>
            </tr>
			<?php
			if ( current_user_can( 'manage_options' ) ) {
				?>
                <tr>
                    <td><label for="sr_centre">Centre</label></td>
                    <td>
                        <select id="sr_centre" name="sr_centre" required style="width: 350px;">
                            <option value="">Select...</option>
							<?php
							populateCentresForSelection( $sr_centres, $sdt->centre );
							?>
                        </select>
                    </td>
                </tr>
				<?php
			}
			?>
        </table>
    </div>

    <div id="t-discoveries">
        <a href="javascript:void(0)" class="add_t_discovery">Add discovery</a>
		<?php
		$t_discoveries = isset( $sr_data['t_discoveries'] ) && count( $sr_data['t_discoveries'] ) ? $sr_data['t_discoveries'] : [];
		// Data always starts from 1
		count( $t_discoveries ) === 0 ? $t_discoveries[1] = array() : $t_discoveries[] = array();
		foreach ( $t_discoveries as $t_discovery_id => $t_discovery ) {
			if ( count( $t_discoveries ) === $t_discovery_id ) {
				$dummy           = "dummy";
				$t_discovery_id  = "%t_discovery_id%";
				$disabled        = "disabled";
				$datePickerClass = "add-datepicker-discovery";
			} else {
				$disabled        = "";
				$dummy           = "";
				$datePickerClass = "my-datepicker";
			}
			?>

            <!-------------------------------- DISCOVERY ------------------------------------>

            <div class="t-discovery" data-id="<?= $t_discovery_id ?>" <?= $dummy ?>>
                <fieldset>
                    <legend>Discovery <?= $t_discovery_id ?></legend>
					<?php
					if ( isset( $t_discovery['deleted'] ) && $t_discovery['deleted'] == 'deleted' ) {
						echo '<input type="hidden" name="t_discoveries[' . $t_discovery_id . '][deleted]" value="deleted"><div class="deleted">Deleted</div></feildset></div>';
						continue;
					}
					?>
                    <img src="/wp-content/plugins/student-reports/img/icon_delete.png" class="close">
                    <div class="form">
                        <p>
                        <div>
                            <span>Start Date: </span>
                            <input class="<?= $datePickerClass ?> t-discovery-input" type="text"
                                   name="t_discoveries[<?= $t_discovery_id ?>][start_date]"
                                   value="<?= set_or_empty( $t_discovery['start_date'] ) ?>" size="10" maxlength="10"
								<?= $disabled ?>/>
                        </div>
                        <div>
                            <span>End Date: </span>
                            <input class="<?= $datePickerClass ?> t-discovery-input" type="text"
                                   name="t_discoveries[<?= $t_discovery_id ?>][end_date]"
                                   value="<?= set_or_empty( $t_discovery['end_date'] ) ?>" size="10" maxlength="10"
								<?= $disabled ?>/>
                        </div>
                        <div>
                            <span class="block">Discovery/Development Learning & Outcome: </span>
                            <textarea class="t-discovery-input"
                                      name="t_discoveries[<?= $t_discovery_id ?>][discovery_text]" rows="10"
                                      cols="100" <?= $disabled ?>><?= set_or_empty( $t_discovery['discovery_text'] ) ?></textarea>
                        </div>
                        </p>
                        <div><a href="javascript:void(0)" class="add_t_goal">Add Goal</a></div>
						<?php
						count( $t_discovery['t_goals'] ) === 0 ? $t_discovery['t_goals'][1] = array() : $t_discovery['t_goals'][] = array();
						foreach ( $t_discovery['t_goals'] as $t_goal_id => $t_goal ) {
							if ( count( $t_discovery['t_goals'] ) === $t_goal_id ) {
								$disabled        = "disabled";
								$dummy           = "dummy";
								$t_goal_id       = "%t_goal_id%";
								$datePickerClass = "add-datepicker-goal";
							} else {
								$disabled        = "";
								$dummy           = "";
								$datePickerClass = "my-datepicker";
							}
							?>

                            <!-------------------------------- GOAL ------------------------------------>

                            <div class="t-goal" data-id="<?= $t_goal_id ?>" <?= $dummy ?>>
                                <fieldset>
                                    <legend>Goal <?= $t_goal_id ?></legend>
									<?php
									if ( isset( $t_goal['deleted'] ) && $t_goal['deleted'] == 'deleted' ) {
										echo '<input type="hidden" name="t_discoveries[' . $t_discovery_id . '][t_goals][' . $t_goal_id . '][deleted]" value="deleted"><div class="deleted">Deleted</div></feildset></div>';
										continue;
									}
									?>
                                    <img src="/wp-content/plugins/student-reports/img/icon_delete.png" class="close">
                                    <div class="form">
                                        <p>
                                        <div>
                                            <span>Date: </span>
                                            <input class="<?= $datePickerClass ?> t-goal-input" type="text"
                                                   name="t_discoveries[<?= $t_discovery_id ?>][t_goals][<?= $t_goal_id ?>][date]"
                                                   value="<?= set_or_empty( $t_goal['date'] ) ?>" size="10"
                                                   maxlength="10" <?= $disabled ?>/>
                                        </div>
                                        <div>
                                            <span>Goal & Strategies / Objectives: </span>
                                            <textarea
                                                    class="t-goal-input"
                                                    name="t_discoveries[<?= $t_discovery_id ?>][t_goals][<?= $t_goal_id ?>][learning_outcome_text]"
                                                    rows="10"
                                                    cols="100" <?= $disabled ?>><?= set_or_empty( $t_goal['learning_outcome_text'] ) ?></textarea>
                                        </div>
                                        <div>
                                            <span>Learning Experience: </span>
                                            <textarea
                                                    class="t-goal-input"
                                                    name="t_discoveries[<?= $t_discovery_id ?>][t_goals][<?= $t_goal_id ?>][experience_text]"
                                                    rows="10"
                                                    cols="100" <?= $disabled ?>><?= set_or_empty( $t_goal['experience_text'] ) ?></textarea>
                                        </div>
                                        <fieldset class="photo-set">
                                            <div class="tgroup-data-block">
												<?php

												// Add a dummy so it can be cloned in JS and start counting from 1
												count( $sdt->photos[ $t_discovery_id ][ $t_goal_id ] ) === 0 ? $sdt->photos[ $t_discovery_id ][ $t_goal_id ][1] = array() : $sdt->photos[ $t_discovery_id ][ $t_goal_id ][] = array();
												ksort( $sdt->photos[ $t_discovery_id ][ $t_goal_id ] );

												foreach ( $sdt->photos[ $t_discovery_id ][ $t_goal_id ] as $photo_no => $photo_info ) {
													$photo_id   = $photo_info['photo'];
													$photo_date = $photo_info['date'];
													if ( count( $sdt->photos[ $t_discovery_id ][ $t_goal_id ] ) === $photo_no ) {
														$photos_disabled   = "disabled";
														$dummy             = "dummy";
														$photo_no_or_dummy = "%id%";
													} else {
														$photos_disabled   = "";
														$dummy             = "";
														$photo_no_or_dummy = $photo_no;

													}
													?>
                                                    <div class="tgroup-photo-block" <?php echo $dummy ?>>
                                                        <p class="add-photo-evidence">
                                                            <label>Add photo evidence:</label>
                                                        </p>
                                                        <p class="sr-thumb">
															<?php
															if ( $photo_id ) {
																echo '<img alt="photo" src="' . $sr_images_url . '?post_id=' . $post->ID . '&object=' . $photo_id . '&x=80&y=90" width="80" height="90" />';
															}
															?>
                                                        </p>
                                                        <p class="sr-upload">
															<?php
															//														echo '<input type="file" name="photos[' . $t_discovery_id . '][' . $t_goal_id . '][' . $photo_no_or_dummy . '][photo]" class="image_field t-photo-input" ' . $photos_disabled . ' />';
															if ( ! $photo_id ) {
																echo '<input type="file" name="photos[' . $t_discovery_id . '][' . $t_goal_id . '][' . $photo_no_or_dummy . '][photo]" class="image_field t-photo-input" ' . $photos_disabled . ' />';
															} else {
																echo '<input type="checkbox" name="photos[' . $t_discovery_id . '][' . $t_goal_id . '][' . $photo_no_or_dummy . '][delete]" value="' . $photo_id . '" id="' . $photo_id . '" ' . $photos_disabled . ' /> <label for="' . $photo_id . '">Delete</label>';
															}
															?>

                                                        </p>
                                                        <!-- PRIO2: Replace this in the future with a proper date input field that defaults to today's date. -->
                                                        <p class="sr-date">
															<?php
															if ( ! is_non_empty_string( $photo_date ) ) {
																$photo_date = date( 'd.m.Y' );
															}
															?>
                                                            <input type="text"
                                                                   name="photos[<?php echo $t_discovery_id; ?>][<?php echo $t_goal_id; ?>][<?php echo $photo_no_or_dummy; ?>][date]"
                                                                   value="<?php echo esc_attr( $photo_date ); ?>"
                                                                   size="10" <?php echo $photos_disabled; ?>
                                                                   maxlength="10" class="t-photo-input"/>
                                                        </p>
                                                    </div>
												<?php } ?>
                                                <input type="button" class="tgroup-one-more"
                                                       value="Add one more photo"/>
                                            </div>
                                        </fieldset>
                                        <div>
                                            <span>Modifications/Spontaneous Experiences: </span>
                                            <textarea
                                                    class="t-goal-input"
                                                    name="t_discoveries[<?= $t_discovery_id ?>][t_goals][<?= $t_goal_id ?>][spontaneous_text]"
                                                    rows="10"
                                                    cols="100" <?= $disabled ?>><?= set_or_empty( $t_goal['spontaneous_text'] ) ?></textarea>
                                        </div>
                                        </p>
                                    </div>
                                </fieldset>
                            </div>

							<?php
						}
						?>

                    </div>
                </fieldset>
            </div>
			<?php
		}
		?>
    </div>

    <!-- PRIO3: change underscores to dashes where possible to keep it consistent -->
    <div id="t-journal-entries" class="appears-below">
        <fieldset>
            <legend>Reflection Journal Entries</legend>
            <a href="javascript:void(0)" class="add_t_journal_entry">Add reflection journal entry</a>
			<?php
			$t_journal_entries = isset( $sr_data['t_journal_entries'] ) && count( $sr_data['t_journal_entries'] ) ? $sr_data['t_journal_entries'] : [];
			// Data always starts from 1
			count( $t_journal_entries ) === 0 ? $t_journal_entries[1] = array() : $t_journal_entries[] = array();
			foreach ( $t_journal_entries as $t_entry_id => $t_entry ) {
				if ( count( $t_journal_entries ) === $t_entry_id ) {
					$dummy           = "dummy";
					$t_entry_id      = "%t_entry_id%";
					$disabled        = "disabled";
					$datePickerClass = "add-datepicker-t-entry";
				} else {
					$disabled        = "";
					$dummy           = "";
					$datePickerClass = "my-datepicker";
				}
				?>

                <!-------------------------------- REFLECTION JOURNAL ENTRY ------------------------------------>

                <div class="t-journal-entry" data-id="<?= $t_entry_id ?>" <?= $dummy ?>>
                    <fieldset>
                        <legend>Entry <?= $t_entry_id ?></legend>
						<?php
						if ( isset( $t_entry['deleted'] ) && $t_entry['deleted'] == 'deleted' ) {
							echo '<input type="hidden" name="t_journal_entries[' . $t_entry_id . '][deleted]" value="deleted"><div class="deleted">Deleted</div></feildset></div>';
							continue;
						}
						?>
                        <img src="/wp-content/plugins/student-reports/img/icon_delete.png" class="close">
                        <div class="form">
                            <p>
                            <div>
                                <span>Date: </span>
                                <input class="<?= $datePickerClass ?> t-entry-input" type="text"
                                       name="t_journal_entries[<?= $t_entry_id ?>][date]"
                                       value="<?= set_or_empty( $t_entry['date'] ) ?>" size="10" maxlength="10"
									<?= $disabled ?>/>
                            </div>
                            <div>
                                <span>Written by: </span>
                                <input class="t-entry-input" type="text"
                                       name="t_journal_entries[<?= $t_entry_id ?>][written_by]"
                                       value="<?= set_or_empty( $t_entry['written_by'] ) ?>" size="20" maxlength="50"
									<?= $disabled ?>/>
                            </div>
                            <div>
                                <span class="block">Text: </span>
                                <textarea class="t-entry-input"
                                          name="t_journal_entries[<?= $t_entry_id ?>][journal_text]"
                                          rows="10"
                                          cols="100" <?= $disabled ?>><?= set_or_empty( $t_entry['journal_text'] ) ?></textarea>
                            </div>
                            </p>
                        </div>
                    </fieldset>
                </div>
				<?php
			}
			?>
        </fieldset>
    </div>

	<?php

}

// Now we are saving the data
function SR_save_post_teacher_group( $post_id, $post, $update ) {
	global $wpdb, $sr_upload_dir;

	// Check post type
	if ( $post->post_type != 'tgroups' ) {
		return;
	}

	// Admin area only
	if ( ! is_admin() ) {
		return;
	}

	// Check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	//check post revision
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	$postmeta = get_post_meta( $post_id );
	$sr_data  = $postmeta['sr_data'][0];

	if ( $sr_data ) {
		$sr_data       = unserialize( $sr_data );
		$program_title = $postmeta['teacher-program-title'][0];
	} else {
		$sr_data       = array();
		$program_title = null;
	}

	if ( $_POST['teacher-program-start-date'] && $_POST['teacher-program-start-date'] != "" ) {
		$sr_data['teacher-program-start-date'] = $_POST['teacher-program-start-date'];
	}

	if ( $_POST['teacher-program-end-date'] && $_POST['teacher-program-end-date'] != "" ) {
		$sr_data['teacher-program-end-date'] = $_POST['teacher-program-end-date'];
	}

	// Process images
	$photos = isset( $sr_data['photos'] ) ? $sr_data['photos'] : array();

	// We need to add new photos?
	foreach ( $_FILES['photos']['tmp_name'] as $discovery_id => $photo_goal_array ) {
		foreach ( $photo_goal_array as $goal_id => $photo_no_array ) {
			foreach ( $photo_no_array as $key => $photo_info ) {
				if ( isset( $photo_info['photo'] ) ) {
					$info = getimagesize( $photo_info['photo'] );

					if ( $info === false || ( $info[2] !== IMAGETYPE_GIF ) && ( $info[2] !== IMAGETYPE_JPEG ) && ( $info[2] !== IMAGETYPE_PNG ) ) {

					} else {
						// Generate file name
						$id        = md5( time() . wp_generate_password( 10, false ) );
						$name      = $id . '.jpg';
						$path      = $sr_upload_dir . '/' . $post_id;
						$full_name = $path . '/' . $name;
						@mkdir( $path, 0777, true );

						if ( move_uploaded_file( $photo_info['photo'], $full_name ) ) {
							$photos[ $discovery_id ][ $goal_id ][ $key ] = array( 'photo' => $id );
						}
					}
				}
			}
		}
	}

	//PRIO3: Possibly refactor this to merge with the above foreach($_FILES) loop
	foreach ( $_POST['photos'] as $discovery_id => $photo_goal_array ) {
		foreach ( $photo_goal_array as $goal_id => $photo_no_array ) {
			foreach ( $photo_no_array as $key => $photo_info ) {
				if ( isset( $photo_info['delete'] ) ) {
					SR_delete_photo( $post_id, $photos[ $discovery_id ][ $goal_id ][ $key ]['photo'] );
					unset( $photos[ $discovery_id ][ $goal_id ][ $key ] );
				} else {
					$date_string                                         = $photo_info['date'];
					$photos[ $discovery_id ][ $goal_id ][ $key ]['date'] = $date_string;
				}
			}
			// Make sure the photos array is sorted by key after adding all the separate _FILES and _POST data.
			recompute_array( $photos[ $discovery_id ][ $goal_id ] );
		}
	}


	// Update post meta
	// PRIO2: Move photos array under t_discoveries array
	$sr_data['photos'] = $photos;

	$sr_data['t_discoveries'] = null;

	if ( $_POST['t_discoveries'] ) {
		$sr_data['t_discoveries'] = $_POST['t_discoveries'];
	}


	$sr_data['t_journal_entries'] = null;

	if ( $_POST['t_journal_entries'] ) {
		$sr_data['t_journal_entries'] = $_POST['t_journal_entries'];
	}

	// Is it a new program?
	if ( is_null( $program_title ) ) {
		// Generate unique post_name
		$post_name = md5( $post_id . time() . wp_generate_password( 10, false ) );
		$wpdb->query( "UPDATE `{$wpdb->posts}` SET `post_name`='{$post_name}' WHERE `ID`='{$post_id}' LIMIT 1" );
	}

	// Check program title not empty if new.
	$program_title = $_POST['teacher-program-title'];
	$program_title = esc_sql( $program_title );
	if ( ! is_non_empty_string( $program_title ) ) {
		return;
	}

	$wpdb->query( "UPDATE `{$wpdb->posts}` SET `post_title`='{$program_title}' WHERE `ID`='{$post_id}' LIMIT 1" );

	if ( current_user_can( 'manage_options' ) ) {
		$sr_centre = $_POST['sr_centre'];
	} else {
		$user      = wp_get_current_user();
		$sr_centre = get_user_meta( $user->ID, 'sr_centre', true );
	}
	update_post_meta( $post_id, 'sr_centre', $sr_centre );
	update_post_meta( $post_id, 'teacher-program-title', $program_title );
	update_post_meta( $post_id, 'sr_data', $sr_data );
}

add_action( 'save_post', 'SR_save_post_teacher_group', 10, 3 );


// Apply special template
function TG_special_post_template_tgroups( $template ) {
	global $post, $sr_upload_dir, $local_testing, $theme;

	$post = get_post();

	if ( $post->post_type === 'tgroups' ) {
		// Check post ID
		$post_id = get_the_ID();

		if ( $post_id < 1 ) {
			die( "Can't find report post ID" );
		}

		// Check access rights
		$allowed = false;

		// Allow local load without permsissions check (for PDF requests)
		$local_load = false;
		if ( isset( $_REQUEST['local_load'] ) ) {
			//if ($_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR']){ TODO Close this security hole!
			$local_load = true;
			//}
		}

		if ( $local_load || is_user_logged_in() ) {
			// Check report student
			$postmeta = get_post_meta( $post_id );
			$sr_data  = $postmeta['sr_data'][0];

			if ( $sr_data ) {
				$sr_data = unserialize( $sr_data );
			} else {
				$sr_data = array();
			}

			$user = wp_get_current_user();
			// If admin or report editor
			if ( $local_load || current_user_can( 'manage_options' || in_array( 'report_editor', $user->roles ) ) ) {
				$allowed = true;
			}
		}


		if ( ! $allowed ) {
			$login_url = wp_login_url( get_permalink() );
			wp_redirect( $login_url );
			die();
		}


		// PDF export support
		if ( isset( $_REQUEST['export'] ) && $_REQUEST['export'] == 'pdf' ) {

			$selections = array();

			generate_pdf( $selections, $sr_upload_dir, $local_testing, true );

		}

		// Prepare template data
		global $SR_vars;

		$SR_vars                     = array();
		$SR_vars['post_id']          = $post_id;
		$SR_vars['sr_data']          = $sr_data;
		$SR_vars['plugin_url']       = plugins_url( '', __FILE__ );
		$SR_vars['plugin_theme_url'] = $SR_vars['plugin_url'] . '/templates/' . $theme;
		$SR_vars['local_load']       = $local_load;
		$SR_vars['post_title']       = $postmeta['teacher-program-title'][0];


		// Template file
		$template = plugin_dir_path( __FILE__ ) . 'templates/' . $theme . '/single-teacher-group-reports.php';
		if ( ! file_exists( $template ) ) {
			die( "Can't find template file for Student Report!" );
		}
	}

	return $template;
}

add_filter( 'template_include', 'TG_special_post_template_tgroups' );

?>