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

		if ($_POST['user_role'] == 'Tutor' ) {

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
			update_user_meta( $customer_id , 'user_role' , $_POST['user_role']);
		}

		if ($_POST['user_role'] == 'Tutor' ) {
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
}