<?php

$local_testing = false;
$wp_path = $_SERVER['DOCUMENT_ROOT'];
$site_root = '/';

$theme = 'theme2';
$plugins_root_path = $site_root . 'wp-content/plugins';
$this_plugin_root_path = $plugins_root_path . '/student-reports';
$templates_folder = $this_plugin_root_path . '/templates/';
$pdf_image = 'pdf.gif';
$pdf_image_location = $templates_folder . $pdf_image;
$plugin_theme_url = $templates_folder . $theme;

$local_load = isset($_GET['local_load']) || (isset($SR_vars['local_load']) && $SR_vars['local_load'] === true);

// Add code in document HEAD section
function SR_themeCustomCode() {
	global $plugin_theme_url, $local_load;

	?>
	<link href="<?php echo $plugin_theme_url . '/style.css?' . time(); ?>" rel="stylesheet" type="text/css">
	<link href="//fonts.googleapis.com/css?family=Muli:400,300" rel="stylesheet" type="text/css">
	<link href="//fonts.googleapis.com/css?family=Aguafina+Script" rel="stylesheet" type="text/css">
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->


	<?php
	if ( $local_load ) {
		?>
		<link href="<?php echo $plugin_theme_url . '/pdf.css?' . time(); ?>" rel="stylesheet"
		      type="text/css"><?php
	} else {
		?>
		<link href="<?php echo $plugin_theme_url . '/featherlight/featherlight.css'; ?>" rel="stylesheet"
		      type="text/css">
		<link href="<?php echo $plugin_theme_url . '/featherlight/featherlight.gallery.css'; ?>"
		      rel="stylesheet" type="text/css">
		<link href="<?php echo $plugin_theme_url . '/datetimepicker/jquery.datetimepicker.css'; ?>"
		      rel="stylesheet" type="text/css">

		<script type="text/javascript"
		        src="<?php echo $plugin_theme_url . '/client.js?' . time(); ?>"></script>
		<script type="text/javascript"
		        src="<?php echo $plugin_theme_url . '/featherlight/featherlight.js'; ?>"></script>
		<script type="text/javascript"
		        src="<?php echo $plugin_theme_url . '/featherlight/featherlight.gallery.js'; ?>"></script>
		<script type="text/javascript"
		        src="<?php echo $plugin_theme_url . '/datetimepicker/jquery.datetimepicker.full.js'; ?>"></script>
		<?php
	}
}

function dateText( $date ) {
	$date = explode( '-', $date );
	if ( count( $date ) != 3 ) {
		return '';
	}
	$date = mktime( 0, 0, 0, $date[0], $date[1], $date[2] );

	return date( 'd F Y', $date );
}

?>