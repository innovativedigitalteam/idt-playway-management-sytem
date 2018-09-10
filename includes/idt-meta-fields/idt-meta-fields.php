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

	?>
	<input type="hidden" id="country" name="country" value="<?php echo get_post_meta( $post->ID,'country',true ); ?>">
	<input type="hidden" id="state" name="state" value="<?php echo get_post_meta( $post->ID,'state',true ); ?>">
	<input type="hidden" id="city" name="city" value="<?php echo get_post_meta( $post->ID,'city',true ); ?>">
	<div>
		<label>Country</label>
			<select name="center_country" id="country-list">
				<option>Select Country</option>
			</select>
	</div>
	<div>
		<label>Province</label>
			<select name="center_province" id="province-list">
				<option>Select Province</option>
			</select>
	</div>
	<div>
		<label>Cities</label>
			<select name="center_city" id="cities-list">
				<option>Select City</option>
			</select>
	</div>
	<?php

}

function idt_save_center_meta($post_id){

    if( isset( $_REQUEST) ){
        update_post_meta( $post_id, 'center_country',wp_kses_post($_POST['center_country'])   );
        update_post_meta( $post_id, 'center_province',wp_kses_post($_POST['center_province'])   );
        update_post_meta( $post_id, 'center_city',wp_kses_post($_POST['center_city'])   );
        update_post_meta( $post_id, 'country',wp_kses_post($_POST['country'])   );
        update_post_meta( $post_id, 'state',wp_kses_post($_POST['state'])   );
        update_post_meta( $post_id, 'city',wp_kses_post($_POST['city'])   );
    }
}


add_action( 'save_post', 'idt_save_center_meta' );