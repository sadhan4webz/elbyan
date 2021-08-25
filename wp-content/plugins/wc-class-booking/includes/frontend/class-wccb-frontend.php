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

		//Register form
		add_action ( 'woocommerce_register_form' , array( 'WCCB_Frontend_View' , 'render_register_form_fields' ) );
		add_action ( 'woocommerce_register_post', array( $this , 'validate_register_field') , 10, 3 );
		add_action ( 'woocommerce_created_customer', array( $this , 'save_register_fields') , 10, 1 );

		//Myaccount page
		add_action ( 'init', array( $this , 'add_myaccount_endpoint') );
		foreach ($this->get_myaccount_menu() as $key => $value) {
			add_action( 'woocommerce_account_'.$key.'_endpoint', array( 'WCCB_Frontend_View' , 'render_my_account_'.$key.'_content') );
		}
		add_action ( 'template_redirect' , array( $this , 'save_tutor_availability' ) );


		//Product detail page
		add_action( 'init' , array( $this , 'remove_woocommerce_actions' ) );
		add_action( 'woocommerce_before_single_product', array( 'WCCB_Frontend_View' , 'product_detail_page_form_start' ), 10 );
		add_action( 'woocommerce_before_single_product_summary', array( 'WCCB_Frontend_View' , 'show_tutor_profile' ), 20 );
		add_action( 'woocommerce_single_product_summary', array( 'WCCB_Frontend_View' , 'show_product_description' ), 6 );
		add_action( 'woocommerce_single_product_summary', array( 'WCCB_Frontend_View' , 'show_product_price' ), 10 );
		add_action( 'woocommerce_single_product_summary', array( 'WCCB_Frontend_View' , 'wccb_package_add_to_cart' ), 60 );
		add_action( 'woocommerce_after_single_product_summary', function(){ echo '<div class="tutor_availability_main_wrapper">
			Tutor availability will show here
		</div>';}, 10 );

		add_action( 'woocommerce_after_single_product' , function(){echo '</form>';} , 10 );


		//Filters

		//My account page
		add_filter ( 'woocommerce_account_menu_items', array( $this , 'customize_my_account_menu' ) );

	}

	public function remove_woocommerce_actions() {
		remove_action( 'woocommerce_before_single_product_summary' , 'woocommerce_show_product_images' , 20 );
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

					if (empty($_POST[$lower_key.'_start_time'])) {
						$errors->add( $lower_key.'_start_time_error' , __( $key.' start time is required field' , PLUGIN_TEXT_DOMAIN ) );
					}

					if (empty($_POST[$lower_key.'_end_time'])) {
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

	public function save_tutor_availability() {

		if (isset( $_POST['save-tutor-availability-nonce'] ) && wp_verify_nonce( $_POST['save-tutor-availability-nonce'], 'save_tutor_availability' ) ) {

			$customer_id     = get_current_user_id();
			$validation_flag = 1;
			$week_day        = WCCB_Helper::get_weekdays_array();

			foreach ($week_day as $key => $value) {
				$lower_key = strtolower($key);

				if (empty($_POST[$lower_key.'_is_unavailable'])) {

					if (empty($_POST[$lower_key.'_start_time'])) {
						wc_add_notice( __( $key.' start time is required field' , PLUGIN_TEXT_DOMAIN ) , 'error' );
						$validation_flag = 0;
					}

					if (empty($_POST[$lower_key.'_end_time'])) {
						wc_add_notice( __( $key.' end time is required field' , PLUGIN_TEXT_DOMAIN ) , 'error' );
						$validation_flag = 0;
					}
				}
			}

			if ($validation_flag) {
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

				wc_add_notice( __( 'Availability saved successfully', PLUGIN_TEXT_DOMAIN ), 'success' );
			}
		}
	}

	public function add_myaccount_endpoint() {
		foreach ($this->get_myaccount_menu() as $key => $value) {
			add_rewrite_endpoint( $key , EP_PAGES );
		}
	}

	public function get_myaccount_menu() {
		$tutor_menu   = array(
			'availability' => 'Availability Settings'
		);
		$student_menu = array(

		);

		//$user_meta = get_userdata(get_current_user_id());
		//$myaccount_menu = $user_meta->roles == 'wccb_tutor' ? $tutor_menu : $student_menu;

		$myaccount_menu = $tutor_menu;
		return $myaccount_menu;
	}

	public function customize_my_account_menu( $links ) {
		$links = array_slice( $links, 0, 5, true ) + $this->get_myaccount_menu() + array_slice( $links, 5, NULL, true );
		return $links;
	}
}