<?php
//debug code
//echo "Got here!";

@ini_set( 'display_errors', 0 );
@error_reporting( 0 );

// Load WP core
$wp_path = $_SERVER['DOCUMENT_ROOT'];
$plugins_path = $wp_path . 'wp-content/plugins';
$this_plugin_path = $plugins_path . '/student-reports';
define( 'WP_USE_THEMES', false );
include_once( $wp_path . '/wp-blog-header.php' );
include_once( $this_plugin_path . '/includes/Functions.php' );

// Begin generating array of experiences

if ( isset( $_GET['childID'] ) ) {
	$child_id = intval( $_GET['childID'] );

	$experience_array = array();

	if ( isset( $_GET['entryID'] ) ) {
		// Return just the single experience requested
		if ( ! isset ( $_GET['observationID'] ) || ! isset ( $_GET['goalID'] ) || ! isset ( $_GET['experienceID'] ) ) {
			// Incorrect data received from Ajax
			$experience_array[] = (object) array(
				'error_code' => 2
			);
		} else {
			$entry_id       = intval( $_GET['entryID'] );
			$observation_id = intval( $_GET['observationID'] );
			$goal_id        = intval( $_GET['goalID'] );
			$experience_id  = intval( $_GET['experienceID'] );

			$experience_array = get_individual_portfolio_experiences($child_id, $entry_id, $observation_id, $goal_id, $experience_id);

			if (count($experience_array) == 0) {
				// data was not found, so must have been removed
				$experience_array[] = (object) array(
					'error_code' => 1
				);
			}
		}
	} else {
		$experience_array = get_individual_portfolio_experiences($child_id);
	}

	echo json_encode( $experience_array );
	die();
}