<?php


include_once "includes/CommonFront.php";

defined( 'ABSPATH' ) or die( "No direct access!" );
global $wpdb;

$sr_upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/../protected-images';
$sr_images_url = plugins_url( '', __FILE__ ) . '/get.php';

include_once "includes/GlobalData.php";
include_once "includes/Functions.php";

include_once "cronReports.php";
include_once "individual-portfolios.php";
include_once "groups.php";
include_once "teacher-group-programs.php";
include_once "child-group-programs.php";


// Load and prepare Centres info
$rows = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}hope_centres`" );

if ( ! count( $rows ) && ! is_admin() ) {
	die( "Table {$wpdb->prefix}hope_centres is empty! (Student Reports plugin)\n" );
}

$sr_centres = array();
foreach ( $rows as $row ) {
	$sr_centres[ $row->id ] = $row;
}

$sr_blocks['empathy']      = array( 'title' => 'Empathy' );
$sr_blocks['knowledge']    = array( 'title' => 'Knowledge' );
$sr_blocks['commitment']   = array( 'title' => 'Commitment' );
$sr_blocks['independence'] = array( 'title' => 'Independence & Persistence' );
$sr_blocks['respect']      = array( 'title' => 'Respect' );
$sr_blocks['reflexivity']  = array( 'title' => 'Reflexivity' );


// Add "Student Reports" post type
function create_SRpost_type() {
	$labels = array(
		'name'               => 'Parent Reports',
		'singular_name'      => 'Report',
		'add_new'            => 'Add New Report',
		'add_new_item'       => 'Add New Report',
		'edit'               => 'Edit Report',
		'edit_item'          => 'Edit Report',
		'new_item'           => 'New Report',
		'view'               => 'View Report',
		'view_item'          => 'View Report',
		'search_items'       => 'Search Reports',
		'not_found'          => 'Nothing found',
		'not_found_in_trash' => 'Nothing found in Trash',
		'parent_item_colon'  => '',
		'all_items'          => 'All Reports',
	);

	$atp_report_slug = 'reports';

	$args = array(
		'labels'              => $labels,
		'description'         => 'Placeholder for all the Reports',
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'hierarchical'        => false,
		'rewrite'             => array( 'slug' => $atp_report_slug, 'with_front' => 'true' ),
		'query_var'           => true,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-welcome-widgets-menus',
		'supports'            => array( 'revisions' ),
		'capability_type'     => 'reports',
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

	register_post_type( 'reports', $args );
}

add_action( 'init', 'create_SRpost_type' );


// Add JS and CSS to admin
function SR_admin_enqueue_scripts() {
	global $plugin_post_types;
	$screen = get_current_screen();

	$allow = false;

	if ( ( $screen->base === 'post' || $screen->base === 'cgroups_page_preview' ) && in_array( $screen->post_type, $plugin_post_types )
	) {

		wp_enqueue_script( 'my-footer', plugins_url( '', __FILE__ ) . '/templates/footer.js', array(
			'jquery',
			'jquery-ui-datepicker'
		), '1', true );
		wp_enqueue_style( 'my-datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' );

		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'admin', plugins_url( '', __FILE__ ) . '/templates/admin.js', array( 'jquery' ), filemtime( __FILE__ ) . rand() );
		wp_enqueue_script( 'chosen', plugins_url( '', __FILE__ ) . '/templates/chosen/chosen.jquery.min.js' );
		wp_enqueue_style( 'admin', plugins_url( '', __FILE__ ) . '/templates/admin.css', array(), filemtime( __FILE__ ) );
		wp_enqueue_style( 'chosen', plugins_url( '', __FILE__ ) . '/templates/chosen/chosen.min.css' );

	} else if ( $screen->base === 'user-edit' ) {
		wp_enqueue_script( 'admin', plugins_url( '', __FILE__ ) . '/templates/admin.js', array( 'jquery' ), filemtime( __FILE__ ) );
		wp_enqueue_script( 'chosen', plugins_url( '', __FILE__ ) . '/templates/chosen/chosen.jquery.min.js' );
		wp_enqueue_style( 'chosen', plugins_url( '', __FILE__ ) . '/templates/chosen/chosen.min.css' );
	}
}

add_action( 'admin_enqueue_scripts', 'SR_admin_enqueue_scripts' );


// Add multipart/form-data support to the post edit form
function SR_post_edit_form_tag() {
	echo ' enctype="multipart/form-data"';
}

add_action( 'post_edit_form_tag', 'SR_post_edit_form_tag' );

function add_meta_keys_to_revision( $keys ) {
	$keys[] = 'sr_data';
	return $keys;
}
add_filter( 'wp_post_revision_meta_keys', 'add_meta_keys_to_revision' );


// Add Student Report form to post edit screen
function SR_add_meta_boxes() {
	add_meta_box(
		'SR_custom_meta_box',      // $id
		'REPORT DETAILS',          // $title
		'SR_custom_meta_box',       // $callback
		'reports',                 // $pageС
		'normal',                  // $context
		'high'                     // $priority
	);
}

add_action( 'add_meta_boxes', 'SR_add_meta_boxes' );


function SR_custom_meta_box() {
	global $post, $wpdb, $sr_blocks, $sr_upload_dir, $sr_images_url, $sr_centres;


	wp_nonce_field( basename( __FILE__ ), 'SR_nonce' );


	// Get current student
	$postmeta   = get_post_meta( $post->ID );
	$sr_data    = $postmeta['sr_data'][0];
	$student_id = $postmeta['sr_student_id'][0];
	if ( $student_id ) {
		$sr_data                 = unserialize( $sr_data );
		$disabled_student_select = "disabled";
		$new_post                = false;
	} else {
		$sr_data                 = array();
		$student_id              = 0;
		$disabled_student_select = "";
		$new_post                = true;
	}


	// Get all parents
	$parents       = get_users( 'orderby=meta_value&meta_key=first_name&role=subscriber' );
	$parentEditors = get_users( 'orderby=meta_value&meta_key=first_name&role=report_editor' );

	$parents = array_merge( $parents, $parentEditors );


	// Check if that editor can work with this report
	$editor_centre = '';
	$user          = wp_get_current_user();
	if ( in_array( 'report_editor', $user->roles ) ) {
		$editor_centre = get_user_meta( $user->ID, 'sr_centre', true );
		$post_centre   = get_post_meta( $post->ID, 'sr_centre', true );

		if ( $post_centre && $editor_centre !== $post_centre ) {
			wp_redirect( admin_url( 'edit.php?post_type=reports' ) );
			die();
		}
	}


	// Notify button
	$button_text = get_post_meta( $post->ID, 'sr_notify_msg', true );
	if ( ! $button_text ) {
		$button_text = 'Send notify email';
	}
	$button_text = esc_attr( $button_text );

	?>
    <div class="sr-notify-area">
        <input name="save" type="button" class="button button-large" id="sr-notify" value="<?php echo $button_text; ?>">
        <input type="hidden" name="send_notify" id="send_notify" value="0">
    </div>


    <label for="report-student" class="student-label">Student</label>&nbsp;
    <select data-placeholder="Choose a student..." style="width: 350px;" class="chosen-select" name="student_id"
            id="student_id" <?= $disabled_student_select ?>>
        <option></option>
		<?php
		if ( $new_post ) {
			echo populateStudentsForSelection( $sr_centres, $editor_centre );
		} else {
			echo populateStudentsForSelection( $sr_centres, $editor_centre, $student_id );
		}
		?>
    </select>
	<?php
	if ( ! $new_post ) {
		?>
        <input type="hidden" name="student_id" value="<?= $student_id ?>">
		<?php
	}
	?>

    <br/>
    <br/>
    <br/>


	<?php
	$oldSay = array( 'teacher-say' => true, 'family-say' => true );
	foreach ( $sr_blocks as $key => $b ) {
		?>
        <fieldset key="<?php echo $key; ?>">
            <legend><?php echo esc_html( $b['title'] ); ?></legend>
            <div class="sr-data-block">
				<?php
				for ( $i = 0; $i < count( $sr_data['photos'][ $key ] ); $i ++ ) {
					$photo    = $sr_data['photos'][ $key ][ $i ];
					$required = ( $key == 'empathy' && $i == 0 ) ? ' required' : '';
					$photo_id = $photo['photo'];

					$teachersSay = @$photo['teachers-say'];
					if ( ! $teachersSay ) {
						$teachersSay = '';
					}

					$familySay = @$photo['family-say'];
					if ( ! $familySay ) {
						$familySay = '';
					}

					if ( ! empty( $teachersSay ) ) {
						$oldSay['teacher-say'] = false;
					}

					if ( ! empty( $familySay ) ) {
						$oldSay['family-say'] = false;
					}

					?>
                    <div class="sr-photo-block">
                        <p class="sr-thumb">
							<?php
							if ( $photo_id ) {
								echo '<img alt="photo" src="' . $sr_images_url . '?post_id=' . $post->ID . '&object=' . $photo_id . '&x=80&y=90" width="80" height="90" />';
							}
							?>
                        </p>
                        <p class="sr-upload">
							<?php
							if ( ! $photo_id ) {
								echo '<input type="file" name="photos[' . $key . '][' . $i . '][photo]"' . $required . ' />';
							} else {
								echo '<input type="checkbox" name="delete_photos[]" value="' . $photo_id . '" id="' . $photo_id . '" /> <label for="' . $photo_id . '">Delete</label>';
							}
							?>
                        </p>
                        <p class="sr-date">
							<?php
							$val = @$sr_data['photos'][ $key ][ $i ]['date'];
							if ( ! $val ) {
								$val = date( 'd.m.Y' );
							}
							$dateFinal = date("d-m-Y", strtotime($val) );
							?>
                            <input type="text" class="my-datepicker" name="photos[<?php echo $key; ?>][<?php echo $i; ?>][date]"
                                   value="<?php echo esc_attr( $dateFinal ); ?>" size="10"
                                   maxlength="10" <?php echo $required; ?> />
                        </p>
                        <p class="sr-teachers-say">
                            <label for="photos[<?php echo $key; ?>][<?php echo $i; ?>][teachers-say]">What my teachers
                                say</label>
                            <textarea name="photos[<?php echo $key; ?>][<?php echo $i; ?>][teachers-say]" rows="2"
                                      cols="60"><?php echo esc_attr( $teachersSay ); ?></textarea>
                        </p>
                        <p class="sr-family-say">
                            <label for="photos[<?php echo $key; ?>][<?php echo $i; ?>][family-say]">What my family
                                say</label>
                            <textarea name="photos[<?php echo $key; ?>][<?php echo $i; ?>][family-say]" rows="2"
                                      cols="60"><?php echo esc_attr( $familySay ); ?></textarea>
                        </p>
                    </div>
					<?php
				}
				?>

            </div>

            <input type="button" class="sr-one-more" value="Add one more photo"/>
			<?php
			$oldTeachersSay = $sr_data['teachers_say'][ $key ];

			if ( $oldTeachersSay && $oldSay['teacher-say'] ) { ?>
                <p class="sr-teachers-say"><span>This text was here before an update. It will disappear when you will enter some text into any of the fields below. (What my teachers say): </span><span><?php echo $oldTeachersSay ?></span>
                </p>
				<?php
			}
			?>

			<?php
			$oldFamilySay = $sr_data['family_say'][ $key ];

			if ( $oldFamilySay && $oldSay['family-say'] ) { ?>
                <p class="sr-family-say"><span>This text was here before an update. It will disappear when you will enter some text into any of the fields below. (What my family say): </span><span><?php echo $oldFamilySay ?></span>
                </p>
				<?php
			}
			?>
        </fieldset>
		<?php
	}
}

function restore_post( $post_id ) {
	global $plugin_post_types;

	$postmeta = get_post_meta( $post_id );
	$post     = get_post( $post_id );

	if ( in_array( $post->post_type, $plugin_post_types ) ) {
		$student_id = $postmeta['sr_student_id'][0];
		if ( $student_id ) {
			if ( student_post_exists( $student_id, $post->post_type ) ) {
				$message = '<p>You can not restore this post as a post of this type for the same child already exists</p>'
				           . '<p><a href="' . admin_url( 'edit.php?post_status=trash&post_type=' . $post->post_type ) . '">Go back to trash</a></p>';
				wp_die( $message, 'You are not allowed to restore from trash.' );
			}
		}
	}
}

add_action( 'untrash_post', 'restore_post' );


function recurse_copy( $src, $dst ) {
	$dir = opendir( $src );
	@mkdir( $dst );
	while ( false !== ( $file = readdir( $dir ) ) ) {
		if ( ( $file != '.' ) && ( $file != '..' ) ) {
			if ( is_dir( $src . '/' . $file ) ) {
				recurse_copy( $src . '/' . $file, $dst . '/' . $file );
			} else {
				copy( $src . '/' . $file, $dst . '/' . $file );
				unlink( $src . '/' . $file );
			}
		}
	}
	closedir( $dir );
}


// Now we are saving the data
function SR_save_post( $post_id, $post, $update ) {
	global $wpdb;


	// Check post type
	if ( $post->post_type != 'reports' ) {
		return;
	}

	// Admin area only
	if ( ! is_admin() ) {
		return;
	}

	// Verify nonce
	if ( ! isset( $_POST['SR_nonce'] ) || ! wp_verify_nonce( $_POST['SR_nonce'], basename( __FILE__ ) ) ) {
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


	// Load Report data
	$postmeta   = get_post_meta( $post_id );
	$student_id = $postmeta['sr_student_id'][0];
	$sr_data    = $postmeta['sr_data'][0];
	if ( $student_id ) {
		$sr_data = unserialize( $sr_data );
	} else {
		if ( is_non_empty_string( $sr_data ) ) {
			// Just in case by some accident there was already data associated with a post where student data was wiped.
			$sr_data = unserialize( $sr_data );
		} else {
			$sr_data = array();
		}
		$student_id = 0;
	}

	// Is a new report?
	if ( $student_id < 1 ) {
		// Do not allow a new report to be generated if it already exists
		if ( $post->post_status == 'publish' && student_post_exists( $_POST['student_id'], $post->post_type ) ) {
			$post->post_status = 'draft';
			unset( $_POST['student_id'] );
			wp_update_post( $post );
			update_post_meta( $post_id, 'sr_student_id', 0 );
			update_post_meta( $post_id, 'sr_data', "" );

			$message = '<p>A report for this student already exists</p>'
			           . '<p><a href="' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ) . '">Go back and edit the post</a></p>';
			wp_die( $message, 'Duplicate student post' );
		} else if ( $post->post_status == 'draft' && ! $_POST['student_id'] ) {
			return;
		}

		// Generate unique post_name
		$post_name = md5( $post_id . time() . wp_generate_password( 10, false ) );
		$wpdb->query( "UPDATE `{$wpdb->posts}` SET `post_name`='{$post_name}' WHERE `ID`='{$post_id}' LIMIT 1" );
	} else if ( $student_id != $_POST['student_id'] || $_POST['student_id'] < 1 ) {
		return;
	}

	$student_id = (int) $_POST['student_id'];

	// Set post title
	$student_meta = get_user_meta( $student_id );
	$parent_meta  = get_user_meta( $student_meta['sr_parent_id'][0] );

	$student_name = implode( ' ', array( $student_meta['first_name'][0], $student_meta['last_name'][0] ) );
	$parent_name  = implode( ' ', array( $parent_meta['first_name'][0], $parent_meta['last_name'][0] ) );

	$post_title = "{$student_name} [{$parent_name}]";
	$post_title = esc_sql( $post_title );

	$wpdb->query( "UPDATE `{$wpdb->posts}` SET `post_title`='{$post_title}' WHERE `ID`='{$post_id}' LIMIT 1" );


	// Process images
	$photos = isset( $sr_data['photos'] ) ? $sr_data['photos'] : array();


	// Process dates, "teachers say", "family say" for existed photos
	foreach ( $photos as $key => $positions ) {
		foreach ( $positions as $i => $data ) {
			$photos[ $key ][ $i ]['date']         = trim( $_POST['photos'][ $key ][ $i ]['date'] );
			$photos[ $key ][ $i ]['teachers-say'] = trim( $_POST['photos'][ $key ][ $i ]['teachers-say'] );
			$photos[ $key ][ $i ]['family-say']   = trim( $_POST['photos'][ $key ][ $i ]['family-say'] );
		}
	}

	// We need to delete some existed photos?
	if ( count( $_POST['delete_photos'] ) ) {
		$tmp = array();
		foreach ( $photos as $key => $positions ) {
			foreach ( $positions as $i => $data ) {
				$photo_id = $data['photo'];

				if ( ! in_array( $photo_id, $_POST['delete_photos'] ) ) {
					// Leave photo
					$tmp[ $key ][] = $data;
				} else {
					// Delete photo
					SR_delete_photo( $post_id, $photo_id );
				}
			}
		}
		$photos = $tmp;
	}

	//Sort the photos according to the date
	foreach ( $photos as $key => &$positions ) {
		mergesort( $positions, function ( $first, $second ) {
			$firstDate  = new DateTime( $first['date'] );
			$secondDate = new DateTime( $second['date'] );

			if ( $firstDate < $secondDate ) {
				return - 1;
			} else if ( $firstDate == $secondDate ) {
				return 0;
			} else {
				return 1;
			}
		} );
	}


	// Update post meta
	$sr_data['photos'] = $photos;

	update_post_meta( $post_id, 'sr_student_id', $student_id );
	update_post_meta( $post_id, 'sr_centre', $student_meta['sr_centre'][0] );
	update_post_meta( $post_id, 'sr_data', $sr_data );


	// Send notify email
	if ( $_POST['send_notify'] ) {
		$SR_vars                    = array();
		$SR_vars['{post_id}']       = $post_id;
		$SR_vars['{parent_first}']  = $parent_meta['first_name'][0];
		$SR_vars['{parent_last}']   = $parent_meta['last_name'][0];
		$SR_vars['{parent_email}']  = $wpdb->get_var( "SELECT `user_email` FROM `{$wpdb->users}` WHERE `ID` = '{$student_meta['sr_parent_id'][0]}'" );
		$SR_vars['{student_first}'] = $student_meta['first_name'][0];
		$SR_vars['{student_last}']  = $student_meta['last_name'][0];
		$SR_vars['{report_url}']    = get_permalink( $post_id );
		$SR_vars['{report_date}']   = $wpdb->get_var( "SELECT `post_modified` FROM `{$wpdb->posts}` WHERE `ID` = '{$post_id}'" );
		$SR_vars['{report_date}']   = date( 'd.m.Y', strtotime( $SR_vars['{report_date}'] ) );
		$SR_vars['{footer}']        = $wpdb->get_var( "SELECT `footer_html` FROM `{$wpdb->prefix}hope_centres` WHERE `id` = '{$student_meta['sr_centre'][0]}'" );

		$SR_vars = array_map( 'esc_html', $SR_vars );

		SR_new_report_notify( $SR_vars );
	}
}

add_action( 'save_post', 'SR_save_post', 10, 3 );


function SR_new_report_notify( $SR_vars ) {
	global $wpdb;


	// Check email
	if ( ! $SR_vars['{parent_email}'] ) {
		return;
	}


	// Set email title
	$title = 'New report';


	// Load email body template
	$body      = '';
	$html      = file_get_contents( get_bloginfo( 'url' ) . '/parent-report-email-template-dont-delete/' );
	$start_pos = strpos( $html, '<div class="entry-content">' );
	$end_pos   = strpos( $html, '</div><!-- .entry-content -->' );

	if ( $start_pos && $end_pos ) {
		$start_pos += 27;
		$length    = $end_pos - $start_pos;

		$body = substr( $html, $start_pos, $length );
	}

	if ( ! $body ) {
		return;
	}


	// Replace placeholders
	$body  = str_replace( array_keys( $SR_vars ), array_values( $SR_vars ), $body );
	$title = str_replace( array_keys( $SR_vars ), array_values( $SR_vars ), $title );

	// Send email
	$status_message = '';
	//if (wp_mail($SR_vars['{parent_email}'], $title, $body)){ // TODO
	if ( wp_mail( 'adam@eatyourveggies.com.au', $title, $body ) ) {
		$status_message = 'Notify email has already been sent';
	} else {
		$status_message = 'Error. Try again';
	}

	// Save status massage
	update_post_meta( $SR_vars['{post_id}'], 'sr_notify_msg', $status_message );
}


// Don't show Reports post content (just in case)
function SR_content_filter( $content ) {
	global $post, $plugin_post_types;


	if ( in_array( $post->post_type, $plugin_post_types ) ) {
		$content = '';
	}


	return $content;
}

add_filter( 'the_content', 'SR_content_filter' );


// Apply special template
function SR_special_post_template( $template ) {
	global $post, $wpdb, $sr_upload_dir, $sr_centres, $local_testing, $theme;

	if ( $post->post_type === 'reports' ) {
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
				$sr_data    = unserialize( $sr_data );
				$student_id = $postmeta['sr_student_id'][0];
			} else {
				$sr_data    = array();
				$student_id = 0;
			}

			if ( $student_id < 1 ) {
				die( "Can't find student ID for this report" );
			}

			// Get student info
			$student = get_user_meta( $student_id );

			if ( ! $student['nickname'][0] ) {
				die( "Can't find student for this report" );
			}

			if ( $local_load || current_user_can( 'manage_options' ) ) {
				// Allow for Admin
				$allowed = true;
			} else {
				$user = wp_get_current_user();
				// Check that user is student parent
				if ( $user->ID == $student['sr_parent_id'][0] ) {
					$allowed = true;
				} else {
					// Check if that user has "Report Editor" role and from this centre
					if ( in_array( 'report_editor', $user->roles ) ) {
						$editor_centre  = get_user_meta( $user->ID, 'sr_centre', true );
						$student_centre = $student['sr_centre'][0];

						if ( $editor_centre && $editor_centre === $student_centre ) {
							$allowed = true;
						}
					}
				}
			}
		}


        // PDF export support
		if ( isset( $_REQUEST['export'] ) && $_REQUEST['export'] == 'pdf' ) {

			$selections = array();

			generate_pdf( $selections, $sr_upload_dir, $local_testing, false );

		}

		// Prepare template data
		global $SR_vars;

		$SR_vars                     = array();
		$SR_vars['post_id']          = $post_id;
		$SR_vars['student']          = $student;
		$SR_vars['parent']           = get_userdata( $student['sr_parent_id'][0] );
		$SR_vars['sr_data']          = $sr_data;
		$SR_vars['centre_title']     = $sr_centres[ $postmeta['sr_centre'][0] ]->title;
		$SR_vars['date']             = date( 'j F Y', strtotime( $post->post_modified ) );
		$SR_vars['plugin_url']       = plugins_url( '', __FILE__ );
		$SR_vars['plugin_theme_url'] = $SR_vars['plugin_url'] . '/templates/' . $theme;

		// Get all students and reports of this parent
		$SR_vars['all_students'] = $wpdb->get_col( "SELECT `user_id` FROM `{$wpdb->usermeta}` WHERE `meta_key`='sr_parent_id' AND `meta_value`='{$student['sr_parent_id'][0]}' ORDER BY `user_id`" );
		$SR_vars['local_load']   = $local_load;


		// Template file
		$template = plugin_dir_path( __FILE__ ) . 'templates/' . $theme . '/single-reports.php';
		if ( ! file_exists( $template ) ) {
			die( "Can't find template file for Student Report!" );
		}
	}

	return $template;
}

add_filter( 'template_include', 'SR_special_post_template' );


// Remove all images when report removed
function SR_delete_post( $post_id ) {
	if ( get_post_type( $post_id ) == 'reports' ) {
		SR_delete_photo( $post_id );
	}
}

add_action( 'delete_post', 'SR_delete_post' );


// Delete report photos
function SR_delete_photo( $post_id, $photo_id = '' ) {
	global $sr_upload_dir;


	$sr_upload_dir .= '/' . $post_id;

	foreach ( glob( "{$sr_upload_dir}/{$photo_id}*" ) as $file ) {
		@unlink( $file );
	}

	@rmdir( $sr_upload_dir );
}


// Disable Admin Bar
function remove_admin_bar() {
	if ( ! current_user_can( 'administrator' ) && ! is_admin() ) {
		show_admin_bar( false );
	}
}

add_action( 'after_setup_theme', 'remove_admin_bar' );


// Dashboard shortcode
function SR_dashboardShortcode() {
	global $wpdb;


	// Show login form if needed
	if ( ! is_user_logged_in() ) {
		$args = array(
			'echo'           => true,
			'remember'       => true,
			'label_username' => 'E-mail',
			'redirect'       => get_bloginfo( 'url' ) . '/dashboard/'
		);

		echo "<h1>Dashboard</h1>";

		wp_login_form( $args );

		// Account links
		?>
        <br/>
        <a href="<?php echo wp_lostpassword_url( get_bloginfo( 'url' ) . '/dashboard/' ); ?>" title="Lost Password?">Lost
            Password?</a>
        <br/>
        <a href="/contact/" title="Change Email?">Change Email?</a>
		<?php

		return;
	}


	// Get latest report for this user
	$post_id            = 0;
	$additional_message = '';

	if ( current_user_can( 'edit_reports' ) ) {
		// Show empty dashboard for admins and editors
		$post_id            = 0;
		$additional_message = "Please use <a href=\"/wp-admin/edit.php?post_type=reports\">Admin page</a> to manage reports.\n";
	} else {
		// Get user students
		$parent_id = get_current_user_id();
		if ( ! $parent_id ) {
			return;
		}

		$students = $wpdb->get_col( "SELECT `user_id` FROM `{$wpdb->usermeta}` WHERE `meta_key`='sr_parent_id' AND `meta_value`='{$parent_id}' ORDER BY `user_id`" );

		if ( count( $students ) ) {
			// Get most fresh report for user's students
			$posts = array();
			foreach ( $students as $student_id ) {
				$posts [] = (int) $wpdb->get_var( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `ID` IN (SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key`='sr_student_id' AND `meta_value`='{$student_id}') AND `post_type` = 'reports' ORDER BY `post_date` DESC LIMIT 1" );
			}

			if ( count( $posts ) ) {
				$post_id = max( $posts );
			}
		}
	}


	if ( $post_id < 1 ) {
		echo "<h1>Dashboard</h1>\n";


		$body      = '';
		$html      = file_get_contents( get_bloginfo( 'url' ) . '/parent-reports-text-before-report-dont-change-title-dont-delete/' );
		$start_pos = strpos( $html, '<div class="entry-content">' );
		$end_pos   = strpos( $html, '</div><!-- .entry-content -->' );

		if ( $start_pos && $end_pos ) {
			$start_pos += 27;
			$length    = $end_pos - $start_pos;

			$body = substr( $html, $start_pos, $length );
		}

		if ( $body ) {
			?>
            <section class="intro-section">
                <div class="container clearfix">
					<?php echo $body; ?>
                </div>
            </section>
			<?php
		}


		if ( $additional_message ) {
			echo $additional_message;
		} else {
			echo "No new reports for you at this moment.\n";
		}

		// Account links
		?>
        <br/><br/>
        <a href="<?php echo wp_logout_url( get_bloginfo( 'url' ) . '/dashboard/' ); ?>">[ Logout ]</a>
		<?php

		return;
	}


	// Redirect to this post
	$link = get_post_permalink( $post_id );

	if ( $link ) {
		?>
        <script type="text/javascript">
            window.location = '<?php echo $link; ?>';
        </script>
		<?php
	}
}

add_shortcode( 'sr-dashboard', 'SR_dashboardShortcode' );


// Add extra fields to user profile
function SR_custom_profile( $user ) {
	global $sr_centres, $wpdb;


	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	};


	// Show only for specified roles
	$allowed_roles = array( 'subscriber', 'student', 'report_editor' );
	$allowed       = false;

	foreach ( $user->roles as $role ) {
		if ( in_array( $role, $allowed_roles ) ) {
			$allowed = true;
			break;
		}
	}

	if ( ! $allowed ) {
		return;
	}


	?>
    <h3>Additional info</h3>
    <table class="form-table">
		<?php
		// Add Mailling Lists
		if ( ! in_array( 'student', $user->roles ) ) {
			?>
            <tr>
                <th><label for="mailling-lists" class="mailling-lists-label">Mailling Lists</label></th>
                <td>
					<?php
					$mp_user_id = (int) $wpdb->get_var( "SELECT `user_id` FROM `{$wpdb->prefix}wysija_user` WHERE `wpuser_id`='{$user->ID}'" );

					// Load users lists
					$user_lists = array();
					if ( $mp_user_id ) {
						$user_lists = $wpdb->get_col( "SELECT `list_id` FROM `{$wpdb->prefix}wysija_user_list` WHERE `user_id`='{$mp_user_id}'" );
					}

					// Get all lists
					$results = $wpdb->get_results( "SELECT `list_id`, `name` FROM `{$wpdb->prefix}wysija_list`" );
					if ( count( $results ) ) {
						foreach ( $results as $l ) {
							$attr = in_array( $l->list_id, $user_lists ) ? ' checked="checked"' : '';
							$id   = "mailling-list-{$l->list_id}";

							echo "<input name=\"mailling_lists[]\" type=\"checkbox\" value=\"{$l->list_id}\" id=\"{$id}\"{$attr} />";
							echo ' ' . "<label for=\"{$id}\">" . esc_html( $l->name ) . "</label>";
							echo "<br />\n";
						}
					}
					?>
                </td>
            </tr>
		<?php } ?>

		<?php
		// Add parent selection for "Student" role
		if ( in_array( 'student', $user->roles ) ) {
			$parent_id = get_user_meta( $user->ID, 'sr_parent_id', true );

			// Get all parents
			$parents       = get_users( 'orderby=meta_value&meta_key=first_name&role=subscriber' );
			$parentEditors = get_users( 'orderby=meta_value&meta_key=first_name&role=report_editor' );

			$parents = array_merge( $parents, $parentEditors );
			?>
            <tr>
                <th><label for="report-parent" class="parent-label">Parent</label></th>
                <td>
                    <select data-placeholder="Choose a parent..." style="width: 350px;" class="chosen-select"
                            name="parent_id" id="parent_id">
                        <option></option>
						<?php
						foreach ( $parents as $parent ) {
							$parent_meta = get_user_meta( $parent->ID );
							$parent_name = esc_html( implode( ' ', array(
								$parent_meta['first_name'][0],
								$parent_meta['last_name'][0]
							) ) );

							$attr = ( $parent->ID == $parent_id ) ? ' selected="selected"' : '';
							echo "<option value=\"{$parent->ID}\"{$attr}>{$parent_name}</option>\n";
						}
						?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="report-inactive">Inactive?</label></th>
                <td><input type="checkbox" name="report-inactive" id="report-inactive" <?php
					$isInactive = get_user_meta( $user->ID, "report-inactive", true );
					if ( $isInactive ) {
						echo "checked";
					}
					?>/></td>
            </tr>
		<?php } ?>
        <tr>
            <th><label for="SR_centre">Centre</label></th>
            <td>
                <select id="SR_centre" name="sr_centre" style="width: 350px;">
                    <option value="">Select...</option>
					<?php
					$sr_centre = get_user_meta( $user->ID, 'sr_centre', true );
					populateCentresForSelection( $sr_centres, $sr_centre );
					?>
                </select>
            </td>
        </tr>
		<?php
		// Children
		if ( in_array( 'subscriber', $user->roles ) || in_array( 'report_editor', $user->roles ) ) {
			$students = $wpdb->get_col( "SELECT `user_id` FROM `{$wpdb->usermeta}` WHERE `meta_key`='sr_parent_id' AND `meta_value`='{$user->ID}' ORDER BY `user_id`" );

			// Show current children
			?>
            <tr>
                <th><label for="report-children" class="children-label">Children</label></th>
                <td>
					<?php
					if ( count( $students ) ) {
						foreach ( $students as $student_id ) {
							$student_meta = get_user_meta( $student_id );
							$name         = esc_html( implode( ' ', array(
								$student_meta['first_name'][0],
								$student_meta['last_name'][0]
							) ) );
							$name         = trim( $name ) ? trim( $name ) : 'noname';
							$centre_name  = esc_html( $sr_centres[ $student_meta['sr_centre'][0] ]->title );
							$delete_link  = "<a href=\"" . wp_nonce_url( "users.php?action=delete&amp;user={$student_id}", 'bulk-users' ) . "\" title=\"Delete user\" style=\"color: red\" target=\"_blank\">[X]</a>";

							echo "{$delete_link} <a href=\"" . admin_url() . "user-edit.php?user_id={$student_id}\" target=\"_blank\">{$name}</a> <i>{$centre_name}</i><br />\n";
						}
					}
					?>
                </td>
            </tr>
            <tr>
                <th><label for="report-children2" class="children-label">Add children</label></th>
                <td>
                    <input name="add_children" type="text" size="30" placeholder="Firstname Lastname"/>
                </td>
            </tr>
		<?php } ?>
    </table>

      <table>
    	<?php
		if (is_admin()) {
				$profile_status = get_user_meta( $user->ID,'teacher_status' , true); 
				
			?>
			<h2>Teacher Profiles</h2>
			<tr>
	                <th><label for="report-children2" class="children-label">Working Status of Teacher :</label></th>
	                <td>
	                    <input name="teacher_status" type="checkbox" value="1" <?php if($profile_status == '1') { echo "checked"; } ?> />
	                </td>
	        </tr>
	    
			<?php
		}

	?>
    </table>

    <script type="text/javascript">
        jQuery(document).ready(function () {
            // Hide some Roles from list
            //jQuery("#role option[value='student']").remove();
            jQuery("#role option[value='contributor']").remove();
            jQuery("#role option[value='author']").remove();
            jQuery("#role option[value='editor']").remove();
            jQuery("#role option[value='']").remove();


            jQuery('form#your-profile').submit(function (event) {
                var validation = true;
                var failed_msg = '';


                if (jQuery('#SR_centre').val() == '') {
                    validation = false;
                    failed_msg = 'Please select the Centre!';
                }

                if (jQuery('#parent_id').length && !jQuery('#parent_id').val()) {
                    validation = false;
                    failed_msg = 'Please select the Parent!';
                }


                if (!validation) {
                    event.preventDefault();

                    alert(failed_msg);
                }
            });
        });
    </script>
	<?php
}

add_action( 'edit_user_profile', 'SR_custom_profile' );


function SR_save_profile( $user_id ) {
	global $sr_centres, $wpdb;


	if ( current_user_can( 'edit_user', $user_id ) ) {
		$user = new WP_User( $user_id );

		// Update Centre
		$sr_centre = $_POST['sr_centre'];

		if ( $sr_centre && in_array( $sr_centre, array_keys( $sr_centres ) ) ) {
			update_user_meta( $user_id, 'sr_centre', $sr_centre );
			/*
			// If user have a "student" role, update also "centre" value in all his reports
			if (in_array('student', $user->roles)){
				$ids = $wpdb->get_col("SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key`='sr_student_id' AND `meta_value`='{$user_id}'");

				$sr_centre = esc_sql($sr_centre);
				foreach ($ids as $post_id){
					$wpdb->query("UPDATE `{$wpdb->postmeta}` SET `meta_value`='{$sr_centre}' WHERE `post_id`='{$post_id}' AND `meta_key`='sr_centre'");
				}
			}
			*/
		}


		// Update Parent
		if ( in_array( 'student', $user->roles ) ) {
			$parent_id = (int) $_POST['parent_id'];
			if ( $parent_id ) {
				update_user_meta( $user_id, 'sr_parent_id', $parent_id );
			}

			//Update inactive status
			$prevState = get_user_meta( $user_id, "report-inactive", true );
			if ( isset( $_POST['report-inactive'] ) ) {
				if ( $_POST['report-inactive'] == "on" ) {
					$newState = true;
				}
			} else {
				$newState = false;
			}

			$newStatesMapping = array(
				true  => "inactive",
				false => "publish"
			);

			if ( $prevState != $newState ) {
				//Set the new state
				update_user_meta( $user_id, "report-inactive", $newState );

				//Set all the reports as "Inactive" or "Published"
				$wpdb->query( "UPDATE {$wpdb->posts} LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = {$wpdb->posts}.ID AND pm.meta_key = 'sr_student_id' SET {$wpdb->posts}.post_status = '{$newStatesMapping[$newState]}' WHERE pm.meta_value = '{$user_id}'" );
			}
		}


		// Update Mailling Lists
		if ( ! in_array( 'student', $user->roles ) ) {
			$mp_user_id = (int) $wpdb->get_var( "SELECT `user_id` FROM `{$wpdb->prefix}wysija_user` WHERE `wpuser_id`='{$user_id}'" );
			if ( $mp_user_id ) {
				// Unlink from old Lists
				$wpdb->query( "DELETE FROM `{$wpdb->prefix}wysija_user_list` WHERE `user_id`='{$mp_user_id}'" );

				if ( isset( $_POST['mailling_lists'] ) ) {
					$t = time();
					foreach ( $_POST['mailling_lists'] as $list_id ) {
						$wpdb->query( "INSERT INTO `{$wpdb->prefix}wysija_user_list` VALUES('{$list_id}', '{$mp_user_id}', '{$t}', '0')" );
					}
				}
			}
		}


		// Add children
		if ( isset( $_POST['add_children'] ) ) {
			list( $first, $last ) = explode( ' ', $_POST['add_children'] );
			$first = trim( $first );
			$last  = trim( $last );

			if ( $first ) {
				$user_email      = 'student-' . wp_generate_password( 8, false ) . '@example.com';
				$random_password = wp_generate_password( 8, false );

				$child_id = wp_create_user( $user_email, $random_password, $user_email );

				if ( $child_id > 0 ) {
					$userdata = array(
						'ID'         => $child_id,
						'first_name' => $first,
						'last_name'  => $last,
						'role'       => 'student'
					);

					wp_update_user( $userdata );

					// Custom fields
					update_user_meta( $child_id, 'sr_is_child', 1 );
					update_user_meta( $child_id, 'sr_parent_id', $user_id );
					update_user_meta( $child_id, 'sr_centre', $sr_centre );
				}
			}
		}
	}

	//staff updation
		if (is_admin()) {
		$profile_status = get_user_meta( $user_id,'teacher_status');


		if (is_array($profile_status) && !empty($profile_status)) {

			

			update_user_meta( $user_id, 'teacher_status',$_REQUEST['teacher_status']);
			
		} elseif (isset($_REQUEST['teacher_status'])) {
			
			
				add_user_meta( $user_id, 'teacher_status',$_REQUEST['teacher_status']);
			}
		
	}
}

add_action( 'edit_user_profile_update', 'SR_save_profile' );


// Add new role for Report Editor
function SR_add_roles_on_plugin_activation() {
	add_role( 'report_editor', 'Report Editor' );


	// Add access to reports to another roles
	$roles = array( 'report_editor', 'editor', 'administrator' );

	foreach ( $roles as $the_role ) {
		$role = get_role( $the_role );

		$role->add_cap( 'read' );
		$role->add_cap( 'publish_reports' );
		$role->add_cap( 'edit_reports' );
		$role->add_cap( 'edit_others_reports' );
		$role->add_cap( 'delete_reports' );
		$role->add_cap( 'delete_others_reports' );
		$role->add_cap( 'read_private_reports' );
		$role->add_cap( 'read_reports' );
	}
}

register_activation_hook( __FILE__, 'SR_add_roles_on_plugin_activation' );


// Hide some admin menus for Report Editor role
function SR_remove_admin_menu() {
	if ( in_array( 'report_editor', wp_get_current_user()->roles ) ) {
		remove_menu_page( 'index.php' );
		remove_menu_page( 'vc-welcome' );
	}
}

add_action( 'admin_init', 'SR_remove_admin_menu' );


function SR_posts_filter( $query ) {
	global $plugin_post_types;
	if ( is_admin() && in_array( $query->query['post_type'], $plugin_post_types )
	) {

		$user = wp_get_current_user();
		//Get all inactive students
		$inactiveStudents = get_users( array(
			"role"       => "student",
			"meta_key"   => "report-inactive",
			"meta_value" => true,
			"fields"     => "ID"
		) );


		$qv               = &$query->query_vars;
		$qv['meta_query'] = array();

		if ( in_array( 'report_editor', $user->roles ) ) {
			$qv['meta_query'][] = array(
				'key'     => 'sr_centre',
				'value'   => get_user_meta( $user->ID, 'sr_centre', true ),
				'compare' => '=',
				'type'    => 'CHAR'
			);
		}

		/*$inactiveCompareMethod = "NOT IN";

		if (isset($_REQUEST['inactive_only'])) {
			$inactiveCompareMethod = "IN";
		}

			$qv['meta_query'][] = array(
				'key' => 'sr_student_id',
				'value' => $inactiveStudents,
				'compare' => $inactiveCompareMethod,
				'type' => 'UNSIGNED'
			);

			if (isset($_REQUEST['mmx_debug']))
				die(print_r($qv, true));*/

		/**
		 * @var WP_Query $query
		 */
	}
}

add_filter( 'parse_query', 'SR_posts_filter' );

function SR_register_inactive_post_status() {
	register_post_status( 'inactive', array(
		'label'                     => "Inactive",
		'public'                    => false,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => false,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>' ),
	) );
}

add_action( 'init', 'SR_register_inactive_post_status' );


/*function SR_posts_join($join)
{
	global $wp_query, $wpdb;

	if(is_admin() && in_array($wp_query->query['post_type'], array('reports', 'portfolios'))) {
		$join .= " INNER JOIN {$wpdb->postmeta} AS pm ON pm.post_id = {$wpdb->posts}.ID AND pm.meta_key = 'sr_student_id'";
		$join .= " LEFT JOIN {$wpdb->usermeta} AS um ON um.user_id = pm.meta_value AND um.meta_key = 'report-inactive'";
	}

	return $join;
}
add_filter('posts_join', 'SR_posts_join');

function SR_posts_where( $where, &$wp_query )
{
	global $wpdb;

	if(is_admin() && in_array($wp_query->query['post_type'], array('reports', 'portfolios'))) {
		$where .= " AND COALESCE(um.meta_value, 'false') = 'false'";
	}

	return $where;
}

add_filter( 'posts_where', 'SR_posts_where', 10, 2 );*/


// Add colums to the users list
function SR_new_modify_user_table( $column ) {
	$column['centre']         = 'Centre';
	$column['parent']         = 'Parent';
	$column['mailling_lists'] = 'Mailling Lists';

	return $column;
}

add_filter( 'manage_users_columns', 'SR_new_modify_user_table' );


function SR_new_modify_user_table_row( $val, $column_name, $user_id ) {
	global $sr_centres, $wpdb;


	switch ( $column_name ) {
		case 'centre':
			$centre = get_user_meta( $user_id, 'sr_centre', true );

			if ( $centre ) {
				$centre = esc_html( $sr_centres[ $centre ]->title );
			}

			return $centre;
			break;

		case 'parent':
			$parent = '';

			$parent_id = get_user_meta( $user_id, 'sr_parent_id', true );
			if ( $parent_id ) {
				$user = get_userdata( $parent_id );
				if ( $user->ID ) {
					$parent = esc_html( $user->first_name . ' ' . $user->last_name );
				}
			}

			return $parent;
			break;

		case 'mailling_lists':
			$mailling_lists = '';

			$results = $wpdb->get_col( "SELECT B.name FROM `{$wpdb->prefix}wysija_user_list` as A LEFT JOIN `{$wpdb->prefix}wysija_list` as B on A.list_id=B.list_id WHERE A.user_id=(SELECT `user_id` FROM `{$wpdb->prefix}wysija_user` WHERE `wpuser_id`='{$user_id}')" );

			if ( is_array( $results ) ) {
				$mailling_lists = implode( ', ', $results );
				$mailling_lists = esc_html( $mailling_lists );
			}

			return $mailling_lists;
			break;

		default:
			break;
	}
}

add_filter( 'manage_users_custom_column', 'SR_new_modify_user_table_row', 10, 3 );


// Modify "Add new user" page in backend
function SR_user_new_form( $current ) {
	global $wpdb, $sr_centres;


	?>
    <h3>Additional info </h3>
    <table class="form-table">
        <tr>
            <th><label for="mailling-lists" class="mailling-lists-label">Mailling Lists <span class="description">(required)</span></label>
            </th>
            <td scope="row">
				<?php
				// Get all lists
				$results = $wpdb->get_results( "SELECT `list_id`, `name` FROM `{$wpdb->prefix}wysija_list`" );
				if ( count( $results ) ) {
					foreach ( $results as $l ) {
						$id = "mailling-list-{$l->list_id}";

						echo "<input name=\"mailling_lists[]\" type=\"checkbox\" value=\"{$l->list_id}\" id=\"{$id}\" />";
						echo ' ' . "<label for=\"{$id}\">" . esc_html( $l->name ) . "</label>";
						echo "<br />\n";
					}
				}
				?>
            </td>
        </tr>
        <tr class="form-field">
            <th><label for="SR_centre">Centre <span class="description">(required)</span></label></th>
            <td>
                <select id="SR_centre" name="sr_centre">
                    <option value="">Select...</option>
					<?php
					foreach ( $sr_centres as $k => $v ) {
						$k = esc_attr( $k );
						$v = esc_html( $v->title );

						echo "<option value=\"{$k}\">{$v}</option>\n";
					}
					?>
                </select>
            </td>
        </tr>
    </table>


    <script type="text/javascript">
        jQuery(document).ready(function () {
            // Uncheck "send notify"
            //jQuery('#send_user_notification').click();

            // Hide some Roles from list
            jQuery("#role option[value='student']").remove();
            jQuery("#role option[value='contributor']").remove();
            jQuery("#role option[value='author']").remove();
            jQuery("#role option[value='editor']").remove();

            // Validate fields
            jQuery('form#createuser').submit(function (event) {
                var validation = true;
                var failed_msg = '';


                if (jQuery('#SR_centre').val() == '') {
                    validation = false;
                    failed_msg = 'Please select the Centre!';
                }

                if (jQuery("[name='mailling_lists[]']:checked").length < 1) {
                    validation = false;
                    failed_msg = 'Please select the Mailling List!';
                }


                if (!validation) {
                    event.preventDefault();

                    alert(failed_msg);
                }
            });
        });
    </script>
	<?php
}

if ( is_admin() ) {
	add_action( 'user_new_form', 'SR_user_new_form' );
}


// Save custom fields when new user added
function SR_edit_user_created_user( $user_id, $notify ) {
	global $wpdb, $sr_centres;


	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( $user_id < 0 ) {
		return;
	}


	$user = new WP_User( $user_id );

	// Update Centre
	$sr_centre = $_POST['sr_centre'];

	if ( $sr_centre && in_array( $sr_centre, array_keys( $sr_centres ) ) ) {
		update_user_meta( $user_id, 'sr_centre', $sr_centre );
	}


	// Update Mailling Lists
	if ( ! in_array( 'student', $user->roles ) ) {
		$mp_user_id = (int) $wpdb->get_var( "SELECT `user_id` FROM `{$wpdb->prefix}wysija_user` WHERE `wpuser_id`='{$user_id}'" );
		if ( $mp_user_id ) {
			if ( isset( $_POST['mailling_lists'] ) ) {
				$t = time();
				foreach ( $_POST['mailling_lists'] as $list_id ) {
					$wpdb->query( "INSERT INTO `{$wpdb->prefix}wysija_user_list` VALUES('{$list_id}', '{$mp_user_id}', '{$t}', '0')" );
				}
			}
		}
	}

	wp_redirect( admin_url() . "user-edit.php?user_id={$user_id}" );
	die();
}

add_action( 'edit_user_created_user', 'SR_edit_user_created_user', 99, 2 );


// Modify WP login page
function SR_login_enqueue_scripts() {
	?>
    <style type="text/css">
        #login h1 a {
            background: url('/wp-content/uploads/2016/03/hopeelc-logo.png') no-repeat;
            background-size: contain;
            width: 320px;
            height: 125px;
        }

        .login {
            background: #fff;
        }

        #loginform {
            border: 1px solid #ddd;
        }
    </style>

    <script type="text/javascript">
        window.onload = function () {
            var elems = document.querySelectorAll('#login h1 a');
            for (var i = 0; i < elems.length; i++) {
                elems[i].href = '/';
            }

            var elems = document.querySelectorAll('#login_error');
            for (var i = 0; i < elems.length; i++) {
                elems[i].innerHTML = elems[i].innerHTML.replace('username', 'E-mail');
            }
        }
    </script>
	<?php
}

add_action( 'login_enqueue_scripts', 'SR_login_enqueue_scripts', 999 );


// Modify WP login page
function SR_login_headertitle() {
	return '';
}

add_filter( 'login_headertitle', 'SR_login_headertitle' );


// Modify WP login page
function SR_login_head() {
	function username_change( $translated_text, $text, $domain ) {
		if ( $text === 'Username' || $text === 'username' ) {
			$translated_text = 'E-mail';
		}

		return $translated_text;
	}

	add_filter( 'gettext', 'username_change', 20, 3 );
}

add_action( 'login_head', 'SR_login_head' );


// Change all plain text emails body to HTML
function SR_wp_mail( $mail ) {
	if (
		stripos( $mail['message'], '<br' ) === false
		&&
		stripos( $mail['message'], '<p' ) === false
		&&
		stripos( $mail['message'], '<div' ) === false
	) {
		$mail['message'] = nl2br( $mail['message'] );
	}

	return $mail;
}

add_filter( 'wp_mail', 'SR_wp_mail' );


// Redefine user notification function
if ( ! function_exists( 'wp_new_user_notification' ) ) {
	function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' ) {
		global $wpdb, $wp_hasher;

		if ( $deprecated !== null ) {
			_deprecated_argument( __FUNCTION__, '4.3.1' );
		}

		$user = get_userdata( $user_id );

		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$message = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
		$message .= sprintf( __( 'E-mail: %s' ), $user->user_email ) . "\r\n";

		@wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration' ), $blogname ), $message );

		if ( 'admin' === $notify || empty( $notify ) ) {
			return;
		}

		// Generate something random for a password reset key.
		$key = wp_generate_password( 20, false );

		/** This action is documented in wp-login.php */
		do_action( 'retrieve_password_key', $user->user_login, $key );

		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}
		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );


		// Load email body template
		$body      = '';
		$html      = file_get_contents( get_bloginfo( 'url' ) . '/new-user-email-template-dont-change-title-dont-delete/' );
		$start_pos = strpos( $html, '<div class="entry-content">' );
		$end_pos   = strpos( $html, '</div><!-- .entry-content -->' );

		if ( $start_pos && $end_pos ) {
			$start_pos += 27;
			$length    = $end_pos - $start_pos;

			$body = substr( $html, $start_pos, $length );
		}

		if ( ! $body ) {
			return;
		}


		// Replace placeholders
		$repl = array(
			'{username}'   => $user->user_login,
			'{reset_link}' => network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' )
		);

		$message = str_replace( array_keys( $repl ), array_values( $repl ), $body );

		wp_mail( $user->user_email, sprintf( __( '[%s] Your access info' ), $blogname ), $message );
	}
}


// Add to WP ability login using E-mail also
function SR_email_login_authenticate( $user, $username, $password ) {
	if ( is_a( $user, 'WP_User' ) ) {
		return $user;
	}

	if ( ! empty( $username ) ) {
		$username = str_replace( '&', '&amp;', stripslashes( $username ) );
		$user     = get_user_by( 'email', $username );

		if ( isset( $user, $user->user_login, $user->user_status ) && 0 == (int) $user->user_status ) {
			$username = $user->user_login;
		}
	}

	return wp_authenticate_username_password( null, $username, $password );
}

remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
add_filter( 'authenticate', 'SR_email_login_authenticate', 20, 3 );


// Redirect Report Editors to the Reports page after login in admin area
function SR_wp_login( $username, $user ) {
	if ( in_array( 'report_editor', $user->roles ) ) {
		wp_redirect( admin_url( 'edit.php?post_type=reports' ) );
		die();
	}
}

add_action( 'wp_login', 'SR_wp_login', 10, 2 );

//Merge "stable" sort
function mergesort( &$array, $cmp_function = 'strcmp' ) {
	// Arrays of size < 2 require no action.
	if ( count( $array ) < 2 ) {
		return;
	}
	// Split the array in half
	$halfway = count( $array ) / 2;
	$array1  = array_slice( $array, 0, $halfway );
	$array2  = array_slice( $array, $halfway );
	// Recurse to sort the two halves
	mergesort( $array1, $cmp_function );
	mergesort( $array2, $cmp_function );
	// If all of $array1 is <= all of $array2, just append them.
	if ( call_user_func( $cmp_function, end( $array1 ), $array2[0] ) < 1 ) {
		$array = array_merge( $array1, $array2 );

		return;
	}
	// Merge the two sorted arrays into a single sorted array
	$array = array();
	$ptr1  = $ptr2 = 0;
	while ( $ptr1 < count( $array1 ) && $ptr2 < count( $array2 ) ) {
		if ( call_user_func( $cmp_function, $array1[ $ptr1 ], $array2[ $ptr2 ] ) < 1 ) {
			$array[] = $array1[ $ptr1 ++ ];
		} else {
			$array[] = $array2[ $ptr2 ++ ];
		}
	}
	// Merge the remainder
	while ( $ptr1 < count( $array1 ) ) {
		$array[] = $array1[ $ptr1 ++ ];
	}
	while ( $ptr2 < count( $array2 ) ) {
		$array[] = $array2[ $ptr2 ++ ];
	}

	return;
}

add_action( 'wp_ajax_save_report_images', 'save_report_images' );
function save_report_images() {
	global $sr_upload_dir;

	$post_id = $_POST['post_id'];

	$path = $sr_upload_dir . '/' . $post_id;
	@mkdir( $path, 0777, true );

	// Load Report data
	$postmeta = get_post_meta( $post_id );
	$sr_data  = $postmeta['sr_data'][0];
	if ( $sr_data ) {
		$sr_data = unserialize( $sr_data );
	} else {
		$sr_data = [];
	}

	if ( get_post_status( $post_id ) == 'auto-draft' ) {
		wp_update_post( [
			'ID'          => $post_id,
			'post_status' => 'draft',
		] );
	}

	$photos = isset( $sr_data['photos'] ) ? $sr_data['photos'] : array();

	foreach ( $_FILES['photos']['tmp_name'] as $key => $keyPhotos ) {
		foreach ( $keyPhotos as $i => $photo ) {
			$info = getimagesize( $photo['photo'] );
			if ( $info === false ) {
				continue;
			}

			if ( ( $info[2] !== IMAGETYPE_GIF ) && ( $info[2] !== IMAGETYPE_JPEG ) && ( $info[2] !== IMAGETYPE_PNG ) ) {
				continue;
			}

			$id        = md5( time() . wp_generate_password( 10, false ) );
			$name      = $id . '.jpg';
			$full_name = $path . '/' . $name;

			$photos[ $key ][] = [ 'photo' => $id ];

			move_uploaded_file( $photo['photo'], $full_name );
		}
	}

	$sr_data['photos'] = $photos;

	update_post_meta( $post_id, 'sr_data', $sr_data );

	die;
}
