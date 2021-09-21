<?php
defined( 'ABSPATH' ) || die();

class WCCB_Helper {

	public static function get_weekdays_array() {
	    global $wp_locale;
	    $days = array();

	    for ( $day_index = 0; $day_index <= 6; $day_index++ ) :
	        $days[$wp_locale->get_weekday($day_index)] = $day_index;
	    endfor;

	    return isset( $days ) ? $days : false;
	}

	public static function get_date_difference( $start_date , $end_date , $return_type = 'days' ) {
		$then       = strtotime($start_date); //Convert it into a timestamp.
		$now        = strtotime($end_date); //Get the current timestamp.
		$difference = $now - $then; //Calculate the difference.
		$days       = (floor($difference / (60*60*24) ));//Convert seconds into days.
 
		/*$date1 = new DateTime($start_date);
		$date2 = new DateTime($end_date);
		$interval = $date1->diff($date2);
		//echo "difference " . $interval->y . " years, " . $interval->m." months, ".$interval->d." days ";
		$days   = $interval->d;
		$months = $interval->m;
		$years  = $interval->y;*/

		switch ($return_type) {
			case 'days':
				$response = $days;
				break;
			case 'months':
				$response = $months;
				break;
			case 'years':
				$response = $years;
				break;
		}

		return $response;
	}

	public static function display_date( $date , $format = 'D M j, Y' ) {
		return wp_date($format , strtotime($date));
	}

	public static function get_particular_date( $date_time , $days , $plus_minue = '+' ) {
		return wp_date('Y-m-d h:i:s' , strtotime($plus_minue.$days.' days' , strtotime($date_time)));
	}

	public static function help_tip( $tip, $allow_html = false ) {
		if ( $allow_html ) {
			$tip = htmlspecialchars(
				wp_kses(
					html_entity_decode( $tip ),
					array(
						'br'     => array(),
						'em'     => array(),
						'strong' => array(),
						'small'  => array(),
						'span'   => array(),
						'ul'     => array(),
						'li'     => array(),
						'ol'     => array(),
						'p'      => array(),
					)
				)
			);
		} else {
			$tip = esc_attr( $tip );
		}

		return '<span class="wpiaf-help-tip wpiaf-tips" data-tip="' . $tip . '"></span>';
	}
}