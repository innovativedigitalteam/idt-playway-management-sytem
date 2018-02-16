<?php

if ( ! defined( 'WPINC' ) ) {
	die( "Direct access is not allowed!" );
}

include_once "includes/CommonFront.php";

global $SR_vars, $sr_images_url;

//Parse SR_vars, extract sections data to a more readable format
$postId    = $SR_vars['post_id'];
$localLoad = $SR_vars['local_load'];
$postTitle = $SR_vars['post_title'];
$data      = $SR_vars['sr_data'];


add_action( 'wp_head', 'SR_themeCustomCode' );

get_header();

$html = "";

$html .= '<div class="savepdf">';
$html .= '<a href="?export=pdf" title="Save as PDF" target="_blank"><img alt="Save as PDF"';
$html .= 'src="' . $pdf_image_location . '"/></a>';
$html .= '</div>';

$html .= '<br/>';
$html .= '<br/>';
$html .= '<br/>';

$html .= '<h3>Program Name: ' . esc_html( $postTitle ) . '</h3>';
$html .= '<h3>Program Start Date: ' . esc_html( $data['teacher-program-start-date'] ) . '</h3>';
$html .= '<h3>Program End Date: ' . esc_html( $data['teacher-program-end-date'] ) . '</h3>';

$html .= '<br/>';
$html .= '<br/>';
$html .= '<br/>';

$t_discoveries = $data['t_discoveries'];
if ( $t_discoveries && count( $t_discoveries ) ) {

	$html .= '<div class="table-summary">';
	$html .= '<table>';

	$html .= '<tr>';
	$html .= '<th>' . 'Discovery / Developmental & Learning Outcome' . '</th>';
	$html .= '<th>' . 'Learning Experiences' . '</th>';
	$html .= '<th>' . 'Goal & Strategies / Objectives' . '</th>';
	$html .= '<th>' . 'Modifications/Spontaneous Experiences' . '</th>';
	$html .= '</tr>';

	foreach ( $t_discoveries as $t_discovery ) {

	    if ( $t_discovery["deleted"] ) {
	        continue;
	    }

		$t_goals        = $t_discovery['t_goals'];
		$discovery_text = $t_discovery['discovery_text'] ? esc_html( $t_discovery['discovery_text'] ) : '&nbsp;';

		if ( ! count( $t_goals ) ) {
			$html .= '<tr>';
			$html .= '<td>' . $discovery_text . '</td>';
			$html .= '<td>&nbsp;</td>';
			$html .= '<td>&nbsp;</td>';
			$html .= '<td>&nbsp;</td>';
			$html .= '</tr>';
		} else {
			foreach ( $t_goals as $t_goal ) {

			    if ( $t_goal["deleted"] ) {
			        continue;
			    }

				$objectives_text   = $t_goal['learning_outcome_text'] ? esc_html( $t_goal['learning_outcome_text'] ) : '&nbsp;';
				$learning_exp_text = $t_goal['experience_text'] ? esc_html( $t_goal['experience_text'] ) : '&nbsp;';
				$spontaneous_text  = $t_goal['spontaneous_text'] ? esc_html( $t_goal['spontaneous_text'] ) : '&nbsp;';
				$html              .= '<tr>';
				$html              .= '<td>' . $discovery_text . '</td>';
				$html              .= '<td>' . $objectives_text . '</td>';
				$html              .= '<td>' . $learning_exp_text . '</td>';
				$html              .= '<td>' . $spontaneous_text . '</td>';
				$html              .= '</tr>';
			}
		}
	}

	$html .= '</table>';
	$html .= '</div>';
}
?>
<?php


if ( ! $SR_vars['local_load'] ) {
	echo $html;
	get_footer();
} else {
	$html = preg_replace( '#<a.*?>(.*?)</a>#i', '\1', $html );
	echo $html;
}


?>