<?php

function registerPostTypes() {

	$labels = array(
		'name'				=> 'Student Rooms',
		'singular_name'		=> 'Room',
		'add_new'			=> 'Add New Room',
		'add_new_item'		=> 'Add New Room',
		'edit'				=> 'Edit Room',
		'edit_item'			=> 'Edit Room',
		'new_item'			=> 'New Room',
		'view'				=> 'View Room',
		'view_item'			=> 'View Room',
		'search_items'		=> 'Search Rooms',
		'not_found'			=> 'Nothing found',
		'not_found_in_trash'=> 'Nothing found in Trash',
		'parent_item_colon'	=> '',
		'all_items' 		=>  'All Rooms',
	);

	$args = array(
		'labels'				=> $labels,
		'description' 			=> 'Student Rooms',
		'public'				=> true,
		'show_ui'				=> true,
		'menu_icon'				=> 'dashicons-welcome-widgets-menus',
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
		'menu_icon'				=> 'dashicons-welcome-widgets-menus',
        'supports' 				=> array(''),
        'capabilities'        => array(
			'publish_posts'       => 'publish_reports',
			'edit_posts'          => 'edit_reports',
			'edit_others_posts'   => 'edit_others_reports',
			'delete_post'         => 'delete_reports',
			'delete_posts'        => 'delete_reports',
			'delete_others_posts' => 'delete_others_reports',
			'read_private_posts'  => 'read_private_reports',
			'edit_post'           => 'edit_reports',
			'read_post'           => 'read_reports',
		),
        // 'taxonomies'            => array( 'category' )
	);

	register_post_type('room_checklists', $args);



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
		'menu_icon'				=> 'dashicons-welcome-widgets-menus',
        'supports' 				=> array(''),
        'capabilities'        => array(
			'publish_posts'       => 'publish_reports',
			'edit_posts'          => 'edit_reports',
			'edit_others_posts'   => 'edit_others_reports',
			'delete_post'         => 'delete_reports',
			'delete_posts'        => 'delete_reports',
			'delete_others_posts' => 'delete_others_reports',
			'read_private_posts'  => 'read_private_reports',
			'edit_post'           => 'edit_reports',
			'read_post'           => 'read_reports',
		),
        // 'taxonomies'            => array( 'category' )
	);

	register_post_type('child_checklists', $args);
	add_action( 'admin_menu', 'my_plugin_menu' ,0);

	function my_plugin_menu() {
		add_menu_page( 
			'Educator Check List',
			'Educator Task Checklist',
			'read',
			'educator_checklist',
			'educator_checklist_display'
		);
	}
	



}

function educator_save_data() {
		global $wpdb;
		
			$post_meta_id = $_POST['post_meta_id'];
			if (!empty($post_meta_id)) {
				$data = array(
					'meta_key' => $_POST['post_meta_task'],
					'meta_value' => 'checked'); 
				$where = array('meta_id' => $post_meta_id);
				
				$wpdb->update($wpdb->prefix.'postmeta', $data, $where);
				return;
			} else {
				$data = array(
					'post_id' => $_POST['post_id'],
					'meta_key' => $_POST['post_meta_task'],
					'meta_value' => 'checked');
				
				$wpdb->insert($wpdb->prefix.'postmeta', $data);
				return;
			}
		
			
 		
}

function get_assign_task_desc ($task_slug) {
		$post_meta_mornings = array(
				'open_windows' => 'Open windows',
				'fill_revitiliser' => 'Fill/Turn on Revitiliser'
				);
		 $post_meta_after_lunchs =  array(
			'room_centre' => 'Room Centre Task (Hallway/Stairs, Staff Bathrooms, Washing)',
			'bath_clean' => 'Childrens Bathroom clean and restock',
			'sink_area' => 'Sink area and benches are clear of clutter',
			'jif_sink' => 'Clean and Jif sink areas and bench, including drink station',
			'clean_cups' => 'Restock and Clean room cups',
			'clean_fridge' => 'Clean Microwave and fridge',
			'replace_spongs' => 'Mon- Replace Sponges',
			'refill_apple' => 'Refill apple detergent and spray bottles if needed',
			'refill_stock' => 'Refill Room stock (Soap, Handtowel etc)',
			'windows_cleaned' => 'Windows/ Doors are cleaned',
			'kickboards_cleaned' => 'Window sills and kickboards cleaned and dusted',
			'restock_indoor' => 'Restock Indoor/Outdoor hygiene tubs'				
			);
		$post_meta_afternoon = array(
			'clean_toys' => 'Clean mouthed toys',
			'remove_laundry' => 'Remove any dirty laundry and put in the wash',
			'disinfect_door' => 'Clean and disinfect door hand handles',
			'vacuum_carpet' => 'Vacuum and mop carpet and other floor areas',
			'reset_invitingly' => 'Reset room invitingly',
			'child_bath' => 'Childrens Bathroom and restock',
			'pack_yards' => 'Pack-Up Yards/ umberellas down',
			'close_windows' => 'Close windows',
			'used_hats' => 'Bring used hats to laundry',
			'turn_revitilisers' => 'Turn off revitilisers (Wash WED/FRI)',
			'outdoor_benches' => 'Outdoor benches are free from clutter',
			'windowsonly_cleaned' => 'Windows cleaned',
			'dirty_hats'=>'Children\'s dirty hats to be taken to the laundry'
			);

		$post_meta_yard = array(
				'yard_check' => 'AM Yard Check',
				'reset_yard' => 'Sweep/Reset yard',
				'pm_yard' => 'PM Yard Check'
				
			); 
 			if (array_key_exists($task_slug, $post_meta_mornings)) {
 				return array('timing' => '<b>Morning </b>',
 							'task' => $post_meta_mornings[$task_slug]
 				 );
 			 }
 			if (array_key_exists($task_slug, $post_meta_after_lunchs)) {
 			 	return array('timing' => '<b>After Lunch  </b>',
 							'task' => $post_meta_after_lunchs[$task_slug]
 				 );
 			 }

 			if (array_key_exists($task_slug, $post_meta_afternoon)) {
 				return array('timing' => '<b>After Noon </b>',
 							'task' => $post_meta_afternoon[$task_slug]
 				 );
 			 }
 			 if (array_key_exists($task_slug, $post_meta_yard)) {
 			 	return array('timing' => '<b>Yards </b>',
 							'task' => $post_meta_yard[$task_slug]
 				 );
 			 }

}
function educator_checklist_display (){
		if ($_POST['task_submit'] == 'Add') {

				educator_save_data();
				
	 		}
	 ?>
	 <div id="postbox-container-2" class="postbox-container" style="width: 98%;">
	<div class="postbox">
<div class="inside ">
	 <h1>Users Tasks List</h1>
	 <?php
		$task_user_id = get_current_user_id();
		global $wpdb; global $post;
		
 		$user_tasks = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."postmeta WHERE meta_key = 'task_uid_".$task_user_id."' ORDER BY `meta_id` DESC ", ARRAY_A );
 		
 		if(is_array($user_tasks) && !empty($user_tasks)){

 			
 			foreach ($user_tasks as $user_task) {
 			$room_checklist = $wpdb->get_results( "SELECT post_title FROM ".$wpdb->prefix."posts WHERE ID = '".$user_task['post_id']."'", ARRAY_A );
			
 			$task_details = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."postmeta WHERE post_id = '".$user_task['post_id']."' AND meta_key = '".$user_task['meta_value']."'", ARRAY_A );
 				
 			 //if ($task_details[0]['meta_value'] == 'checked') {echo "checked";}
 			$tasks_detail = get_assign_task_desc ($user_task['meta_value']);
 			
 			 	echo '<div class="postbox updated settings-error "> <br> <b>Room Checkist Detail:</b> '.$room_checklist[0]['post_title'].'<br/><b>Timing :</b> '.$tasks_detail['timing'];
 			 
?><div>	<form action="" method="POST">
 				<!-- input conditions set -->
 				
 					<input type="checkbox" style="vertical-align: text-bottom;" name="post_meta_task" value="<?php echo $user_task['meta_value']; ?>" 
 					<?php if ($task_details[0]['meta_value'] == 'checked') {echo "checked";} ?>
 					
 					<?php if ($task_details[0]['meta_value'] == 'checked') {echo "disabled";} ?>
 					>
 					<label><?php echo $tasks_detail['task'] ?>   </label>

 					<!-- conditions checks -->
 					<input type="hidden" name="post_meta_id" value="<?php echo $task_details[0]['meta_id']; ?>">
 					
 					<input type="hidden" name="post_id" value="<?php echo $user_task['post_id']; ?>">
 					<button type="submit" name="task_submit" class="button button-primary button-large" value="Add" <?php if ($task_details[0]['meta_value'] == 'checked') {echo "disabled";} ?>
 					>Save</button>
 				</form><br> </div>

 			<?php 

 			echo '</div>';

 			}
 			
 			
 			
 		} else {
 			echo '<div class="updated notice-warning">';
 				echo "<h2>You do not have any assigned task yet.</h2>";
 			echo "</div>";
 		
 		}
 		echo "</div></div></div>";
		return true;	
	}


// get staff list by center
	
function staff_list ($field_key = null,$task_status = null) {
	global $post;
	
	$cur_staff_members = get_post_meta($post->ID, 'staff_members', true);	
			
	$room_post_id = get_post_meta( $post->ID,'room',true );
		if (empty($room_post_id)) {
			$room_post_id = get_post_meta( $post->ID,'info_room',true );
		}
			
		
		
		$center_id = get_post_meta( $room_post_id,'center_id',true );

		 $args = array(
					'role__in'     => array('report_editor','administrator'),
					'meta_key'     => 'first_name',
					'orderby'      => 'meta_value',
					
				 );
	 $staff_members = get_users($args);

	 foreach ($staff_members as $staff_member) {
	 	$staff_center_id = get_user_meta($staff_member->id);
	 	$staff_status = get_user_meta( $staff_member->id, 'teacher_status', true );
	 	if (((int)$staff_center_id['sr_centre'][0] == (int)$center_id) && ($staff_status == 1)) {
	 		$room_staffs[] = array( 'id' => $staff_member->id,
	 			'staff_name'=> esc_html(implode(' ', array($staff_center_id['first_name'][0], $staff_center_id['last_name'][0])))

	 			);
	 	}
	 	 
	 }
	
	 if ($field_key == null ) {
	?>
		<b><label for="staff_members" class="student_label">Room educators</label></b>
		<select data-placeholder="Choose room educators..." multiple style="width: 350px;" class="chosen-select" name="staff_members[]" id="staff_members">
			<option></option>
		<?php
				foreach ($room_staffs as $room_staff) {

					$selected = (in_array($room_staff['id'], $cur_staff_members)) ? 'selected' : '';
					$staff_options .= "<option value=\"{$room_staff['id']}\" {$selected}> {$room_staff['staff_name']} </option>\n";

				}
				if ($staff_options){
					echo $staff_options;
				}
			?>
		</select>

	<?php
	 } else {


	 $educator_id = get_staff_id_from_metavalue($field_key);
	 ?>
	 <b><label for="<?php echo $field_key; ?>" class="student_label">Select Educator</label></b>
		<select data-placeholder="Choose a staff member..." style="width: 150px;" class="chosen-select" name="<?php echo $field_key; ?>_assign_id" id="<?php echo $field_key; ?>" <?php echo
		($task_status == 'true') ? "disabled" : '';  ?> >
			<option>N/A</option>
			<?php
				foreach ($room_staffs as $room_staff) {

					$selected = ($educator_id[0]['meta_key'] == 'task_uid_'.$room_staff['id']) ? 'selected' : '';
					$staff_options .= "<option value=\"{$room_staff['id']}\" {$selected}> {$room_staff['staff_name']} </option>\n";

				}
				if ($staff_options){
					echo $staff_options;
				}
			?>
		</select>
	 

	
	
	<?php 
	}
}

// get child list by center

// get staff list by center
	
function get_children_list () {
	global $post;
		//print_r($post);
	$cur_students = get_post_meta($post->ID, 'info_students', true);
	$room_post_id = get_post_meta( $post->ID,'info_room',true );
	$center_id = get_post_meta( $room_post_id,'center_id',true );

		 $args = array(
					'role__in'     => array('student'),
					'meta_key'     => 'first_name',
					'orderby'      => 'meta_value',
					
				 );
	 $childrens = get_users($args);
	
	 foreach ($childrens as $children) {
	 	$child_id = get_user_meta($children->id);
	 	$child_status = get_user_meta( $children->id,'report-inactive',true );
	 	if (((int)$child_id['sr_centre'][0] == (int)$center_id) && ($child_status != 1)) {
	 		$childs[] = array( 'id' => $children->id,
	 			'child_name'=> esc_html(implode(' ', array($child_id['first_name'][0], $child_id['last_name'][0])))

	 			);
	 	}
	 	 
	 }
	 
?>
	 <b><label for="info_students" class="student_label">Students</label></b>
		<select data-placeholder="Select students..." multiple style="width: 350px;" class="chosen-select" name="info_students[]" id="info_students">
			<option></option>
			<?php

			
				foreach ($childs as $child) {
					$selected = (in_array($child['id'], $cur_students)) ? 'selected' : '';

					$student_options .= "<option value=\"{$child['id']}\" {$selected} >{$child['child_name']}</option>\n";
				}
				if ($student_options){
					echo $student_options;
				}
			?>
		</select>
		<?php
	
	
	
}

function get_staff_id_from_metavalue($meta_value) {
	global $wpdb; global $post;
	
 $staff_id = $wpdb->get_results( "SELECT meta_key FROM ".$wpdb->prefix."postmeta WHERE  post_id = '".$post->ID."' AND meta_value = '".$meta_value."' ORDER BY meta_id DESC LIMIT 1", ARRAY_A );
	return $staff_id;	 
	 
}


// room metaboxfor adding center for that room.
//@roomsmetabox meta box for centers

function hope_room_meta_boxes( $post ) {
    add_meta_box('room_center',__( 'Select room center' ),'hope_render_room','rooms','normal','high');
}

  add_action( 'add_meta_boxes', 'hope_room_meta_boxes' );

function hope_render_room($post)
{
   global $post,$wpdb;
 	$sr_centres = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}hope_centres`" );
	$center_id =  get_post_meta( $post->ID,'center_id', true );
	
?>

<h2 class="hndle ui-sortable-handle"><strong>Select center</strong></h2>
<select name="center_id">
	<option <?php if (empty($center_id)) { echo "selected"; } ?> ></option>
	<?php 
		foreach ($sr_centres as $sr_centre) {
			?>

			<option value="<?php echo $sr_centre->id;  ?>" <?php if (!empty((int)$center_id) && ($center_id == $sr_centre->id)) { echo "selected"; } ?>><?php echo $sr_centre->title;  ?></option>
			<?php
		}
	?>
	
</select>

<?php
}

function hope_save_room_center($post_id){

    if( isset( $_REQUEST['center_id'] ) ){
        update_post_meta( $post_id, 'center_id',$_POST['center_id'] );
    }
}


add_action( 'save_post', 'hope_save_room_center' );



function checklistMetaInit()
{
	$post_id = $_GET['post'] ? $_GET['post'] : $_POST['post_ID'];

	$author_id = get_post_field( 'post_author', $post_id );
	$author_name = get_the_author_meta('display_name', $author_id);

    $post_type = get_post_field( 'post_type', $post_id );
	if ($post_type == 'ar_room_checklists') {
		add_meta_box('room_meta', 'Room checklist', 'room_meta', 'ar_room_checklists', 'normal', 'high');
	} else {
		add_meta_box('room_meta', 'Room checklist', 'room_meta', 'room_checklists', 'normal', 'high');
	}
    
    if ($post_type == 'ar_child_checklists') {
		 add_meta_box('child_meta', 'Child checklist', 'child_meta', 'ar_child_checklists', 'normal', 'high');
	} else {
		 add_meta_box('child_meta', 'Child checklist', 'child_meta', 'child_checklists', 'normal', 'high');
	}

    function room_meta() {

	    global $post;

	    if ($post->post_author != get_current_user_id()) {
	    	echo '<div class="notice notice-warning is-dismissible"><p>You can not update this roomlist.</p></div>';
	    }
	    // Noncename needed to verify where the data originated
	    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' .
	    wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

	    // Get the location data if its already been entered
	    $staff_members = get_users('orderby=meta_value&meta_key=first_name&role=report_editor');
	    $rooms = get_posts(array('post_type'=>'rooms', 'posts_per_page'=>-1, 'numberposts'=>-1));

	    $checklist_date = get_post_meta($post->ID, 'checklist_date', true);
	    
	    // $checklist_date = date("d-m-Y", strtotime($checklist_date));
	    $cur_staff_member = (int)get_post_meta($post->ID, 'staff_member', true);
	    $cur_room = get_post_meta($post->ID,'room');
	   
	    $cur_date = date('d/m/Y');

	    $post_meta = get_post_meta($post->ID);
	    
	    foreach ($post_meta as $param_name => $param_val) {
	    	if (startsWith($param_name, 'yardam') || startsWith($param_name, 'yardpm')) {
	    		${$param_name}  = $post_meta[$param_name][0];
	    	}
	    }

	?>
	    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
		<script type="text/javascript">

		jQuery(document).ready(function() {
		(function ($) {

		  $('#checklist_date').datepicker({ dateFormat: 'dd/mm/yy' });

		  })(jQuery);
		});

		
		</script>
		<?php
		// get center Id from cuurent room id;
		
		$center_id = get_post_meta( $cur_room[0], 'center_id', true );
		if (!empty($center_id)) {
			?>
			<input type="hidden" name="center_id" value="<?php echo $center_id; ?>">
			<?php
		} else {
				?>
				<script type="text/javascript">
					var ajaxurl = '<?php echo admin_url("admin-ajax.php"); ?>';

					jQuery(function($) {
					jQuery('body').on('change', '#room', function() {
						var center = $(this).val();
						if(center != '') {
							var data = {
								action: 'get_center_by_room',
								center: center
							}

							jQuery.post(ajaxurl, data, function(response) {
								$('#center_id').html(response);
							});
						}
					});
					});
				</script>
				<div id="center_id" style="display: none;"></div>

				<?php
		}

		?>
		

		

		<b><label for="room" class="student_label">Room</label></b>
		
		<select data-placeholder="Choose room..." style="width: 350px;" class="chosen-select" name="room" id="room">
						<option <?php echo (empty($cur_room)) ? 'selected' : ''; ?>>Select Room</option>
			<?php

				foreach ($rooms as $room) {

					$selected = ($cur_room[0] == $room->ID) ? 'selected' : '';
					$room_options .= "<option value=\"{$room->ID}\" {$selected}> {$room->post_title} </option>\n";
				}

				if ($room_options){
					echo $room_options;
				}
			?>
		</select>
		<br /><br />

		

		<b><label for="checklist_date">Date</label></b>
	    <input type="text" name="checklist_date" id="checklist_date" value="<?php echo ($checklist_date=='') ? $cur_date : $checklist_date ?>"></input>
		<br />
		<br />
		<br />
		<b>Morning</b><br /><br />
		
		<?php 
			$post_meta_mornings = array(
				'open_windows' => 'Open windows',
				'fill_revitiliser' => 'Fill/Turn on Revitiliser'
				);
		
		 foreach ($post_meta_mornings as $key => $value) {
			?>

			<div class="meta_field">

			<input type="checkbox" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo $post_meta[$key][0]; ?>" <?php if(!empty( $post_meta[$key][0] == 'checked')) {echo "checked";} ?> ></input>
			<label for="<?php echo $key; ?>"><?php echo $value; ?></label>
			<?php 
			$task_done = ($post_meta[$key][0] == 'checked') ? true : false;
			staff_list($key,$task_done);
			?> 
		    <br/>

			</div>
			<?php
		}  ?>

		<br />
		<br />
	    <b>After Lunch/ During Rest Time</b><br /><br />
	    <?php 
			$post_meta_after_lunchs =  array(
				'room_centre' => 'Room Centre Task (Hallway/Stairs, Staff Bathrooms, Washing)',
				'bath_clean' => 'Childrens Bathroom clean and restock',
				'sink_area' => 'Sink area and benches are clear of clutter',
				'jif_sink' => 'Clean and Jif sink areas and bench, including drink station',
				'clean_cups' => 'Restock and Clean room cups',
				'clean_fridge' => 'Clean Microwave and fridge',
				'replace_spongs' => 'Mon- Replace Sponges',
				'refill_apple' => 'Refill apple detergent and spray bottles if needed',
				'refill_stock' => 'Refill Room stock (Soap, Handtowel etc)',
				'windows_cleaned' => 'Windows/ Doors are cleaned',
				'kickboards_cleaned' => 'Window sills and kickboards cleaned and dusted',
				'restock_indoor' => 'Restock Indoor/Outdoor hygiene tubs'				
				);
		foreach ($post_meta_after_lunchs as $key => $value) {
			?>

			<div class="meta_field">
			<input type="checkbox" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo $post_meta[$key][0]; ?>" <?php if(!empty( $post_meta[$key][0])) {echo "checked";} ?> ></input>
			<label for="<?php echo $key; ?>"><?php echo $value; ?></label> 
			<?php 
			$task_done = ($post_meta[$key][0] == 'checked') ? true : false;
			staff_list($key,$task_done);
			?>
			
		    <br/>
			</div>
			<?php
		}
		?>
		<br />
		<br />
		
	    <b>Afternoon</b><br /><br />
	    <?php 
		$post_meta_afternoon = array(
				'clean_toys' => 'Clean mouthed toys',
				'remove_laundry' => 'Remove any dirty laundry and put in the wash',
				'disinfect_door' => 'Clean and disinfect door hand handles',
				'vacuum_carpet' => 'Vacuum and mop carpet and other floor areas',
				'reset_invitingly' => 'Reset room invitingly',
				'child_bath' => 'Childrens Bathroom and restock',
				'pack_yards' => 'Pack-Up Yards/ umberellas down',
				'close_windows' => 'Close windows',
				'used_hats' => 'Bring used hats to laundry',
				'turn_revitilisers' => 'Turn off revitilisers (Wash WED/FRI)',
				'outdoor_benches' => 'Outdoor benches are free from clutter',
				'windowsonly_cleaned' => 'Windows cleaned',
				'dirty_hats'=>'Children\'s dirty hats to be taken to the laundry'
			);

		foreach ($post_meta_afternoon as $key => $value) {
			?>

			<div class="meta_field">
			<input type="checkbox" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo $post_meta[$key][0]; ?>" <?php echo ($post_meta[$key][0]) ? "checked" : ''; ?> ></input>
			<label for="<?php echo $key; ?>"><?php echo $value; ?></label>
			<?php 
			$task_done = ($post_meta[$key][0] == 'checked') ? true : false;
			staff_list($key,$task_done);
			?>
		    <br/>
			</div>
			<?php
		}
		?>
		<br /><br />

		<b>Yards</b><br /><br />
		
		<div class="meta_field yards">
			<input type="checkbox" name="yard_check" id="yard_check" <?php echo ($post_meta['yard_check'][0]) ? "checked" : ''; ?>></input>
			<label for="yard_check">AM Yard Check</label>
			<?php 
			$task_done = ($post_meta['yard_check'][0] == 'checked') ? true : false;
			staff_list('yard_check',$task_done);
			
			?>
			<label >Comment</label><input type="text" name="yard_comment_am" value="<?php echo $post_meta['yard_comment_am'][0]; ?>" >
			<button class="add_yardam_button page-title-action hidden">Add New</button>
			
			<div class="yardsam hidden">

			<?php $counter = 0; ?>
				<?php while ($counter<10): ?>
					<?php if (isset(${'yardam' . $counter})): ?>
						<div class="yardam">
			    			<input type="hidden" class="yardam-counter" value="<?php echo $counter ?>">
							<b><label for="yardam<?php echo $counter ?>">Check <?php echo (int)$counter+1 . ": "; ?></label></b>
			    			<input type="text" name="yardam<?php echo $counter ?>" id="yardam<?php echo $counter ?>" value="<?php echo ${'yardam' . $counter} ? ${'yardam' . $counter} : ''; ?>" ></input>
		    			</div>
					<?php endif; ?>
					<?php $counter++; ?>
				<?php endwhile; ?>
			</div>
		</div>
		<div class="meta_field">
			<input type="checkbox" name="reset_yard" id="reset_yard" <?php echo ($post_meta['reset_yard'][0]) ? "checked" : ''; ?>></input>
	    <label for="reset_yard">Sweep/Reset yard</label>

	    <?php 
			$task_done = ($post_meta['reset_yard'][0] == 'checked') ? true : false;
			staff_list('reset_yard',$task_done);
			?>
	    <br/>
		</div>
		<div class="meta_field yards">

	    	<input type="checkbox" name="pm_yard" id="pm_yard" <?php echo ($post_meta['pm_yard'][0]) ? "checked" : ''; ?>></input>
			<label for="pm_yard">PM Yard Check</label>
			<?php 
			$task_done = ($post_meta['pm_yard'][0] == 'checked') ? true : false;
			staff_list('pm_yard',$task_done);
			?>
			<label >Comment</label><input type="text" name="yard_comment_pm" value="<?php echo $post_meta['yard_comment_pm'][0]; ?>" >
			<button class="add_yardpm_button page-title-action hidden">Add New</button>
			
			<div class="yardspm hidden">

			<?php $counter = 0; ?>
				<?php while ($counter<10): ?>
					<?php if (isset(${'yardpm' . $counter})): ?>
						<div class="yardpm">
			    			<input type="hidden" class="yardpm-counter" value="<?php echo $counter ?>">
							<b><label for="yardpm<?php echo $counter ?>">Check <?php echo (int)$counter+1 . ": "; ?></label></b>
			    			<input type="text" name="yardpm<?php echo $counter ?>" id="yardpm<?php echo $counter ?>" value="<?php echo ${'yardpm' . $counter} ? ${'yardpm' . $counter} : ''; ?>" ></input>
		    			</div>
					<?php endif; ?>
					<?php $counter++; ?>
				<?php endwhile; ?>
			</div>

		</div>
		<?php 
		$post_meta_yards = array(
			'yard_check' => 'AM Yard Check',
			'reset_yard' => 'Sweep/Reset yard',
			'pm_yard' => 'PM Yard Check'
			);
		?>



		<?php
    }

    function child_meta() {

	    global $post;
	    // Noncename needed to verify where the data originated
	    echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' .
	    wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

	    //$staff_members = get_users('orderby=meta_value&meta_key=first_name&role=report_editor');
	    $students = get_users('orderby=meta_value&meta_key=first_name&role=student');
	    $rooms = get_posts(array('post_type'=>'rooms', 'posts_per_page'=>-1, 'numberposts'=>-1));

	    $checklist_date = get_post_meta($post->ID, 'info_checklist_date', true);
	    // $checklist_date = date("d/m/Y", strtotime($checklist_date));
	    $cur_staff_members = get_post_meta($post->ID, 'staff_members', true);
	    $cur_students = get_post_meta($post->ID, 'info_students', true);


	    // @start Functionality to allow only selected educators can edit in child checklist can edit current child checklist,
	    // get current post author 
	    $post_author_id = get_post_field( 'post_author', $post->ID );
	    // message for users who cannot edit the childchecklist
	    if (in_array(get_current_user_id(), $cur_staff_members) || get_current_user_id() == $post_author_id || current_user_can( 'administrator' )) {
	    	echo "";
	    } else {
	    	?>
	    	<style type="text/css">
	    		#submitdiv {
	    			display: none !important;
	    		}
	    		#side-sortables {
	    			border: none;
	    		}
	    	</style>

	    	<?php
	    	echo '<h1 style="color:red;">You are not allowed to edit this Child checklist</h1><br>';
	    }

	    //end
	    
	    $cur_room = (int)get_post_meta($post->ID, 'info_room', true);
	   

	    $cur_date = date('d/m/Y');

		$post_meta = get_post_meta($post->ID);

	    $sleep1 = unserialize($post_meta['sleep1'][0]);
	    $sleep2 = unserialize($post_meta['sleep2'][0]);
	    $sleep3 = unserialize($post_meta['sleep3'][0]);
	    $bottles = unserialize($post_meta['bottles'][0]);
	    $sunscreen = unserialize($post_meta['sunscreen'][0]);
	    $comments = unserialize($post_meta['comments'][0]);

	    $cattendance = unserialize($post_meta['cattendance'][0]);
		$cbreakfast = unserialize($post_meta['cbreakfast'][0]);
		$cbreakfast_t = unserialize($post_meta['cbreakfast_t'][0]);
		$cmorning = unserialize($post_meta['cmorning'][0]);
		$clunch = unserialize($post_meta['clunch'][0]);
		$cafternoon = unserialize($post_meta['cafternoon'][0]);
		$sleeps = unserialize($post_meta['sleeps'][0]);

		$clunchbowl = unserialize($post_meta['clunchbowl'][0]);

		$counter = 0;
		foreach ($post_meta as $param_name => $param_val) {
	    	if (startsWith($param_name, 'nappies') || startsWith($param_name, 'gnappies')) {
	    		${$param_name}  = unserialize($param_val[0]);
	    	}
	    }


	    ?>

	    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
		<script type="text/javascript">

		jQuery(document).ready(function() {
		(function ($) {

		  $('#info_checklist_date').datepicker({ dateFormat: 'dd/mm/yy' });

		  })(jQuery);
		});
		</script>

		<?php
		// get center Id from cuurent room id;
		
		$center_id = get_post_meta($post->ID, 'center_id', true );
		$cur_room_center = get_post_meta( $cur_room, 'center_id', true );
		
		if (isset($center_id)) {
			if (!empty($center_id) && ($center_id == $cur_room_center) ) {
				?><input type="hidden" name="center_id" value="<?php echo $center_id; ?>"> <?php
			} else {
				?><input type="hidden" name="center_id" value="<?php echo $cur_room_center; ?>"> <?php
			}
			?>
			
			<?php
		} else {
				?>
				<script type="text/javascript">
					var ajaxurl = '<?php echo admin_url("admin-ajax.php"); ?>';

					jQuery(function($) {
					jQuery('body').on('change', '#info_room', function() {
						var center = $(this).val();
						if(center != '') {
							var data = {
								action: 'get_center_by_room',
								center: center
							}

							jQuery.post(ajaxurl, data, function(response) {
								$('#center_id').html(response);
							});
						}
					});
					});
				</script>
				<div id="center_id" style="display: none;"></div>

				<?php
		}

		?>
		


	    <b><label for="info_room" class="student_label">Room</label></b>
		<select data-placeholder="Choose room..." style="width: 350px;" class="chosen-select" name="info_room" id="info_room">
			<option <?php echo (empty($cur_room)) ? 'selected' : ''; ?>>Select Room</option>
			<?php
				foreach ($rooms as $room) {
					$selected = ($cur_room == $room->ID) ? 'selected' : '';
					$room_options .= "<option value=\"{$room->ID}\" {$selected}> {$room->post_title} </option>\n";
				}

				if ($room_options){
					echo $room_options;
				}
			?>
		</select>
		<br /><br />

		<b><label for="info_checklist_date">Date</label></b>
	    <input type="text" name="info_checklist_date" id="info_checklist_date" value="<?php echo ($checklist_date=='') ? $cur_date : $checklist_date ?>"></input>
		<br /><br />
		<?php  staff_list(); ?>
		
		<br /><br />
		<?php get_children_list(); ?>
		
		<br /><br /><br />


		<?php

		function get_child_fields() {
			?>

			<script type="text/javascript" >
			jQuery(document).ready(function() {
			(function ($) {


			$('#info_students').on('change', function() {

		  		var ids = $(this).val();
		  		console.log(ids);

		        var data = {
					action: 'get_student_fields',
					post_id: '<?php echo get_the_ID(); ?>',
					student_ids: ids
				};

				// с версии 2.8 'ajaxurl' всегда определен в админке
				jQuery.post( ajaxurl, data, function(response) {
					$('#child_fields').html(response.result);
				});


		  	});

		  	})(jQuery);
			});					
			</script>
			

			<?php
		}
		?>

		<div id="child_fields">

		<?php if (is_array($cur_students)): ?>
			<?php foreach ($cur_students as $student_id): ?>
				<?php

				$id = (int)($student_id);
				$student_meta  = get_user_meta($student_id);
				$student_name  = esc_html(implode(' ', array($student_meta['first_name'][0], $student_meta['last_name'][0])));
				$student_name .= esc_html($centre_name);

				?>

				<?php //$current_nappies0 = $nappies0[$id]; ?>
				<?php //$current_nappies1 = $nappies1[$id]; ?>

				<?php

				$counter = 0;

				foreach ($post_meta as $param_name => $param_val) {
			    	if (startsWith($param_name, 'nappies')) {
			    		${"current_" . $param_name}  = ${$param_name}[$id];
			    	}
			    }

				?>
				<?php $current_attendance = $cattendance[$id]; ?>
				<?php $current_cbreakfast = $cbreakfast[$id] ?>
				<?php $current_cmorning = $cmorning[$id] ?>
				<?php $current_clunch = $clunch[$id] ?>
				<?php $current_cafternoon = $cafternoon[$id] ?>
				<div id="student-<?php echo $id ?>" class="postbox closed" >
					<button type="button" class="handlediv" aria-expanded="false"><span class="screen-reader-text">Toggle panel: <b style="font-size: 18px"><?php echo $student_name ?></b><br /><br /></span><span class="toggle-indicator" aria-hidden="false"></span></button><h2 class="hndle ui-sortable-handle"><span><b style="font-size: 18px"><?php echo $student_name ?></b></span></h2>
					<div class="inside">
						<div class="att">
						<label>Attendenace <span style="font-size: 10px; ">(Tick the box if the child is absent.)</span>  : </label><input type="checkbox" id="cattendance-<?php echo $id ?>" name="cattendance[<?php echo $id ?>]" value="a" <?php echo ($current_attendance=='a') ? "checked" : ''; ?>>
						<br>
						<?php echo ($current_attendance=='a') ? '<p style="font-size: 25px; color:red; ">ABSENT</p>' : ''; ?>
						<br>
					</div>
					<div <?php echo ($current_attendance=='a') ? "class='hide'" : ''; ?>>
				<div class="top">
					
					
					<div class="cbreakfast info_left">
						<b><label for="cbreakfast-<?php echo $id ?>">Breakfast</label><span><input type="text" name="cbreakfast_t[<?php echo $id ?>]" value="<?php echo $cbreakfast_t[$id]; ?>"></span></b><br /><br/>
						<label for="cbreakfast-<?php echo $id ?>">Taste</label>
						<input type="radio" name="cbreakfast[<?php echo $id ?>]" id="cbreakfast-<?php echo $id ?>" value="taste" <?php echo ($current_cbreakfast=='taste') ? "checked" : ''; ?>></input>
						<label for="cbreakfast-<?php echo $id ?>">Half</label>
		    			<input type="radio" name="cbreakfast[<?php echo $id ?>]" id="cbreakfast-<?php echo $id ?>" value="half" <?php echo ($current_cbreakfast=='half') ? "checked" : ''; ?>></input>
						<label for="cbreakfast-<?php echo $id ?>">All</label>
		    			<input type="radio" name="cbreakfast[<?php echo $id ?>]" id="cbreakfast-<?php echo $id ?>" value="all" <?php echo ($current_cbreakfast=='all') ? "checked" : ''; ?>></input>
		    			<label for="cbreakfast-<?php echo $id ?>">N/A</label>
		    			<input type="radio" name="cbreakfast[<?php echo $id ?>]" id="cbreakfast-<?php echo $id ?>" value="na" <?php echo ($current_cbreakfast=='na') ? "checked" : ''; ?>></input>
		    			<br/>
					</div>
					<div class="cmorning info_left">
							<b><label for="cmorning-<?php echo $id ?>">Morning Tea</label></b><br /><br/>
							<label for="cmorning-<?php echo $id ?>">Taste</label>
							<input type="radio" name="cmorning[<?php echo $id ?>]" id="cmorning-<?php echo $id ?>" value="taste" <?php echo ($current_cmorning=='taste') ? "checked" : ''; ?>></input>
							<label for="cmorning-<?php echo $id ?>">Half</label>
			    			<input type="radio" name="cmorning[<?php echo $id ?>]" id="cmorning-<?php echo $id ?>" value="half" <?php echo ($current_cmorning=='half') ? "checked" : ''; ?>></input>
							<label for="cmorning-<?php echo $id ?>">All</label>
			    			<input type="radio" name="cmorning[<?php echo $id ?>]" id="cmorning-<?php echo $id ?>" value="all" <?php echo ($current_cmorning=='all') ? "checked" : ''; ?>></input>
			    			<label for="cmorning-<?php echo $id ?>">N/A</label>
			    			<input type="radio" name="cmorning[<?php echo $id ?>]" id="cmorning-<?php echo $id ?>" value="na" <?php echo ($current_cmorning=='na') ? "checked" : ''; ?>></input>
			    			<br/>
					</div>
					<div class="clunch info_left">
							<b><label for="cbreakfast-<?php echo $id ?>">Lunch   (</label></b>
			    			<input type="checkbox" name="clunchbowl[<?php echo $id ?>]" id="clunchbowl-<?php echo $id ?>" <?php echo ($clunchbowl[$id]) ? "checked" : ''; ?>></input>
							<b><label for="clunchbowl-<?php echo $id ?>">more than 1 bowl )</label></b><br><br>
							<label for="clunch-<?php echo $id ?>">Taste</label>
							<input type="radio" name="clunch[<?php echo $id ?>]" id="clunch-<?php echo $id ?>" value="taste" <?php echo ($current_clunch=='taste') ? "checked" : ''; ?>></input>
							<label for="clunch-<?php echo $id ?>">Half</label>
			    			<input type="radio" name="clunch[<?php echo $id ?>]" id="clunch-<?php echo $id ?>" value="half" <?php echo ($current_clunch=='half') ? "checked" : ''; ?>></input>
							<label for="clunch-<?php echo $id ?>">All</label>
			    			<input type="radio" name="clunch[<?php echo $id ?>]" id="clunch-<?php echo $id ?>" value="all" <?php echo ($current_clunch=='all') ? "checked" : ''; ?>></input>
			    			<label for="clunch-<?php echo $id ?>">N/A</label>
			    			<input type="radio" name="clunch[<?php echo $id ?>]" id="clunch-<?php echo $id ?>" value="na" <?php echo ($current_clunch=='na') ? "checked" : ''; ?>></input>
			    			<br/>
					</div>
					<div class="clear"></div>
					<div class="cafternoon" style="margin-top: 0; padding-top: 10px;">
						<br>
							<b><label for="cafternoon-<?php echo $id ?>">Afternoon Tea</label></b><br /><br/>

							<label for="cafternoon-<?php echo $id ?>">Taste</label>
							<input type="radio" name="cafternoon[<?php echo $id ?>]" id="cafternoon-<?php echo $id ?>" value="taste" <?php echo ($current_cafternoon=='taste') ? "checked" : ''; ?>></input>
							<label for="cafternoon-<?php echo $id ?>">Half</label>
			    			<input type="radio" name="cafternoon[<?php echo $id ?>]" id="cafternoon-<?php echo $id ?>" value="half" <?php echo ($current_cafternoon=='half') ? "checked" : ''; ?>></input>
							<label for="cafternoon-<?php echo $id ?>">All</label>
			    			<input type="radio" name="cafternoon[<?php echo $id ?>]" id="cafternoon-<?php echo $id ?>" value="all" <?php echo ($current_cafternoon=='all') ? "checked" : ''; ?>></input>
			    			<label for="cafternoon-<?php echo $id ?>">N/A</label>
			    			<input type="radio" name="cafternoon[<?php echo $id ?>]" id="cafternoon-<?php echo $id ?>" value="na" <?php echo ($current_cafternoon=='na') ? "checked" : ''; ?>></input>
			    			<br/>
					</div>
				</div>

				<br />

				<div class="bottom">

					<div class="info_left">
						<b><label for="sleep1-<?php echo $id ?>">Sleep1</label></b><br /><br />
						<input type="text" name="sleep1[<?php echo $id ?>]" id="sleep1-<?php echo $id ?>" value="<?php echo $sleep1[$id] ? $sleep1[$id] : ''; ?>"></input><br />
		    			<br/>
					</div>

					<div class="info_left">
						<b><label for="sleep2-<?php echo $id ?>">Sleep2</label></b><br /><br />
						<input type="text" name="sleep2[<?php echo $id ?>]" id="sleep2-<?php echo $id ?>" value="<?php echo $sleep2[$id] ? $sleep2[$id] : ''; ?>"></input><br />
		    			<br/>
					</div>

					<div class="info_left">
						<b><label for="sleep3-<?php echo $id ?>">Sleep3</label></b><br /><br />
						<input type="text" name="sleep3[<?php echo $id ?>]" id="sleep3-<?php echo $id ?>" value="<?php echo $sleep3[$id] ? $sleep3[$id] : ''; ?>"></input><br />
		    			<br/>
					</div>

					<div class="info_left">
						<b><label for="bottles-<?php echo $id ?>">Bottles</label></b><br /><br />
						<input type="text" name="bottles[<?php echo $id ?>]" id="bottles-<?php echo $id ?>" value="<?php echo $bottles[$id] ? $bottles[$id] : ''; ?>"></input><br />
		    			<br/>
					</div>
					<div class="info_left">
			    		<b><label for="sunscreen-<?php echo $id ?>">Sunscreen times</label></b><br /><br />
						<input type="text" name="sunscreen[<?php echo $id ?>]" id="sunscreen-<?php echo $id ?>" value="<?php echo $sunscreen[$id] ? $sunscreen[$id] : ''; ?>"></input><br />
			    		<br/>
		    		</div>
		    		<div >
		    			<b><label for="comments-<?php echo $id ?>">Comments</label></b><br /><br />
						<textarea name="comments[<?php echo $id ?>]" id="comments-<?php echo $id ?>"><?php echo $comments[$id] ? $comments[$id] : ''; ?></textarea><br />
		    			<br/>
		    		</div>

	    		</div>
				<div class="nappies-wrapper">
				<b class="nappies-title"><span>Nappies</span></b>
				<button class="add_field_button page-title-action">Add New</button>
    			<input type="hidden" class="nappies-id" value="<?php echo $id ?>">
				<?php $counter = 0; ?>
				<?php while ($counter<10): ?>
					<?php if (isset(${'nappies' . $counter})): ?>
		    		<div class="nappies">
		    			<input type="hidden" class="nappies-counter" value="<?php echo $counter ?>">
						<input type="text" name="gnappies<?php echo $counter ?>[<?php echo $id ?>]" id="gnappies<?php echo $counter ?>-<?php echo $id ?>" value="<?php echo ${'gnappies' . $counter}[$id] ? ${'gnappies' . $counter}[$id] : ''; ?>"></input><br />
						<label for="nappies<?php echo $counter ?>-<?php echo $id ?>">Dry</label>
						<input type="radio" name="nappies<?php echo $counter ?>[<?php echo $id ?>]" id="nappies<?php echo $counter ?>-<?php echo $id ?>" value="dry" <?php echo (${'current_nappies' . $counter}=='dry') ? "checked" : ''; ?>></input>
						<label for="nappies<?php echo $counter ?>-<?php echo $id ?>">Wet</label>
		    			<input type="radio" name="nappies<?php echo $counter ?>[<?php echo $id ?>]" id="nappies<?php echo $counter ?>-<?php echo $id ?>" value="wet" <?php echo (${'current_nappies' . $counter}=='wet') ? "checked" : ''; ?>></input>
						<label for="nappies<?php echo $counter ?>-<?php echo $id ?>">Soiled</label>
		    			<input type="radio" name="nappies<?php echo $counter ?>[<?php echo $id ?>]" id="nappies<?php echo $counter ?>-<?php echo $id ?>" value="soiled" <?php echo (${'current_nappies' . $counter}=='soiled') ? "checked" : ''; ?>></input>
		    			<br/>
					</div>

					<?php endif; ?>
					<?php $counter++; ?>
				<?php endwhile; ?>
				</div>
			</div>
					</div>
	    	</div>


			<?php endforeach; ?>
		<?php endif; ?>
		</div>
		<hr>
		<br />
		<div class="breakfast">
			<!-- <div class="info_left">
				<b><label for="breakfast">Breakfast</label></b><br /><br />
				<textarea name="breakfast" id="breakfast"><?php //echo ($post_meta['breakfast'][0]) ? $post_meta['breakfast'][0] : ''; ?></textarea>
		    </div> -->
		    <div class="info_left">
				<b><label for="morning_tea">Morning Tea</label></b><br /><br />
				<textarea name="morning_tea" id="morning_tea"><?php echo ($post_meta['morning_tea'][0]) ? $post_meta['morning_tea'][0] : ''; ?></textarea>
		    </div>
		    <div class="info_left">
				<b><label for="lunch">Lunch</label></b><br /><br />
				<textarea name="lunch" id="lunch"><?php echo ($post_meta['lunch'][0]) ? $post_meta['lunch'][0] : ''; ?></textarea>
		    </div>
		    <div>
		    	<b><label for="afternoon_tea">Afternon Tea</label></b><br /><br />
				<textarea name="afternoon_tea" id="afternoon_tea"><?php echo ($post_meta['afternoon_tea'][0]) ? $post_meta['afternoon_tea'][0] : ''; ?></textarea>
		    </div>
	    </div>
		<br />

	    <?php
    }

    function my_meta_save($post_id, $post) {

	    if ( !wp_verify_nonce( $_POST['eventmeta_noncename'], plugin_basename(__FILE__) )) {
	    	return $post->ID;
	    }
	   
	   
	    // Is the user allowed to edit the post or page?
	    if ( !current_user_can( 'edit_post', $post->ID ))
	        return $post->ID;

		if (get_post_type($post_id) == 'room_checklists') {

			// check fields morning
		    $events_meta['checklist_date'] = $_POST['checklist_date'];
    	    $events_meta['staff_member'] = $_POST['staff_member'];
    	    $events_meta['room'] = $_POST['room'];
			$events_meta['center_id'] = $_POST['center_id'];



    	    // check fields after lunch
    	    $events_meta['open_windows'] = $_POST['open_windows'];
		    $events_meta['fill_revitiliser'] = $_POST['fill_revitiliser'];
		    $events_meta['room_centre'] = $_POST['room_centre'];
		    $events_meta['bath_clean'] = $_POST['bath_clean'];
		    $events_meta['sink_area'] = $_POST['sink_area'];
		    $events_meta['jif_sink'] = $_POST['jif_sink'];
		    $events_meta['clean_cups'] = $_POST['clean_cups'];
		    $events_meta['clean_fridge'] = $_POST['clean_fridge'];
		    $events_meta['replace_spongs'] = $_POST['replace_spongs'];
		    $events_meta['refill_apple'] = $_POST['refill_apple'];
		    $events_meta['refill_stock'] = $_POST['refill_stock'];
		    $events_meta['windows_cleaned'] = $_POST['windows_cleaned'];
		    $events_meta['windowsonly_cleaned'] = $_POST['windowsonly_cleaned'];
		    $events_meta['kickboards_cleaned'] = $_POST['kickboards_cleaned'];
		    $events_meta['restock_indoor'] = $_POST['restock_indoor'];
		    // staff linking
		    $user_task_meta['open_windows'] = $_POST['open_windows_assign_id'];
		    $user_task_meta['fill_revitiliser'] = $_POST['fill_revitiliser_assign_id'];
		    $user_task_meta['room_centre'] = $_POST['room_centre_assign_id'];
		    $user_task_meta['bath_clean'] = $_POST['bath_clean_assign_id'];
		    $user_task_meta['sink_area'] = $_POST['sink_area_assign_id'];
		    $user_task_meta['jif_sink'] = $_POST['jif_sink_assign_id'];
		    $user_task_meta['clean_cups'] = $_POST['clean_cups_assign_id'];
		    $user_task_meta['clean_fridge'] = $_POST['clean_fridge_assign_id'];
		    $user_task_meta['replace_spongs'] = $_POST['replace_spongs_assign_id'];
		    $user_task_meta['refill_apple'] = $_POST['refill_apple_assign_id'];
		    $user_task_meta['refill_stock'] = $_POST['refill_stock_assign_id'];
		    $user_task_meta['windows_cleaned'] = $_POST['windows_cleaned_assign_id'];
		    $user_task_meta['windowsonly_cleaned'] = $_POST['windowsonly_cleaned_assign_id'];
		    $user_task_meta['kickboards_cleaned'] = $_POST['kickboards_cleaned_assign_id'];
		    $user_task_meta['restock_indoor'] = $_POST['restock_indoor_assign_id'];

		    // checklist of afternoon 
		    $events_meta['clean_toys'] = $_POST['clean_toys'];
		    $events_meta['remove_laundry'] = $_POST['remove_laundry'];
		    $events_meta['disinfect_door'] = $_POST['disinfect_door'];
		    $events_meta['vacuum_carpet'] = $_POST['vacuum_carpet'];
		    $events_meta['reset_invitingly'] = $_POST['reset_invitingly'];
		    $events_meta['child_bath'] = $_POST['child_bath'];
		    $events_meta['pack_yards'] = $_POST['pack_yards'];
		    $events_meta['close_windows'] = $_POST['close_windows'];
		    $events_meta['used_hats'] = $_POST['used_hats'];
		    $events_meta['turn_revitilisers'] = $_POST['turn_revitilisers'];
		    $events_meta['outdoor_benches'] = $_POST['outdoor_benches'];
		    $events_meta['dirty_hats'] = $_POST['dirty_hats'];

		    // checklist of afternoon  staff listing id
		    $user_task_meta['clean_toys'] = $_POST['clean_toys_assign_id'];
		    $user_task_meta['remove_laundry'] = $_POST['remove_laundry_assign_id'];
		    $user_task_meta['disinfect_door'] = $_POST['disinfect_door_assign_id'];
		    $user_task_meta['vacuum_carpet'] = $_POST['vacuum_carpet_assign_id'];
		    $user_task_meta['reset_invitingly'] = $_POST['reset_invitingly_assign_id'];
		    $user_task_meta['child_bath'] = $_POST['child_bath_assign_id'];
		    $user_task_meta['pack_yards'] = $_POST['pack_yards_assign_id'];
		    $user_task_meta['close_windows'] = $_POST['close_windows_assign_id'];
		    $user_task_meta['used_hats'] = $_POST['used_hats_assign_id'];
		    $user_task_meta['turn_revitilisers'] = $_POST['turn_revitilisers_assign_id'];
		    $user_task_meta['outdoor_benches'] = $_POST['outdoor_benches_assign_id'];
		    $user_task_meta['dirty_hats'] = $_POST['dirty_hats_assign_id'];

		    // checklist of yards 
		    $events_meta['yard_check'] = $_POST['yard_check'];
		    $events_meta['yard_comment_am'] = $_POST['yard_comment_am'];
		    $events_meta['reset_yard'] = $_POST['reset_yard'];
		    $events_meta['pm_yard'] = $_POST['pm_yard'];
		    $events_meta['yard_comment_pm'] = $_POST['yard_comment_pm'];
		    // checklist of yards staff listing id
		    $user_task_meta['yard_check'] = $_POST['yard_check_assign_id'];
		    $user_task_meta['reset_yard'] = $_POST['reset_yard_assign_id'];
		    $user_task_meta['pm_yard'] = $_POST['pm_yard_assign_id'];

		    
		    
		    foreach ($_POST as $param_name => $param_val) {
		    	
		    	if (startsWith($param_name, 'yardam') || startsWith($param_name, 'yardpm')) {
		    		$events_meta[$param_name] = $_POST[$param_name];
		    	}
		    	 
		    }
		  
		} elseif (get_post_type($post_id) == 'child_checklists') {
			global $wpdb;
			// checking users can edit post . if not allowded then redirect to current post page
			$cur_staff_members = get_post_meta($post_id, 'staff_members', true);
	    	$post_author_id = get_post_field( 'post_author', $post_id );
	    	// message for users who cannot edit the childchecklist
	    	if (in_array(get_current_user_id(), $cur_staff_members) || get_current_user_id() == $post_author_id || current_user_can( 'administrator' )) 
	    	{
	    		echo "";
	  		} else {
	    		 $location = $_SERVER['HTTP_REFERER'];
			        wp_safe_redirect($location);
			        exit();
	    	}

	    	$events_meta['center_id'] = $_POST['center_id'];
			$events_meta['info_checklist_date'] = $_POST['info_checklist_date'];
	    	$events_meta['staff_members'] = $_POST['staff_members'];
	    	$events_meta['info_room'] = $_POST['info_room'];
	    	$events_meta['info_students'] = $_POST['info_students'];

	    	$events_meta['breakfast'] = $_POST['breakfast'];
		    $events_meta['morning_tea'] = $_POST['morning_tea'];
		    $events_meta['lunch'] = $_POST['lunch'];
		    $events_meta['afternoon_tea'] = $_POST['afternoon_tea'];

		    $events_meta['bottles'] = array($_POST['bottles'])[0];
		    $events_meta['sunscreen'] = array($_POST['sunscreen'])[0];
		    $events_meta['comments'] = array($_POST['comments'])[0];

		    // $events_meta['nappies0'] = array($_POST['nappies0'])[0];
		    // $events_meta['nappies1'] = array($_POST['nappies1'])[0];
		    
		    $events_meta['cattendance'] = array($_POST['cattendance'])[0];
		    $events_meta['cbreakfast'] = array($_POST['cbreakfast'])[0];
		    $events_meta['cbreakfast_t'] = array($_POST['cbreakfast_t'])[0];
		    $events_meta['cmorning'] = array($_POST['cmorning'])[0];
		    $events_meta['clunch'] = array($_POST['clunch'])[0];
		    $events_meta['cafternoon'] = array($_POST['cafternoon'])[0];
		    $events_meta['sleep1'] = array($_POST['sleep1'])[0];
		    $events_meta['sleep2'] = array($_POST['sleep2'])[0];
		    $events_meta['sleep3'] = array($_POST['sleep3'])[0];

		    $events_meta['clunchbowl'] = array($_POST['clunchbowl'])[0];
		    // $events_meta['gnappies0'] = array($_POST['gnappies0'])[0];
		    // $events_meta['gnappies1'] = array($_POST['gnappies1'])[0];
		    foreach ($_POST as $param_name => $param_val) {
		    	if (startsWith($param_name, 'nappies') || startsWith($param_name, 'gnappies')) {
		    		$events_meta[$param_name] = array($_POST[$param_name])[0];
		    	}
		    }

		    $www = array_reverse($events_meta);
		}
		
		

	    foreach ($events_meta as $key => $value) { // Cycle through the $events_meta array!
	        // if( $post->post_type == 'checklists' ) return; // Don't store custom data twice
	        $val = ($value=='checked') ? 'checked' : '';

	        if ($value == 'checked') {
	        	$val = true;
	        } elseif ($value == null) {
	        	$val = false;
	        } else {
	        	$val = $value;
	        }
	        
	        if(get_post_meta($post->ID, $key, FALSE)) { // If the custom field already has a value

	        	
	           update_post_meta($post->ID, $key, $val);
	        } else { // If the custom field doesn't have a value
	            add_post_meta($post->ID, $key, $val);
	        }
	        if(!$val) delete_post_meta($post->ID, $key); // Delete if blank
	    } 

	    // users tasks meta upoad

	    foreach ($user_task_meta as $key => $value) { 
	    	// add only if the $value have any staff id.
	    	global $wpdb; 
	    	
	    	$staff_id = get_staff_id_from_metavalue($key);
	    		
	    	if (($staff_id[0]['meta_key'] == 'task_uid_'.$value) && !empty($value)) {
	    		
	    		 delete_post_meta($post->ID,$staff_id[0]['meta_key']);
	    		 
	    		 add_post_meta($post->ID, 'task_uid_'.$value, $key);
	    	} elseif(!empty($value)) {
	    		add_post_meta($post->ID, 'task_uid_'.$value, $key);
	    	}
	    	
	    	
	    	
	        
	       
	        
	    }

	}

	function startsWith($haystack, $needle) {
    	$length = strlen($needle);
    	return (substr($haystack, 0, $length) === $needle);
	}

	function get_student_fields() {

		$post_id = $_POST['post_id'];
		$students = $_POST['student_ids'];
		$result = '';

		$post_meta = get_post_meta($post_id);

		// $nappies0 = unserialize($post_meta['nappies'][0]);
		// $nappies1 = unserialize($post_meta['nappies1'][0]);
		$cattendance = unserialize($post_meta['cattendance'][0]);
		$cbreakfast = unserialize($post_meta['cbreakfast'][0]);
		$cbreakfast_t = unserialize($post_meta['cbreakfast_t'][0]);

		$cmorning = unserialize($post_meta['cmorning'][0]);
		$clunch = unserialize($post_meta['clunch'][0]);
		$cafternoon = unserialize($post_meta['cafternoon'][0]);
		$sleeps = unserialize($post_meta['sleeps'][0]);

		$clunchbowl = unserialize($post_meta['clunchbowl'][0]);

		// $gnappies0 = unserialize($post_meta['gnappies'][0]);
		// $gnappies1 = unserialize($post_meta['gnappies1'][0]);

		$counter = 0;
		foreach ($post_meta as $param_name => $param_val) {
	    	if (startsWith($param_name, 'nappies') || startsWith($param_name, 'gnappies')) {
	    		${$param_name}  = unserialize($post_meta[$param_name][0]);
	    	}
	    }

		foreach ($students as $student_id) {
			$id = (int)($student_id);
			$student_meta  = get_user_meta($student_id);
			$student_name  = esc_html(implode(' ', array($student_meta['first_name'][0], $student_meta['last_name'][0])));
			$student_name .= esc_html($centre_name);

			// $current_nappies0 = $nappies0[$id];
			// $current_nappies1 = $nappies1[$id];

			$counter = 0;
			foreach ($post_meta as $param_name => $param_val) {
		    	if (startsWith($param_name, 'nappies')) {
		    		${"current_" . $param_name}  = ${$param_name}[$id];
		    	}
		    }
		    $current_attendance = $cattendance[$id];
			$current_cbreakfast = $cbreakfast[$id];
			$current_cmorning = $cmorning[$id];
			$current_clunch = $clunch[$id];
			$current_cafternoon = $cafternoon[$id];

			$www = $clunchbowl;

			$div = '<button type="button" class="handlediv" aria-expanded="false"><span class="screen-reader-text">Toggle panel: <b style="font-size: 18px">' . $student_name . '</b><br /><br /></span><span class="toggle-indicator" aria-hidden="false"></span></button><h2 class="hndle ui-sortable-handle"><span><b style="font-size: 18px">' . $student_name . '</b></span></h2>
					<div class="inside">
						<div class="att">
						<label>Attendenace <span style="font-size: 10px; ">(Tick the box if the child is absent.)</span>  : </label><input type="checkbox" id="cattendance-'.$id.'" name="cattendance['. $id .']" value="a" >
						<br>
					
						<br>
					</div>
				<div>
				<div class="top">
					
					<div class="cbreakfast info_left">
						<b><label for="cbreakfast-' . $id . '">Breakfast</label><span><input type="text" name="cbreakfast_t[<?php echo $id ?>]" value="'.$cbreakfast_t[$id].'"></span></b><br /><br/>
						<label for="cbreakfast-' . $id. '">Taste</label>
						<input type="radio" name="cbreakfast[' . $id . ']" id="cbreakfast-' . $id . '" value="taste"' . ($current_cbreakfast=='taste' ? "checked" : '') .'></input>
						<label for="cbreakfast-'. $id .'">Half</label>
		    			<input type="radio" name="cbreakfast[' . $id . ']" id="cbreakfast-' . $id .'" value="half"' . ($current_cbreakfast=='half' ? "checked" : '') .'></input>
						<label for="cbreakfast-' . $id .'">All</label>
		    			<input type="radio" name="cbreakfast[' . $id .']" id="cbreakfast-' . $id .'" value="all"' . ($current_cbreakfast=='all' ? "checked" : '') .'></input>
		    			<label for="cbreakfast-' . $id .'">N/A</label>
		    			<input type="radio" name="cbreakfast[' . $id .']" id="cbreakfast-' . $id .'" value="na"' . ($current_cbreakfast=='na' ? "checked" : '') .'></input>
		    			<br/>
					</div>
					<div class="cmorning info_left">
						<b><label for="cmorning-' . $id . '">Morning tea</label></b><br /><br/>
						<label for="cmorning-' . $id. '">Taste</label>
						<input type="radio" name="cmorning[' . $id . ']" id="cmorning-' . $id . '" value="taste"' . ($current_cmorning=='taste' ? "checked" : '') .'></input>
						<label for="cmorning-'. $id .'">Half</label>
		    			<input type="radio" name="cmorning[' . $id . ']" id="cmorning-' . $id .'" value="half"' . ($current_cmorning=='half' ? "checked" : '') .'></input>
						<label for="cmorning-' . $id .'">All</label>
		    			<input type="radio" name="cmorning[' . $id .']" id="cmorning-' . $id .'" value="all"' . ($current_cmorning=='all' ? "checked" : '') .'></input>
		    			<label for="cmorning-' . $id .'">N/A</label>
		    			<input type="radio" name="cmorning[' . $id .']" id="cmorning-' . $id .'" value="na"' . ($current_cmorning=='na' ? "checked" : '') .'></input>
		    			<br/>
					</div>
					<div class="clunch info_left">
						<b><label for="cbreakfast-' . $id . '">Lunch   (</label></b>
		    			<input type="checkbox" name="clunchbowl[' . $id . ']" id="clunchbowl-' . $id . '"' . ($clunchbowl[$id] ? "checked" : '') . '></input>
						<b><label for="clunchbowl-' . $id . '">more than 1 bowl )</label></b><br><br>
						<label for="clunch-' . $id . '">Taste</label>
						<input type="radio" name="clunch[' . $id . ']" id="clunch-' . $id . '" value="taste"' . ($current_clunch=='taste' ? "checked" : '') .'></input>
						<label for="clunch-'. $id .'">Half</label>
		    			<input type="radio" name="clunch[' . $id . ']" id="clunch-' . $id .'" value="half"' . ($current_clunch=='half' ? "checked" : '') .'></input>
						<label for="clunch-' . $id .'">All</label>
		    			<input type="radio" name="clunch[' . $id .']" id="clunch-' . $id .'" value="all"' . ($current_clunch=='all' ? "checked" : '') .'></input>
		    			<label for="clunch-' . $id .'">N/A</label>
		    			<input type="radio" name="clunch[' . $id .']" id="clunch-' . $id .'" value="na"' . ($current_clunch=='na' ? "checked" : '') .'></input>
		    			<br/>
					</div>
					<div class="cafternoon" style="margin-top: 0; padding-top: 10px;">
						<b><label for="cafternoon-' . $id . '">Afternon tea</label></b><br /><br/>
						<label for="cafternoon-' . $id. '">Taste</label>
						<input type="radio" name="cafternoon[' . $id . ']" id="cafternoon-' . $id . '" value="taste"' . ($current_cafternoon=='taste' ? "checked" : '') .'></input>
						<label for="cafternoon-'. $id .'">Half</label>
		    			<input type="radio" name="cafternoon[' . $id . ']" id="cafternoon-' . $id .'" value="half"' . ($current_cafternoon=='half' ? "checked" : '') .'></input>
						<label for="cafternoon-' . $id .'">All</label>
		    			<input type="radio" name="cafternoon[' . $id .']" id="cafternoon-' . $id .'" value="all"' . ($current_cafternoon=='all' ? "checked" : '') .'></input>
		    			<label for="cafternoon-' . $id .'">N/A</label>
		    			<input type="radio" name="cafternoon[' . $id .']" id="cafternoon-' . $id .'" value="na"' . ($current_cafternoon=='na' ? "checked" : '') .'></input>
		    			<br/>
					</div>
				</div>

					<br />
				<div class="bottom">

					<div class="info_left">
						<b><label for="sleep1-' . $id . '">Sleep1</label></b><br /><br />
						<input type="text" name="sleep1['. $id . ']" id="sleep1-' . $id .'" value="' . ($sleep1[$id] ? $sleep1[$id] : '') . '"></input><br />
		    			<br/>
					</div>
					<div class="info_left">
						<b><label for="sleep2-' . $id . '">Sleep2</label></b><br /><br />
						<input type="text" name="sleep2['. $id . ']" id="sleep2-' . $id .'" value="' . ($sleep2[$id] ? $sleep2[$id] : '') . '"></input><br />
		    			<br/>
					</div>
					<div class="info_left">
						<b><label for="sleep3-' . $id . '">Sleep3</label></b><br /><br />
						<input type="text" name="sleep3['. $id . ']" id="sleep3-' . $id .'" value="' . ($sleep3[$id] ? $sleep3[$id] : '') . '"></input><br />
		    			<br/>
					</div>
					<div class="info_left">
						<b><label for="bottles-' . $id . '">Bottles</label></b><br /><br />
						<input type="text" name="bottles['. $id . ']" id="bottles-' . $id .'" value="' . ($bottles[$id] ? $bottles[$id] : '') . '"></input><br />
		    			<br/>
					</div>
					<div class="info_left">
						<b><label for="sunscreen-' . $id . '">Sunscreen times</label></b><br /><br />
						<input type="text" name="sunscreen['. $id . ']" id="sunscreen-' . $id .'" value="' . ($sunscreen[$id] ? $sunscreen[$id] : '') . '"></input><br />
		    			<br/>
					</div>
					<div>
		    			<b><label for="comments-' . $id . '">Comments</label></b><br /><br />
						<textarea name="comments[' . $id . ']" id="comments-' . $id . '">' . ($comments[$id] ? $comments[$id] : '') . '</textarea><br />
		    			<br/>
		    		</div>

				</div>';

				$div .= '<div class="nappies-wrapper"><b><span class="nappies-title">Nappies</span></b><button class="add_field_button page-title-action">Add New</button><input type="hidden" class="nappies-id" value="' . $id .'">';

				$counter = 0;
				while ($counter < 10) {
				if (isset(${'nappies' . $counter})) {

			    $div .= '<div class="nappies">
			    	<input type="hidden" class="nappies-counter" value="' . $counter .'">
					<input type="text" name="gnappies' . $counter .'['. $id . ']" id="gnappies' . $counter .'-' . $id .'" value="' . (${'gnappies' . $counter}[$id] ? ${'gnappies' . $counter}[$id] : '') . '"></input><br />
					<label for="nappies' . $counter . '-' . $id . '">Dry</label>
					<input type="radio" name="nappies' . $counter . '[' . $id . ']" id="nappies' . $counter . '-' . $id . '" value="dry"' . (${'current_nappies' . $counter}=='dry' ? "checked" : '') . '></input>
					<label for="nappies' . $counter . '-' . $id . '">Wet</label>
					<input type="radio" name="nappies' . $counter . '[' . $id . ']" id="nappies' . $counter . '-' . $id . '" value="wet"' . (${'current_nappies' . $counter}=='wet' ? "checked" : '') . '></input>
					<label for="nappies' . $counter .'-' . $id . '">Soiled</label>
	    			<input type="radio" name="nappies' . $counter . '[' . $id . ']" id="nappies' . $counter . '-' . $id . '" value="soiled"' . (${'current_nappies' . $counter}=='soiled' ? "checked" : '') . '></input>
	    			<br/>
				</div>';
					}
				$counter++;

			    }

			$div .=	'</div></div><br />
				<hr><br />
			';
			$result .= $div;
		}

		wp_send_json(array('result' => $result));
	}

	function modify_post_title( $data ) {
		$room = (empty($_POST['info_room'])) ? $_POST['room'] : $_POST['info_room'];
		$date = (empty($_POST['info_checklist_date'])) ? $_POST['checklist_date'] : $_POST['info_checklist_date'];

		$author = wp_get_current_user();
		$author_name = get_the_author_meta('display_name', $author->ID);

		if ( $data['post_type'] == 'room_checklists' || $data['post_type'] == 'child_checklists') {
			$data['post_title'] = get_the_title($room) . " (" . $date . ") - " . $author_name;
		}

		return $data; // Returns the modified data.
	}

	add_action('save_post','my_meta_save', 10, 2);
	add_filter( 'wp_insert_post_data' , 'modify_post_title' , '99', 1 );
}

add_action('init', 'registerPostTypes');
add_action('admin_init','checklistMetaInit');
add_action('admin_print_footer_scripts', 'get_child_fields', 99);
add_action('wp_ajax_get_student_fields', 'get_student_fields');


add_action('wp_ajax_get_center_by_room', 'get_center_by_room_callback');
add_action('wp_ajax_nopriv_get_center_by_room', 'get_center_by_room_callback');



function get_center_by_room_callback() {
	$room = $_POST['center'];
	$center_id = get_post_meta( $room, 'center_id', true );
	?>

	<input type="hidden" name="center_id" value="<?php echo $center_id ;  ?>">
	<?php
}


