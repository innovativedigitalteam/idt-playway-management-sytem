<?php

// die('nooch');

if ( ! defined( 'WPINC' ) ) {
	die( "Direct access is not allowed!" );
}

global $SR_vars, $sr_images_url;

//die($SR_vars['post_id']."");

//Parse SR_vars, extract sections data to a more readable format
$postId    = $SR_vars['post_id'];
$localLoad = $SR_vars['local_load'];

$sections = array(
	"background"   => "Background Information",
	"journal"      => "Journal",
	"commitment"   => "Commitment",
	"independence" => "Independence &amp; Persistence",
	"respect"      => "Respect",
	"reflexivity"  => "Reflexivity"
);

// Load Report data
$postmeta = get_post_meta( $postId );
//         $studentId = $postMeta['sr_student_id'];
//         die($studentId);
$sr_data = $postmeta['sr_data'][0];

if ( $sr_data ) {
	$sr_data    = unserialize( $sr_data );
	$student_id = $postmeta['sr_student_id'][0];
} else {
	$sr_data    = array();
	$student_id = 0;
}

//  die(var_dump($sr_data));


$sdt = (object) [
	'id'        => $student_id,
	'name'      => $sr_data['portfolio-name'],
	'group'     => $sr_data['portfolio-group'],
	'birthdate' => $sr_data['portfolio-birthday'],
	'mother'    => $sr_data['portfolio-mother-name'],
	'father'    => $sr_data['portfolio-father-name'],
	'cultural'  => $sr_data['portfolio-cultural-background'],
	'religion'  => $sr_data['portfolio-religion'],
	'medical'   => $sr_data['portfolio-medical'],
	'photo'     => array(
		'id'  => $sr_data['photos']['portfolio-photo'][0]['photo'],
		'url' => $sr_images_url . "?post_id=" . $post_id . "&object=" . $sr_data['photos']['portfolio-photo'][0]['photo']
	)
];

$sectionsData = array();

$sectionsData['knowledge'] = array(

	'header' => "Knowledge",
	'photos' => array(
		"url"        => $sr_images_url . '?post_id=' . $postId . '&object=' . $std->photo->id . '&x=1600.jpg',
		"thumb"      => $sr_images_url . '?post_id=' . $postId . '&object=' . $std->photo->id . '&x=89&y=157',
		"largeThumb" => $sr_images_url . '?post_id=' . $postId . '&object=' . $std->photo->id . '&x=177&y=314',

	)

);

$data  = $SR_vars['sr_data'];
$photo = isset( $data['photos']['main'][0]['photo'] ) ? $data['photos']['main'][0]['photo'] : '';

// Add code in document HEAD section
function SR_themeCustomCode_portfolio() {
	global $SR_vars;

	$body      = '';
	$html      = file_get_contents( get_bloginfo( 'url' ) . '/parent-reports-text-before-report-dont-change-title-dont-delete/' );
	$start_pos = strpos( $html, '<div class="entry-content">' );
	$end_pos   = strpos( $html, '</div><!-- .entry-content -->' );

	if ( $start_pos && $end_pos ) {
		$start_pos += 27;
		$length = $end_pos - $start_pos;

		$body = substr( $html, $start_pos, $length );
	}

	?>
    <link href="<?php echo $SR_vars['plugin_theme_url'] . '/style.css?' . time(); ?>" rel="stylesheet" type="text/css">
    <link href="//fonts.googleapis.com/css?family=Muli:400,300" rel="stylesheet" type="text/css">
    <link href="//fonts.googleapis.com/css?family=Aguafina+Script" rel="stylesheet" type="text/css">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->


	<?php
	if ( $SR_vars['local_load'] ) {
		?>
        <link href="<?php echo $SR_vars['plugin_theme_url'] . '/pdf.css?' . time(); ?>" rel="stylesheet"
              type="text/css"><?php
	} else {
		?>
        <link href="<?php echo $SR_vars['plugin_theme_url'] . '/featherlight/featherlight.css'; ?>" rel="stylesheet"
              type="text/css">
        <link href="<?php echo $SR_vars['plugin_theme_url'] . '/featherlight/featherlight.gallery.css'; ?>"
              rel="stylesheet" type="text/css">
        <link href="<?php echo $SR_vars['plugin_theme_url'] . '/datetimepicker/jquery.datetimepicker.css'; ?>"
              rel="stylesheet" type="text/css">

        <script type="text/javascript"
                src="<?php echo $SR_vars['plugin_theme_url'] . '/client.js?' . time(); ?>"></script>
        <script type="text/javascript"
                src="<?php echo $SR_vars['plugin_theme_url'] . '/featherlight/featherlight.js'; ?>"></script>
        <script type="text/javascript"
                src="<?php echo $SR_vars['plugin_theme_url'] . '/featherlight/featherlight.gallery.js'; ?>"></script>
        <script type="text/javascript"
                src="<?php echo $SR_vars['plugin_theme_url'] . '/datetimepicker/jquery.datetimepicker.full.js'; ?>"></script>
		<?php
	}
}

add_action( 'wp_head', 'SR_themeCustomCode_portfolio' );

function da_portfolioteText( $date ) {
	$date = explode( '-', $date );
	if ( count( $date ) != 3 ) {
		return '';
	}
	$date = mktime( 0, 0, 0, $date[0], $date[1], $date[2] );

	return date( 'd F Y', $date );
}


ob_start();
get_header();


?>
    <style>
        .background-information ul {
            width: 100%;
            text-align: left;
            color: #000;
            font-size: 18pt;
        }

        .background-information ul li {
            border-top: 2px solid #000;
            padding: 5px;
        }

        .background-information ul li span {
            float: right;
        }
    </style>
    <div class="sr-welcome">
        <h1 class="sr-report-title">Welcome <?php echo esc_html( implode( ' ', array(
				$SR_vars['parent']->first_name,
				$SR_vars['parent']->last_name
			) ) ); ?></h1>
    </div>
    <section class="one-section-background">
        <div class="container clearfix">
            <div class="savepdf">
                <a href="?export=pdf" title="Save as PDF" target="_blank"><img alt="Save as PDF"
                                                                               src="<?php echo $SR_vars['plugin_url'] . '/templates/pdf.gif' ?>"/></a>
            </div>

            <h1><?php echo esc_html( implode( ' ', array(
					$SR_vars['student']['first_name'][0],
					$SR_vars['student']['last_name'][0]
				) ) ); ?></h1>
            <h3>Learning &amp; development celebrations </h3>
            <h4>Hope Early Learning Centre - <?php echo esc_html( $SR_vars['centre_title'] ); ?></h4>
        </div>
    </section>
    <section class="two-section-background">
        <div class="container clearfix">
            <p>A child's journey of learning...</p>
            <p>A child’s journey of learning and development follows a path that is unique to their own individual self
                and has the child’s learning at the core. It involves a commitment to respectful and reciprocal
                relationships between educators and families as both have a influential role within a child’s world.</p>
            <p>Your child’s individual portfolio is a living document that allows for both educators and families to
                contribute to. It ensures that there is a foundation and clear focus for planning, promoting and
                assessing children’s learning and wellbeing. Learning outcomes are most likely to be achieved when early
                childhood educators work in partnership with families as these partnerships are based upon the
                foundation of understanding each other’s expectations and attitudes and building on the strength of each
                others knowledge (Educators Belonging, Being and Becoming, 2010).</p>

        </div>
    </section>
    <section class="photos-section-background sr-adaptive">
        <div class="container clearfix">
            <h1>Background Information</h1>
            <hr/>
			<?php if ( $photo ) : ?>
                <img style="float:left;width: 275px;height: 375px;"
                     src="http://hope.loc/wp-content/plugins/student-reports/get.php?post_id=<?= $postId ?>&object=<?= $photo ?>">
			<?php else : ?>
                <div style="float:left;width: 275px;height: 375px;background: grey;"></div>
			<?php endif; ?>
            <div style="text-align: left;width: 60%;float:right;border-left: 25px;padding: 25px;margin-left: 15px;"
                 class="clearfix">
                <div class="background-information">
                    <ul>
                        <li>Name: <span><?php echo $sdt->name; ?></span></li>
                        <li>Group: <span><?php echo $sdt->group; ?></span></li>
                        <li>Mother: <span><?php echo $sdt->mother; ?></span></li>
                        <li>Father: <span><?php echo $sdt->father; ?></span></li>
                        <li>Cultural Background: <span><?php echo $sdt->cultural; ?></span></li>
                        <li>Religion: <span><?php echo $sdt->religion; ?></span></li>
                        <li>Medical Conditions: <span><?php echo $sdt->medical; ?></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <section class="photos-section-background sr-adaptive">
        <div class="container clearfix">
            <h1>Journal entries</h1>
            <hr/>
			<?php foreach ( $data['entries'] as $entry ) : ?>
				<?php if ( ! isset( $entry['deleted'] ) ) : ?>
					<?php foreach ( $entry['observations'] as $observation ) : ?>
						<?php if ( ! isset( $observation['deleted'] ) ) : ?>
                            <div class="observation">
                                <div><strong>Observation/Collaborative Link:</strong></div>
                                <div class="date">(Date: <?= dateText( $observation['collaborative_date'] ) ?>
                                    Time: <?= $observation['collaborative_time'] ?><?= $observation['collaborative_time_sign'] ?>
                                    Place: <?= $observation['place'] ?>)
                                </div>
                                <div class="text"><?= $observation['collaborative_text'] ?></div>
								<?php if ( $observation['veyldf'] && count( $observation['veyldf'] ) ) : ?>
                                    <div class="veyldf mt20">
                                        <strong>Links to VEYLDF:</strong>
                                        <ul>
											<?= in_array( 1, $observation['veyldf'] ) ? '<li>Outcome 1: Identity</li>' : '' ?>
											<?= in_array( 2, $observation['veyldf'] ) ? '<li>Outcome 2: Community</li>' : '' ?>
											<?= in_array( 3, $observation['veyldf'] ) ? '<li>Outcome 3: Wellbeing</li>' : '' ?>
											<?= in_array( 4, $observation['veyldf'] ) ? '<li>Outcome 4: Learning</li>' : '' ?>
											<?= in_array( 5, $observation['veyldf'] ) ? '<li>Outcome 5: Communication</li>' : '' ?>
                                        </ul>
                                    </div>
								<?php endif; ?>
                                <div class="mt20"><strong>Interpretation:</strong></div>
                                <div class="text"><?= $observation['interpretation'] ?></div>
								<?php if ( $observation['goals'] && count( $observation['goals'] ) ) : ?>
                                    <div class="title mt20"><strong>Goals:</strong></div>
                                    <div class="goals">
										<?php foreach ( $observation['goals'] as $goal ) : ?>
											<?php if ( ! isset( $goal['deleted'] ) ) : ?>
                                                <div class="goal">
                                                    <div class="text mt20"><?= $goal['text'] ?></div>
                                                    <div class="date mt20"><strong>Start
                                                            date: </strong><?= dateText( $goal['start_date'] ) ?></div>
													<?php if ( $goal['exp'] && count( $goal['exp'] ) ) : ?>
                                                        <div class="exp_list">
															<?php foreach ( $goal['exp'] as $exp ) : ?>
																<?php if ( ! isset( $exp['deleted'] ) ) : ?>
                                                                    <div class="exp">
                                                                        <div class="mt20"><strong>Learning
                                                                                Experience: </strong>(program
                                                                            date: <?= dateText( $exp['program_date'] ) ?>
                                                                            )
                                                                        </div>
                                                                        <div><?= $exp['program_text'] ?></div>
                                                                        <div class="mt20"><strong>Learning/Content/Behavioural
                                                                                Objective:</strong></div>
                                                                        <div class="text"><?= $exp['objective'] ?></div>
                                                                        <div class="mt20">
                                                                            <strong>Follow-up Observation:</strong><br>
                                                                            (<strong>Date: </strong><?= $exp['observation_date'] ?>
                                                                            <strong>Time: </strong><?= $exp['observation_time'] . $exp['observation_time_sign'] ?>
                                                                            <strong>Place: </strong><?= $exp['observation_place'] ?>
                                                                            )
                                                                        </div>
                                                                        <div class="text"><?= $exp['observation_text'] ?></div>
                                                                        <div class="mt20">
                                                                            <strong>Interpretation:</strong></div>
                                                                        <div class="text"><?= $exp['interpretation'] ?></div>
                                                                    </div>
																<?php endif; ?>
															<?php endforeach; ?>
                                                        </div>
													<?php endif; ?>
                                                    <div class="evaluation mt20">
                                                        <div class="title"><strong>Evaluation of Learning &
                                                                Development:</strong></div>
                                                        <div class="text"><?= $goal['evaluation'] ?></div>
                                                    </div>
                                                </div>
											<?php endif; ?>
										<?php endforeach; ?>
                                    </div>
								<?php endif; ?>
                            </div>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php endforeach; ?>
            <div style="height: 80px"></div>
        </div>
    </section>
    <section class="twenty-section-background">
        <div class="container clearfix">
            <h1>THANK YOU</h1>
            <h3>Thank you for the learning, the patience, the acknowledgement and the encouragement to be all I can
                be.</h3>
        </div>
    </section>


<?php
$html = ob_get_contents();
ob_end_clean();


if ( ! $SR_vars['local_load'] ) {
	echo $html;
	get_footer();
} else {
	$html = preg_replace( '#<a.*?>(.*?)</a>#i', '\1', $html );
	echo $html;
}


function sr_generateSection( $sectionData, $scroll ) {
	$photosCount = count( $sectionData['photos'] );

	$sectionsCount    = $scroll ? 1 : max( 1, ceil( $photosCount / 2 ) );
	$photosPerSection = $scroll ? $photosCount : 2;

	for ( $sectionNo = 0; $sectionNo < $sectionsCount; $sectionNo ++ ) {
		?>
        <section class="photos-section-background sr-adaptive">
            <div class="container clearfix">
				<?php
				if ( $sectionNo == 0 ) {
					echo "<h1>" . $sectionData['header'] . "</h1>";//Insert header to the first section
				}

				if ( $photosCount == 0 ) {
					//No photos here
					echo "<p>There is no information for you at the moment!</p></div></section>";
					continue;
				}

				if ( $scroll ) {
					echo "<div class=\"scroll-wrapper\">";//Embed a scroll wrapper
				}

				//Output photos table
				echo "<table>";
				sr_showImagesRow( $sectionData['photos'], $sectionNo * $photosPerSection, $photosPerSection, "thumb", ! $scroll );
				echo "</table>";

				if ( $scroll ) {
					echo "</div>";
				}

				//Output smbdy say info if scrolling
				if ( $scroll ) {
					$sayMeta = array(
						"teachers" => "teachers-say",
						"family"   => "family-say"
					);
					if ( false ) {
						//foreach ($sayMeta as $caption => $key) {
						?>
                        <div class="sr-what-say">
                            <p class="sr-description">What my <?php echo $caption; ?> say</p>
							<?php
							foreach ( $sectionData['photos'] as $photo ) {
								?>
                                <p class="sr-content">
									<?php
									echo $photo[ $key ];
									?>
                                </p>
								<?php
							}
							?>
                        </div>
						<?php
					}
				}
				?>
            </div>
        </section>
		<?php
	}
}

function sr_showBlock( $key, $starting = 0 ) {
	global $sr_images_url, $SR_vars;

	$photos_count = count( $SR_vars['sr_data']['photos'][ $key ] );

	//Check if it is no data
	if ( $photos_count == 0 ) {
		echo "<p>There is no information for you at the moment!</p>";

		return;
	}

	//Check if it is old styled "Say"
	$oldStyledSay = array( 'teachers-say' => true, 'family-say' => true );

	for ( $i = 0; $i < $photos_count; $i ++ ) {
		if ( ! empty( $SR_vars['sr_data']['photos'][ $key ][ $i ]['teachers-say'] ) ) {
			$oldStyledSay['teachers-say'] = false;
			break;
		}
	}

	for ( $i = 0; $i < $photos_count; $i ++ ) {
		if ( ! empty( $SR_vars['sr_data']['photos'][ $key ][ $i ]['family-say'] ) ) {
			$oldStyledSay['family-say'] = false;
			break;
		}
	}


	?>
    <div data-featherlight-gallery data-featherlight-filter="a" class="block_container">
		<?php if ( ! $SR_vars['local_load'] ) { ?>

        <div class="scroll-wrapper">

			<?php } ?>

            <table>
				<?php
				$imagesInARow = $photos_count;

				if ( $SR_vars['local_load'] ) {
					$imagesInARow = 1;
					$photos_count = $starting + 1;
				}

				for ( $i = $starting; $i < $photos_count; $i += $imagesInARow ) {
					sr_showImagesRow( $SR_vars, $sr_images_url, $key, $i, $imagesInARow, $SR_vars['local_load'], $oldStyledSay );
				}

				?>
            </table>

			<?php if ( ! $SR_vars['local_load'] ) { ?>

        </div>

        <div class="sr-what-say">
            <p class="sr-description">What my teachers say</p>
			<?php
			if ( $oldStyledSay['teachers-say'] ) {
				$teachersSay = $SR_vars['sr_data']['teachers_say'][ $key ];
				?>
                <p class="sr-content-old"><?php echo $teachersSay; ?></p>
				<?php
			} else {
				for ( $i = 0; $i < $photos_count; $i ++ ) { ?>
                    <p class="sr-content"><?php
						$teachersSay = $SR_vars['sr_data']['photos'][ $key ][ $i ]['teachers-say'];

						echo $teachersSay;
						?>
                    </p>
				<?php }
			} ?>
        </div>

        <div class="sr-what-say">
            <p class="sr-description">What my family say</p>
			<?php
			if ( $oldStyledSay['family-say'] ) {
				$familySay = $SR_vars['sr_data']['family_say'][ $key ];
				?>
                <p class="sr-content-old"><?php echo $familySay; ?></p>
				<?php
			} else {
				for ( $i = 0; $i < $photos_count; $i ++ ) { ?>
                    <p class="sr-content"><?php
						$familySay = $SR_vars['sr_data']['photos'][ $key ][ $i ]['family-say'];

						echo $familySay;
						?>
                    </p>
				<?php }
			} ?>
        </div>
	<?php } ?>

    </div>
	<?php
}


function sr_showImagesRow( $photos, $from, $count, $thumbSize = "thumb", $includeSayInfo = false ) {
	global $localLoad;
	?>
    <tr class="sr-photos">
		<?php
		for ( $i = $from; $i < min( count( $photos ), ( $from + $count ) ); $i ++ ) { ?>
            <td><?php
				$img_url   = $photos[ $i ]['url'];
				$img_thumb = $photos[ $i ][ $thumbSize ];

				if ( $localLoad ) {
					$img_thumb .= '&local_load';
				}

				echo "<a href=\"{$img_url}\"><img src=\"{$img_thumb}\" alt=\"\" /></a>";
				?>
            </td>
		<?php } ?>
    </tr>

    <tr class="sr-dates">
		<?php
		for ( $i = $from; $i < min( count( $photos ), ( $from + $count ) ); $i ++ ) { ?>
            <td><?php
				echo $photos[ $i ]['date'];
				?>
            </td>
		<?php } ?>
    </tr>

	<?php
	if ( $includeSayInfo ) {
		?>
        <tr class="sr-say-caption">
            <td colspan="<?php echo $count; ?>">What my teachers say</td>
        </tr>
        <tr class="sr-what-say">
			<?php
			for ( $i = $from; $i < min( count( $photos ), ( $from + $count ) ); $i ++ ) { ?>
                <td>
					<?php echo $photos[ $i ]['teachers-say']; ?>
                </td>
			<?php } ?>
        </tr>

        <tr class="sr-say-caption">
            <td colspan="<?php echo $count; ?>">What my family say</td>
        </tr>
        <tr class="sr-what-say">
			<?php
			for ( $i = $from; $i < min( count( $photos ), ( $from + $count ) ); $i ++ ) { ?>
                <td>
					<?php echo $photos[ $i ]['family-say']; ?>
                </td>
			<?php } ?>
        </tr>
		<?php
	}
}


?>