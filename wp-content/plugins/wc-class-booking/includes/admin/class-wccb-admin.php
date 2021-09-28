<?php
defined( 'ABSPATH' ) || die();
class WCCB_Admin {

	/**
	 * Init class.
	 */
	public function init() {
		$this->register_hooks();
	}

	/**
	 * Hook into WordPress.
	 */
	private function register_hooks() {
		
		//Actions
		add_action( 'woocommerce_product_data_panels' , array( 'WCCB_Admin_View' , 'wccb_course_tab_product_tab_content') );
		add_action( 'woocommerce_process_product_meta' , array( $this , 'save_custom_field_data' ) );

		add_action( 'woocommerce_after_order_itemmeta', array( $this , 'display_admin_order_item_custom_button'), 10, 3 );
		add_action( 'woocommerce_product_options_general_product_data', array( 'WCCB_Admin_View' , 'wccb_course_general_tab_custom_fields'));

		//Filters

		add_filter( 'product_type_selector', array( $this , 'add_custom_product_type') );
		add_filter( 'woocommerce_product_data_tabs', array( $this , 'package_product_tab') );
		add_filter( 'gettext', array( $this , 'change_backend_product_regular_price_label' ) , 100, 3 );
		add_filter( 'woocommerce_order_item_display_meta_key', array( $this , 'change_order_item_meta_key') , 20, 3 );
		add_filter( 'woocommerce_order_item_display_meta_value', array( $this , 'change_order_item_meta_value' ) , 20, 3 );
	}

	public function add_custom_product_type( $types ) {
	    $types[ 'wccb_course' ] = __( 'Course' , WC_CLASS_BOOKING_TEXT_DOMAIN );
	    return $types;
	}

	public function package_product_tab( $tabs) {

	    $tabs['wccb_course'] = array(
	      'label'	 => __( 'Course Options', WC_CLASS_BOOKING_TEXT_DOMAIN ),
	      'target' => 'wccb_course_options',
	      'class'  => array('show_if_wccb_product', 'hide_if_simple', 'hide_if_grouped', 'hide_if_external' , 'hide_if_variable'),
	     );
	    //$tabs['general']['class']          = 'show_if_wccb_course';
	    $tabs['inventory']['class'][]      = 'hide_if_wccb_course';
	    $tabs['shipping']['class'][]       = 'hide_if_wccb_course';
	    $tabs['linked_product']['class'][] = 'hide_if_wccb_course';
	    $tabs['attribute']['class'][]      = 'hide_if_wccb_course';
	    $tabs['variations']['class'][]     = 'hide_if_wccb_course';
	    $tabs['advanced']['class'][]       = 'hide_if_wccb_course';


	    //echo '<pre>';
	    //print_r($tabs);
	    //echo'</pre>';

	    return $tabs;
	}

	public function change_backend_product_regular_price_label( $translated_text , $text , $domain ) {
		global $pagenow, $post_type;

	    if ( is_admin() && in_array( $pagenow, ['post.php', 'post-new.php'] ) && 'product' === $post_type && 'Regular price' === $text  && 'woocommerce' === $domain )
	    {
	        $translated_text =  __( 'Hourly Price', $domain );
	    }

	    return $translated_text;
	}

	public function save_custom_field_data( $post_id ) {
		$product = wc_get_product($post_id);

		// save tutor field
	    $product->update_meta_data('tutor_ids', sanitize_text_field($_POST['tutor_ids']));

	    // save course type
	    $product->update_meta_data('course_type', sanitize_text_field($_POST['course_type']));

	    // save course quantity
	    if (!empty($_POST['course_quantity'])) {
	    	$product->update_meta_data('course_quantity', sanitize_text_field($_POST['course_quantity']));
	    }

	    $product->save();
	}

	public function change_order_item_meta_key( $display_key, $meta, $item ) {

	    // Change display label for specific order item meta key
	    if( is_admin() && $item->get_type() === 'line_item' && $meta->key === '_tutor_id' ) {
	        $display_key = __( 'Selected Tutor' , WC_CLASS_BOOKING_TEXT_DOMAIN );
	    }

	    return $display_key;
	}

	public function change_order_item_meta_value( $value , $meta , $item ) {
		
		//Change display value for specific order item meta key value
    	if( is_admin() && $meta->key === '_tutor_id' && $item->get_type() === 'line_item' ) {
    		$tutor = get_userdata($value);
    		$value = __( $tutor->display_name , WC_CLASS_BOOKING_TEXT_DOMAIN );
    	}

		return $value;
	}

	function display_admin_order_item_custom_button( $item_id, $item, $product ){
	    // Only "line" items and backend order pages
	    if( ! ( is_admin() && $item->is_type('line_item') ) )
	        return;

	    $booking_slots = $item->get_meta('booking_slots'); // Get custom item meta data (array)

	    if( ! empty($booking_slots) ) {
	        $num_slots = 0;
			foreach ($booking_slots as $key => $value) {
				$num_slots += count($value);
			}
	        echo '<p><b>Number of slots :</b> '.$num_slots.'</p>';
	    }
	}

}