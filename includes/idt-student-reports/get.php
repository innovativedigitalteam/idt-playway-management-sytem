<?php

// Global enviroment settings
@ini_set( 'display_errors', 0 );
@error_reporting( 0 );


// Check input values
$post_id     = (int) $_REQUEST['post_id'];
$object_hash = trim( $_REQUEST['object'] );

$x = (int) $_REQUEST['x'];
$y = (int) $_REQUEST['y'];
if ( $x < 1 ) {
	$x = 900;
}
if ( $y < 1 ) {
	$y = 0;
}


if ( ! $post_id || ! $object_hash ) {
	USC_show404();
}

$oh = explode( '.', $object_hash );
if ( isset( $oh[1] ) && $oh[1] == 'pdf' ) {
	$filename = $_SERVER['DOCUMENT_ROOT'] . '/../protected-images/' . $post_id . '/' . $object_hash;
	header( "Content-type:application/pdf" );
	header( "Content-Disposition:attachment;filename='" . $object_hash . "'" );
	readfile( $filename );
	die;
}


// Add 304 header support
// This is cheating a bit (doesn't verify the date), but is valid as long as you don't mind browsers keeping cached file forever.
if ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) {
	header( 'HTTP/1.1 304 Not Modified' );
	die();
}


// Load WP core
$wp_path = $_SERVER['DOCUMENT_ROOT'];
define( 'WP_USE_THEMES', false );
include_once( $wp_path . '/wp-blog-header.php' );
//include_once($wp_path .'/wp-config.php');
//include_once($wp_path .'/wp-load.php');
//include_once($wp_path .'/wp-includes/wp-db.php');
//include_once($wp_path .'/wp-includes/pluggable.php');


// Load object data
$sr_data_serialized = $wpdb->get_var( "SELECT `meta_value` FROM `{$wpdb->postmeta}` WHERE `meta_key`='sr_data' AND `post_id`='{$post_id}' LIMIT 1" );

// Qick & dirty check if this object linked with a post
if ( ! $sr_data_serialized && strpos( $sr_data_serialized, $object_hash ) === false ) {
	USC_show404();
}

$sr_data = @unserialize( $sr_data_serialized );

if ( ! is_array( $sr_data ) ) {
	USC_show404();
}

// Check permissions
USC_CanAccess( $post_id, $sr_data );


// Show object
$protected_dir = $wp_path . '/../protected-images';

if ( ! is_dir( $protected_dir ) ) {
	die( "Objects directory is not existed!\n" );
}

$protected_dir = realpath( $protected_dir );

if ( count( explode( '.', $object_hash ) ) > 1 ) {
	$oh   = explode( '.', $object_hash );
	$file = $protected_dir . '/' . $post_id . '/' . $oh[0] . '_' . $x . 'x' . $y . '.' . $oh[1];
} else {
	$file        = $protected_dir . '/' . $post_id . '/' . $object_hash . '_' . $x . 'x' . $y . '.jpg';
	$object_hash .= '.jpg';
}


include_once( 'includes/Zebra_Image.php' );

if ( ! file_exists( $file ) ) {
	// Generate thumb
	$image                         = new Zebra_Image();
	$image->source_path            = $protected_dir . '/' . $post_id . '/' . $object_hash;
	$image->target_path            = $file;
	$image->jpeg_quality           = 90;
	$image->preserve_aspect_ratio  = true;
	$image->enlarge_smaller_images = false;
	$image->preserve_time          = false;

	$image->resize( $x, $y );
}

$ext = pathinfo( $file, PATHINFO_EXTENSION );
$ext = strtolower( $ext );

header( 'Cache-control: private, max-age=' . ( 3600 * 24 ) );
header( 'Pragma: private' );
header( 'Content-transfer-encoding: binary' );
header( 'Content-length: ' . filesize( $file ) );
header( 'Expires: ' . gmdate( DATE_RFC1123, time() + 3600 * 24 ) );
header( 'Last-Modified: ' . gmdate( DATE_RFC1123, filemtime( $file ) ) );

switch ( $ext ) {
	case 'png':
		header( 'Content-Type: image/png' );
		break;

	case 'gif':
		header( 'Content-Type: image/gif' );
		break;

	case 'jpg':
	case 'jpeg':
	default:
		header( 'Content-Type: image/jpeg' );
		break;
}

//header("X-Image-Path: " . $file);


@readfile( $file );

die();


function USC_CanAccess( $post_id, $sr_data ) {
	$allowed = false;

	$post = get_post( $post_id );
	if ( $post->post_type !== 'tgroups' ) {
		$student_id = get_post_meta( $post_id, 'sr_student_id', true );

		if ( $student_id < 1 ) {
			USC_show403();
		}
	}

	// Allow local load without permsissions check (for PDF)
	$local_load = false;
	if ( isset( $_REQUEST['local_load'] ) ) {
		//if ($_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR']){TODO Fix this potential security hole!
		$local_load = true;
		//}
	}

//header("X-Image-Path: " . $file);

	// Check access rights
	if ( $local_load || is_user_logged_in() ) {
		if ( $local_load || current_user_can( 'manage_options' ) ) {
			// Allow for Admin
			$allowed = true;
		} else {
			$user    = wp_get_current_user();
			$student = get_user_meta( $student_id );

			if ( $post->post_type === 'tgroups' ) {
				if ( in_array( 'report_editor', $user->roles ) ) {
					$editor_centre  = get_user_meta( $user->ID, 'sr_centre', true );
					$post_centre = get_post_meta( $post_id, 'sr_centre', true );

					if ( $editor_centre && $editor_centre === $post_centre ) {
						$allowed = true;
					}
				}
			} else {
				// Check if that user is student parent
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
	}


	if ( ! $allowed ) {
		USC_show403();
	}
}


function USC_show403() {
	header( $_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden' );
	echo "<h1>403 Unauthorized</h1>\n";
	die();
}


function USC_show404() {
	header( $_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found' );
	echo "<h1>404 Not Found</h1>\n";
	die();
}