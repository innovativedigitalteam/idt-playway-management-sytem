<?php
define( "MAX_STUDENTS_PER_GROUP", 20 );

function set_or_empty( $var ) {
	return isset( $var ) ? $var : "";
}

function valid_id( $var ) {
	if ( isset( $var ) && $var > 0 ) {
		return true;
	} else {
		return false;
	}
}

function recompute_array( &$array ) {
	ksort( $array );
	$array = array_combine( range( 1, count( $array ) ), array_values( $array ) );
}

function student_post_exists( $student_id, $post_type ) {
	global $wpdb;
	$results = $wpdb->get_results( $wpdb->prepare( "

            SELECT * 

			FROM wpafse_posts AS POSTS

			JOIN (

				SELECT * 

				FROM wpafse_postmeta

				WHERE meta_key = 'sr_student_id' AND meta_value = %s

			) AS POSTMETA

			ON POSTS.ID = POSTMETA.post_id

			WHERE post_type = %s AND ( post_status = 'publish' OR post_status = 'inactive' )

        ", $student_id, $post_type ) );

	if ( count( $results ) ) {
		return true;
	} else {
		return false;
	}
}

function get_children_in_group( $group ) {
	global $wpdb;

	//PRIO2: Find out what to do if child is inactive in this case
	$results = $wpdb->get_results( $wpdb->prepare( "

            SELECT user_id

            FROM {$wpdb->prefix}usermeta

            WHERE meta_key = 'gr_focus_group' AND meta_value = %s

        ", $group ) );

	return $results;
}

function generate_pdf( $selections, $upload_dir, $local_testing, $landscape = false ) {

	if ( count( $selections ) ) {
		$post_url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];

		$name = "";
		foreach ( $selections as $key => $selection ) {
			$name .= $key;
			$name .= '_';
			$name .= $_REQUEST[ $selection ];
			$name .= '_';
		}
		// Remove last _ from the string
		$name = substr( $name, 0, - 1 );
	} else {
		global $post;
		$post_url = get_post_permalink( $post->ID );

		if ( $post_url ) {
			$name = '';
			if ( preg_match( '/([a-z0-9]{32})/', $post_url, $m ) ) {
				$name = $m[1];
			}
		} else {
			die();
		}
	}

	$fname    = $upload_dir . '/' . $name . '.pdf';
	$post_url .= '?local_load';
	if ( count($selections) ) {
		foreach ( $selections as $selection ) {
			$post_url .= '&' . $selection . '=' . $_REQUEST[$selection];
	    }
	}

	// Generate PDF file
	if ( ! $local_testing ) {

		$orientation = "";
		if ( $landscape ) {
		    $orientation = "--orientation Landscape";
		}
		@exec( "xvfb-run --server-args=\"-screen 4, 1280x1024x24\" /home/hopeelc/wkhtmltox/bin/wkhtmltopdf --use-xserver {$orientation} --disable-javascript \"{$post_url}\" {$fname}" );

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
	} else {
		header( 'Location: ' . $post_url );
	};

	die();
}

function is_non_empty_string( $str ) {
	$trimmedString = trim( $str );
	if ( is_string( $trimmedString ) && strlen( $trimmedString ) > 0 ) {
		return true;
	} else {
		return false;
	}
}

function populateGroupsForSelection() {
	global $wpdb;

	$posts = $wpdb->get_results( $wpdb->prepare( "

            SELECT ID, post_title

            FROM {$wpdb->prefix}posts

            WHERE post_type = 'groups'

        " ) );

	foreach ( $posts as $post ) {
		$post_title = $post->post_title;

		echo "<option value='" . $post->ID . "'>" . $post->post_title . "</option>";
	}

}

function populateCentresForSelection( $centres, $centre = null, $editor_centre = null ) {
	foreach ( $centres as $k => $v ) {
		if ( $centre !== null ) {
			$sel = ( $k == $centre ) ? ' selected="selected"' : '';
		} else if ( $editor_centre !== null ) {
			$sel = ( $k == $editor_centre ) ? ' selected="selected"' : '';
		}

		if ( ! $editor_centre || $k == $editor_centre ) {
			$k = esc_attr( $k );
			$v = esc_html( $v->title );

			echo "<option value=\"{$k}\"{$sel}>{$v}</option>\n";
		}
	}
}

function populateStudentsForSelection( $sr_centres, $editor_centre, $student_id = null, &$sdt = null ) {
	global $wpdb;

	//Get all parents

	$parents       = get_users( 'orderby=meta_value&meta_key=first_name&role=subscriber' );
	$parentEditors = get_users( 'orderby=meta_value&meta_key=first_name&role=report_editor' );

	$parents = array_merge( $parents, $parentEditors );

	$output = '';

	foreach ( $parents as $parent ) {
// Get students
		$students = $wpdb->get_results( "SELECT `user_id` FROM `{$wpdb->usermeta}` WHERE `meta_key`='sr_parent_id' AND `meta_value`='{$parent->ID}'" );

		if ( count( $students ) ) {
			$parent_meta = get_user_meta( $parent->ID );
			$parent_name = esc_html( implode( ' ', array(
				$parent_meta['first_name'][0],
				$parent_meta['last_name'][0]
			) ) );

			$options = '';

			foreach ( $students as $student ) {
				$student_meta = get_user_meta( $student->user_id );

				$centre_name = '';
				if ( $editor_centre ) {
					if ( $student_meta['sr_centre'][0] !== $editor_centre ) {
						continue;
					}
				} else {
					$centre_name = ' [' . $sr_centres[ $student_meta['sr_centre'][0] ]->title . ']';
				}

				$student_name = esc_html( implode( ' ', array(
					$student_meta['first_name'][0],
					$student_meta['last_name'][0]
				) ) );
				$just_student = $student_name;
				$student_name .= esc_html( $centre_name );

				if ( $student_id != null ) {
					$attr = ( $student->user_id == $student_id ) ? ' selected="selected"' : '';

					if ( $student->user_id == $student_id ) {
						$sdt->name = $just_student;
					}
				}

				$options .= "<option value=\"{$student->user_id}\"{$attr}>{$student_name}</option>\n";
			}

			if ( $options ) {
				$output .= "<optgroup label=\"{$parent_name}\">\n";
				$output .= $options;
				$output .= "</optgroup>\n";
			}
		}
	}

	return $output;
}

function log_post_save( $edit_log ) {
	global $wpdb;

	$wpdb->query( $wpdb->prepare( "
			INSERT INTO {$wpdb->prefix}sr_temp_log (encoded_data) VALUES(%s)
	", serialize( $edit_log ) ) );
}

function get_post_save_log() {
	global $wpdb;

	$results = $wpdb->get_results( $wpdb->prepare( "

            SELECT *

            FROM {$wpdb->prefix}sr_temp_log

        " ) );

	return $results;
}

function get_individual_portfolio_experiences( $student_id, $entry_id = null, $observation_id = null, $goal_id = null, $experience_id = null ) {
	global $wpdb;

	$results = $wpdb->get_results( $wpdb->prepare( "

            SELECT post_id

            FROM {$wpdb->prefix}postmeta

            WHERE meta_key = 'sr_student_id' AND meta_value = %s

        ", $student_id ) );

	foreach ( $results as $result ) {
		$child_post = get_post( $result->post_id );
		if ( $child_post->post_type === 'portfolios' ) {
			$sr_data = get_post_meta( $child_post->ID, 'sr_data', true );
		}
	}

	$experience_array = array();

	if ( $entry_id && $observation_id && $goal_id && $experience_id ) {
		// Get single experience array from the individual portfolio IDs
		if ( array_key_exists( $entry_id, $sr_data['entries'] ) ) {
			$entry = $sr_data['entries'][ $entry_id ];
			if ( array_key_exists( $observation_id, $entry['observations'] ) ) {
				$observation = $entry['observations'][ $observation_id ];
				if ( array_key_exists( $goal_id, $observation['goals'] ) ) {
					$goal = $observation['goals'][ $goal_id ];
					if ( array_key_exists( $experience_id, $goal['exp'] ) ) {
						// All good - prepare the experience
						$experience         = $goal['exp'][ $experience_id ];
						$experience_array[] = (object) array(
							'error_code'     => 0,
							'program_date'   => $experience['program_date'],
							'entry_id'       => $entry_id,
							'observation_id' => $observation_id,
							'goal_id'        => $goal_id,
							'experience_id'  => $experience_id,
							'program_text'   => $experience['program_text'],
							'objective'      => $experience['objective'],
							'goal_text'      => $goal['text']
						);
					}
				}
			}
		}
	} else {
		// Get an array of all experiences from the individual portfolio
		if ( isset( $sr_data ) ) {
			foreach ( $sr_data['entries'] as $entry_id => $entry ) {
				foreach ( $entry['observations'] as $observation_id => $observation ) {
					foreach ( $observation['goals'] as $goal_id => $goal ) {
						foreach ( $goal['exp'] as $experience_id => $experience ) {
							$experience_array[] = (object) array(
								'error_code'     => 0,
								'program_date'   => $experience['program_date'],
								'entry_id'       => $entry_id,
								'observation_id' => $observation_id,
								'goal_id'        => $goal_id,
								'experience_id'  => $experience_id,
								'program_text'   => $experience['program_text'],
								'objective'      => $experience['objective'],
								'goal'           => $goal['text']
							);
						}
					}
				}
			}
		}
	}

	return $experience_array;
}


?>