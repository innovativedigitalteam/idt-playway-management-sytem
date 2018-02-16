<?php
$plugin_post_types = array(
	'reports',
	'portfolios',
	'groups',
	'tgroups',
	'cgroups'
);


global $plugin_post_types;


/*
*
* Removing trash option for non admins for custom posts
* 
*/

function register_groups_bulk_actions($bulk_actions) {
  // checing for user is admin
  if (current_user_can('administrator')) { return $bulk_actions; }

}

function remove_lists_options() {
	global $plugin_post_types;

	foreach ($plugin_post_types as $key ) {
		add_filter( 'views_edit-'.$key, function( $views )
		{
		    if( current_user_can('administrator')) { return $views; } else {
		    	$remove_views = [ 'all','future','sticky','draft','pending','trash' ];
			    foreach( (array) $remove_views as $view )
			    { if( isset( $views[$view] ) )
			            unset( $views[$view] ); } return $views; }  
		});

		// hooke for adding option in buk options for @child checklist
		add_filter( 'bulk_actions-edit-'.$key, 'register_groups_bulk_actions' );
 

	}

	
}
add_action('init','remove_lists_options' );