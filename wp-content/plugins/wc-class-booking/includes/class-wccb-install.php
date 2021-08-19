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
		delete_transient( 'wccb_installing' );
	}
	public static function uninstall() {
		self::remove_roles();
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