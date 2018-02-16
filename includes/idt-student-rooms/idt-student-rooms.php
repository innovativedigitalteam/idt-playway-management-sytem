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

require plugin_dir_path( __FILE__ ) . 'includes/rooms-archive.php';

function sm_load_styles() {
	wp_enqueue_style('sm-bootstrap-style', plugin_dir_url( __FILE__ ) . 'assets/bootstrap.css' );
	wp_enqueue_style('sm-main-style', plugin_dir_url( __FILE__ ) . 'assets/style.css' );
	wp_enqueue_style('sm-calendar-style', plugin_dir_url( __FILE__ ) . 'assets/calendar.css' );

	wp_register_script('sm-main-script', plugin_dir_url( __FILE__ ) . 'assets/script.js', array('jquery'));
	wp_enqueue_script('sm-main-script');
}

add_action( 'admin_enqueue_scripts', 'sm_load_styles' );

/*
*
* Removing trash option for non admins for custom posts
* room_checklists
* ar_room_checklists
* child_checklits
* ar_child_checklists
* 
*/

function remove_lists_types()
{
		$plugin_post_types = array(
						'room_checklists',
						'ar_room_checklists',
						'child_checklits',
						'ar_child_checklists'
					);

		foreach ($plugin_post_types as $key ) {
		add_filter( 'views_edit-'.$key, function( $views )
		{
		    if( current_user_can('administrator')) { return $views; } else {
		    	$remove_views = [ 'all','future','sticky','draft','pending','trash' ];
			    foreach( (array) $remove_views as $view )
			    { if( isset( $views[$view] ) )
			            unset( $views[$view] ); } return $views; }  
		});
	}

	// if user is not admin , then move to trash option is disabled.
	if(!current_user_can('administrator'))//not and admin
	{
	    global $pagenow;
	    if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {
	        add_action( 'admin_head', 'wpse_125800_custom_publish_box' );
	        function wpse_125800_custom_publish_box() {
	            $style = '';
	            $style .= '<style type="text/css">';
	            $style .= '#delete-action, .bulkactions';
	            $style .= '{display: none; }';
	            $style .= '</style>';

	            echo $style;
	        }
	    }
	}
}
add_action('init', 'remove_lists_types');

//removing row action below each post
add_filter( 'post_row_actions', 'remove_row_actions', 10, 1 );
function remove_row_actions( $actions )
{
	if( current_user_can('administrator'))
    {
        return $actions;
    } else {
	    if( get_post_type() === ('room_checklists' || 'ar_room_checklists' || 'child_checklits' || 'ar_child_checklists') )

	        unset( $actions['clone'] );
	        unset( $actions['trash'] );

	    return $actions;
	}
}



/*
*
* Cron jobs for moving child checklist and room checklist to archives checklist to their respective checklist, Job is scheduled to run on daily basis.
*
*/

add_action('init',function (){

	$time = wp_next_scheduled('checklists_to_archive_cron_hook' );
	wp_unschedule_event( $time, 'checklists_to_archive_cron_hook' );
  if (!wp_next_scheduled('checklists_to_archive_cron_hook' )) {
    wp_schedule_event( time(), 'daily', 'checklists_to_archive_cron_hook' );
  }
} );

add_action('checklists_to_archive_cron_hook',function (){
	// cron job for child checklists for moving to archive
   	global $post;

	  $args_child = array( 
	    'post_type'       => 'child_checklists',
	    'posts_per_page'  => -1,
	  );

	  $listings_child = get_posts( $args_child );
	    foreach($listings_child as $post) : setup_postdata($post);

	  $today = date( 'Ymd' );
	  $expire = get_the_time('Ymd', $post->ID);

        if ( $expire < $today ) :
           
           $status =  set_post_type( $post->ID, 'ar_child_checklists' );
       		
        endif;  
    endforeach;

    wp_reset_query();
	// cron job for room checklists for moving to archive

      $args_room_checklist = array( 
	    'post_type'       => 'room_checklists',
	    'posts_per_page'  => -1,
	  );

	  $listings_room_checklist = get_posts( $args_room_checklist );
	    foreach($listings_room_checklist as $post) : setup_postdata($post);

	  $today = date( 'Ymd' );
	  $expire = get_the_time('Ymd', $post->ID);

        if ( $expire < $today ) :
           
           $status =  set_post_type( $post->ID, 'ar_room_checklists' );
       		
        endif;  
    endforeach;

 
} );


