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
		$content = WCCB_Frontend_View::get_tutor_availability_calendar( $_REQUEST['tutor_id'] , $_REQUEST['date'] , $_REQUEST['num_days'] , $_POST['slot'] );
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