<?php

// die('nooch');

if ( ! defined( 'WPINC' ) ) {
	die( "Direct access is not allowed!" );
}

global $SR_vars, $sr_images_url;

//Parse SR_vars, extract sections data to a more readable format
$postId    = $SR_vars['post_id'];
$localLoad = $SR_vars['local_load'];

$sections = array(
	"empathy"      => "Empathy",
	"knowledge"    => "Knowledge",
	"commitment"   => "Commitment",
	"independence" => "Independence &amp; Persistence",
	"respect"      => "Respect",
	"reflexivity"  => "Reflexivity"
);

$sectionsData = array();
foreach ( $sections as $key => $header ) {
	//Check if it is old styled "Say"
	$oldStyledSay = array( 'teachers-say' => true, 'family-say' => true );

	if ( isset( $SR_vars['sr_data']['photos'][ $key ] ) && is_array( $SR_vars['sr_data']['photos'][ $key ] ) ) {
		foreach ( $SR_vars['sr_data']['photos'][ $key ] as $photo ) {
			if ( ! empty( $photo['teachers-say'] ) ) {
				$oldStyledSay['teachers-say'] = false;
				break;
			}
		}

		foreach ( $SR_vars['sr_data']['photos'][ $key ] as $photo ) {
			if ( ! empty( $photo['family-say'] ) ) {
				$oldStyledSay['family-say'] = false;
				break;
			}
		}
	}

	$data = array();

	$photos = array();
	if ( isset( $SR_vars['sr_data']['photos'][ $key ] ) && is_array( $SR_vars['sr_data']['photos'][ $key ] ) ) {
		foreach ( $SR_vars['sr_data']['photos'][ $key ] as $photo ) {
			$photo_id = $photo['photo'];
			$photos[] = array(
				"url"          => $sr_images_url . '?post_id=' . $postId . '&object=' . $photo_id . '&x=1600.jpg',
				"thumb"        => $sr_images_url . '?post_id=' . $postId . '&object=' . $photo_id . '&x=89&y=157',
				"largeThumb"   => $sr_images_url . '?post_id=' . $postId . '&object=' . $photo_id . '&x=177&y=314',
				"date"         => $photo['date'],
				"family-say"   => $oldStyledSay['family-say'] ? " " : $photo['family-say'],
				"teachers-say" => $oldStyledSay['teachers-say'] ? " " : $photo['teachers-say']
			);
		}
	}

	if ( $oldStyledSay['family-say'] && count( $photos ) > 0 ) {
		$photos[0]['family-say'] = $SR_vars['sr_data']['family_say'][ $key ];
	}

	if ( $oldStyledSay['teachers-say'] && count( $photos ) > 0 ) {
		$photos[0]['teachers-say'] = $SR_vars['sr_data']['teachers_say'][ $key ];
	}

	$data['header']       = $header;
	$data['photos']       = $photos;
	$sectionsData[ $key ] = $data;
}

add_action( 'wp_head', 'SR_themeCustomCode' );


ob_start();
get_header();
?>
    <div class="sr-welcome">
        <h1 class="sr-report-title">Welcome <?php echo esc_html( implode( ' ', array(
				$SR_vars['parent']->first_name,
				$SR_vars['parent']->last_name
			) ) ); ?></h1>
    </div>


<?php
$body      = '';
$html      = file_get_contents( get_bloginfo( 'url' ) . '/parent-reports-text-before-report-dont-change-title-dont-delete/' );
$start_pos = strpos( $html, '<div class="entry-content">' );
$end_pos   = strpos( $html, '</div><!-- .entry-content -->' );

if ( $start_pos && $end_pos ) {
	$start_pos += 27;
	$length = $end_pos - $start_pos;

	$body = substr( $html, $start_pos, $length );
}

if ( $body ) {
	?>
    <section class="intro-section">
        <div class="container clearfix">
			<?php echo $body; ?>
        </div>
    </section>
	<?php
}
?>


    <section class="dashboard-section">
        <div class="container clearfix">
            <div class="sr-logout-links">
                <a href="<?php echo wp_lostpassword_url( get_bloginfo( 'url' ) . '/dashboard/' ); ?>"
                   title="Lost Password?">[ Lost Password? ]</a>
                &nbsp;
                <a href="<?php echo wp_logout_url( get_bloginfo( 'url' ) . '/dashboard/' ); ?>">[ Logout ]</a>
            </div>

            <div class="sr-dashboard-title">Select a report date</div>

            <table cellpadding="0" cellspacing="0" border="0" class="sr-recent-reports">
				<?php
				$cur_permalink = get_post_permalink( $SR_vars['post_id'] );
				$i             = 1;
				$all_r         = array();
				foreach ( $SR_vars['all_students'] as $student_id ) {
					$udata = get_userdata( $student_id );

					$key = "datetimepicker-{$i}";

					// Get all reports for this student
					$reports = $wpdb->get_results( "SELECT `ID`, DATE_FORMAT(`post_date`, '%d.%m.%Y') AS post_date FROM `{$wpdb->posts}` WHERE `ID` IN (SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key`='sr_student_id' AND `meta_value`='{$student_id}') AND `post_type` = 'reports' ORDER BY `post_date` DESC" );

					if ( count( $reports ) ) {
						foreach ( $reports as $r ) {
							$all_r[ $key ][] = array( $r->post_date, get_post_permalink( $r->ID ) );
						}
						?>
                        <tr>
                            <td colspan="6" class="sr-child-title"><?php echo esc_html( implode( ' ', array(
									$udata->first_name,
									$udata->last_name
								) ) ); ?></td>
                        </tr>
                        <tr>
							<?php
							$ir = 1;
							foreach ( $all_r[ $key ] as $v ) {
								if ( $cur_permalink === $v[1] ) {
									?>
                                    <td width="100"><a href="<?php echo $v[1]; ?>" class="sr-report-link"
                                                       id="sr-current-report"><?php echo $v[0]; ?></a></td><?php
								} else {
									?>
                                    <td width="100"><a href="<?php echo $v[1]; ?>"
                                                       class="sr-report-link"><?php echo $v[0]; ?></a></td><?php
								}

								if ( $ir == 5 ) {
									break;
								}

								$ir ++;
							}
							?>
                            <td><a href="#" class="sr-more" title="More..."><img alt=""
                                                                                 src="<?php echo $SR_vars['plugin_url'] . '/templates/calendar-icon.png' ?>"
                                                                                 width="36" height="36"
                                                                                 id="<?php echo $key; ?>"/></a></td>
                        </tr>
						<?php
					}

					$i ++;
				}
				?>
            </table>
        </div>

        <script>
            jQuery('document').ready(function () {
                jQuery('.sr-more').click(function (event) {
                    event.preventDefault();
                });


                var dateSelected = function (dp, $input) {
                    var reports = [];


					<?php
					$keys = array_keys( $all_r );

					foreach ( $keys as $key ) {
						echo "reports['{$key}'] = [];\n";

						foreach ( $all_r[ $key ] as $v ) {
							echo "reports['{$key}']['{$v[0]}'] = '{$v[1]}';\n";
						}
					}
					?>


                    var key1 = $input.attr('id');
                    var key2 = $input.val();

                    report_url = reports[key1][key2];
                    window.location.href = report_url;
                };


				<?php
				$keys = array_keys( $all_r );

				foreach($keys as $key){
				?>
                jQuery('#<?php echo $key; ?>').datetimepicker({
                    timepicker: false,
					<?php
					$tmp = array();
					foreach ( $all_r[ $key ] as $v ) {
						$tmp [] = "'{$v[0]}'";
					}
					echo "allowDates: [" . implode( ',', $tmp ) . "],\n";
					?>
                    format: 'd.m.Y',
                    onChangeDateTime: dateSelected
                });
				<?php
				}
				?>

            });
        </script>

    </section>
    <section class="one-section-background">
        <div class="container clearfix">
			<?php do_shortcode( '[wp_objects_pdf]' ); ?>
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
            <p>The purpose of this portfolio…</p>
            <p>As I grow and learn I start to see what I can do and know and who I can become. It is important for me to
                share this with you so that you can see the new things I can do and learn about the me I am
                ‘becoming.’</p>
            <p>All you have to do is click on each video in the section of the portfolio and you will see how much I
                have grown. You will see what I can do, what I like to do, what I have learnt and how I have overcome
                struggles.</p>
            <p>This portfolio belongs to me and I get to say what I want to share (which is usually a lot!) But if there
                is something you want to see me do, you can also tell my teacher and together we will work out a way
                this can be done.</p>
            <p>Sometimes I won’t bring paintings or artwork home, but it doesn’t mean I haven’t learnt, made or
                discovered something. Sometimes I will do things at Hope that I don’t at home and vice versa. This is a
                way for both you and my teacher to video my learning and share with each other...but remember, I get to
                choose because it is all about me!</p>
            <p>Please keep this in a safe place because when I am older these memories will be so important to me and
                also to you.</p>
        </div>
    </section>
    <section class="three-section-background">
        <div class="container clearfix">
            <div class="col-1">
                <h3>I’ve Been Busy</h3>
                <h4>By Joan Waters</h4>
                <p>Did you do a painting? No, But … I did a lot of other things, Almost too many to say. I talked and
                    laughed and thought and played. I’ve had a lovely, busy day.</p>
                <p>Did you do a painting? No, But… I climbed up and down the trestle And held on tight at the top. I
                    talked on the phone, and told my friend What I wanted at her shop.</p>
            </div>
            <div class="col-2">
                <p>Did you do a painting? No, But… I found some worms where the soil is wet. We dug the holes quite
                    deep. For lunch I ate tomatoes and rice, Then I settled down for a sleep.</p>
                <p>Did you do a painting? No, But… I used five boxes to make a house. We took the teddy in, too. I sat
                    on the mat with my legs tucked up And listened to “Wombat Stew”. </p>
                <p>Did you do a painting? No, But… I watered all the growing plants, Then I saw you at the gate. I might
                    do a painting tomorrow – Or perhaps you’ll have to wait! </p>
            </div>
        </div>
    </section>
    <section class="four-section-background">
        <div class="container clearfix">
            <h1>how I have learnt to understand and care for others</h1>
            <h3>Core Value 1: Empathy</h3>
        </div>
    </section>
    <section class="five-section-background">
        <div class="container clearfix">
            <div class="col-3">
                <h3>Empathy</h3>
                <p>This involves the child’s ability to develop and use their emotional intelligence to sense the
                    emotions in others around them, paired with the ability to imagine what the thoughts and feelings
                    that someone else might experiencing. </p>
                <p>It is also the child’s ability to develop and use emotional intelligence to understand and make sense
                    of their own emotion in order to see it in others as well as take measures to support their own
                    emotional wellbeing and develop self-regulation and resilience.</p>
            </div>
        </div>
    </section>
<?php
sr_generateSection( $sectionsData['empathy'], ! $localLoad );
?>

    <section class="seven-section-background">
        <div class="container clearfix">
            <h1>what I have learnt about the world and how to think and understand</h1>
            <h3>Core Value 2: Knowledge</h3>
        </div>
    </section>
    <section class="eight-section-background">
        <div class="container clearfix">
            <div class="col-3">
                <h3>Knowledge</h3>
                <p>This is the child’s ability to use their existing knowledge to further explore and expand their
                    knowledge of the world around them including foundational skills in developing their literacy and
                    numeracy skills, scientific thinking, artistic and creative expression and physical competence. </p>
                <p>It is also the child’s ability to link their knowledge in a holistic manner to progress their
                    developmental skills. Everything a child learns stems from their thinking and ability to use a
                    complex arrangement of thinking skills to support all areas of their development; including social,
                    emotional, language and physical development.</p>
            </div>
        </div>
    </section>
<?php
sr_generateSection( $sectionsData['knowledge'], ! $localLoad );
?>
    <section class="ten-section-background">
        <div class="container clearfix">
            <h1>how I have learnt to persist and keep going</h1>
            <h3>Core Value 3: Commitment</h3>
        </div>
    </section>
    <section class="eleven-section-background">
        <div class="container clearfix">
            <div class="col-3">
                <h3>Commitment</h3>
                <p>This is the child’s ability to demonstrate genuine commitment to their learning, understanding their
                    rights as a child, including feeling enabled to have a voice and contribute to decisions regarding
                    matters that affect them.</p>
                <p>It is also the child’s ability to demonstrate genuine commitment to each other through positive
                    interaction, authentic and constructive engagement and a commitment to a positive and productive
                    learning environment. </p>
                <p>Reach for the stars</p>
            </div>
        </div>
    </section>
<?php
sr_generateSection( $sectionsData['commitment'], ! $localLoad );
?>

<?php
sr_generateSection( $sectionsData['independence'], ! $localLoad );
?>
    <section class="fourteen-section-background">
        <div class="container clearfix">
            <h1>how I show others I admire them and accept them for who they are</h1>
            <h3>Core Value 4: Respect</h3>
        </div>
    </section>
    <section class="fifteen-section-background">
        <div class="container clearfix">
            <div class="col-3">
                <h3>Respect</h3>
                <p>This relates to the child’s ability to demonstrate through their behaviour and conduct
                    acknowledgement of themselves’ and another’s sense of worth and contribution they make to the world,
                    including listening, considering, understanding and compassion to each other.
                </p>
            </div>
        </div>
    </section>

<?php
sr_generateSection( $sectionsData['respect'], ! $localLoad );
?>
    <section class="seventeen-section-background">
        <div class="container">
            <h1>how I am able to think about my learning and improve </h1>
            <h3>Core Value 5: Reflexivity </h3>
        </div>
    </section>
    <section class="eighteen-section-background">
        <div class="container clearfix">
            <div class="col-3">
                <h1>Reflexivity</h1>
                <p>This is the child’s ability to see themselves as learners in the powerful sense of truth and reality;
                    who they really are, how they actually learn, how they can improve in their learning, and how they
                    can contribute.</p>
            </div>
        </div>
    </section>

<?php
sr_generateSection( $sectionsData['reflexivity'], ! $localLoad );
?>
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

					foreach ( $sayMeta as $caption => $key ) {
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