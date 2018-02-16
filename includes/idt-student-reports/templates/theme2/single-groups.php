<?php

if ( ! defined( 'WPINC' ) ) {
	die( "Direct access is not allowed!" );
}

add_action( 'wp_head', 'SR_themeCustomCode' );

get_header();

$html = "";

$user = wp_get_current_user();

$centre = null;
$room   = $_REQUEST['select-room'];
if ( $_REQUEST['select-centre'] ) {
	$centre = $_REQUEST['select-centre'];
} else {
	if ( in_array( 'report_editor', $user->roles ) ) {
		$centre = get_user_meta( $user->ID, 'sr_centre', true );
	}
}

if ( ! $centre || ! $room ) {
	wp_redirect( admin_url( 'edit.php?post_type=groups' ) );
	die();
}

$groups = $wpdb->get_results( $wpdb->prepare( "

            SELECT ID, post_title

            FROM {$wpdb->prefix}posts

            WHERE post_type = 'groups' AND post_status = 'publish'

        " ) );

$groups_filtered = array();
foreach ( $groups as $group ) {
	$post_meta    = get_post_meta( $group->ID );
	$group_centre = $post_meta['sr_centre'][0];
	$group_room   = $post_meta['gr_room'][0];

	if ( $group_centre == $centre && $group_room == $room ) {
		$groups_filtered[ $group->post_title ] = $group->ID;
	}
}


$html .= '<div class="savepdf">';
$html .= '<a href="?export=pdf&select-centre=' . $_REQUEST['select-centre'] . '&select-room=' . $_REQUEST['select-room'] . '" title="Save as PDF" target="_blank"><img alt="Save as PDF"';
$html .= 'src="' . $pdf_image_location . '"/></a>';
$html .= '</div>';

$html .= '<div class="table-summary">';
$html .= '<table>';

$html .= '<tr>';

ksort( $groups_filtered );
$groups_students = array();
foreach ( $groups_filtered as $group_title => $group_id ) {
	$html                         .= '<th>';
	$html                         .= $group_title;
	$groups_students[ $group_id ] = array();
	$html                         .= '</th>';
}

$html .= '</tr>';

$students = $wpdb->get_results( $wpdb->prepare( "

            SELECT user_id

            FROM {$wpdb->prefix}usermeta

            WHERE meta_key = 'sr_is_child' AND meta_value = '1'

        " ) );
foreach ( $groups_filtered as $group_id ) {
	foreach ( $students as $student ) {
		$usermeta = get_user_meta( $student->user_id );
		if ( $usermeta['gr_focus_group'] &&
		     $group_id == $usermeta['gr_focus_group'][0]
		) {
			$groups_students[ $group_id ][] = $usermeta['first_name'][0] . " " . $usermeta['last_name'][0];
		}
	}
}


$count = 0;
while ( $count < MAX_STUDENTS_PER_GROUP ) {
	$html .= '<tr>';
	foreach ( $groups_students as $students_in_group ) {
		if ( $students_in_group[ $count ] ) {
			$student = $students_in_group[ $count ];
			$html    .= '<td>' . $student . '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
	}
	$html .= '</tr>';
	$count ++;
}


$html .= '</table>';
$html .= '</div>';

?>
<?php


if ( ! $local_load ) {
	echo $html;
	get_footer();
} else {
	$html = preg_replace( '#<a.*?>(.*?)</a>#i', '\1', $html );
	echo $html;
}


?>