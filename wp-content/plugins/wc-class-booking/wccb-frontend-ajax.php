<?php
/**
 * User: Md Sahel Aktar
 */

//---  mimic the actuall admin-ajax  -------------------------------------------
define('DOING_AJAX', true);
 
if (!isset( $_REQUEST['action']))
  die('-1');
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

//---  Typical headers  --------------------------------------------------------
header('Content-Type: text/html');
send_nosniff_header();

//---  Disable caching  --------------------------------------------------------
header('Cache-Control: no-cache');
header('Pragma: no-cache');

$response 		= array( 'event' => null, 'msg' => null, 'content' => null );
$action    		= esc_attr( trim( $_REQUEST['action'] ) );
$html_error		= (bool)( isset( $_REQUEST['html_error'] ) ? $_REQUEST['html_error'] : true );
switch ( $action ) {

	case 'get_tutor_profile':

		$event   = 'success';
		$content = WCCB_Frontend_View::show_tutor_profile( $_REQUEST['product_id']);
		$msg     = 'Work in progress message';

		$response["event"] 	 = $event;
		$response["msg"] 	 = $msg;
		$response['content'] = $content;
		echo json_encode( $response, JSON_HEX_QUOT | JSON_HEX_TAG );
		break;

	case 'get_tutor_availability_calendar':

		$event   = 'success';
		$content = WCCB_Frontend_View::get_tutor_availability_calendar( $_REQUEST['product_id'] , $_REQUEST['tutor_id'] , $_REQUEST['date'] , $_REQUEST['num_days'] , $_POST['slot'] );
		$msg     = 'Work in progress message';

		$response["event"] 	 = $event;
		$response["msg"] 	 = $msg;
		$response['content'] = $content;
		echo json_encode( $response, JSON_HEX_QUOT | JSON_HEX_TAG );
		break;
	

	case 'student_add_hour':
		global $wpdb;

		if ( ! isset( $_POST['add_hour_nonce_field'] ) || ! wp_verify_nonce( $_POST['add_hour_nonce_field'], 'add_hour_nonce' ) 
		) {
			$event   = 'error';
			$content = '';
			$msg     = '<div class="woocommerce-notices-wrapper">
							<div class="woocommerce-message">Sorry your nonce not verified. Please refresh the page and try again.
							</div>
						</div>';
		} 
		else {

			$validation_flag     = 1;
			if (empty($_REQUEST['user_id']) || empty($_REQUEST['product_id']) || empty($_REQUEST['hour'])) {

				$validation_flag = 0;
				$event = 'error';
				$msg   = '<div class="woocommerce-notices-wrapper">
							<div class="woocommerce-message">
								<ul>';

				if (empty($_REQUEST['user_id'])) {
					$msg   .= '<li>Please select student.</li>';
				}

				if (empty($_REQUEST['product_id'])) {
					$msg   .= '<li>Please select class.</li>';
				}

				if (empty($_REQUEST['hour'])) {
					$msg   .= '<li>Please enter hour</li>';
				}

				$msg .= '</<ul></div></div>';

			}
		}

		if ($validation_flag) {
			//Insert hour into hour history
			$table_name = $wpdb->prefix.'hour_history';
			$data       = array(
				'user_id'         => $_REQUEST['user_id'],
				'product_id'      => $_REQUEST['product_id'],
				'purchased_hours' => $_REQUEST['hour'],
				'date_purchased'  => wp_date('Y-m-d H:i:s')
			);

			if($wpdb->insert($table_name , $data)) {
				$event   = 'success';
				$content = '';
				$msg     = '<div class="woocommerce-notices-wrapper">
								<div class="woocommerce-message">The hour has been added for the student. Now class can be booked.</div>
							</div>';
			}
			else {
				$event   = 'error';
				$content = '';
				$msg     = '<div class="woocommerce-notices-wrapper">
								<div class="woocommerce-message">Database error during insertion.</div>
							</div>';
			}
			//End hour history
		}

		$response["event"] 	 = $event;
		$response["msg"] 	 = $msg;
		$response['content'] = $content;
		echo json_encode( $response, JSON_HEX_QUOT | JSON_HEX_TAG );
		break;

	case 'get_available_hour_product':
		global $wpdb;
		$hour_table = $wpdb->prefix.'hour_history';

		if (empty($_REQUEST['user_id'])) {
			$event   = 'error';
			$content = '';
			$msg     = 'User ID is blank';
		}
		else {
			$event      = 'success';
			$query      = "select * from $hour_table where user_id='".$_REQUEST['user_id']."'";
			$hours      = $wpdb->get_results( $query ); // db call ok. no cache ok
			$content    = '<option value="">Select</option>';
			if (count($hours)) {
				foreach ($hours as $hour) {
					$product        = wc_get_product($hour->product_id);
					$days           = WCCB_Helper::get_date_difference( $hour->date_purchased, date('Y-m-d') );
					$available_hour = 0;
					if ($days < WC_CLASS_BOOKING_HOUR_EXPIRE_DAYS ) {
						$available_hour = WCCB_Frontend_Myaccount::get_student_total_available_hours($hour->user_id , $hour->ID);
					}
					if ($available_hour > 0 ) {
						$no_hour_flag = 0;
						$expire_date  = WCCB_Helper::get_particular_date($hour->date_purchased , WC_CLASS_BOOKING_HOUR_EXPIRE_DAYS);
						$content .= '<option value="'.$hour->ID.'" data-hour_id="'.$hour->ID.'" data-display_expire_date="'.wp_date('F j, Y, g:i a',strtotime($expire_date)).'">'.$product->get_name().' - (Available hours : '.$available_hour.')</option>';
					}
				}
			}
			$content = $content;
			$msg     = 'Product generated';
		}
		

		$response["event"] 	 = $event;
		$response["msg"] 	 = $msg;
		$response['content'] = $content;
		echo json_encode( $response, JSON_HEX_QUOT | JSON_HEX_TAG );
		break;

	case 'student_deduct_hour':
		global $wpdb;

		if ( ! isset( $_POST['deduct_hour_nonce_field'] ) || ! wp_verify_nonce( $_POST['deduct_hour_nonce_field'], 'deduct_hour_nonce' ) 
		) {
			$event   = 'error';
			$content = '';
			$msg     = '<div class="woocommerce-notices-wrapper">
							<div class="woocommerce-message">Sorry your nonce not verified. Please refresh the page and try again.
							</div>
						</div>';
		} 
		else {

			$validation_flag     = 1;
			if (empty($_REQUEST['user_id']) || empty($_REQUEST['hour_id']) || empty($_REQUEST['hour'])) {

				$validation_flag = 0;
				$event = 'success';
				$msg   = '<div class="woocommerce-notices-wrapper">
							<div class="woocommerce-message">
								<ul>';

				if (empty($_REQUEST['user_id'])) {
					$msg   .= '<li>Please select student.</li>';
				}

				if (empty($_REQUEST['hour_id'])) {
					$msg   .= '<li>Please select class.</li>';
				}

				if (empty($_REQUEST['hour'])) {
					$msg   .= '<li>Please enter hour</li>';
				}

				$msg .= '</<ul></div></div>';

			}

			$available_hour = WCCB_Frontend_Myaccount::get_student_total_available_hours($_REQUEST['user_id'], $_REQUEST['hour_id']);

			if ((float)$available_hour < (float)$_REQUEST['hour']  && $validation_flag == 1) {
				$validation_flag = 0;
				$event = 'success';
				$msg   = '<div class="woocommerce-notices-wrapper">
							<div class="woocommerce-message">
								<ul>';
				$msg   .= '<li>You have entered more hour than available hour -'.$available_hour.'</li>';
				$msg .= '</<ul></div></div>';
			}
		}

		if ($validation_flag) {
			$table_name     = $wpdb->prefix.'hour_history';
			$hour_row       = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE ID = %s", $_REQUEST['hour_id'] ) );
			$deducted_hours = (float)$hour_row->deducted_hours + (float)$_REQUEST['hour'];

			$updated = $wpdb->update(
			    $table_name,
			    array( 
			        'deducted_hours' => $deducted_hours
			    ),
			    array(
			        'ID'         => $hour_row->ID
			    )
			);

			if($updated) {

				//Log update
				$log_text  = 'Hour dedcucted on '.wp_date('d-m-Y h:i a').' ## ';
				$log_text .= 'Student ID : '. $_REQUEST['user_id'].' ## ';
				$log_text .= 'Hour deducted : '.$_REQUEST['hour'].' ## ';
				$log_text .= 'Hour ID : '.$_REQUEST['hour_id'];

				WC_CLASS_Booking::hour_deducted_log($log_text);

				do_action('deduct_hour_notification', $hour_row , $_REQUEST['hour'] );

				$event   = 'success';
				$content = '';
				$msg     = '<div class="woocommerce-notices-wrapper">
								<div class="woocommerce-message">The hour has been deducted form the student purchased hours.</div>
							</div>';

				$response["reset_form"] 	 = 'yes';
			}
			else {
				$event   = 'error';
				$content = '';
				$msg     = '<div class="woocommerce-notices-wrapper">
								<div class="woocommerce-message">Database error during insertion.</div>
							</div>';
			}
			//End hour history
		}

		$response["event"] 	 = $event;
		$response["msg"] 	 = $msg;
		$response['content'] = $content;
		echo json_encode( $response, JSON_HEX_QUOT | JSON_HEX_TAG );
		break;

	case 'change_class_status':
		$validation_flag = true;
		if (empty($_REQUEST['booking_id'])) {
			$validation_flag = false;
			$event   = 'error';
			$content = '';
			$msg     = '<div class="woocommerce-notices-wrapper">
							<div class="woocommerce-message">Booking ID is blank</div>
						</div>';
		}

		if ($validation_flag) {
			global $wpdb;
			$table_name     = $wpdb->prefix.'booking_history';

			$updated = $wpdb->update(
			    $table_name,
			    array( 
			        'delivery_status' => $_REQUEST['delivery_status']
			    ),
			    array(
			        'ID'         => $_REQUEST['booking_id']
			    )
			);

			if($updated) {
				$row       = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE ID = %d", $_REQUEST['booking_id'] ) );
				$user  = get_userdata($row->user_id);
				$tutor = get_userdata($row->tutor_id);
				//hook for notification of class
				do_action( 'class_status_notification' , $row , $user , $tutor ); //Parameter booking object , user object , tutor object
				

				$event   = 'success';
				$content = '';
				$msg     = '<div class="woocommerce-notices-wrapper">
								<div class="woocommerce-message">The class status has been updated.</div>
							</div>';
			}
			else {
				$event   = 'error';
				$content = '';
				$msg     = '<div class="woocommerce-notices-wrapper">
								<div class="woocommerce-message">Database error during insertion.</div>
							</div>';
			}
		}
		
		$response["event"] 	 = $event;
		$response["msg"] 	 = $msg;
		$response['content'] = $content;
		echo json_encode( $response, JSON_HEX_QUOT | JSON_HEX_TAG );
		break;

	// Default ajax response	
	default:
		$response["event"] 	= "error";
		$response["msg"] 	= '<p class="wstdl-msg-entry wstdl-msg-error">Please check your action : '.$action.'</p>';
		$response["msg"]	= $html_error ? $response["msg"] : wp_strip_all_tags( $response["msg"] );
		echo json_encode( $response, JSON_HEX_QUOT | JSON_HEX_TAG );
		break;
}

wp_die();
?>