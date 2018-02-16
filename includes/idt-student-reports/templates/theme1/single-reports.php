<?php

	get_header();
	
	?>
	<div class="sr-student-report-container">
		<h1 class="sr-report-title">Welcome <?php echo esc_html(implode(' ', array($SR_vars['parent']->first_name, $SR_vars['parent']->last_name))); ?></h1>
	
		<br />
		
		<div class="sr-page" id="dashboard">
			<span class="sr-page-number">Dashboard</span>
			<div class="sr-page-title">Recent reports</div>
			
			<table cellpadding="0" cellspacing="0" border="0" width="100" class="sr-recent-reports">
				<tr>
					<td>Gabrielle Woods</td>
					<td><a href="#" class="sr-report-link" id="sr-current-report">12.02.2016</a></td>
					<td><a href="#" class="sr-report-link">11.02.2016</a></td>
					<td><a href="#" class="sr-report-link">10.02.2016</a></td>
					<td><a href="#" class="sr-report-link">09.02.2016</a></td>
					<td><a href="#" class="sr-report-link">08.02.2016</a></td>
					<td><a href="#" class="sr-more" title="More..."><img alt="" src="<?php echo $SR_vars['plugin_url'] . '/templates/calendar-icon.png' ?>" width="36" height="36" /></a></td>
				</tr>
				<tr>
					<td>Ava Woods</td>
					<td><a href="#" class="sr-report-link">12.02.2016</a></td>
					<td><a href="#" class="sr-report-link">11.02.2016</a></td>
					<td><a href="#" class="sr-report-link">10.02.2016</a></td>
					<td></td>
					<td></td>
					<td><a href="#" class="sr-more" title="More..."><img alt="" src="<?php echo $SR_vars['plugin_url'] . '/templates/calendar-icon.png' ?>" width="36" height="36" /></a></td>
				</tr>				
			</table>
		</div>
	
		<div class="sr-page" id="page-1">
			<span class="sr-page-number">Page 1</span>
			<div class="sr-current-report-label">current report</div>
			<h2 class="sr-report-student-title">
				<?php echo esc_html(implode(' ', array($SR_vars['student']['firstname'], $SR_vars['student']['lastname']))); ?>
			</h2>
			<div class="sr-report-date">generated on <?php echo esc_html($SR_vars['date']); ?></div>
		</div>
		<div class="sr-page-empty" id="page-2">
			<span class="sr-page-number">Page 2</span>
		</div>
		<div class="sr-page-empty" id="page-3">
			<span class="sr-page-number">Page 3</span>
		</div>
		<div class="sr-page-empty" id="page-4">
			<span class="sr-page-number">Page 4</span>
		</div>
		<div class="sr-page-empty" id="page-5">
			<span class="sr-page-number">Page 5</span>
		</div>
		<div class="sr-page" id="page-6">
			<span class="sr-page-number">Page 6</span>
			<?php sr_showBlock('empathy', 'Empathy', 8); ?>
		</div>
		<div class="sr-page-empty" id="page-7">
			<span class="sr-page-number">Page 7</span>
		</div>
		<div class="sr-page-empty" id="page-8">
			<span class="sr-page-number">Page 8</span>
		</div>
		<div class="sr-page" id="page-9">
			<span class="sr-page-number">Page 9</span>
			<?php sr_showBlock('knowledge', 'Knowledge', 8); ?>
		</div>
		<div class="sr-page-empty" id="page-10">
			<span class="sr-page-number">Page 10</span>
		</div>
		<div class="sr-page-empty" id="page-11">
			<span class="sr-page-number">Page 11</span>
		</div>
		<div class="sr-page" id="page-12">
			<span class="sr-page-number">Page 12</span>
			<?php sr_showBlock('commitment', 'Commitment', 8); ?>
		</div>
		<div class="sr-page" id="page-13">
			<span class="sr-page-number">Page 13</span>
			<?php sr_showBlock('independence', 'Independence &amp; Persistence', 8); ?>
		</div>
		<div class="sr-page-empty" id="page-14">
			<span class="sr-page-number">Page 14</span>
		</div>
		<div class="sr-page-empty" id="page-15">
			<span class="sr-page-number">Page 15</span>
		</div>
		<div class="sr-page" id="page-16">
			<span class="sr-page-number">Page 16</span>
			<?php sr_showBlock('respect', 'Respect', 8); ?>
		</div>
		<div class="sr-page-empty" id="page-17">
			<span class="sr-page-number">Page 17</span>
		</div>
		<div class="sr-page-empty" id="page-18">
			<span class="sr-page-number">Page 18</span>
		</div>
		<div class="sr-page" id="page-19">
			<span class="sr-page-number">Page 19</span>
			<?php sr_showBlock('reflexivity', 'Reflexivity', 8); ?>
		</div>		
		<div class="sr-page" id="page-20">
			<span class="sr-page-number">Page 20</span>
			<div class="sr-thankyou">THANK YOU</div>
		</div>
	</div>
	<?php
	
	get_footer();
	
	
	
	
	
	
	
	function sr_showBlock($blockname, $blocktitle, $photos_count){
		global $SR_vars;
		
		
		?>
			<div class="sr-page-title"><?php echo esc_html($blocktitle); ?></div>

			<section data-featherlight-gallery data-featherlight-filter="a">			
				<table cellpadding="0" cellspacing="0" border="0" width="100" class="photos-table">
					<tr>
					<?php
						$photos_count = 8;
					
						for ($i=1; $i<=$photos_count; $i++){						
							$img_url = $SR_vars['plugin_url'] . "/images/{$i}.jpg";
								
							if ($img_url){
								echo "<td class=\"sr-photo\"><a href=\"{$img_url}\"><img src=\"{$img_url}\" alt=\"{$i}\" /></a></td>\n";
							}else{
								echo "<td class=\"sr-photo\"></td>\n";
							}
						}
					?>
					</tr>
					<tr>
					<?php
						for ($i=1; $i<=$photos_count; $i++){
							echo "<td class=\"sr-photo-date\">12.02.2016</td>\n";
						}
					?>
					</tr>					
				</table>
			</section>
				
			<table cellpadding="0" cellspacing="0" border="0" width="100">
				<tr>
					<td>What my teachers have to say...</td>
					<td width="20px">&nbsp;</td>
					<td>What my family has to say...</td>
				</tr>					
			</table>
		<?php
	}

?>