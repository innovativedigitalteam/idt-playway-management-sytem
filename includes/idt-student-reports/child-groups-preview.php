<?php

include_once "includes/CommonFront.php";

define( 'WP_USE_THEMES', false );
include_once( $wp_path . '/wp-blog-header.php' );
$plugins_path     = $wp_path . '/plugins';
$this_plugin_path = $plugins_path . '/student-reports';
include_once( $this_plugin_path . '/includes/Functions.php' );

// PDF export support
if ( isset( $_REQUEST['export'] ) && $_REQUEST['export'] == 'pdf' ) {

	$selections = array (
		'date' => 'week-starting-date',
		'group' => 'select-group'
	);

	generate_pdf ( $selections, $sr_upload_dir, $local_testing, true );

}


include_once( 'templates/' . $theme . '/single-child-group-reports.php' );

?>