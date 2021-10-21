<?php
defined( 'ABSPATH' ) or die();
class WCCB_Scheduler {
	/**
	 * Initialize class
	 */
	public function init() {
		$this->register_hooks();
		self::create_schedule_hooks( );
	}

	/**
	 * Hook into WordPress
	 */
	private function register_hooks() {
		
	}

	public static function create_schedule_hooks( ) {

		//Actions
		add_action( "wccb_schedule_class_notification", array( 'WCCB_Scheduler', 'check_class_notification' ), 10 );
		add_action( "wccb_schedule_class_reminder", array( 'WCCB_Scheduler', 'check_class_reminder' ), 10 );
		add_action( "wccb_schedule_class_completion", array( 'WCCB_Scheduler', 'check_class_completion' ), 10 );
		//Filters
		add_filter( "cron_schedules", array( 'WCCB_Scheduler', 'add_cron_interval' ) );
	}

	/**
	 * Create custom cron job schedules
	 *
	 * @since 1.0.0
	 * 
	 * @param none
	 *
	 * @return array of schedules
	 *
	*/
	public static function add_cron_interval( $schedules ) { 

	    $schedules['wccb_five_minutes'] = array(
	        'interval' => 300,
	        'display'  => esc_html__( 'Every Five Minutes (WCCB)' ), 
	    );

	    return $schedules;
	}

	public static function activate_schedules( ) {
		$schedule = WCCB_Settings::get_notification_cron_interval();
		self::schedule( 'class_notification', $schedule );

		$schedule = WCCB_Settings::get_reminder_cron_interval();
		self::schedule( 'class_reminder', $schedule );

		$schedule = WCCB_Settings::get_completion_cron_interval();
		self::schedule( 'class_completion', $schedule );

	}

	public static function deactivate_schedules( ) {
		self::unschedule( 'class_notification' );
		self::unschedule( 'class_reminder' );
		self::unschedule( 'class_completion' );
	}

	public static function schedule( $type, $schedule ) {
		if ( 'none' === $schedule ) {
			self::unschedule( $type );
		} else {
			$timestamp = wp_next_scheduled( "wccb_schedule_" . $type, array( $type ) );
			if ( false !== $timestamp ) {
				self::unschedule( $type );
			}
			wp_schedule_event( time(), $schedule, "wccb_schedule_" . $type, array( $type ) );
		}
	}

	public static function unschedule( $type ) {
		wp_clear_scheduled_hook( "wccb_schedule_" . $type, array( $type ) );
	}

	public static function check_class_notification() {
		global $wpdb;
		$table_name = $wpdb->prefix.'booking_history';
		$query      = "select * from $table_name where class_date='".wp_date('Y-m-d')."' and status = 'Upcoming'";
		$results    = $wpdb->get_results( $query ); // db call ok. no cache ok.
		
		//print_r($results).'<br><br><br>';
		if (count($results)) {
			foreach( $results as $row ) {
				$student = get_userdata($row->user_id);
				$tutor   = get_userdata($row->tutor_id);

				//hook for notification of class
				do_action( 'upcoming_class_notification' , $row , $student , $tutor ); //Parameter booking object , student object , tutor object
			}
		}
	}

	public static function check_class_reminder() {
		global $wpdb;
		$table_name = $wpdb->prefix.'booking_history';
		$query      = "select * from $table_name where class_date='".wp_date('Y-m-d')."' and status = 'Upcoming'";


		$results    = $wpdb->get_results( $query ); // db call ok. no cache ok.
		if (count($results)) {
			foreach( $results as $row ) {
				$class_time_exp = explode('-' , $row->class_time);
				$class_time     = $row->class_date.' '.$class_time_exp[0];
				$datetime1      = new DateTime(wp_date('Y-m-d h:i a'));
				$datetime2      = new DateTime($class_time);
				$interval       = $datetime1->diff($datetime2);
				$days           = $interval->d;
				$hour           = $interval->h;
				$minute         = $interval->i;
				$total_minute   = ($hour * 60 + $minute);

				if ( $days == 0 && $total_minute < WC_CLASS_BOOKING_SEND_CLASS_REMINDER_BEFORE ) {
					$user  = get_userdata($row->user_id);
					$tutor = get_userdata($row->tutor_id);
					//hook for notification of class
					do_action( 'upcoming_class_reminder' , $row , $user , $tutor ); //Parameter booking object , user object
				}
			}
		}
	}

	public static function check_class_completion() {
		global $wpdb;
		$table_name = $wpdb->prefix.'booking_history';
		$query      = "select * from $table_name where class_date<='".wp_date('Y-m-d')."' and status = 'Upcoming'";
		$results    = $wpdb->get_results( $query ); // db call ok. no cache ok.
		if (count($results)) {
			foreach( $results as $row ) {
				$class_time_exp = explode('-' , $row->class_time);
				$class_time     = $row->class_date.' '.$class_time_exp[0];
				$time_diff      = strtotime(wp_date('Y-m-d H:i:s')) - strtotime(wp_date('Y-m-d H:i:s', strtotime($class_time)));
				if ( $time_diff > 0 ) {

					//Update class status
					$wpdb->update(
						$table_name,

						array(
							'status' => 'Completed'
						),

						array('ID'   => $row->ID)
					);

					$user  = get_userdata($row->user_id);
					$tutor = get_userdata($row->tutor_id);
					//hook for notification of class
					do_action( 'class_completion_notification' , $row , $user , $tutor ); //Parameter booking object , user object
				}
			}
		}
	}	
}
