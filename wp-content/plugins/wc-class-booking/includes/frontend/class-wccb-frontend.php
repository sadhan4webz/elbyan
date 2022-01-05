<?php
defined( 'ABSPATH' ) || die();
class WCCB_Frontend {

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

		//Loader html
		add_action( 'wp_footer', array( 'WCCB_Frontend_View', 'render_loader_html' ) );

		//Register form
		add_action ( 'woocommerce_register_form' , array( 'WCCB_Frontend_View' , 'render_register_form_fields' ) );
		add_action ( 'woocommerce_register_post', array( $this , 'validate_register_field') , 10, 3 );
		add_action ( 'woocommerce_created_customer', array( $this , 'save_register_fields') , 10, 1 );
		

		//Shop page
		add_action( 'woocommerce_shop_loop_item_title' , array( 'WCCB_Frontend_View' , 'shop_page_product_title') , 10 );
		add_action( 'woocommerce_after_shop_loop_item_title' , array( 'WCCB_Frontend_View' , 'shop_page_product_description' ) , 5 );
		add_action( 'woocommerce_after_shop_loop_item_title' , array( 'WCCB_Frontend_view' , 'shop_page_product_hour' ) , 10 );
		add_action( 'woocommerce_after_shop_loop_item_title' , array( 'WCCB_Frontend_view' , 'shop_page_product_price' ) , 15 );


		//Product detail page
		add_action( 'init' , array( $this , 'remove_woocommerce_actions' ) );

		add_action( 'woocommerce_before_single_product', array( 'WCCB_Frontend_View' , 'product_detail_page_form_start' ), 10 );

		add_action( 'woocommerce_before_single_product_summary', function(){echo '<div class="left_column_wrapper">';}, 10 );
		add_action( 'woocommerce_before_single_product_summary', 'woocommerce_template_single_title', 20 );
		add_action( 'woocommerce_before_single_product_summary', array( 'WCCB_Frontend_View' , 'show_product_description' ), 25 );
		add_action( 'woocommerce_before_single_product_summary', function(){echo '</div>';}, 30 );

		add_action( 'woocommerce_single_product_summary', array( 'WCCB_Frontend_View' , 'show_product_price' ), 10 );
		add_action( 'woocommerce_single_product_summary', array( 'WCCB_Frontend_View' , 'wccb_course_add_to_cart' ), 60 );

		add_action( 'woocommerce_after_single_product_summary', array( $this , 'get_tutor_profile' ), 20 );
		add_action( 'woocommerce_after_single_product_summary', array( 'WCCB_Frontend_View' , 'render_tutor_availability_container'), 25 );

		add_action( 'woocommerce_after_single_product' , function(){echo '</form>';} , 10 );

		//Checkout page
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this , 'create_order_line_item' ) , 10, 4 );
		add_action( 'woocommerce_checkout_process' , array( $this , 'checkout_page_validation') );
		add_action( 'woocommerce_checkout_terms_and_conditions' , array( 'WCCB_Frontend_View' , 'display_refund_policy_checkbox') );

		add_action( 'woocommerce_thankyou' , array( $this , 'update_booking_and_hour_history') , 10 , 1 );

		//Worldpay IPN handler
		add_action( 'valid-wpform-request', array( $this, 'worldpay_ipn_handler' ) , 100 , 1 );

		// Old way, Worldpay IPN handler
		add_action( 'valid-worldpay-request', array( $this, 'worldpay_ipn_handler_old_way' ) );


		//Filters

		//* Allow to override WC /templates path
		add_filter( 'wc_get_template' , array( $this , 'get_wccb_woocommerce_template' ), 10, 5 );
		add_filter( 'wc_get_template_part' , array( $this , 'get_wccb_woocommerce_template_part' ), 10, 3 );

		//Shop page
		add_filter( 'woocommerce_show_page_title' , function(){ return false;} );
		add_filter( 'woocommerce_loop_add_to_cart_link' , array( 'WCCB_Frontend_View' , 'shop_page_add_to_cart_button' ), 10, 3 );

		//Empty cart before adding product
		add_filter( 'woocommerce_add_cart_item_data' , array( $this , 'wccb_empty_cart') );

		//Product detail page
		add_filter( 'woocommerce_add_to_cart_validation' , array( $this , 'tutor_booking_add_to_cart_validation'), 10, 4 );

		//Cart page
		add_filter( 'woocommerce_add_cart_item_data' , array( $this , 'add_cart_item_data'), 10, 3 );
		add_filter( 'woocommerce_get_item_data' , array( $this , 'get_cart_item_data') , 10, 2 );

		add_filter( 'woocommerce_order_item_name' , array( $this , 'order_item_name' ), 10, 2 );


		//Shortcodes
		add_shortcode( 'show-header-button' , array( 'WCCB_Frontend_View' , 'show_header_button' ) );
	}

	public static function get_price_page_link() {
		return get_permalink('9');
	}

	public static function get_myaccount_page_link() {
		return get_permalink('96');
	}

	public static function get_tutor_future_booking( $tutor_id ) {
		if (empty($tutor_id)) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix.'booking_history';

		$query         = "SELECT * FROM $table_name WHERE tutor_id='".$_REQUEST['tutor_id']."' and class_date >= '".date('Y-m-d')."' and status = 'Upcoming'";
		$results       = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
		foreach ($results as $key => $value) {
			$slot_booked_array[] = $value['class_date'].'|'.$value['class_time'];
		}

		return $slot_booked_array;
	}

	public function remove_woocommerce_actions() {

		//Shop
		remove_action( 'woocommerce_before_shop_loop_item' , 'woocommerce_template_loop_product_link_open' , 10 );

		remove_action( 'woocommerce_before_shop_loop' , 'woocommerce_result_count' , 20 );
		remove_action( 'woocommerce_before_shop_loop' , 'woocommerce_catalog_ordering' , 30 );

		remove_action( 'woocommerce_before_shop_loop_item_title' , 'woocommerce_template_loop_product_thumbnail' , 10 );

		remove_action( 'woocommerce_shop_loop_item_title' , 'woocommerce_template_loop_product_title' , 10 );

		remove_action( 'woocommerce_after_shop_loop_item_title' , 'woocommerce_template_loop_price' , 10 );
		remove_action( 'woocommerce_after_shop_loop_item' , 'woocommerce_template_loop_product_link_close' , 5 );

		//Product detail
		remove_action( 'woocommerce_before_single_product_summary' , 'woocommerce_show_product_images' , 20 );

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

		remove_action( 'woocommerce_after_single_product_summary' , 'woocommerce_output_product_data_tabs' , 10 );
		remove_action( 'woocommerce_after_single_product_summary' , 'woocommerce_upsell_display' , 15 );
		remove_action( 'woocommerce_after_single_product_summary' , 'woocommerce_output_related_products' , 20 );
	}

	public static function get_editable_roles() {
	    global $wp_roles;

	    $all_roles      = $wp_roles->roles;
	    $editable_roles = apply_filters('editable_roles', $all_roles);
	    return $editable_roles;
	}

	public function get_wccb_woocommerce_template( $located, $template_name, $args, $template_path, $default_path ) {
		$newtpl = str_replace( 'woocommerce/templates', basename( WC_CLASS_BOOKING_PLUGIN_DIR ) . '/woocommerce', $located );
		
		if ( file_exists( $newtpl ) )
			return $newtpl;

		return $located;
	}

	public function get_wccb_woocommerce_template_part( $template, $slug, $name ) {
		$newtpl = str_replace( 'woocommerce/templates',basename( WC_CLASS_BOOKING_PLUGIN_DIR ) . '/woocommerce', $template );
		
		if ( file_exists( $newtpl ) )
			return $newtpl;

		return $template;
	}

	public function wccb_empty_cart( $cart_item_data ) {
		WC()->cart->empty_cart();

		return $cart_item_data;
	}

	public static function get_avilability_times_from_post( $field_array ) {
		$availability_times = array();

		foreach (WCCB_Helper::get_weekdays_array() as $key => $value) {
			$lower_key = strtolower($key);
			$availability_times[$lower_key]['is_unavailable'] =  $field_array[$lower_key.'_is_unavailable'];

			if (!empty($field_array[$lower_key.'_start_time'])) {
				$temp_start_time = $field_array[$lower_key.'_start_time'];
				$temp_end_time   = $field_array[$lower_key.'_end_time'];

				foreach ($field_array[$lower_key.'_start_time'] as $key2 => $value2 ) {
					if ($temp_start_time[$key2] < $temp_end_time[$key2] ) {
						$time_flag = 1;

						if (!empty($availability_times[$lower_key]['available_time'])) {
							foreach ( $availability_times[$lower_key]['available_time'] as $key3 => $value3) {
								if ($temp_start_time[$key2] == $value3['start_time'] && $temp_end_time[$key2] == $value3['end_time']) {
									$time_flag = 0;
								}
								if ($temp_start_time[$key2] >= $value3['end_time']) {
									$time_flag = 1;
								}
								else {
									$time_flag = 0;
								}
							}
						}
						
						if ($time_flag) {
							$availability_times[$lower_key]['available_time'][] = array(
								'start_time' => $temp_start_time[$key2], 
								'end_time'   => $temp_end_time[$key2]
							);
						}
					}
				}
			}
		}

		return $availability_times;
	}

	public function validate_register_field( $username , $email , $errors ) {
		if (empty($_POST['mobile_no'])) {
			$errors->add( 'mobile_no_error' , __('Mobile no. is required field' , WC_CLASS_BOOKING_TEXT_DOMAIN) );
		}
		if (!empty($_POST['mobile_no']) && strlen($_POST['mobile_no']) > 14  ) {
			$errors->add( 'mobile_no_error' , __('Mobile no. should not be more than 14 digit' , WC_CLASS_BOOKING_TEXT_DOMAIN) );
		}
		if (empty($_POST['first_name'])) {
			$errors->add( 'first_name_error' , __('First name is required field' , WC_CLASS_BOOKING_TEXT_DOMAIN) );
		}
		if (empty($_POST['last_name'])) {
			$errors->add( 'last_name_error' , __('Last name is required field',WC_CLASS_BOOKING_TEXT_DOMAIN) );
		}
		if (empty($_POST['gender'])) {
			$errors->add( 'gender_error' , __('Gender is required field' , WC_CLASS_BOOKING_TEXT_DOMAIN) );
		}
		if (empty($_POST['user_role'])) {
			$errors->add( 'user_role_error' , __('Registration type is required field' , WC_CLASS_BOOKING_TEXT_DOMAIN) );
		}

		if ($_POST['user_role'] == 'wccb_tutor' ) {
			$field_array        = $_POST;
			$availability_times = array();

			foreach (WCCB_Helper::get_weekdays_array() as $key => $value) {
				$lower_key = strtolower($key);
				$availability_times[$lower_key]['is_unavailable'] =  $field_array[$lower_key.'_is_unavailable'];

				if (!empty($field_array[$lower_key.'_start_time'])) {
					$temp_start_time = $field_array[$lower_key.'_start_time'];
					$temp_end_time   = $field_array[$lower_key.'_end_time'];

					foreach ($field_array[$lower_key.'_start_time'] as $key2 => $value2 ) {
						if ($temp_start_time[$key2] < $temp_end_time[$key2] ) {
							$time_flag = 1;

							if (!empty($availability_times[$lower_key]['available_time'])) {
								foreach ( $availability_times[$lower_key]['available_time'] as $key3 => $value3) {
									if ($temp_start_time[$key2] == $value3['start_time'] && $temp_end_time[$key2] == $value3['end_time']) {
										$time_flag = 0;
										$errors->add( 'availability_time_error' , __('Start time and end time is same for '.$lower_key , WC_CLASS_BOOKING_TEXT_DOMAIN) );
									}
									if ($temp_start_time[$key2] >= $value3['end_time']) {
										$time_flag = 1;
									}
									else {
										$time_flag = 0;
										$errors->add( 'availability_time_error' , __('Start time and end time is not properly set for '.$lower_key , WC_CLASS_BOOKING_TEXT_DOMAIN) );
									}
								}
							}
							
						}
						else {
							$errors->add( 'availability_time_error' , __('Start time is greater than end time for '.$lower_key , WC_CLASS_BOOKING_TEXT_DOMAIN) );
						}
					}
				}
			}
		}
	}

	public function save_register_fields( $customer_id ) {
		if (!empty($_POST['mobile_no'])) {
			update_user_meta( $customer_id , 'mobile_no', $_POST['mobile_no']);
		}
		if (!empty($_POST['first_name'])) {
			update_user_meta( $customer_id , 'first_name', $_POST['first_name']);
		}
		if (!empty($_POST['last_name'])) {
			update_user_meta( $customer_id , 'last_name' , $_POST['last_name']);
		}
		if (!empty($_POST['gender'])) {
			update_user_meta( $customer_id , 'gender' , $_POST['gender']);
		}
		if (!empty($_POST['user_role'])) {
			wp_update_user( array( 'ID' => $customer_id, 'role' => $_POST['user_role'] ) );
		}

		if ($_POST['user_role'] == 'wccb_tutor' ) {
			$availability_times = WCCB_Frontend::get_avilability_times_from_post($_POST);
			update_user_meta( $customer_id , 'availability' , $availability_times );
		}
	}

	public static function get_tutor_profile() {
		echo WCCB_Frontend_View::show_tutor_profile();
	}

	public static function get_date_wise_slots( $slots ) {
		$date_wise_slot = array();

		if (!empty($slots)) {
			foreach ($slots  as $key => $value) {
				$slot_date_time = explode( '|' , $value);
				if (empty($date_wise_slot[$slot_date_time[0]])) {
					$date_wise_slot[$slot_date_time[0]] = array($slot_date_time[1]);
				}
				else {
					if (!in_array($slot_date_time[1], $date_wise_slot[$slot_date_time[0]])) {
						array_push($date_wise_slot[$slot_date_time[0]], $slot_date_time[1]);
					}
				}
			}
		}

		return $date_wise_slot;
	}

	public static function date_wise_slot_availability_validation( $tutor_id , $slot_date , $slot_time ) {
		global $wpdb;
		$passed            = true;
		$table_name        = $wpdb->prefix.'booking_history';
		$request_slot_time = explode( '-' , $slot_time);
		$request_start_time = strtotime($request_slot_time[0]);
		$request_end_time  = strtotime($request_slot_time[1]);

		$query = "SELECT * FROM $table_name WHERE tutor_id='".$tutor_id."' and class_date='".$slot_date."' and status = 'Upcoming'";
		$results = $wpdb->get_results( $query ); // db call ok. no cache ok.

		/*if ($slot_date == '2021-10-23') {
			echo $query.'<br><br><pre>';

			echo 'Input Array:<br>';
			print_r($request_slot_time);

			echo 'UST:'.$request_start_time.'<br>';
			echo 'UET:'.$request_end_time.'<br>';
		}*/

		if (count($results)>0) {
			foreach ($results as $row ) {
				$passed            = false;

				$booked_slot_time  = explode('-' , $row->class_time);
				$booked_start_time = strtotime($booked_slot_time[0]);
				$booked_end_time   = strtotime($booked_slot_time[1]);
				/*if ($slot_date == '2021-10-23') {
					echo 'Database Array<br>';
					print_r($booked_slot_time);
					echo 'DST:'.$booked_start_time.'<br>';
					echo 'DET:'.$booked_end_time.'<br>';
				}*/
				
				if( $request_start_time > $booked_end_time ){
					$passed = true;
				}
				if( $request_start_time == $booked_end_time ){
					$passed = true;
				}
				if( $request_start_time < $booked_end_time ){
					if( $booked_start_time >= $request_end_time ){
						$passed = true;
					}
				}

				if (!$passed) {
					break;
				}
			}
		}

		return $passed;
	}

	public function tutor_booking_add_to_cart_validation( $passed , $product_id , $quantity , $variation_id = null ) {

		global $wpdb;
		$product        = wc_get_product($product_id);
		$slot           = WCCB_Helper::get_unique_array($_POST['slot']);
		$date_wise_slot = array();

		if (!is_bool($product)) {
			if($product->is_type( 'wccb_course' )) {
				
				//Check if user already avail FREE course
				if (get_current_user_id()) {
					if ($product->get_regular_price() == 0 && get_user_meta(get_current_user_id() , 'avail_free_course' , true ) == 'yes' ) {
						$passed = false;
						wc_add_notice( __( 'You have already avail FREE course earlier. Free trail is only available  once per user' , WC_CLASS_BOOKING_TEXT_DOMAIN) , 'error');
						return $passed;
					}
				}

				if (!empty($_POST['tutor_id'] )) {

					if (empty($slot)) {
						$passed = false;
						wc_add_notice( __( 'Please select slot form tutor availability' , WC_CLASS_BOOKING_TEXT_DOMAIN) , 'error');
						return $passed;
					}

					//Collecting slots date wise
					$date_wise_slot = WCCB_Frontend::get_date_wise_slots( $slot ); 

					$slots_time = array();
					foreach ($date_wise_slot as $key => $value) {
						foreach ($value as $key2 => $value2) {
							$slot_time  = explode('-', $value2);
							$temp_time = array(
								'start_time' => $slot_time[0],
								'end_time'   => $slot_time[1]
							);
							array_push($slots_time, $temp_time);
						}
					}

					$used_hours = WCCB_Helper::get_total_hours_from_slots( $slots_time );

					if ($product->get_regular_price() == 0) {
						$used_hours = $used_hours * 2;
					}

					if ( $quantity < $used_hours ) {
						$passed        = false;
						$quantity_flag = 1;
					}					

					//Validation for tutor availability
					
					$availability_flag = 0;
					if (!empty($date_wise_slot) && $passed != false ) {
						foreach ($date_wise_slot as $key => $value) {
							foreach ($value as $key2 => $value2) {
								//Check if tutor is booked for slot by other student
								$passed = WCCB_Frontend::date_wise_slot_availability_validation( $_POST['tutor_id'] , $key , $value2 );
								if (!$passed) {
									$availability_flag = 1;
									wc_add_notice( __( 'The slot '.wp_date('D M j, Y',strtotime($key)).', '.$value2.' already booked. Try other slots.' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error');
								}
							}
						}
					}

					if ($quantity_flag) {
						wc_add_notice( __('You have added more slots than you have available hours' , WC_CLASS_BOOKING_TEXT_DOMAIN ) , 'error');
					}
				}

			}
		}

		return $passed;
	}

	public function add_cart_item_data( $cart_item_data , $product_id , $variation_id ) {

		$temp_product = wc_get_product($product_id);

		if (!is_bool($temp_product)) {
			if($temp_product->is_type( 'wccb_course' )) {
				$cart_item_data['tutor_id']         = !empty($_POST['tutor_id']) ? $_POST['tutor_id'] : get_post_meta($temp_product->get_id() , 'tutor_ids' , true );
				$cart_item_data['booking_slots']    = WCCB_Frontend::get_date_wise_slots(WCCB_Helper::get_unique_array($_POST['slot']));
			}
		}

		return $cart_item_data;
	}

	public function get_cart_item_data( $item_data , $cart_item_data ) {
		$temp_product = wc_get_product($cart_item_data["product_id"]);

		if (!is_bool($temp_product)) {
			if($temp_product->get_type() == "wccb_course") {
				if (!empty($cart_item_data['tutor_id']) && !is_array($cart_item_data['tutor_id'])) {
					$tutor_info = get_userdata($cart_item_data['tutor_id']);

					$item_data[] = array(
						'key' => __( 'Selected Tutor', WC_CLASS_BOOKING_TEXT_DOMAIN ),
						'value' => wc_clean( $tutor_info->display_name)
					);
				}
				if (!empty($cart_item_data['booking_slots'])) {

					$num_slots = 0;
					foreach ($cart_item_data['booking_slots'] as $key => $value) {
						$num_slots += count($value);
					}

					$text   = _n( 'Slot Booked', 'Slots Booked', $num_slots, WC_CLASS_BOOKING_TEXT_DOMAIN );
					$item_data[] = array(
						'key' => $text,
						'value' => wc_clean($num_slots)
					);
				}
			}
		}

		return $item_data;
	}

	/**
	 * Add course product item to order
	 */

	public function create_order_line_item( $item, $cart_item_key, $values, $order ) {
		if(!empty($values["tutor_id"])) {

			//Store tutor id as order item meta
			$item->add_meta_data(
				__( "_tutor_id", WC_CLASS_BOOKING_TEXT_DOMAIN ),
				$values['tutor_id'],
				true
			);
		}

		if(!empty($values["booking_slots"])) {

			//Store slots as order item meta
			$item->add_meta_data(
				__( "booking_slots", WC_CLASS_BOOKING_TEXT_DOMAIN ),
				$values['booking_slots'],
				true
			);
		}
	}

	public function order_item_name($product_name , $item ) {
		if( !empty( $item['_tutor_id'] ) ) {
			$tutor_info        = get_userdata($item['_tutor_id']);
			if (!empty($tutor_info->display_name)) {
				$product_name .= sprintf('<p>%s: %s</p>', __( 'Tutor Name' , WC_CLASS_BOOKING_TEXT_DOMAIN ), $tutor_info->display_name);
			}

			$slots         = $item['booking_slots'];
			if (!empty($slots)) {
				$num_slots = 0;
				foreach ($slots as $key => $value) {
					$num_slots += count($value);
				}
				$text   = _n( 'Slot Booked', 'Slots Booked', $num_slots, WC_CLASS_BOOKING_TEXT_DOMAIN );
				$product_name .= sprintf('<p>%s: %s</p>', $text , $num_slots);
			}
		}

		return $product_name;
	}

	public function checkout_page_validation() {
		// Loop over $cart items
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id = $cart_item['product_id'];
			$product    = wc_get_product($product_id);
			$price      = $product->get_regular_price();

			if ($price == 0 && get_user_meta(get_current_user_id() , 'avail_free_course' , true ) == 'yes' ) {
				wc_add_notice( __( 'You have already avail FREE course earlier. Free trail is only available once per user' , WC_CLASS_BOOKING_TEXT_DOMAIN) , 'error');
				break;
			}
		}

		if (!isset($_POST['refund_policy'])) {
			wc_add_notice( __( 'Please read and accept the refund and return policy to proceed with your order.' , WC_CLASS_BOOKING_TEXT_DOMAIN) , 'error');
		}
	}

	public function worldpay_ipn_handler($response) {
		WCCB()->log($response);
		$this->update_booking_and_hour_history($response['MC_order']); //Update booking and hour history from IPN
	}

	public function worldpay_ipn_handler_old_way($response) {
		WCCB()->log('Old way');
		WCCB()->log($response);
		$this->update_booking_and_hour_history($response['order']); //Update booking and hour history from IPN
	}

	public function update_booking_and_hour_history( $order_id ) {
		if ( ! $order_id )
        return;

	    // Allow code execution only once 
	    if( ! get_post_meta( $order_id, '_thankyou_action_done', true )) {
	    	global $wpdb;
	    	$quantity = 0;
	    	// Get an instance of the WC_Order object
	    	$order = wc_get_order( $order_id );
	        if($order->is_paid()) {
		        // Loop through order items
		        foreach ( $order->get_items() as $item_id => $item ) {
		        	//print_r($item);
		            // Get the product object
		            $product = $item->get_product();
		            if (!is_bool($product)) {
						if($product->get_type() == "wccb_course") {

							//Update user meta if course is FREE
							if ($product->get_regular_price() == 0 ) {
								update_user_meta( get_current_user_id() , 'avail_free_course' , 'yes' );
							}


							$tutor_id  = $item->get_meta('_tutor_id');
							$slots     = $item->get_meta('booking_slots');

							if (!empty($slots)) {
								$slots_time = array();
								foreach ($slots as $key => $value) {
									foreach ($value as $key2 => $value2) {
										$slot_time  = explode('-', $value2);
										$temp_time = array(
											'start_time' => $slot_time[0],
											'end_time'   => $slot_time[1]
										);
										array_push($slots_time, $temp_time);
									}
								}

								$used_hours = WCCB_Helper::get_total_hours_from_slots( $slots_time );
							}
							
							//Insert hour into hour history
							$course_type = get_post_meta( $product->get_id() , 'course_type' , true );
							if ($course_type == 'fixed') {
								$course_quantity = get_post_meta( $product->get_id() , 'course_quantity' , true );
							}
							else {
								$course_quantity = $item->get_quantity();
							}
							
							$table_name = $wpdb->prefix.'hour_history';
							$data       = array(
								'user_id'         => get_current_user_id(),
								'product_id'      => $product->get_id(),
								'order_id'        => $order->get_id(),
								'purchased_hours' => $course_quantity,
								'date_purchased'  => wp_date('Y-m-d H:i:s'),
								'used_hours'      => $used_hours

							);

							
							$wpdb->insert($table_name , $data);
							$hour_id  = $wpdb->insert_id;
							//End hour history

							//Insert slots into booking history
							if (!empty($slots)) {
								$table_name = $wpdb->prefix.'booking_history';
								foreach ($slots as $key => $value) {
									foreach ($value as $key2 => $value2) {
										$data = array(
											'user_id'      => get_current_user_id(),
											'product_id'   => $item->get_product_id(),
											'amount'       => $product->get_regular_price(),
											'tutor_id'     => $tutor_id,
											'hour_id'      => $hour_id,
											'class_date'   => $key,
											'class_time'   => $value2,
											'status'       => 'Upcoming',
											'booking_date' => wp_date('Y-m-d H:i:s')
										);

										$wpdb->insert($table_name , $data);

										do_action('class_booking_notification' , $wpdb->insert_id);
									}
								}
							}
							//End booking history
						}
					}
		        }
	        }

	        // Flag the action as done (to avoid repetitions on reload for example)
	        $order->update_meta_data( '_thankyou_action_done', true );
	        $order->save();
	    }
	}
}