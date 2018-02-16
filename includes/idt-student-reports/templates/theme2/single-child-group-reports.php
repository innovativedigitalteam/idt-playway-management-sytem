<?php

if ( ! defined( 'WPINC' ) ) {
	die( "Direct access is not allowed!" );
}

include_once "includes/CommonFront.php";

add_action( 'wp_head', 'SR_themeCustomCode' );

get_header();

$html = "";

$html .= '<br/>';
$html .= '<br/>';
$html .= '<br/>';

$starting_date  = $_REQUEST['week-starting-date'];
$selected_group = $_REQUEST['select-group'];

if ( ! $starting_date || ! $selected_group ) {
	wp_redirect( admin_url( 'edit.php?post_type=cgroups' ) );
	die();
}

$cgroups = $wpdb->get_results( $wpdb->prepare( "

            SELECT ID

            FROM {$wpdb->prefix}posts

            WHERE post_type = 'cgroups' AND post_status = 'publish'

        " ) );


$html = "";

$html .= '<div class="savepdf">';
$html .= '<a href="?export=pdf&week-starting-date=' . $starting_date . '&select-group=' . $selected_group . '" title="Save as PDF" target="_blank"><img alt="Save as PDF"';
$html .= 'src="' . $pdf_image_location . '"/></a>';
$html .= '</div>';

$html .= '<div class="table-summary">';

$first = true;
foreach ( $cgroups as $cgroup ) {
	$cgroup_meta = get_post_meta( $cgroup->ID );
	$sr_data     = $cgroup_meta['sr_data'][0];
	$student_id  = $cgroup_meta['sr_student_id'][0];

	$student_meta  = get_user_meta( $student_id );
	$student_group = $student_meta['gr_focus_group'][0];

	if ( $sr_data ) {
		$sr_data = unserialize( $sr_data );
	} else {
		$sr_data = array();
	}

	if ( $sr_data['week-starting-date'] == $starting_date && $student_group == $selected_group ) {
		if ( $first ) {
			$first = false;
			$html  .= '<table>';

			$html .= '<tr>';
			$html .= '<th>' . 'Discovery / Developmental & Learning Outcome' . '</th>';
			$html .= '<th>' . 'Goal' . '</th>';
			$html .= '<th>' . 'Strategies / Objectives' . '</th>';
			$html .= '<th>' . 'Learning Experiences' . '</th>';
			$html .= '<th>' . 'Modifications/Spontaneous Experiences' . '</th>';
			$html .= '</tr>';
		}
		// Process this post (group) as it matches the starting date
		$selected_experience = $sr_data['select-experience'];

		if ( $student_id ) {
			if ( $selected_experience ) {
				$experience_ids   = explode( "/", $selected_experience );
				$experience_array = get_individual_portfolio_experiences( $student_id, $experience_ids[1], $experience_ids[2], $experience_ids[3], $experience_ids[4] );
			} else {
				$experience_array = null;
			}
			$goal_text    = "";
			$program_text = "";
			$objective    = "";
			if ( isset( $experience_ids ) ) {
				$goal_text    = $experience_array[0]->goal_text;
				$program_text = $experience_array[0]->program_text;
				$objective    = $experience_array[0]->objective;
			}
			$html .= '<tr>';
			$html .= '<td>' . $sr_data['discovery'] . '</td>';
			$html .= '<td>' . $goal_text . '</td>';
			$html .= '<td>' . $objective . '</td>';
			$html .= '<td>' . $program_text . '</td>';
			$html .= '<td>' . $sr_data['spontaneous'] . '</td>';
			$html .= '</tr>';
		}

	}
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