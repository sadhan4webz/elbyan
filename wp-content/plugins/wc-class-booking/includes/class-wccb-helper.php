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
}