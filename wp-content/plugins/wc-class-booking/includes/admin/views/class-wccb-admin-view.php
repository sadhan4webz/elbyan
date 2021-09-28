<?php
defined( 'ABSPATH' ) || die();
class WCCB_Admin_View {

	public static function wccb_course_tab_product_tab_content() {
		global $post;
		$exist_tutor_ids = get_post_meta($post->ID , 'tutor_ids' , ture );
		$args   = array(
			'role__in' => array('wccb_tutor')
		);
		$tutors = get_users( $args );
		?>
		<div id='wccb_course_options' class='panel woocommerce_options_panel'>
		 	<div class='options_group'>
		 		<p class="form-field">
		        	<label for="tutor_label"><?php _e( 'Select Tutors', WC_CLASS_BOOKING_TEXT_DOMAIN ); ?></label>
			        <select class="wc-tutor-search" multiple="multiple" style="width: 50%;" id="tutor_ids" name="tutor_ids[]" data-placeholder="<?php esc_attr_e( 'Search for one or more tutors&hellip;', WC_CLASS_BOOKING_TEXT_DOMAIN ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="">
			        	<?php
			        	foreach ($tutors as $row) {
			        		?>
			        		<option value="<?php echo $row->ID;?>" <?php if(in_array($row->ID , $exist_tutor_ids)){?> selected="selected" <?php }?>><?php echo $row->display_name;?></option>
			        		<?php
			        	}
			        	?>
			        </select>
		    	</p>
		 		
		 	</div>
		 </div>
		<?php
	}

	public static function wccb_course_general_tab_custom_fields() {
		global $post;
		$course_type      = get_post_meta($post->ID , 'course_type' , ture );
		$course_quantity  = get_post_meta($post->ID , 'course_quantity' , ture );
		?>
		<p class="form-field">
        	<label for="course_type"><?php _e( 'Course Type', WC_CLASS_BOOKING_TEXT_DOMAIN ); ?></label>
	        <select style="width: 50%;" id="course_type" name="course_type">
	        	<option value="custom" <?php selected($course_type , 'custom');?>>Custom</option>
	        	<option value="fixed"  <?php selected($course_type , 'fixed');?>>Fixed</option>
	        </select>
    	</p>
    	<p class="form-field" id="course_quantity_wrapper" style="display: <?php echo $course_type == 'fixed' ? 'block' : 'none';?>;">
    		<label for="course_quantity"><?php _e( 'Quantity', WC_CLASS_BOOKING_TEXT_DOMAIN ); ?></label>
    		<input type="text" name="course_quantity" id="course_quantity" value="<?php echo !empty($course_quantity) ? $course_quantity : '';?>">
    	</p>
		<?php
	}

}