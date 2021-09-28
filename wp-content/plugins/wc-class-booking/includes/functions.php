<?php
function register_product_type() {
	class WC_Product_WCCB_Course extends WC_Product {
		
		public function __construct( $product ) {
			$this->product_type = 'wccb_course';
			parent::__construct($product);
		}

		public function add_to_cart_url() {
		    $url = $this->is_purchasable() && $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );

		    return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
		}
	}
}
add_action( 'init', 'register_product_type' );

function wccb_user_get_display_name( $user ){
	$display_name = null;
	if( $user->ID ){
		$db_display_name= $user->display_name;
		$display_name 	= ( $db_display_name == '' OR
                        $db_display_name == NULL )
						? $user->first_name . ' ' . $user->last_name
						: $db_display_name;
		$display_name 	= trim( $display_name );
	}
	return $display_name;
}
?>