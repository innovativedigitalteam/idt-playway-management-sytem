<?php

function createGroupPostType() {
	$labels = array(
		'name'               => 'Program Groups',
		'singular_name'      => 'Program Group',
		'add_new'            => 'Add New Program Group',
		'add_new_item'       => 'Add New Program Group',
		'edit'               => 'Edit Program Group',
		'edit_item'          => 'Edit Program Group',
		'new_item'           => 'New Program Group',
		'view'               => 'View Program Group',
		'view_item'          => 'View Program Group',
		'search_items'       => 'Search Program Groups',
		'not_found'          => 'Nothing found',
		'not_found_in_trash' => 'Nothing found in Trash',
		'parent_item_colon'  => '',
		'all_items'          => 'All Program Groups',
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'hierarchical'        => false,
		'rewrite'             => array( 'slug' => 'groups', 'with_front' => 'true' ),
		'query_var'           => true,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-welcome-widgets-menus',
		'supports'            => array( '' ),
		'capability_type'     => 'groups',
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

	register_post_type( 'groups', $args );
//    flush_rewrite_rules();
}

add_action( 'init', 'createGroupPostType' );


//-----------------------------
//Add Group form to post edit screen
function groupsAddMetaBoxes() {
	add_meta_box(
		'group_custom_metabox',       // $id
		'Program Group',              // $title
		'groupCustomMetabox',         // $callback
		'groups',                     // $pageС
		'normal',                     // $context
		'high'                        // $priority
	);
}

add_action( 'add_meta_boxes', 'groupsAddMetaBoxes' );

// Add new role for Report Editor
function groups_add_roles_on_plugin_activation() {
	add_role( 'group_editor', 'Group Editor' );

	// Add access to reports to other roles
	$roles = array( 'group_editor', 'editor', 'administrator' );

	foreach ( $roles as $the_role ) {
		$role = get_role( $the_role );

		$role->add_cap( 'read' );
		$role->add_cap( 'publish_groups' );
		$role->add_cap( 'edit_groups' );
		$role->add_cap( 'edit_others_groups' );
		$role->add_cap( 'delete_groups' );
		$role->add_cap( 'delete_others_groups' );
		$role->add_cap( 'read_private_groups' );
		$role->add_cap( 'read_groups' );
	}
}

register_activation_hook( __FILE__, 'groups_add_roles_on_plugin_activation' );

function groups_preview_settings_page() {
	global $sr_centres;
	?>
    <div class="wrap">
        <h2>Groups Preview Settings</h2>
        <form action="../wp-content/plugins/student-reports/groups-preview.php" method="post"
              target="_blank">
            <br/>
            <br/>

            <div class="child-program-summary">
                <table>

					<?php
					$user = wp_get_current_user();
					if ( ! in_array( 'report_editor', $user->roles ) ) {
						?>

                        <tr>
                            <td><label for="select-group">Select Centre</label></td>
                            <td>
                                <select data-placeholder="Choose a centre..." style="width: 350px;"
                                        class="chosen-select"
                                        name="select-centre"
                                        id="select-centre"
                                        required>
                                    <option></option>
									<?php
									echo populateCentresForSelection( $sr_centres );
									?>
                                </select>
                            </td>
                        </tr>

						<?php
					}
					?>
                    <tr>
                        <td><label for="select-room">Select Room</label></td>
                        <td><input name="select-room" id="select-room"
                                   required></td>
                    </tr>

                </table>
            </div>

            <br/>
            <br/>
            <input name="Submit" type="submit" value="Preview"
                   class="button button-primary"/>
        </form>
    </div>
	<?php
}


function groups_options_page() {
	global $submenu;

	add_submenu_page(
		'edit.php?post_type=groups',
		'Program Groups Preview',
		'Program Groups Preview',
		'publish_reports',
		'preview',
		'groups_preview_settings_page'
	);
}

add_action( 'admin_menu', 'groups_options_page' );


function groupCustomMetabox() {
	global $post, $sr_centres;

	$selected_centre = $_POST['sr_centre'];
	wp_nonce_field( basename( __FILE__ ), 'group_nonce' );

	// PRIO2: Pull this up to Functions.php
	// Check if that editor can work with this portfolio
	$editor_centre = '';
	$user          = wp_get_current_user();
	if ( in_array( 'report_editor', $user->roles ) ) {
		$editor_centre = get_user_meta( $user->ID, 'sr_centre', true );
		$post_centre   = get_post_meta( $post->ID, 'sr_centre', true );

		if ( $post_centre && $editor_centre !== $post_centre ) {
			wp_redirect( admin_url( 'edit.php?post_type=groups' ) );
			die();
		}
	}

	// Load Report data
	$postmeta     = get_post_meta( $post->ID );
	$group_centre = $postmeta['sr_centre'][0];
	$group_name   = $postmeta['gr_name'][0];
	$group_room   = $postmeta['gr_room'][0];

	if ( is_non_empty_string( $group_name ) ) {
		$new_post = false;
	} else {
		$new_post = true;
	}

	$is_admin = ! in_array( 'report_editor', $user->roles );

	if ( $new_post && $is_admin ) {
		$is_centre_disabled = false;
		$disabled_text      = "";
	} else {
		$is_centre_disabled = true;
		$disabled_text      = "disabled";
	}


	$user = wp_get_current_user();

	?>

    <div class="gr_group">
        <table>
            <tr>
                <td><label for="gr_group_name">Group name</label></td>
                <td><input name="gr_group_name" id="gr_group_name" required value="<?php echo $group_name; ?>"></td>
            </tr>
            <tr>
                <td><label for="sr_centre">Centre</label></td>
                <td>
                    <select id="sr_centre" name="sr_centre" required style="width: 350px;" <?= $disabled_text ?>>
                        <option value="">Select...</option>
						<?php
						populateCentresForSelection( $sr_centres, $group_centre, $editor_centre );
						?>
                    </select>
					<?php
					if ( $is_centre_disabled ) {
						$centre_to_post = $is_admin ? $group_centre : $editor_centre;
						echo '<input type="hidden" name="sr_centre" value="' . $centre_to_post . '"';
					}
					?>
                </td>
            </tr>
            <tr>
                <td><label for="gr_group_room">Room</label></td>
                <td><input name="gr_group_room" id="gr_group_room" required value="<?php echo $group_room; ?>"></td>
            </tr>
        </table>
    </div>

    <br/>
    <br/>
    <br/>

	<?php

	if ( $is_admin ) {
		// If Administrator
		$centre_for_student_selection = $selected_centre ? $selected_centre : $group_centre;
	} else {
		// If report editor
		$centre_for_student_selection = $selected_centre ? $selected_centre : $editor_centre;
	}

	if ( $new_post ) {
		$display = "display:none";
	} else {
		if ( ! $centre_for_student_selection ) {
			wp_redirect( admin_url( 'edit.php?post_type=groups' ) );
			die();
		}
		$display = "";
	}


	$children_list = get_children_in_group( $post->ID );

	foreach ( $children_list as $child ) {
		$full_name = get_user_meta( $child->user_id, 'first_name', true ) . ' ' . get_user_meta( $child->user_id, 'last_name', true )
		?>
        <input name="children[]" type="checkbox" value="<?= $child->user_id ?>">
        <a href="<?php echo admin_url() . "user-edit.php?user_id={$child->user_id}" ?>"
           target="_blank"><?= $full_name ?></a>
        <br/>
		<?php
	}

	?>
    <br/>

	<?php
	if ( count( $children_list ) > 0 ) {
		?>
        <input type="submit" class="button button-secondary" name="delete-children"
               value="Delete Selected Children"></input>
		<?php
	}
	?>

    <br/>
    <br/>

	<?php

	if ( count( $children_list ) < MAX_STUDENTS_PER_GROUP ) {
		?>
        <div style="<?php echo $display; ?>">
            <select data-placeholder="Choose a student to add..." style="width: 350px;"
                    class="chosen-select" name="gr_student_id">
                <option></option>
				<?php
				echo populateStudentsForSelection( $sr_centres, $centre_for_student_selection );
				?>
            </select>
            <!-- PRIO2: Replace the following with commented out button when adding children without
						page refresh is implemented. -->
            <label> Press <strong>Update</strong> to add selected child. </label>
        </div>

        <br/>
        <br/>
        <br/>

		<?php
	}
}

// Now we are saving the data
function SR_save_post_group( $post_id, $post, $update ) {
	global $wpdb, $sr_centres;

	// Check post type
	if ( $post->post_type != 'groups' ) {
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
	$group_name   = $_POST['gr_group_name'];
	$group_room   = $_POST['gr_group_room'];
	$group_centre = $_POST['sr_centre'];
	$student_id   = $_POST['gr_student_id'];

	$postmeta = get_post_meta( $post_id );
	if ( is_array( $postmeta ) && array_key_exists( 'gr_name', $postmeta ) ) {
		$new_post = false;
	} else {
		// New Post
		$new_post = true;
	}

	// PRIO2: Create some back-end checking to provide some error output if the fields were somehow blank
	if ( ! $_POST['gr_group_name'] || ( $new_post && ! $_POST['sr_centre'] ) ) {
		// This should never happen
		return;
	}

	if ( ! isset( $_POST['delete-children'] ) ) {
		if ( $new_post ) {
			// Generate unique post_name
			$post_name = md5( $post_id . time() . wp_generate_password( 10, false ) );
			$wpdb->query( "UPDATE `{$wpdb->posts}` SET `post_name`='{$post_name}' WHERE `ID`='{$post_id}' LIMIT 1" );
		}

		$post_title           = "{$group_name} [{$sr_centres[$group_centre]->title}]";
		$post_title_sanitized = esc_sql( $post_title );

		$wpdb->query( "UPDATE `{$wpdb->posts}` SET `post_title`='{$post_title_sanitized}' WHERE `ID`='{$post_id}' LIMIT 1" );

		update_post_meta( $post_id, 'gr_name', $group_name );
		update_post_meta( $post_id, 'gr_room', $group_room );
		update_post_meta( $post_id, 'sr_centre', $group_centre );

		if ( ! $new_post && valid_id( $student_id ) ) {
			update_user_meta( $student_id, 'gr_focus_group', $post_id );
		}

	} else {
		$children_to_delete = $_POST['children'];
		foreach ( $children_to_delete as $child_id ) {
			update_user_meta( $child_id, 'gr_focus_group', "" );
		}
	}
}

add_action( 'save_post', 'SR_save_post_group', 10, 3 );

?>