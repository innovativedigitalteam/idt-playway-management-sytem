<?php

function createChildGroupPostType() {
	$labels = array(
		'name'               => 'Group Programs (Child Initiated)',
		'singular_name'      => 'Group Program (Child Initiated)',
		'add_new'            => 'Add New Group Program (Child Initiated)',
		'add_new_item'       => 'Add New Group Program (Child Initiated)',
		'edit'               => 'Edit Group Program (Child Initiated)',
		'edit_item'          => 'Edit Group Program (Child Initiated)',
		'new_item'           => 'New Group Program (Child Initiated)',
		'view'               => 'View Group Program (Child Initiated)',
		'view_item'          => 'View Group Program (Child Initiated)',
		'search_items'       => 'Search Group Programs (Child Initiated)',
		'not_found'          => 'Nothing found',
		'not_found_in_trash' => 'Nothing found in Trash',
		'parent_item_colon'  => '',
		'all_items'          => 'All Group Programs (Child Initiated)',
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'hierarchical'        => false,
		'rewrite'             => array( 'slug' => 'cgroups', 'with_front' => 'true' ),
		'query_var'           => true,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-welcome-widgets-menus',
		'supports'            => array( '' ),
		'capability_type'     => 'cgroups',
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

	register_post_type( 'cgroups', $args );
}

add_action( 'init', 'createChildGroupPostType' );


//-----------------------------
//Add Group form to post edit screen
function childGroupsAddMetaBoxes() {
	add_meta_box(
		'child_group_custom_metabox',       // $id
		'Group (Child Initiated)',          // $title
		'childGroupCustomMetabox',          // $callback
		'cgroups',                            // $pageС
		'normal',                             // $context
		'high'                                // $priority
	);
}

add_action( 'add_meta_boxes', 'childGroupsAddMetaBoxes' );

// Add new role for Report Editor
function child_groups_add_roles_on_plugin_activation() {
	add_role( 'cgroup_editor', 'Child Initiated Group Editor' );

	// Add access to reports to other roles
	$roles = array( 'cgroup_editor', 'editor', 'administrator' );

	foreach ( $roles as $the_role ) {
		$role = get_role( $the_role );

		$role->add_cap( 'read' );
		$role->add_cap( 'publish_cgroups' );
		$role->add_cap( 'edit_cgroups' );
		$role->add_cap( 'edit_others_cgroups' );
		$role->add_cap( 'delete_cgroups' );
		$role->add_cap( 'delete_others_cgroups' );
		$role->add_cap( 'read_private_cgroups' );
		$role->add_cap( 'read_' );
	}
}

register_activation_hook( __FILE__, 'child_groups_add_roles_on_plugin_activation' );

function cgroups_preview_settings_page() {
	?>
    <div class="wrap">
        <h2>Child Initiated Report Preview Settings</h2>
        <form action="../wp-content/plugins/student-reports/child-groups-preview.php" method="post"
              target="_blank">

            <br/>
            <br/>

            <div class="child-program-summary">
                <table>
                    <tr>
                        <td><label for="week-starting-date">Week starting</label></td>
                        <td><input class="my-datepicker" name="week-starting-date" id="week-starting-date"
                                   required></td>
                    </tr>
                    <tr>
                        <td><label for="select-group">Select Group</label></td>
                        <td>
                            <select data-placeholder="Choose a group..." style="width: 350px;"
                                    class="chosen-select"
                                    name="select-group"
                                    id="select-group"
                                    required>
                                <option></option>
								<?php
								echo populateGroupsForSelection();
								?>
                            </select>
                        </td>
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

function child_groups_options_page() {
	add_submenu_page(
		'edit.php?post_type=cgroups',
		'Child Initiated Reports Preview',
		'Child Initiated Reports Preview',
		'publish_reports',
		'preview',
		'cgroups_preview_settings_page'
	);
}

add_action( 'admin_menu', 'child_groups_options_page' );

function childGroupCustomMetabox() {
	global $post, $sr_centres;

	wp_nonce_field( basename( __FILE__ ), 'cgroup_nonce' );

	// PRIO2: Pull this up to Functions.php
	// Check if that editor can work with this portfolio
	$editor_centre = '';
	$user          = wp_get_current_user();
	if ( in_array( 'report_editor', $user->roles ) ) {
		$editor_centre = get_user_meta( $user->ID, 'sr_centre', true );
		$post_centre   = get_post_meta( $post->ID, 'sr_centre', true );

		if ( $post_centre && $editor_centre !== $post_centre ) {
			wp_redirect( admin_url( 'edit.php?post_type=cgroups' ) );
			die();
		}
	}

	// Load Report data
	$postmeta = get_post_meta( $post->ID );
	$sr_data  = $postmeta['sr_data'][0];

	if ( $sr_data ) {
		$sr_data = unserialize( $sr_data );
	} else {
		$sr_data = array();
	}

	$sdt = (object) [
		'id'          => $postmeta['sr_student_id'][0],
		'startdate'   => $sr_data['week-starting-date'],
		'writtenby'   => $sr_data['written-by'],
		'experience'  => $sr_data['select-experience'],
		'discovery'   => $sr_data['discovery'],
		'spontaneous' => $sr_data['spontaneous']
	];

	$already_loaded = "";
	if ( $sdt->id ) {
		$already_loaded = " select-loaded";
	}

	?>

    <div class="child-program-summary">
        <table>
            <tr>
                <td><label for="week-starting-date">Week starting</label></td>
                <td><input class="my-datepicker" name="week-starting-date" id="week-starting-date"
                           value="<?php echo $sdt->startdate; ?>"></td>
            </tr>
            <tr>
                <td><label for="written-by">Written By</label></td>
                <td><input name="written-by" id="written-by" value="<?php echo $sdt->writtenby; ?>">
                </td>
            </tr>
            <tr>
                <td><label for="select-child">Select Child</label></td>
                <td>
                    <select data-placeholder="Choose a student..." style="width: 350px;"
                            class="chosen-select<?= $already_loaded ?>"
                            name="select-child"
                            id="select-child">
                        <option></option>
						<?php
						echo populateStudentsForSelection( $sr_centres, $editor_centre, $sdt->id, $sdt );
						?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="focus-group">Focus Group</label></td>
                <td><input id="focus-group" value="" readonly>
                </td>
            </tr>
            <tr>
                <td><label for="teacher-program-start-date">Experience Date</label></td>
                <td>
                    <select data-placeholder="Choose an experience..." style="width: 350px;" class="chosen-select"
                            name="select-experience" loaded-value="<?php echo $sdt->experience; ?>"
                            id="select-experience">
                        <option></option>
                        <!-- This will be populated in jQuery -->
                    </select>
                </td>
            </tr>
        </table>
    </div>
    <br/>
    <br/>
    <div class="child-program-details">
        <div>
            <span>Goal: </span>
            <textarea id="goal_text" rows="10" cols="100" readonly>Data will be pulled in when experience is selected...</textarea>
        </div>
        <div>
            <span>Strategies / Objectives: </span>
            <textarea id="objectives_text" rows="10" cols="100" readonly>Data will be pulled in when experience is selected...</textarea>
        </div>
        <br/>
        <div>
            <span>Learning Experience: </span>
            <textarea id="experience_text" rows="10" cols="100" readonly>Data will be pulled in when experience is selected...</textarea>
        </div>
        <br/>
        <div>
            <span>Discovery / Developmental Learning & Outcomes: </span>
            <textarea name="discovery" rows="10" cols="100"><?php echo $sdt->discovery; ?></textarea>
        </div>
        <br/>
        <div>
            <span>Modifications/Spontaneous Experiences: </span>
            <textarea name="spontaneous" rows="10" cols="100"><?php echo $sdt->spontaneous; ?></textarea>
        </div>
    </div>
	<?php
}

// Now we are saving the data
function SR_save_post_child_group( $post_id, $post, $update ) {
	global $wpdb;

	// Check post type
	if ( $post->post_type != 'cgroups' ) {
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
		$sr_data = unserialize( $sr_data );
	} else {
		$sr_data = array();
	}

	if ( $_POST['week-starting-date'] && $_POST['week-starting-date'] != "" ) {
		$sr_data['week-starting-date'] = $_POST['week-starting-date'];
	}

	if ( $_POST['written-by'] && $_POST['written-by'] != "" ) {
		$sr_data['written-by'] = $_POST['written-by'];
	}

	if ( $_POST['select-experience'] && $_POST['select-experience'] != "" ) {
		$sr_data['select-experience'] = $_POST['select-experience'];
	}

	if ( $_POST['discovery'] && $_POST['discovery'] != "" ) {
		$sr_data['discovery'] = $_POST['discovery'];
	}

	if ( $_POST['spontaneous'] && $_POST['spontaneous'] != "" ) {
		$sr_data['spontaneous'] = $_POST['spontaneous'];
	}

	// Check student ID
	$post_student_id = (int) $_POST['select-child'];
	if ( $post_student_id < 1 ) {
		return;
	}

	// Is it a new program?
	//RPRIO1: We don't need this as posts will never be viewed directly
//	if ( is_null( $program_title ) ) {
//		// Generate unique post_name
//		$post_name = md5( $post_id . time() . wp_generate_password( 10, false ) );
//		$wpdb->query( "UPDATE `{$wpdb->posts}` SET `post_name`='{$post_name}' WHERE `ID`='{$post_id}' LIMIT 1" );
//	}

	// Set post title
	$student_meta = get_user_meta( $post_student_id );

	$student_name = implode( ' ', array( $student_meta['first_name'][0], $student_meta['last_name'][0] ) );

	$post_title = "{$student_name} - {$sr_data['week-starting-date']}";
	$post_title = esc_sql( $post_title );

	$wpdb->query( "UPDATE `{$wpdb->posts}` SET `post_title`='{$post_title}' WHERE `ID`='{$post_id}' LIMIT 1" );

	update_post_meta( $post_id, 'sr_student_id', $post_student_id );
	update_post_meta( $post_id, 'sr_centre', $student_meta['sr_centre'][0] );
	update_post_meta( $post_id, 'sr_data', $sr_data );
}

add_action( 'save_post', 'SR_save_post_child_group', 10, 3 );

/*
// Apply special template
function IP_special_post_template_portfolio($template)
{
    global $post, $wpdb, $sr_upload_dir, $sr_centres;

    $post = get_post();

    if ($post->post_type === 'portfolios') {
        // Check post ID
        $post_id = get_the_ID();

        if ($post_id < 1) {
            die("Can't find report post ID");
        }




        // Check access rights
        $allowed = false;

        // Allow local load without permsissions check (for PDF requests)
        $local_load = false;
        if (isset($_REQUEST['local_load'])) {
            //if ($_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR']){ TODO Close this security hole!
            $local_load = true;
            //}
        }

        if ($local_load || is_user_logged_in()) {
            // Check report student
            $postmeta = get_post_meta($post_id);
            $gr_data = $postmeta['gr_data'][0];
            if ($gr_data) {
                $gr_data = unserialize($gr_data);
                $student_id = $postmeta['sr_student_id'][0];
            } else {
                $gr_data = array();
                $student_id = 0;
            }

            if ($student_id < 1) {
                die("Can't find student ID for this report");
            }

            // Get student info
            $student = get_user_meta($student_id);

            if (!$student['nickname'][0]) {
                die("Can't find student for this report");
            }

            if ($local_load || current_user_can('manage_options')) {
                // Allow for Admin
                $allowed = true;
            } else {
                $user = wp_get_current_user();
                // Check that user is student parent
                if ($user->ID == $student['sr_parent_id'][0]) {
                    $allowed = true;
                } else {
                    // Check if that user has "Report Editor" role and from this centre
                    if (in_array('report_editor', $user->roles)) {
                        $editor_centre = get_user_meta($user->ID, 'sr_centre', true);
                        $student_centre = $student['sr_centre'][0];

                        if ($editor_centre && $editor_centre === $student_centre) {
                            $allowed = true;
                        }
                    }
                }
            }
        }


        if (!$allowed) {
            $login_url = wp_login_url(get_permalink());
            wp_redirect($login_url);
            die();
        }


        // PDF t_export support
        if (isset($_REQUEST['t_export']) && $_REQUEST['t_export'] == 'pdf') {
            $post_url = get_post_permalink($post->ID);

            if ($post_url) {
                $name = '';
                if (preg_match('/([a-z0-9]{32})/', $post_url, $m)) {
                    $name = $m[1];
                }

                if ($name) {
                    $fname = $sr_upload_dir . '/' . $name . '.pdf';
                    $post_url .= '?local_load';

                    // Generate PDF file
                    @exec("xvfb-run --server-args=\"-screen 4, 1280x1024x24\" wkhtmltopdf --use-xserver --disable-javascript \"{$post_url}\" {$fname}");

                    // Output to browser
                    if (file_exists($fname)) {
                        header('Content-Type: application/pdf');
                        header('Content-Length: ' . filesize($fname));
                        header('Content-disposition: inline; filename="' . basename($fname) . '"');
                        header('Cache-Control: public, must-revalidate, max-age=0');
                        header('Pragma: public');
                        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
                        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                        readfile($fname);

                        @unlink($fname);
                    }
                }


            }

            die();
        }


        // Prepare template data
        global $SR_vars;

        $SR_vars = array();
        $SR_vars['post_id'] = $post_id;
        $SR_vars['student'] = $student;
        $SR_vars['parent'] = get_userdata($student['sr_parent_id'][0]);
        $SR_vars['gr_data'] = $gr_data;
        $SR_vars['centre_title'] = $sr_centres[$postmeta['sr_centre'][0]]->title;
        $SR_vars['date'] = date('j F Y', strtotime($post->post_modified));
        $SR_vars['plugin_url'] = plugins_url('', __FILE__);
        $SR_vars['plugin_theme_url'] = $SR_vars['plugin_url'] . '/templates/' . $theme;

        // Get all students and reports of this parent
        $SR_vars['all_students'] = $wpdb->get_col("SELECT `user_id` FROM `{$wpdb->usermeta}` WHERE `meta_key`='sr_parent_id' AND `meta_value`='{$student['sr_parent_id'][0]}' ORDER BY `user_id`");
        $SR_vars['local_load'] = $local_load;


        // Template file


        $template = plugin_dir_path(__FILE__) . 'templates/' . $theme . '/single-portfolios.php';
        if (!file_exists($template)) {
            die("Can't find template file for Student Report!");
        }
    }

    //  die($template);

    return $template;
}

add_filter('template_include', 'IP_special_post_template_portfolio');

*/

?>