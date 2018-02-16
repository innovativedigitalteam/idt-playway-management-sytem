<?php
//debug code
//echo "Got here!";

@ini_set( 'display_errors', 0 );
@error_reporting( 0 );

// Load WP core
$wp_path = $_SERVER['DOCUMENT_ROOT'];

define( 'WP_USE_THEMES', false );
include_once( $wp_path . '/wp-blog-header.php' );

$group_name = "";
// Begin generating array of experiences
if ( isset( $_GET['childID'] ) ) {
	$child_id = intval( $_GET['childID'] );
	$group_id = get_user_meta( $child_id, 'gr_focus_group', true );
	if ( $group_id ) {
		$group_id   = intval( $group_id );
		$group_name = get_post_meta( $group_id, 'gr_name', true );
	}

	echo $group_name;
}