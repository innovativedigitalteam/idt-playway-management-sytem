<?php
/*
Plugin Name: IDT PlayWay Management system
Description: 
Version: 0.1
Author: www.innnovativedigitalteam.com
*/

require_once plugin_dir_path( __FILE__ ) . 'includes/idt-student-rooms/idt-student-rooms.php';
require_once plugin_dir_path( __FILE__ ) .'includes/idt-meta-fields/idt-meta-fields.php';
//require_once plugin_dir_path( __FILE__ ) . 'includes/idt-student-reports/idt-student-reports.php';


function sm_load_styles() {
	wp_enqueue_style('sm-bootstrap-style', plugin_dir_url( __FILE__ ) . 'assets/bootstrap.css' );
	wp_enqueue_style('sm-main-style', plugin_dir_url( __FILE__ ) . 'assets/style.css' );
	wp_enqueue_style('sm-calendar-style', plugin_dir_url( __FILE__ ) . 'assets/calendar.css' );

	wp_register_script('sm-main-script', plugin_dir_url( __FILE__ ) . 'assets/script.js', array('jquery'));
	wp_enqueue_script('sm-main-script');
	wp_register_script('idt-admin-custom', plugin_dir_url( __FILE__ ) . 'assets/custom.js', array('jquery'));


	wp_enqueue_script('idt-admin-custom');

	$plugin_dir_url = array( 'pluginUrl' => plugin_dir_url( __FILE__ ) );
	//after wp_enqueue_script
	wp_localize_script( 'idt-admin-custom', 'path', $plugin_dir_url );
}

add_action( 'admin_enqueue_scripts', 'sm_load_styles' );