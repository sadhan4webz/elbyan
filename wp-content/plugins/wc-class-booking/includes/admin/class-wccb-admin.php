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
		add_action( 'woocommerce_product_data_panels' , array( 'WCCB_Admin_View' , 'wccb_package_tab_product_tab_content') );
		add_action( 'woocommerce_process_product_meta' , array( $this , 'save_tutor_for_package' ) );

		//Filters

		add_filter( 'product_type_selector', array( $this , 'add_custom_product_type') );
		add_filter( 'woocommerce_product_data_tabs', array( $this , 'package_product_tab') );
		add_filter( 'gettext', array( $this , 'change_backend_product_regular_price_label' ) , 100, 3 );
		add_filter( 'woocommerce_order_item_display_meta_key', array( $this , 'change_order_item_meta_key') , 20, 3 );
		add_filter( 'woocommerce_order_item_display_meta_value', array( $this , 'change_order_item_meta_value' ) , 20, 3 );
	}

	public function add_custom_product_type( $types ) {
	    $types[ 'wccb_package' ] = __( 'Package' , PLUGIN_TEXT_DOMAIN );
	    return $types;
	}

	public function package_product_tab( $tabs) {

	    $tabs['wccb_package'] = array(
	      'label'	 => __( 'Package Options', PLUGIN_TEXT_DOMAIN ),
	      'target' => 'wccb_package_options',
	      'class'  => array('show_if_wccb_product', 'hide_if_simple', 'hide_if_grouped', 'hide_if_external' , 'hide_if_variable'),
	     );
	    //$tabs['general']['class']          = 'show_if_wccb_package';
	    $tabs['inventory']['class'][]      = 'hide_if_wccb_package';
	    $tabs['shipping']['class'][]       = 'hide_if_wccb_package';
	    $tabs['linked_product']['class'][] = 'hide_if_wccb_package';
	    $tabs['attribute']['class'][]      = 'hide_if_wccb_package';
	    $tabs['variations']['class'][]     = 'hide_if_wccb_package';
	    $tabs['advanced']['class'][]       = 'hide_if_wccb_package';


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

	public function save_tutor_for_package( $post_id ) {
		// save tutor field
	    $tutor_ids = $_POST['tutor_ids'];
	    update_post_meta( $post_id, 'tutor_ids', $tutor_ids );
	}

	public function change_order_item_meta_key( $display_key, $meta, $item ) {

	    // Change display label for specific order item meta key
	    if( is_admin() && $item->get_type() === 'line_item' && $meta->key === 'tutor_id' ) {
	        $display_key = __( 'Selected Tutor' , PLUGIN_TEXT_DOMAIN );
	    }

	    return $display_key;
	}

	public function change_order_item_meta_value( $value , $meta , $item ) {
		
		//Change display value for specific order item meta key value
    	if( is_admin() && $meta->key === 'tutor_id' && $item->get_type() === 'line_item' ) {
    		$tutor = get_userdata($value);
    		$value = __( $tutor->display_name , PLUGIN_TEXT_DOMAIN );
    	}

		return $value;
	}

}