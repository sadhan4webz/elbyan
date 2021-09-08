<?php
defined( 'ABSPATH' ) || die();
class WCCB_Admin_View {

	public static function wccb_package_tab_product_tab_content() {
		global $post;
		$exist_tutor_ids = get_post_meta($post->ID , 'tutor_ids' , ture );
		$args   = array(
			'role__in' => array('wccb_tutor')
		);
		$tutors = get_users( $args );
		?>
		<div id='wccb_package_options' class='panel woocommerce_options_panel'>
		 	<div class='options_group'>
		 		<p class="form-field">
		        	<label for="tutor_label"><?php _e( 'Select Tutors', PLUGIN_TEXT_DOMAIN ); ?></label>
			        <select class="wc-tutor-search" multiple="multiple" style="width: 50%;" id="tutor_ids" name="tutor_ids[]" data-placeholder="<?php esc_attr_e( 'Search for one or more tutors&hellip;', PLUGIN_TEXT_DOMAIN ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="">
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

}