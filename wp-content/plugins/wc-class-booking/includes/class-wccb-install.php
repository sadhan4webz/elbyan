<?php
defined( 'ABSPATH' ) || exit;
class WCCB_Install {
	public static function init() {
	
	}
	
	public static function install() {
		
		// Check if we are not already running this routine.
		if ( 'yes' === get_transient( 'wccb_installing' ) ) {
			return;
		}

		// If we made it till here nothing is running yet, lets set the transient now.
		set_transient( 'wccb_installing', 'yes', MINUTE_IN_SECONDS * 10 );		
		self::create_roles();
		self::create_tables();
		WCCB_Scheduler::activate_schedules();
		delete_transient( 'wccb_installing' );
	}
	public static function uninstall() {
		self::remove_roles();
		WCCB_Scheduler::deactivate_schedules();
	}
	
	public static function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles(); // @codingStandardsIgnoreLine
		}

		// Dummy gettext calls to get strings in the catalog.
		/* translators: user role */
		_x( 'Tutor', 'User role', 'wccb' );
		_x( 'Student', 'User role', 'wccb' );

		// Tutor & Student role.
		add_role(
			'wccb_tutor',
			'Tutor',
			array(
				'read' => true,
			)
		);
		add_role(
			'wccb_student',
			'Student',
			array(
				'read' => true,
			)
		);	
	}

	public static function create_tables() {
		global $wpdb;
	    $collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$table_array = array(
			array(
				'table_name' => $wpdb->prefix.'hour_history',
				'sql_query'  => "CREATE TABLE IF NOT EXISTS {table_name} (
								  `ID` int(11) NOT NULL AUTO_INCREMENT,
								  `user_id` varchar(100) NOT NULL,
								  `order_id` varchar(100) NOT NULL,
								  `product_id` varchar(100) NOT NULL,
								  `purchased_hours` varchar(100) NOT NULL,
								  `used_hours` varchar(100)  NULL DEFAULT NULL,
								  `expired_hours` varchar(100)  NULL DEFAULT NULL,
								  `deducted_hours` varchar(100)  NULL DEFAULT NULL,
								  `date_purchased` datetime NOT NULL,
								  PRIMARY KEY (ID)
								) $collate"
			),
			array(
				'table_name' => $wpdb->prefix.'booking_history',
				'sql_query'  => "CREATE TABLE IF NOT EXISTS {table_name} (
								  `ID` int(11) NOT NULL AUTO_INCREMENT,
								  `user_id` varchar(100) NOT NULL,
								  `tutor_id` varchar(100) NOT NULL,
								  `hour_id` varchar(100) NOT NULL,
								  `product_id` varchar(100) NOT NULL,
								  `amount` varchar(100) NOT NULL,
								  `class_date` date NOT NULL,
								  `class_time` varchar(100) NOT NULL,
								  `booking_date` datetime NOT NULL,
								  `status` varchar(100) NOT NULL,
								  PRIMARY KEY (ID)
								) $collate"
			),
			array(
				'table_name' => $wpdb->prefix.'booking_history_meta',
				'sql_query'  => "CREATE TABLE IF NOT EXISTS {table_name} (
								  `ID` int(11) NOT NULL AUTO_INCREMENT,
								  `booking_id` varchar(100) NOT NULL,
								  `meta_key` varchar(1000) NOT NULL,
								  `meta_value` longtext NOT NULL
								  PRIMARY KEY (ID)
								) $collate"
			)
			
		);

		foreach ($table_array as $key => $value) {
			dbDelta(str_replace('{table_name}', $value['table_name'], $value['sql_query']));
		}
	}
	
	public static function remove_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles(); // @codingStandardsIgnoreLine
		}

		remove_role( 'wccb_tutor' );
		remove_role( 'wccb_student' );
	}
}
WCCB_Install::init();