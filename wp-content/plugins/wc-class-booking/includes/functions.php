<?php
function register_product_type() {
	class WC_Product_WCCB_Package extends WC_Product {
		
		public function __construct( $product ) {
			$this->product_type = 'wccb_package';
			parent::__construct($product);
		}

		public function add_to_cart_url() {
		    $url = $this->is_purchasable() && $this->is_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );

		    return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
		}
	}
}
add_action( 'init', 'register_product_type' );
?>