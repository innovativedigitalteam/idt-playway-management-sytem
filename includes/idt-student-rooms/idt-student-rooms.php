<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function activateSmTest() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/Activator.php';
	Activator::activate();
}

function deactivateSmTest() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/Deactivator.php';
	Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activateSmTest' );
register_deactivation_hook( __FILE__, 'deactivateSmTest' );

require plugin_dir_path( __FILE__ ) . 'includes/StudentRooms.php';

function idt_playway_post_types()
{
		$labels = array(
		'name'				=> 'Center',
		'singular_name'		=> 'center',
		'add_new'			=> 'Add New center',
		'add_new_item'		=> 'Add New center',
		'edit'				=> 'Edit center',
		'edit_item'			=> 'Edit center',
		'new_item'			=> 'New center',
		'view'				=> 'View center',
		'view_item'			=> 'View center',
		'search_items'		=> 'Search Rooms',
		'not_found'			=> 'Nothing found',
		'not_found_in_trash'=> 'Nothing found in Trash',
		'parent_item_colon'	=> '',
		'all_items' 		=>  'All Rooms',
	);
	 $args = array(
    'labels'        => $labels,
    'description'       => 'Centers',
    'public'        => true,

    'show_ui'       => true,
    'menu_icon'       => 'dashicons-building',
    'supports'        => array('title'),
    'capability_type' => 'post',
    'capabilities' => array(
      'create_posts' => true, 
    ),
    'map_meta_cap' => true, 
    );

  	register_post_type('center', $args);
$room = 'Class';
  	$labels = array(
		'name'				=> $room,
		'singular_name'		=> $room,
		'add_new'			=> 'Add New '.$room,
		'add_new_item'		=> 'Add New '.$room,
		'edit'				=> 'Edit '.$room,
		'edit_item'			=> 'Edit '.$room,
		'new_item'			=> 'New '.$room,
		'view'				=> 'View '.$room,
		'view_item'			=> 'View '.$room,
		'search_items'		=> 'Search Rooms',
		'not_found'			=> 'Nothing found',
		'not_found_in_trash'=> 'Nothing found in Trash',
		'parent_item_colon'	=> '',
		'all_items' 		=>  'All '.$room,
	);

	$args = array(
		'labels'				=> $labels,
		'description' 			=> 'Student '.$room,
		'public'				=> true,
		'show_ui'				=> true,
		'menu_icon'				=> 'dashicons-store',
        'supports' 				=> array('title')
	);

	register_post_type('rooms', $args);

	$labels = array(
		'name'				=> 'Room Checklists',
		'singular_name'		=> 'Room Checklist',
		'add_new'			=> 'Create Room Checklist',
		'add_new_item'		=> 'Create Room Checklist',
		'edit'				=> 'Edit Room Checklist',
		'edit_item'			=> 'Edit Room Checklist',
		'new_item'			=> 'New Room Checklist',
		'view'				=> 'View Room Checklist',
		'view_item'			=> 'View Room Checklist',
		'search_items'		=> 'Search Checklist',
		'not_found'			=> 'Nothing found',
		'not_found_in_trash'=> 'Nothing found in Trash',
		'parent_item_colon'	=> '',
		'all_items' 		=>  'All Room Checklists',
	);

	$args = array(
		'labels'				=> $labels,
		'description' 			=> 'Room Checklists',
		'public'				=> true,
		'show_ui'				=> true,
		'menu_icon'				=> 'dashicons-yes',
        'supports' 				=> array(''),
       
        // 'taxonomies'            => array( 'category' )
	);

	register_post_type('room-checklists', $args);



	$labels = array(
		'name'				=> 'Child Checklists',
		'singular_name'		=> 'Child Checklist',
		'add_new'			=> 'Create Child Checklist',
		'add_new_item'		=> 'Create Child Checklist',
		'edit'				=> 'Edit Child Checklist',
		'edit_item'			=> 'Edit Child Checklist',
		'new_item'			=> 'New Child Checklist',
		'view'				=> 'View Child Checklist',
		'view_item'			=> 'View Child Checklist',
		'search_items'		=> 'Search Checklist',
		'not_found'			=> 'Nothing found',
		'not_found_in_trash'=> 'Nothing found in Trash',
		'parent_item_colon'	=> '',
		'all_items' 		=>  'All Child Checklists',
	);

	$args = array(
		'labels'				=> $labels,
		'description' 			=> 'Child Checklists',
		'public'				=> true,
		'show_ui'				=> true,
		'menu_icon'				=> 'dashicons-list-view',
        'supports' 				=> array(''),
      
        // 'taxonomies'            => array( 'category' )
	);

	register_post_type('child-checklists', $args);

	


}


add_action('init', 'idt_playway_post_types');


function idt_get_centers()
{
	$args = array(
  		'posts_per_page' => -1,
	  	'post_type'   => 'center'
	);
	 
	$centers = get_posts( $args );
	
	foreach ($centers as $center) {
		$return_array[] = (object)array('id' =>$center->ID , 'title' =>$center->post_title );
		
	}
	
	return  $return_array ;
}