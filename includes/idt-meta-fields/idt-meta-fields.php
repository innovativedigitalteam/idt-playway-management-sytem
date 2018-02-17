<?php

//@center meta box

function idt_center_meta_boxes( $post ) {
    add_meta_box('center_name',__( 'Center Name' ),'idt_render_centers','center','normal','high'
    );
}

  add_action( 'add_meta_boxes', 'idt_center_meta_boxes' );

function idt_render_centers($post)
{
   	global $post;

	$center_name =  get_post_meta( $post->ID,'center_name', true );
	?>
	<div>
		<label>Country</label>
			<select id="country-list">
				<option>Select Country</option>
			</select>
	</div>
	<div>
		<label>Country</label>
			<select id="province-list">
				<option>Select Province</option>
			</select>
	</div>

	<?php

}

function idt_save_center_meta($post_id){

    if( isset( $_REQUEST['center_name'] ) ){
        update_post_meta( $post_id, 'center_name',wp_kses_post($_POST['center_name'])   );
    }
}


add_action( 'save_post', 'idt_save_center_meta' );