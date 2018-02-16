<?php

@ini_set( 'display_errors', 0 );
@error_reporting( 0 );

// Load WP core
$wp_path = $_SERVER['DOCUMENT_ROOT'] . '/hope-elc';
$plugins_path = $wp_path . '/plugins';
$this_plugin_path = $plugins_path . '/student-reports';
define( 'WP_USE_THEMES', false );
include_once( $wp_path . '/wp-blog-header.php' );
include_once( $this_plugin_path . '/includes/Functions.php' );

$results = get_post_save_log();

foreach ( $results as $result ) {
	$result->encoded_data = unserialize($result->encoded_data);
	echo "<br /><pre>";
	print_r( $result );
	echo "</pre>";
}

?>