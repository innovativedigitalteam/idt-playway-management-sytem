<?php

/* -----Process list---
* Process for archiveing the posts for child checklist and rooms checklist includes in rooms-archive.php file.
* Bulk action is added for customs for for respective posts
* posts type is changes to arh_{_posy_type} 
* new page is added to show all custom posts for all archive posts
*/



/*
*
* custom post types for viewing child checklist and room checklist
*
*/


function registerPostTypesArchives() {
  //archive post type for child checklist
  $labels = array(
    'name'        => 'Archive Child Checklists',
    'singular_name'   => 'ArchiveChild Checklist',
    'new_item'      => 'New Child Checklist',
    'view'        => 'View Archive Child Checklist',
    'view_item'     => 'View Archive Child Checklist',
    'search_items'    => 'Search Archive Checklist',
    'not_found'     => 'Nothing found',
    'not_found_in_trash'=> 'Nothing found in Trash',
    'parent_item_colon' => '',
    'all_items'     =>  'All Archive Child Checklists',
  );

  $args = array(
    'labels'        => $labels,
    'description'       => 'Archive Child Checklists',
    'public'        => true,

    'show_ui'       => true,
    'menu_icon'       => 'dashicons-welcome-widgets-menus',
    'supports'        => array(''),
    'capability_type' => 'post',
    'capabilities' => array(
      'create_posts' => false, // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
    ),
    'map_meta_cap' => true, 
    );

  register_post_type('ar_child_checklists', $args);

  //archive post type for child checklist
  $labels = array(
    'name'        => 'Archive Room Checklists',
    'singular_name'   => 'Archive Room Checklist',
    'new_item'      => 'New Room Checklist',
    'view'        => 'View Archive Room Checklist',
    'view_item'     => 'View Archive Room Checklist',
    'search_items'    => 'Search Archive Checklist',
    'not_found'     => 'Nothing found',
    'not_found_in_trash'=> 'Nothing found in Trash',
    'parent_item_colon' => '',
    'all_items'     =>  'All Archive Room Checklists',
  );

  $args = array(
    'labels'        => $labels,
    'description'       => 'Archive Room Checklists',
    'public'        => true,

    'show_ui'       => true,
    'menu_icon'       => 'dashicons-welcome-widgets-menus',
    'supports'        => array(''),
    'capability_type' => 'post',
    'capabilities' => array(
      'create_posts' => false, // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
    ),
    'map_meta_cap' => true, 
    );

  register_post_type('ar_room_checklists', $args);


}

add_action('init', 'registerPostTypesArchives');





/*------------------------------------------------------------------------------------------- ----- Archive option for child checklist and room checklist in bulk options ----------------- -------------------------------------------------------------------------------------------*/

/*
*
* hooke for adding option in bulk options for post type @child_checklists
* This function moves the child checklist to archive child cecklists
*
*/

// Child checklist function  starts
add_filter( 'bulk_actions-edit-child_checklists', 'register_child_checklist_bulk_actions' );
 
function register_child_checklist_bulk_actions($bulk_actions) {
  // checing for user is admin
  if (!current_user_can('administrator')) { return; }

  $bulk_actions['archive_child_checklists'] = __( 'Move to Archive', 'archive_child_checklists');
  return $bulk_actions;
}


add_filter( 'handle_bulk_actions-edit-child_checklists', 'hope_child_checklist_bulk_action_handler', 10, 3 );
 
function hope_child_checklist_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
  if ( $doaction !== 'archive_child_checklists' ) {
    return $redirect_to;
  }
  foreach ( $post_ids as $post_id ) {
   

   set_post_type( $post_id, 'ar_child_checklists' );
  }
  
  $redirect_to = add_query_arg( 'archive_child_checklists', count( $post_ids ), $redirect_to );
  return $redirect_to;
}
// Child checklist function ends


/*
*
* hooke for adding option in bulk options for post type @room_checklists
* This function moves the room checklist to archive room cecklists
*
*/

// room checklist function starts
add_filter( 'bulk_actions-edit-room_checklists', 'register_room_checklist_bulk_actions' );
 
function register_room_checklist_bulk_actions($bulk_actions) {
  // checing for user is admin
  if (!current_user_can('administrator')) { return; }

  $bulk_actions['archive_room_checklists'] = __( 'Move to Archive', 'archive_room_checklists');
  return $bulk_actions;
}


add_filter( 'handle_bulk_actions-edit-room_checklists', 'hope_room_checklist_bulk_action_handler', 10, 3 );
 
function hope_room_checklist_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
  if ( $doaction !== 'archive_room_checklists' ) {
    return $redirect_to;
  }
  foreach ( $post_ids as $post_id ) {
   

   set_post_type( $post_id, 'ar_room_checklists' );
  }
  
  $redirect_to = add_query_arg( 'archive_room_checklists', count( $post_ids ), $redirect_to );
  return $redirect_to;
}
// room checklist function ends




/*
*
* hooke for adding option in bulk options for @ar_room_checklist
* This function moves the room checklist from archive to main room cecklists
*
*/

add_filter( 'bulk_actions-edit-ar_room_checklists', 'register_ar_room_checklists_bulk_actions' );
 
function register_ar_room_checklists_bulk_actions($bulk_actions) {
  // checing for user is admin
  if (!current_user_can('administrator')) { return; }

  $bulk_actions['room_checklists'] = __( 'Move to Room Checklist', 'room_checklists');
  return $bulk_actions;
}


add_filter( 'handle_bulk_actions-edit-ar_room_checklists', 'hope_ar_room_checklists_bulk_action_handler', 10, 3 );
 
function hope_ar_room_checklists_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
  if ( $doaction !== 'room_checklists' ) {
    return $redirect_to;
  }
  foreach ( $post_ids as $post_id ) {
   

   set_post_type( $post_id, 'room_checklists' );
  }
  
  $redirect_to = add_query_arg( 'room_checklists', count( $post_ids ), $redirect_to );
  return $redirect_to;
}

/*
*
* hooke for adding option in bulk options for @ar_child_checklists
* This function moves the child checklist from archive to main child cecklists
*
*/

// hooke for adding option in buk options for @child checklist
add_filter( 'bulk_actions-edit-ar_child_checklists', 'register_ar_child_checklists_bulk_actions' );
 
function register_ar_child_checklists_bulk_actions($bulk_actions) {
  // checing for user is admin
  if (!current_user_can('administrator')) { return; }

  $bulk_actions['child_checklists'] = __( 'Move to child checklist', 'archive_child_checklists');
  return $bulk_actions;
}


add_filter( 'handle_bulk_actions-edit-ar_child_checklists', 'hope_ar_child_checklists_bulk_action_handler', 10, 3 );
 
function hope_ar_child_checklists_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
  if ( $doaction !== 'child_checklists' ) {
    return $redirect_to;
  }
  foreach ( $post_ids as $post_id ) {
   

   set_post_type( $post_id, 'child_checklists' );
  }
  
  $redirect_to = add_query_arg( 'child_checklists', count( $post_ids ), $redirect_to );
  return $redirect_to;
}


/**
 * filter by post meta of centers
 * Filter is added in child checklist 
 * Two process works together custom post coulnm for checklists and custom filters
 */

function checklists_admin_posts_filter_restrict_manage_posts(){
    global $wpdb;
    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }

    //only add filter to post type you want
    if ('child_checklists' == $type || 'room_checklists' == $type || 'ar_child_checklists' == $type || 'ar_room_checklists' == $type)
    {
        // get all centers list
        $sr_centres = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}hope_centres` " );

        ?>
        <select name="ADMIN_FILTER_FIELD_VALUE">
        <option value=""><?php _e('Filter By ', 'wose45436'); ?></option>
        <?php
            $current_v = isset($_GET['ADMIN_FILTER_FIELD_VALUE'])? $_GET['ADMIN_FILTER_FIELD_VALUE']:'';
            foreach ($sr_centres as $sr_centre) {
                printf
                    (
                        '<option value="%d"%s>%s</option>',
                        $sr_centre->id,
                        $sr_centre->id == $current_v? ' selected="selected"':'',
                        $sr_centre->title
                    );
                }
        ?>
        </select>
        <?php
    }
}

add_action('restrict_manage_posts','checklists_admin_posts_filter_restrict_manage_posts');

add_filter( 'parse_query', 'checklists_posts_filter' );

function checklists_posts_filter( $query ){
    global $pagenow;
    $type = 'post';
    if (isset($_GET['post_type'])) {
        $type = $_GET['post_type'];
    }

    if ( ('child_checklists' == $type || 'room_checklists' == $type || 'ar_child_checklists' == $type || 'ar_room_checklists' == $type) && is_admin() && $pagenow=='edit.php' && isset($_GET['ADMIN_FILTER_FIELD_VALUE']) && $_GET['ADMIN_FILTER_FIELD_VALUE'] != '') {
     
        $query->query_vars['meta_key'] = 'center_id';
        $query->query_vars['meta_value'] = $_GET['ADMIN_FILTER_FIELD_VALUE'];
    }
}

/*
* New colums are added by using these functions in $checklists = array
                  (1 =>'room_checklists' , 
                   2 => 'child_checklists',
                   3 => 'ar_child_checklists',
                   4 => 'ar_room_checklists'
   );
* colunm shows the name of center
*/



function checklist_columns_head($defaults) {
    $defaults['center_name'] = 'Center Name';
    return $defaults;
}
 
// SHOW THE FEATURED IMAGE
function checklist_columns_content($column_name, $post_ID) {
   
   global $wpdb;
  
  $center_id = get_post_meta( $post_ID, 'center_id', true );
  $sr_centre = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}hope_centres` WHERE id = '$center_id' " );
  echo $sr_centre->title;

}


add_filter('manage_room_checklists_posts_columns', 'checklist_columns_head');
add_action('manage_room_checklists_posts_custom_column', 'checklist_columns_content', 10, 2);

add_filter('manage_child_checklists_posts_columns', 'checklist_columns_head');
add_action('manage_child_checklists_posts_custom_column', 'checklist_columns_content', 10, 2);

add_filter('manage_ar_child_checklists_posts_columns', 'checklist_columns_head');
add_action('manage_ar_child_checklists_posts_custom_column', 'checklist_columns_content', 10, 2);

add_filter('manage_ar_room_checklists_posts_columns', 'checklist_columns_head');
add_action('manage_ar_room_checklists_posts_custom_column', 'checklist_columns_content', 10, 2);


// Custom code for column in room_checklist column ends
