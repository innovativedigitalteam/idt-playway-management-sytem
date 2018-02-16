<?php
/**
 * Created by PhpStorm.
 * User: madmax
 * Date: 21.06.16
 * Time: 22:57
 */

/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

//Add "Individual Portfolios" post type
function createIndividualPortfoliosPostType() {
	$labels = array(
		'name'               => 'Individual Portfolios',
		'singular_name'      => 'Individual Portfolio',
		'add_new'            => 'Add New Individual Portfolio',
		'add_new_item'       => 'Add New Individual Portfolio',
		'edit'               => 'Edit Individual Portfolio',
		'edit_item'          => 'Edit Individual Portfolio',
		'new_item'           => 'New Individual Portfolio',
		'view'               => 'View Individual Portfolio',
		'view_item'          => 'View Individual Portfolio',
		'search_items'       => 'Search Individual Portfolios',
		'not_found'          => 'Nothing found',
		'not_found_in_trash' => 'Nothing found in Trash',
		'parent_item_colon'  => '',
		'all_items'          => 'All Individual Portfolios',
	);

	$args = array(
		'labels'              => $labels,
		'description'         => 'Placeholder for all the Individual Portfolios',
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'hierarchical'        => false,
		'rewrite'             => array( 'slug' => 'portfolios', 'with_front' => 'true' ),
		'query_var'           => true,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-welcome-widgets-menus',
		'supports'            => array( 'revisions' ),
		'capability_type'     => 'portfolios',
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

	register_post_type( 'portfolios', $args );
	//  flush_rewrite_rules();
}

add_action( 'init', 'createIndividualPortfoliosPostType' );

//-----------------------------
//Add Individual Portfolio form to post edit screen
function individualPortfoliosAddMetaBoxes() {
	add_meta_box(
		'portfolio_custom_metabox',      // $id
		'Individual Portfolio',          // $title
		'portfolioCustomMetabox',       // $callback
		'portfolios',                 // $pageС
		'normal',                  // $context
		'high'                     // $priority
	);
}

add_action( 'add_meta_boxes', 'individualPortfoliosAddMetaBoxes' );

// Add new role for Report Editor
function IP_add_roles_on_plugin_activation() {
	add_role( 'portfolio_editor', 'Portfolio Editor' );


	// Add access to reports to another roles
	$roles = array( 'portfolio_editor', 'editor', 'administrator' );

	foreach ( $roles as $the_role ) {
		$role = get_role( $the_role );

		$role->add_cap( 'read' );
		$role->add_cap( 'publish_portfolios' );
		$role->add_cap( 'edit_portfolios' );
		$role->add_cap( 'edit_others_portfolios' );
		$role->add_cap( 'delete_portfolios' );
		$role->add_cap( 'delete_others_portfolios' );
		$role->add_cap( 'read_private_portfolios' );
		$role->add_cap( 'read_' );
	}
}

register_activation_hook( __FILE__, 'IP_add_roles_on_plugin_activation' );


function portfolioCustomMetabox() {
	global $post, $wpdb, $sr_blocks, $sr_upload_dir, $sr_images_url, $sr_centres;

	wp_nonce_field( basename( __FILE__ ), 'portfolio_nonce' );

	// Load Report data
	$postmeta = get_post_meta( $post->ID );
	//$studentId = $postMeta['sr_student_id'];
	$sr_data = $postmeta['sr_data'][0];

	if ( $sr_data ) {
		$sr_data    = unserialize( $sr_data );
		$student_id = $postmeta['sr_student_id'][0];
	} else {
		$sr_data    = array();
		$student_id = 0;
	}

	$sdt = (object) [
		'id'        => $student_id,
		'name'      => $sr_data['portfolio-name'],
		'birthdate' => $sr_data['portfolio-birthday'],
		'mother'    => $sr_data['portfolio-mother-name'],
		'father'    => $sr_data['portfolio-father-name'],
		'cultural'  => $sr_data['portfolio-cultural-background'],
		'religion'  => $sr_data['portfolio-religion'],
		'medical'   => $sr_data['portfolio-medical'],
		'photo'     => array(
			'id'  => $sr_data['photos']['main'][0]['photo'],
			'url' => $sr_images_url . "?post_id=" . $post_id . "&object=" . $sr_data['photos']['main'][0]['photo']
		)
	];

	//  die();

	$postMeta = get_post_meta( $post->ID, "portfolio", true );

	// Check if that editor can work with this portfolio
	$editor_centre = '';
	$user          = wp_get_current_user();
	if ( in_array( 'report_editor', $user->roles ) ) {
		$editor_centre = get_user_meta( $user->ID, 'sr_centre', true );
		$post_centre   = get_post_meta( $post->ID, 'sr_centre', true );

		if ( $post_centre && $editor_centre !== $post_centre ) {
			wp_redirect( admin_url( 'edit.php?post_type=portfolios' ) );
			die();
		}
	}

	if ( $sdt->photo['id'] != "" ) {
		$img = "<img alt='photo' src='" . $sr_images_url . "?post_id=" . $post->ID . "&object=" . $sdt->photo['id'] . "' width='80' height='90' />";
		$photoarray = $sdt->photo;
	} else {
		$img = "";
	}

	?>

    <div id="individual_portfolio">
        <div class="portfolio-photo" style="float: left;">
            <p>Photo</p>
            <div>
				<?php echo $img; ?><br/>
                <input type="file" name="photo" id="portfolio-photo" class="image_field" >
            </div>
        </div>
        <div class="clearfix"></div>

		<?php

		if ( $sdt->name != "" ) {
			$display = "display:none;";
			$label   = $sdt->name;
		} else {
			$display = "";
			$label   = "Student";
		}

		$focus_group = "";

		?>

        <label for="report-student" c#lass="student-label"><?php echo $label; ?></label>&nbsp;
        <div style="<?php echo $display; ?>">
            <select data-placeholder="Choose a student..." style="width: 350px;" class="chosen-select" name="student_id"
                    id="student_id" style="<?php echo $display; ?>">
                <option></option>
				<?php
				echo populateStudentsForSelection( $sr_centres, $editor_centre, $student_id, $sdt );
				?>
            </select>
        </div>

        <br/>
        <br/>
        <br/>

        <div class="portfolio-summary">
            <div class="portfolio-info">
                <table>
                    <tr>
                        <td><label for="portfolio-name">Name</label></td>
                        <td><input name="portfolio-name" id="portfolio-name" value="<?php echo $sdt->name; ?>"></td>
                    </tr>
                    <tr>
                        <td><label for="portfolio-group">Group</label></td>
                        <td><input name="portfolio-group" id="portfolio-group" value="" disabled></td>
                    </tr>
            </div>
            <tr>
                <td><label for="portfolio-birthday">Date of birth</label></td>
                <td><input class="my-datepicker" name="portfolio-birthday" id="portfolio-birthday"
                           value="<?php echo $sdt->birthdate ?>" ></td>
            </tr>
            <tr>
                <td><label for="portfolio-mother-name">Mother name</label></td>
                <td><input name="portfolio-mother-name" id="portfolio-mother-name" value="<?php echo $sdt->mother; ?>">
                </td>
                <td><input type="button" class="portfolio-button-insert-parent" value="Fill from selected parent"></td>
            </tr>
            <tr>
                <td><label for="portfolio-father-name">Father name</label></td>
                <td><input name="portfolio-father-name" id="portfolio-father-name" value="<?php echo $sdt->father; ?>">
                </td>
                <td><input type="button" class="portfolio-button-insert-parent" value="Fill from selected parent"></td>
            </tr>
            <tr>
                <td><label for="portfolio-cultural-background">Cultural background</label></td>
                <td><input name="portfolio-cultural-background" id="portfolio-cultural-background"
                           value="<?php echo $sdt->cultural; ?>"></td>
            </tr>
            <tr>
                <td><label for="portfolio-religion">Religion</label></td>
                <td><input name="portfolio-religion" id="portfolio-religion" value="<?php echo $sdt->religion; ?>"></td>
            </tr>
            <tr>
                <td><label for="portfolio-medical">Medical conditions</label></td>
                <td><textarea name="portfolio-medical" id="portfolio-medical"><?php echo $sdt->medical; ?></textarea>
                </td>
            </tr>
            <tr>
                <td><label for="portfolio-medical">Attach PDF</label></td>
                <td>
                    <div class="pdf_files">
						<?php foreach ( $sr_data['pdf'] as $pdf ) : ?>
                            <div>
                                <a href="<?= $sr_images_url ?>?post_id=<?= $post->ID ?>&object=<?= $pdf ?>"><?= $pdf ?></a>
                                <img src="/wp-content/plugins/student-reports/img/icon_delete.png" class="close">
                                <input type="hidden" name="pdf[]" value="<?= $pdf ?>">
                            </div>
						<?php endforeach; ?>
                    </div>
                    <a href="javascript:void(0)" id="add_pdf_file">Add file</a>
                </td>
            </tr>
            </table>
        </div>

		<?php
		echo '<div id="entries"><a href="javascript:void(0)" class="add_entry">Add journal entry</a>';
		$entries = isset( $sr_data['entries'] ) && count( $sr_data['entries'] ) ? $sr_data['entries'] : [];
		foreach ( $entries as $entry_id => $entry ) {
			?>
            <div class="entry" data-id="<?= $entry_id ?>">
                <fieldset>
                    <legend>Journal entry <?= $entry_id ?></legend>
					<?php
					if ( isset( $entry['deleted'] ) && $entry['deleted'] == 'deleted' ) {
						echo '<input type="hidden" name="entries[' . $entry_id . '][deleted]" value="deleted"><div class="deleted">Deleted</div></feildset></div>';
						continue;
					}
					?>
                    <img src="/wp-content/plugins/student-reports/img/icon_delete.png" class="close">
                    <div class="form">
                        <a href="javascript:void(0)" class="add_observation">Add observation</a>
						<?php
						foreach ( $entry['observations'] as $observation_id => $observation ) {
							$date = isset( $observation['date'] ) ? $observation['date'] : null;
							if ( ! $date ) {
								$date = date( 'd.m.Y' );
							}
							?>
                            <div class="observation" data-id="<?= $observation_id ?>">
                                <fieldset>
                                    <legend>Observation <?= $observation_id ?></legend>
									<?php
									if ( isset( $observation['deleted'] ) && $observation['deleted'] == 'deleted' ) {
										echo '<input type="hidden" name="entries[' . $entry_id . '][observations][' . $observation_id . '][deleted]" value="deleted"><div class="deleted">Deleted</div></feildset></div>';
										continue;
									}
									?>
                                    <img src="/wp-content/plugins/student-reports/img/icon_delete.png" class="close">
                                    <div class="form">
                                        <p>
                                        <div>
                                            <label>Attachment:</label>
                                            <select class="att_type"
                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][attachment_type]"
                                                    id="">
                                                <option value="photo" <?= $observation['attachment_type'] == 'photo' ? 'selected' : '' ?>>
                                                    Photo
                                                </option>
                                                <option value="video" <?= $observation['attachment_type'] == 'video' ? 'selected' : '' ?>>
                                                    Video
                                                </option>
                                                <option value="sample" <?= $observation['attachment_type'] == 'sample' ? 'selected' : '' ?>>
                                                    Sample of work
                                                </option>
                                            </select>
                                        </div>
                                        <div class="attachment_type" data-type="photo">
											<?php if ( isset( $observation['photo'] ) && $observation['photo'] ) : ?>
                                                <img src="<?= $sr_images_url ?>?post_id=<?= $post->ID ?>&object=<?= $observation['photo'] ?>"
                                                     alt="" height="50">
                                                <br>
                                                <input type="hidden"
                                                       name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][photo]"
                                                       value="<?= $observation['photo'] ?>">

											<?php endif; ?>
                                            Photo: <input type="file"
                                                          name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][photo]"
                                                          class="image_field">

                                        </div>
                                        <div class="attachment_type" data-type="video">
                                            Video: <input type="text"
                                                          name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][video]"
                                                          value="<?= $observation['video'] ?>">
                                        </div>
                                        <div class="attachment_type" data-type="sample">
											<?php if ( isset( $observation['sample'] ) && $observation['sample'] ) : ?>
                                                <a href="<?= $sr_images_url ?>?post_id=<?= $post->ID ?>&object=<?= $observation['sample'] ?>">Download
                                                    PDF</a>
                                                <br>
                                                <input type="hidden"
                                                       name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][sample]"
                                                       value="<?= $observation['sample'] ?>">
											<?php endif; ?>
                                            Sample of work: <input type="file"
                                                                   name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][sample]">
                                        </div>
                                        </p>
                                        <p>
                                            <label>Observation or Collaborative Link:</label>
                                        <div>
                                            <span>Date: </span>
                                            <input class="my-datepicker" type="text"
                                                   name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][collaborative_date]"
                                                   value="<?= $observation['collaborative_date'] ?>" size="10"
                                                   maxlength="10" />
                                        </div>
                                        <div>
                                            <span>Time: </span>
                                            <input type="text"
                                                   name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][collaborative_time]"
                                                   value="<?= $observation['collaborative_time'] ?>" size="5"
                                                   maxlength="5"/>
                                            <select name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][collaborative_time_sign]">
                                                <option <?= $observation['collaborative_time_sign'] == 'am' ? ' selected' : '' ?>
                                                        value="am">am
                                                </option>
                                                <option <?= $observation['collaborative_time_sign'] == 'pm' ? ' selected' : '' ?>
                                                        value="pm">pm
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <span>Place: </span>
                                            <div><input type="radio"
                                                        name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][place]"
                                                        value="indoor" <?= $observation['place'] == 'indoor' ? ' checked="checked"' : '' ?>>
                                                Indoor
                                            </div>
                                            <div><input type="radio"
                                                        name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][place]"
                                                        value="outdoor" <?= $observation['place'] == 'outdoor' ? ' checked="checked"' : '' ?>>
                                                Outdoor
                                            </div>
                                        </div>
                                        <div>
                                            <span>Text: </span>
                                            <textarea
                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][collaborative_text]"
                                                    rows="10"
                                                    cols="100"><?= $observation['collaborative_text'] ?></textarea>
                                        </div>
                                        </p>
                                        <p>
                                            <label>Links to VEYLDF:</label>
                                        <div><input type="checkbox"
                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][veyldf][]"
                                                    id="veyldf1"
                                                    value="1" <?= in_array( 1, $observation['veyldf'] ) ? ' checked' : '' ?>>
                                            Outcome 1: Identity
                                        </div>
                                        <div><input type="checkbox"
                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][veyldf][]"
                                                    id="veyldf2"
                                                    value="2" <?= in_array( 2, $observation['veyldf'] ) ? ' checked' : '' ?>>
                                            Outcome 2: Community
                                        </div>
                                        <div><input type="checkbox"
                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][veyldf][]"
                                                    id="veyldf3"
                                                    value="3" <?= in_array( 3, $observation['veyldf'] ) ? ' checked' : '' ?>>
                                            Outcome 3: Wellbeing
                                        </div>
                                        <div><input type="checkbox"
                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][veyldf][]"
                                                    id="veyldf4"
                                                    value="4" <?= in_array( 4, $observation['veyldf'] ) ? ' checked' : '' ?>>
                                            Outcome 4: Learning
                                        </div>
                                        <div><input type="checkbox"
                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][veyldf][]"
                                                    id="veyldf5"
                                                    value="5" <?= in_array( 5, $observation['veyldf'] ) ? ' checked' : '' ?>>
                                            Outcome 5: Communication
                                        </div>
                                        </p>
                                        <p>
                                            <label>Interpretation:</label>
                                            <textarea
                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][interpretation]"
                                                    rows="10"
                                                    cols="100"><?= $observation['interpretation'] ?></textarea>
                                        </p>
                                        <div><a href="javascript:void(0)" class="add_goal">Add goal</a></div>
										<?php foreach ( $observation['goals'] as $g_id => $goal ) { ?>
                                            <div class="goal" data-id="<?= $g_id ?>">
                                                <fieldset>
                                                    <legend>Goal <?= $g_id ?></legend>
													<?php
													if ( isset( $goal['deleted'] ) && $goal['deleted'] == 'deleted' ) {
														echo '<input type="hidden" name="entries[' . $entry_id . '][observations][' . $observation_id . '][goals][' . $g_id . '][deleted]" value="deleted"><div class="deleted">Deleted</div></feildset></div>';
														continue;
													}
													?>
                                                    <img src="/wp-content/plugins/student-reports/img/icon_delete.png"
                                                         class="close">
                                                    <div class="form">
                                                        <p>
                                                        <div>
                                                            <span>Start date: </span>
                                                            <input class="my-datepicker" type="text"
                                                                   name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][start_date]"
                                                                   size="10" maxlength="10"
                                                                   value="<?= $goal['start_date'] ?>"/>
                                                        </div>
                                                        <div>
                                                            <span>Text: </span>
                                                            <textarea
                                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][text]"
                                                                    rows="10" cols="100"><?= $goal['text'] ?></textarea>
                                                        </div>
                                                        <div>
                                                            <span>Evaluation of Learning & Development: </span>
                                                            <textarea
                                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][evaluation]"
                                                                    rows="10"
                                                                    cols="100"><?= $goal['evaluation'] ?></textarea>
                                                        </div>
                                                        <div>
                                                            <span>Date goal evaluated: </span>
                                                            <input class="my-datepicker" type="text"
                                                                   name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][achieved_date]"
                                                                   size="10" maxlength="10"
                                                                   value="<?= $goal['achieved_date'] ?>"/>
                                                        </div>
                                                        </p>
                                                        <div><a href="javascript:void(0)" class="add_experience">Add
                                                                learning
                                                                experience</a></div>
														<?php foreach ( $goal['exp'] as $exp_id => $exp ) { ?>
                                                            <div class="exp" data-id="<?= $exp_id ?>">
                                                                <fieldset>
                                                                    <legend>Learning Experience <?= $exp_id ?></legend>
																	<?php
																	if ( isset( $exp['deleted'] ) && $exp['deleted'] == 'deleted' ) {
																		echo '<input type="hidden" name="entries[' . $entry_id . '][observations][' . $observation_id . '][goals][' . $g_id . '][exp][' . $exp_id . '][deleted]" value="deleted"><div class="deleted">Deleted</div></feildset></div>';
																		continue;
																	}

																	?>
                                                                    <img src="/wp-content/plugins/student-reports/img/icon_delete.png"
                                                                         class="close">
                                                                    <div class="form">
                                                                        <p>
                                                                        <div>
                                                                            <span>Program date: </span>
                                                                            <input class="my-datepicker" type="text"
                                                                                   name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][exp][<?= $exp_id ?>][program_date]"
                                                                                   size="10" maxlength="10"
                                                                                   value="<?= $exp['program_date'] ?>"/>
                                                                        </div>
                                                                        <div>
                                                                            <span>Program text: </span>
                                                                            <textarea
                                                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][exp][<?= $exp_id ?>][program_text]"
                                                                                    rows="10"
                                                                                    cols="100"><?= $exp['program_text'] ?></textarea>
                                                                        </div>
                                                                        <div>
                                                                            <span>Learning/Content/Behavioural Objective: </span>
                                                                            <textarea
                                                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][exp][<?= $exp_id ?>][objective]"
                                                                                    rows="10"
                                                                                    cols="100"><?= $exp['objective'] ?></textarea>
                                                                        </div>
                                                                        <label>Follow-up Observation:</label>
                                                                        <div>
                                                                            <span>Date: </span>
                                                                            <input class="my-datepicker" type="text"
                                                                                   name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][exp][<?= $exp_id ?>][observation_date]"
                                                                                   size="10" maxlength="10"
                                                                                   value="<?= $exp['observation_date'] ?>"/>
                                                                        </div>
                                                                        <div>
                                                                            <span>Time: </span>
                                                                            <input type="text"
                                                                                   name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][exp][<?= $exp_id ?>][observation_time]"
                                                                                   size="5" maxlength="5"
                                                                                   value="<?= $exp['observation_time'] ?>"/>
                                                                            <select name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][exp][<?= $exp_id ?>][observation_time_sign]">
                                                                                <option <?= $observation['observation_time_sign'] == 'am' ? ' selected' : '' ?>
                                                                                        value="am">am
                                                                                </option>
                                                                                <option <?= $observation['observation_time_sign'] == 'am' ? ' selected' : '' ?>
                                                                                        value="pm">pm
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                        <div>
                                                                            <span>Place: </span>
                                                                            <div><input type="radio"
                                                                                        name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][exp][<?= $exp_id ?>][observation_place]"
                                                                                        value="indoor" <?= $exp['place'] == 'indoor' ? ' checked="checked"' : '' ?>>
                                                                                Indoor
                                                                            </div>
                                                                            <div><input type="radio"
                                                                                        name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][exp][<?= $exp_id ?>][observation_place]"
                                                                                        value="outdoor" <?= $exp['place'] == 'outdoor' ? ' checked="checked"' : '' ?>>
                                                                                Outdoor
                                                                            </div>
                                                                        </div>
                                                                        <div>
                                                                            <span>Text: </span>
                                                                            <textarea
                                                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][exp][<?= $exp_id ?>][observation_text]"
                                                                                    rows="10"
                                                                                    cols="100"><?= $exp['observation_text'] ?></textarea>
                                                                        </div>
                                                                        </p>
                                                                        <p>
                                                                        <div>
                                                                            <span>Interpretation: </span>
                                                                            <textarea
                                                                                    name="entries[<?= $entry_id ?>][observations][<?= $observation_id ?>][goals][<?= $g_id ?>][exp][<?= $exp_id ?>][interpretation]"
                                                                                    rows="10"
                                                                                    cols="100"><?= $exp['interpretation'] ?></textarea>
                                                                        </div>
                                                                        </p>
                                                                    </div>
                                                                </fieldset>
                                                            </div>
														<?php } // End foreach (experiences) ?>
                                                    </div>
                                                </fieldset>
                                            </div>
										<?php } // End foreach (goals) ?>
                                    </div>
                                </fieldset>
                            </div>
						<?php } // End foreach (goals) ?>
                    </div>
                </fieldset>
            </div>
		<?php }   // End foreach (entries)	?>
    </div>
	<?php
}


// Now we are saving the data
function SR_save_post_portfolio( $post_id, $post ) {
	global $wpdb, $sr_upload_dir;

	// Check post type
	if ( $post->post_type != 'portfolios' ) {
		return;
	}

	// Admin area only
	if ( ! is_admin() ) {
		return;
	}

	// Check autosave”
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	//check post revision
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Load Report data
	$postmeta = get_post_meta( $post_id );
	$sr_data  = $postmeta['sr_data'][0];

	if ( $sr_data ) {
		$sr_data    = unserialize( $sr_data );
		$student_id = $postmeta['sr_student_id'][0];
	} else {
		$sr_data    = array();
		$student_id = 0;
	}

	if ( $_POST['portfolio-name'] && $_POST['portfolio-name'] != "" ) {
		$sr_data['portfolio-name'] = $_POST['portfolio-name'];
	}

	if ( $_POST['portfolio-birthday'] && $_POST['portfolio-birthday'] != "" ) {
		$sr_data['portfolio-birthday'] = $_POST['portfolio-birthday'];
	}

	if ( $_POST['portfolio-mother-name'] && $_POST['portfolio-mother-name'] != "" ) {
		$sr_data['portfolio-mother-name'] = $_POST['portfolio-mother-name'];
	}

	if ( $_POST['portfolio-father-name'] && $_POST['portfolio-father-name'] != "" ) {
		$sr_data['portfolio-father-name'] = $_POST['portfolio-father-name'];
	}

	if ( $_POST['portfolio-cultural-background'] && $_POST['portfolio-cultural-background'] != "" ) {
		$sr_data['portfolio-cultural-background'] = $_POST['portfolio-cultural-background'];
	}

	if ( $_POST['portfolio-religion'] && $_POST['portfolio-religion'] != "" ) {
		$sr_data['portfolio-religion'] = $_POST['portfolio-religion'];
	}

	if ( $_POST['portfolio-medical'] && $_POST['portfolio-medical'] != "" ) {
		$sr_data['portfolio-medical'] = $_POST['portfolio-medical'];
	}

	$sr_data['entries'] = null;
	if ( $_POST['entries'] ) {
		$sr_data['entries'] = $_POST['entries'];
	}

	foreach ( $_POST['entries'] as $entry_id => $entry ) {
		foreach ( $entry['observations'] as $observation_id => $observation ) {
			if ( $observation['attachment_type'] == 'photo' ) {
				if ( isset( $_FILES['entries']['tmp_name'][ $entry_id ]['observations'][ $observation_id ]['photo'] ) ) {
					$tmp  = $_FILES['entries']['tmp_name'][ $entry_id ]['observations'][ $observation_id ]['photo'];
					$info = getimagesize( $tmp );


					if ( $info === false || ( $info[2] !== IMAGETYPE_GIF ) && ( $info[2] !== IMAGETYPE_JPEG ) && ( $info[2] !== IMAGETYPE_PNG ) ) {

					} else {
						// Generate file name
						$ext       = pathinfo( $_FILES['entries']['name'][ $entry_id ]['observations'][ $observation_id ]['photo'], PATHINFO_EXTENSION );
						$id        = md5( time() . wp_generate_password( 10, false ) );
						$name      = $id . '.' . $ext;
						$path      = $sr_upload_dir . '/' . $post_id;
						$full_name = $path . '/' . $name;
						@mkdir( $path, 0777, true );


						if ( move_uploaded_file( $tmp, $full_name ) ) {
							$sr_data['entries'][ $entry_id ]['observations'][ $observation_id ]['photo'] = $name;
						}
					}
				}
			} elseif ( $observation['attachment_type'] == 'sample' ) {
				if ( isset( $_FILES['entries']['tmp_name'][ $entry_id ]['observations'][ $observation_id ]['sample'] ) ) {
					$tmp = $_FILES['entries']['tmp_name'][ $entry_id ]['observations'][ $observation_id ]['sample'];
					$ext = pathinfo( $_FILES['entries']['name'][ $entry_id ]['observations'][ $observation_id ]['sample'], PATHINFO_EXTENSION );

					if ( $ext == 'pdf' ) {
						// Generate file name
						$id        = md5( time() . wp_generate_password( 10, false ) );
						$name      = $id . '.pdf';
						$path      = $sr_upload_dir . '/' . $post_id;
						$full_name = $path . '/' . $name;
						@mkdir( $path, 0777, true );

						if ( move_uploaded_file( $tmp, $full_name ) ) {
							$sr_data['entries'][ $entry_id ]['observations'][ $observation_id ]['sample'] = $name;
						}
					}
				}
			}
		}
	}

	$sr_data['pdf'] = [];

	foreach ( $_POST['pdf'] as $pdf ) {
		$sr_data['pdf'][] = $pdf;
	}

	if ( isset( $_FILES['pdf']['name'] ) ) {
		for ( $i = 0; $i < count( $_FILES['pdf']['name'] ); $i ++ ) {
			if ( $_FILES['pdf']['type'][ $i ] == 'application/pdf' ) {
				$id        = md5( time() . wp_generate_password( 10, false ) );
				$name      = $id . '.pdf';
				$path      = $sr_upload_dir . '/' . $post_id;
				$full_name = $path . '/' . $name;
				@mkdir( $path, 0777, true );

				if ( move_uploaded_file( $_FILES['pdf']['tmp_name'][ $i ], $full_name ) ) {
					$sr_data['pdf'][] = $name;
				}
			}
		}
	}

	// Is a new report?
	if ( $student_id < 1 ) {
		// Do not allow a new portfolio to be generated if it already exists
		if ( $post->post_status == 'publish' && student_post_exists( $_POST['student_id'], $post->post_type ) ) {
			$post->post_status = 'draft';
			unset( $_POST['student_id'] );
			wp_update_post( $post );
			update_post_meta( $post_id, 'sr_student_id', "0" );
			update_post_meta( $post_id, 'sr_data', "" );

			$message = '<p>A portfolio for this student already exists</p>'
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

	// We need to add new photos?
	if ( isset( $_FILES['photo']['tmp_name'] ) ) {
		$info = getimagesize( $_FILES['photo']['tmp_name'] );

		if ( $info === false || ( $info[2] !== IMAGETYPE_GIF ) && ( $info[2] !== IMAGETYPE_JPEG ) && ( $info[2] !== IMAGETYPE_PNG ) ) {

		} else {
			// Generate file name
			$id        = md5( time() . wp_generate_password( 10, false ) );
			$name      = $id . '.jpg';
			$path      = $sr_upload_dir . '/' . $post_id;
			$full_name = $path . '/' . $name;
			@mkdir( $path, 0777, true );

			if ( move_uploaded_file( $_FILES['photo']['tmp_name'], $full_name ) ) {
				$photos['main'][0] = array( 'photo' => $id );
			}
		}
	}

	// Update post meta
	$sr_data['photos'] = $photos;

	update_post_meta( $post_id, 'sr_student_id', $student_id );
	update_post_meta( $post_id, 'sr_centre', $student_meta['sr_centre'][0] );
	update_post_meta( $post_id, 'sr_data', $sr_data );

	// Logging for debugging purposes
	$log_data                  = array();
	$log_data['_POST']         = $_POST;
	$log_data['sr_data']       = $sr_data;
	$log_data['sr_student_id'] = $student_id;
	$log_data['sr_centre']     = $student_meta['sr_centre'][0];

	log_post_save( $log_data );
}

add_action( 'save_post', 'SR_save_post_portfolio', 10, 3 );

// Apply special template
function IP_special_post_template_portfolio( $template ) {
	global $post, $wpdb, $sr_upload_dir, $sr_centres, $theme;

	$post = get_post();

	if ( $post->post_type === 'portfolios' ) {
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


		if ( ! $allowed ) {
			$login_url = wp_login_url( get_permalink() );
			wp_redirect( $login_url );
			die();
		}


		// PDF export support
		if ( isset( $_REQUEST['export'] ) && $_REQUEST['export'] == 'pdf' ) {
			$post_url = get_post_permalink( $post->ID );

			if ( $post_url ) {
				$name = '';
				if ( preg_match( '/([a-z0-9]{32})/', $post_url, $m ) ) {
					$name = $m[1];
				}

				if ( $name ) {
					$fname    = $sr_upload_dir . '/' . $name . '.pdf';
					$post_url .= '?local_load';

					// Generate PDF file
					@exec( "xvfb-run --server-args=\"-screen 4, 1280x1024x24\" wkhtmltopdf --use-xserver --disable-javascript \"{$post_url}\" {$fname}" );

					// Output to browser
					if ( file_exists( $fname ) ) {
						header( 'Content-Type: application/pdf' );
						header( 'Content-Length: ' . filesize( $fname ) );
						header( 'Content-disposition: inline; filename="' . basename( $fname ) . '"' );
						header( 'Cache-Control: public, must-revalidate, max-age=0' );
						header( 'Pragma: public' );
						header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
						header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
						readfile( $fname );

						@unlink( $fname );
					}
				}


			}

			die();
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


		$template = plugin_dir_path( __FILE__ ) . 'templates/' . $theme . '/single-portfolios.php';
		if ( ! file_exists( $template ) ) {
			die( "Can't find template file for Student Report!" );
		}
	}

	//  die($template);

	return $template;
}

add_filter( 'template_include', 'IP_special_post_template_portfolio' );

?>