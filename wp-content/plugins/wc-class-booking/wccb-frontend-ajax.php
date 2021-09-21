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