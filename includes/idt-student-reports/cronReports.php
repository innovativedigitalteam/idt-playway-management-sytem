<?php
/**
 * Created by PhpStorm.
 * User: madmax
 * Date: 23.04.16
 * Time: 9:19
 */


function addCronScheduleReport( $schedules ) {
	$schedules['Monthly'] = array(
		'interval' => 60 * 60 * 24 * 30,
		'display'  => __( "Once A Month" ),
	);

	return $schedules;
}

add_filter( "cron_schedules", "addCronScheduleReport" );

if ( ! wp_next_scheduled( "cron_report_on_reports_action" ) ) {
	wp_schedule_event( time(), "Monthly", "cron_report_on_reports_action" );
}

function cronSendReportOnReports() {
	global $wpdb;

	$centres = $wpdb->get_results(
		"SELECT id, title FROM {$wpdb->prefix}hope_centres ORDER BY id"
	);

	$notModifiedReports = $wpdb->get_results(
		"SELECT ID, post_title, post_modified FROM {$wpdb->posts} WHERE TIMESTAMPDIFF(DAY, post_modified, NOW()) >= 30 AND post_type = 'reports' ORDER BY post_modified"
	);

	if ( $notModifiedReports ) {
		$emailBody = "<p>Hello!</p><p>The following reports were not updated in the last month (follow the provided links to edit them):</p>";
		foreach ( $centres as $centre ) {
			$first = true;

			foreach ( $notModifiedReports as $report ) {
				$c         = get_post_meta( $report->ID, 'sr_centre' );
				$studentID = get_post_meta( $report->ID, 'sr_student_id', true );
				$studentID ? $isInactive = get_user_meta( $studentID, "report-inactive", true ) : $isInactive = false;
				if ( $c[0] == $centre->id && ! $isInactive ) {
					if ( $first ) {
						$emailBody .= "<br><b>" . $centre->title . "</b>";
						$emailBody .= "<ul>";
						$first = false;
					}
					$emailBody .= "<li><a href=\"" . admin_url( "post.php?post={$report->ID}&action=edit" ) . "\">{$report->post_title} (Last modified on {$report->post_modified} )</a></li>";
				}
			}
			if ( ! $first ) {
				$emailBody .= "</ul>";
			}
		}

		$emailBody .= "<p>Best regards</p>";

		wp_mail( "rob@hopeelc.com.au", "The following reports were not updated in the last two weeks", $emailBody, "Content-type: text/html" );
	}
}

add_action( "cron_report_on_reports_action", "cronSendReportOnReports" );