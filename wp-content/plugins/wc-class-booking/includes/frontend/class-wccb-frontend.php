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
		add_action( 'woocommerce_after_shop_loop_item_title' , array( 'WCCB_Frontend_view' , 'shop_page_product_price' ) , 10 );


		//Product detail page
		add_action( 'init' , array( $this , 'remove_woocommerce_actions' ) );

		add_action( 'woocommerce_before_single_product', array( 'WCCB_Frontend_View' , 'product_detail_page_form_start' ), 10 );

		add_action( 'woocommerce_before_single_product_summary', function(){echo '<div class="left_column_wrapper">';}, 10 );
		add_action( 'woocommerce_before_single_product_summary', 'woocommerce_template_single_title', 20 );
		add_action( 'woocommerce_before_single_product_summary', array( 'WCCB_Frontend_View' , 'show_product_description' ), 25 );
		add_action( 'woocommerce_before_single_product_summary', function(){echo '</div>';}, 30 );

		add_action( 'woocommerce_single_product_summary', array( 'WCCB_Frontend_View' , 'show_product_price' ), 10 );
		add_action( 'woocommerce_single_product_summary', array( 'WCCB_Frontend_View' , 'wccb_package_add_to_cart' ), 60 );

		add_action( 'woocommerce_after_single_product_summary', array( 'WCCB_Frontend_View' , 'show_tutor_profile' ), 20 );
		add_action( 'woocommerce_after_single_product_summary', array( 'WCCB_Frontend_View' , 'render_tutor_availability_container'), 25 );

		add_action( 'woocommerce_after_single_product' , function(){echo '</form>';} , 10 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this , 'create_order_line_item' ) , 10, 4 );
		add_action( 'woocommerce_thankyou' , array( $this , 'update_booking_and_hour_history') , 10 , 1 );


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

		$query         = "SELECT * FROM $table_name WHERE tutor_id='".$_REQUEST['tutor_id']."' and class_date >= '".date('Y-m-d')."'";
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

	public function validate_register_field( $username , $email , $errors ) {
		if (empty($_POST['first_name'])) {
			$errors->add( 'first_name_error' , __('First name is required field' , PLUGIN_TEXT_DOMAIN) );
		}
		if (empty($_POST['last_name'])) {
			$errors->add( 'last_name_error' , __('Last name is required field',PLUGIN_TEXT_DOMAIN) );
		}
		if (empty($_POST['gender'])) {
			$errors->add( 'gender_error' , __('Gender is required field' , PLUGIN_TEXT_DOMAIN) );
		}
		if (empty($_POST['user_role'])) {
			$errors->add( 'user_role_error' , __('Registration type is required field' , PLUGIN_TEXT_DOMAIN) );
		}

		if ($_POST['user_role'] == 'wccb_tutor' ) {

			foreach (WCCB_Helper::get_weekdays_array() as $key => $value) {
				$lower_key = strtolower($key);

				if (empty($_POST[$lower_key.'_is_unavailable'])) {

					if (empty($_POST[$lower_key.'_start_time']) && $_POST[$lower_key.'_start_time'] != '0') {
						$errors->add( $lower_key.'_start_time_error' , __( $key.' start time is required field' , PLUGIN_TEXT_DOMAIN ) );
					}

					if (empty($_POST[$lower_key.'_end_time']) && $_POST[$lower_key.'_end_time'] != '0') {
						$errors->add( $lower_key.'_end_time_error' , __( $key.' end time is required field' , PLUGIN_TEXT_DOMAIN ) );
					}
				}
			}
		}
	}

	public function save_register_fields( $customer_id ) {
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

			$availability = array();
			foreach (WCCB_Helper::get_weekdays_array() as $key => $value) {
				$lower_key = strtolower($key);
				$temp_day  = array(
					'is_unavailable' => $_POST[$lower_key.'_is_unavailable'],
					'start_time'     => $_POST[$lower_key.'_start_time'],
					'end_time'       => $_POST[$lower_key.'_end_time']
				);

				$availability[$lower_key] = $temp_day;
			}

			update_user_meta( $customer_id , 'availability' , $availability );
		}
	}

	public static function get_date_wise_slots( $slots ) {
		foreach ($slots  as $key => $value) {
			$slot_date_time = explode( '|' , $value);
			if (empty($date_wise_slot[$slot_date_time[0]])) {
				$date_wise_slot[$slot_date_time[0]] = array($slot_date_time[1]);
			}
			else {
				array_push($date_wise_slot[$slot_date_time[0]], $slot_date_time[1]);
			}
		}

		return $date_wise_slot;
	}

	public static function date_wise_slot_availability_validation( $tutor_id , $slot_date , $slot_time ) {
		global $wpdb;
		$table_name    = $wpdb->prefix.'booking_history';
		$passed         = true;

		$query = "SELECT * FROM $table_name WHERE tutor_id='".$tutor_id."' and class_date='".$slot_date."' and class_time='".$slot_time."'";
		$results = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.

		if (count($results)>0) {
			$passed = false;
		}

		return $passed;
	}

	public function date_wise_slot_hour_validation( $date_wise_slot , $cart_quantity = 0 ) {
		global $wpdb;
		$table_name            = $wpdb->prefix.'hour_history';
		$db_slot_hours  = array();
		$passed                = true;
		$total_available_hours = $cart_quantity;
		$request_total_slots   = 0;

		//Current user total hours for next 35 days
		$query   = "SELECT * FROM $table_name WHERE user_id='".get_current_user_id()."'";
		$results = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.

		foreach ($results as $key => $value) {
			$days = WCCB_Helper::get_date_difference( $value['date_purchased'] , date('Y-m-d') );
			if ($days < HOUR_EXPIRE_DAYS ) {
				$total_available_hours += $value['purchased_hours'] - $value['used_hours'];
			}
		}
		//////////////////////////


		//Collect available hours based on date
		foreach ($date_wise_slot  as $key => $value) {
			$request_total_slots += count($value);
			$query   = "SELECT * FROM $table_name WHERE user_id='".get_current_user_id()."'";
			$results = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.

			foreach ($results as $key2 => $value2) {
				$days = WCCB_Helper::get_date_difference( $value2['date_purchased'] , $key );
				if ($days < HOUR_EXPIRE_DAYS ) {
					if (empty($db_slot_hours[$key])) {
						$db_slot_hours[$key] = $value2['purchased_hours'] - $value2['used_hours'];
					}
					else {
						$db_slot_hours[$key] += $value2['purchased_hours'] - $value2['used_hours'];
					}
				}
			}
		}

		//print_r($db_slot_hours);
		//echo '<br><br>'.$total_available_hours;
		//Print_r($date_wise_slot);
		//////

		//Check if total available hour is greater than request slots
		if ($request_total_slots > $total_available_hours ) {
			$passed = false;
			wc_add_notice( __('You have added more slots than you have total available hours' , PLUGIN_TEXT_DOMAIN ) , 'error');
		}

		//Check hour is available for the slot
		foreach ($date_wise_slot as $key => $value) {
			if (!array_key_exists($key, $db_slot_hours)) {
				$passed = false;
				wc_add_notice( __('You don\'t have available hours to book slot for the date -'.wp_date('D M j, Y',strtotime($key)), PLUGIN_TEXT_DOMAIN ) , 'error');
			}
			else {

				$available_hour = $db_slot_hours[$key]+$cart_quantity;
				if ($available_hour < count($date_wise_slot[$key])) {
					$passed = false;
					wc_add_notice( __('You don\'t have enough hours to book slot for the date -'.wp_date('D M j, Y',strtotime($key)).'. Available hours: '.$db_slot_hours[$key], PLUGIN_TEXT_DOMAIN ) , 'error');
				}
			}
		}
		///////

		return $passed;
	}

	public function date_wise_slot_hour_validation_v2( $slot_date , $slot_count , $cart_quantity = 0 ) {
		global $wpdb;
		$table_name     = $wpdb->prefix.'hour_history';
		$db_slot_hours  = array();
		$passed         = true;


		$query   = "SELECT * FROM $table_name WHERE user_id='".get_current_user_id()."'";
		$results = $wpdb->get_results( $query, ARRAY_A ); // db call ok. no cache ok.
		foreach ($results as $key => $value) {
			$days = WCCB_Helper::get_date_difference( $value['date_purchased'] , $slot_date );
			if ($days < HOUR_EXPIRE_DAYS ) {
				if (empty($db_slot_hours[$slot_date])) {
					$db_slot_hours[$slot_date] = $value['purchased_hours'] - $value['used_hours'];
				}
				else {
					$db_slot_hours[$slot_date] += $value['purchased_hours'] - $value['used_hours'];
				}
			}
		}

		//print_r($db_slot_hours);
		//echo '<br><br>';
		//Print_r($date_wise_slot);
		//////

		//Check hour is available for the slot
		if (!array_key_exists($slot_date, $db_slot_hours)) {
			$passed = false;
			wc_add_notice( __('You don\'t have available hours to book slot for the date -'.wp_date('D M j, Y',strtotime($slot_date)), PLUGIN_TEXT_DOMAIN ) , 'error');
		}
		else {
			$available_hour = $db_slot_hours[$key]+$cart_quantity;
			if ( $available_hour < $slot_count ) {
				$passed = false;
				wc_add_notice( __('You don\'t have enough hours to book slot for the date -'.wp_date('D M j, Y',strtotime($slot_date)).'. Available hours :'.$db_slot_hours[$key], PLUGIN_TEXT_DOMAIN ) , 'error');
			}
		}
		///////

		return $passed;
	}

	public function tutor_booking_add_to_cart_validation( $passed , $product_id , $quantity , $variation_id = null ) {

		global $wpdb;
		$product        = wc_get_product($product_id);
		$slot           = $_POST['slot'];
		$date_wise_slot = array();

		if (!is_bool($product)) {
			if($product->is_type( 'wccb_package' )) {
				if (!empty($_POST['tutor_id'] )) {

					if (empty($slot)) {
						$passed = false;
						wc_add_notice( __( 'Please select slot form tutor availability' , PLUGIN_TEXT_DOMAIN) , 'error');
						return $passed;
					}
					

					//Validation for guest user

					//Collecting slots date wise
					$date_wise_slot = WCCB_Frontend::get_date_wise_slots( $slot );

					/*if (!WC()->cart->is_empty()) {
						// Loop over $cart items
						foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
							if ($product_id == $cart_item['product_id']) {
								$quantity += $cart_item['quantity'];
							}
						}
					}*/

					if ( $quantity < count($slot)) {
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
									wc_add_notice( __( 'The slot '.wp_date('D M j, Y',strtotime($key)).', '.$value2.' already booked. Try other slots.' , PLUGIN_TEXT_DOMAIN ) , 'error');
								}
							}
						}
					}
					//Validation end for guest user

					//Get available credit for logged in user for that particular slot date
					/*if (get_current_user_id() && $availability_flag == 0 && $quantity_flag == 1) {

						$passed        = $this->date_wise_slot_hour_validation( $date_wise_slot , $quantity );
						$quantity_flag = 0;
					}*/

					if ($quantity_flag) {
						wc_add_notice( __('You have added more slots than you have available hours' , PLUGIN_TEXT_DOMAIN ) , 'error');
					}
				}
			}
		}

		return $passed;
	}

	public function add_cart_item_data( $cart_item_data , $product_id , $variation_id ) {

		$temp_product = wc_get_product($product_id);

		if (!is_bool($temp_product)) {
			if($temp_product->is_type( 'wccb_package' )) {
				$cart_item_data['tutor_id']         = !empty($_POST['tutor_id']) ? $_POST['tutor_id'] : get_post_meta($temp_product->get_id() , 'tutor_ids' , true );
				$cart_item_data['booking_slots']    = WCCB_Frontend::get_date_wise_slots($_POST['slot']);
			}
		}

		return $cart_item_data;
	}

	public function get_cart_item_data( $item_data , $cart_item_data ) {
		$temp_product = wc_get_product($cart_item_data["product_id"]);

		if (!is_bool($temp_product)) {
			if($temp_product->get_type() == "wccb_package") {
				if (!empty($cart_item_data['tutor_id']) && !is_array($cart_item_data['tutor_id'])) {
					$tutor_info = get_userdata($cart_item_data['tutor_id']);

					$item_data[] = array(
						'key' => __( 'Selected Tutor', PLUGIN_TEXT_DOMAIN ),
						'value' => wc_clean( $tutor_info->display_name)
					);
				}
				if (!empty($cart_item_data['booking_slots'])) {

					$num_slots = 0;
					foreach ($cart_item_data['booking_slots'] as $key => $value) {
						$num_slots += count($value);
					}

					$item_data[] = array(
						'key' => __( 'Number of slots', PLUGIN_TEXT_DOMAIN ),
						'value' => wc_clean($num_slots)
					);
				}
			}
		}

		return $item_data;
	}

	/**
	 * Add offer product item to order
	 */

	public function create_order_line_item( $item, $cart_item_key, $values, $order ) {
		if(!empty($values["tutor_id"])) {

			//Store tutor id as order item meta
			$item->add_meta_data(
				__( "tutor_id", PLUGIN_TEXT_DOMAIN ),
				$values['tutor_id'],
				true
			);
		}

		if(!empty($values["booking_slots"])) {

			//Store slots as order item meta
			$item->add_meta_data(
				__( "booking_slots", PLUGIN_TEXT_DOMAIN ),
				$values['booking_slots'],
				true
			);
		}
	}

	public function order_item_name($product_name , $item ) {
		if( !empty( $item['tutor_id'] ) ) {
			$tutor_info    = get_userdata($item['tutor_id']);
			$product_name .= sprintf('<ul><li>%s: %s</li></ul>', __( 'Tutor Name' , PLUGIN_TEXT_DOMAIN ), $tutor_info->display_name);
		}

		return $product_name;
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
						if($product->get_type() == "wccb_package") {
							$tutor_id  = $item->get_meta('tutor_id');
							$slots     = $item->get_meta('booking_slots');

							if (!empty($slots)) {
								$used_hours = 0;
								foreach ($slots as $key => $value) {
									$used_hours += count($value);
								}
							}
							
							//Insert hour into hour history
							$table_name = $wpdb->prefix.'hour_history';
							$data       = array(
								'user_id'         => get_current_user_id(),
								'order_id'        => $order->get_id(),
								'purchased_hours' => $item->get_quantity(),
								'date_purchased'  => wp_date('Y-m-d H:i:s'),
								'used_hours'      => $used_hours

							);
							$wpdb->insert($table_name , $data);
							$hour_id   = $lastid = $wpdb->insert_id;
							//End hour history

							//Insert slots into booking history
							if (!empty($slots)) {
								$table_name = $wpdb->prefix.'booking_history';
								foreach ($slots as $key => $value) {
									foreach ($value as $key2 => $value2) {
										$data = array(
											'user_id'      => get_current_user_id(),
											'product_id'   => $item->get_product_id(),
											'tutor_id'     => $tutor_id,
											'hour_id'      => $hour_id,
											'class_date'   => $key,
											'class_time'   => $value2,
											'status'       => 'Upcoming',
											'booking_date' => wp_date('Y-m-d H:i:s')
										);

										$wpdb->insert($table_name , $data);
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