<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Class_Booking_Dependency_Checker {
	const MINIMUM_PHP_VERSION = '5.6.20';
	const MINIMUM_WP_VERSION  = '4.9.0';

	public static function check_dependencies() {
		if ( ! self::check_necessary_plugins() ) {
			add_action( 'admin_notices', array( 'WC_Class_Booking_Dependency_Checker', 'add_necessary_plugins_notice' ) );
			add_action( 'admin_init', array( __CLASS__, 'deactivate_self' ) );
			return false;
		}
		
		if ( ! self::check_php() ) {
			add_action( 'admin_notices', array( 'WC_Class_Booking_Dependency_Checker', 'add_php_notice' ) );
			add_action( 'admin_init', array( __CLASS__, 'deactivate_self' ) );
			return false;
		}

		if ( ! self::check_wp() ) {
			add_action( 'admin_notices', array( 'WC_Class_Booking_Dependency_Checker', 'add_wp_notice' ) );
			add_filter( 'plugin_action_links_' . WC_CLASS_BOOKING_PLUGIN_BASENAME, array( 'WC_Class_Booking_Dependency_Checker', 'wp_version_plugin_action_notice' ) );
		}

		return true;
	}
	
	private static function check_necessary_plugins() {
		if ( ! function_exists( 'is_plugin_active' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			
		return ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) ;
	}
	
	public static function add_necessary_plugins_notice(){
		$screen        = get_current_screen();
		$valid_screens = self::get_critical_screen_ids();

		if ( null === $screen || ! current_user_can( 'activate_plugins' ) || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}
		
		$necessary_plugins_notice = '';
		if ( current_user_can( 'update_core' ) ) {
			// translators: %s is the URL for the page where users can go to update WordPress.
			$necessary_plugins_notice = ' ' . sprintf( __( 'Please activate %s to use this plugin.', 'wccb' ), '<strong>Woocommerce</strong>' );
		}

		echo '<div class="error">';
		echo '<p>' . wp_kses_post( __( '<strong>Woocommerce Class Booking</strong> requires necessary plugin.', 'wccb' ) . $necessary_plugins_notice ) . '</p>';
		echo '</div>';
	}
	
	/**
	 * Checks for our PHP version requirement.
	 *
	 * @return bool
	 */
	private static function check_php() {
		return version_compare( phpversion(), self::MINIMUM_PHP_VERSION, '>=' );
	}

	/**
	 * Adds notice in WP Admin that minimum version of PHP is not met.
	 *
	 * @access private
	 */
	public static function add_php_notice() {
		$screen        = get_current_screen();
		$valid_screens = self::get_critical_screen_ids();

		if ( null === $screen || ! current_user_can( 'activate_plugins' ) || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		// translators: %1$s is version of PHP that Woocommerce Class Booking requires; %2$s is the version of PHP WordPress is running on.
		$message = sprintf( __( '<strong>Woocommerce Class Booking</strong> requires a minimum PHP version of %1$s, but you are running %2$s.', 'wccb' ), self::MINIMUM_PHP_VERSION, phpversion() );

		echo '<div class="error"><p>';
		echo wp_kses( $message, array( 'strong' => array() ) );
		$php_update_url = 'https://wordpress.org/support/update-php/';
		if ( function_exists( 'wp_get_update_php_url' ) ) {
			$php_update_url = wp_get_update_php_url();
		}
		printf(
			'<p><a class="button button-primary" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></p>',
			esc_url( $php_update_url ),
			esc_html__( 'Learn more about updating PHP', 'wccb' ),
			/* translators: accessibility text */
			esc_html__( '(opens in a new tab)', 'wccb' )
		);
		echo '</p></div>';
	}

	/**
	 * Deactivate self.
	 */
	public static function deactivate_self() {
		deactivate_plugins( WC_CLASS_BOOKING_PLUGIN_BASENAME );
	}

	/**
	 * Checks for our WordPress version requirement.
	 *
	 * @return bool
	 */
	private static function check_wp() {
		global $wp_version;

		return version_compare( $wp_version, self::MINIMUM_WP_VERSION, '>=' );
	}

	/**
	 * Adds notice in WP Admin that minimum version of WordPress is not met.
	 *
	 * @access private
	 */
	public static function add_wp_notice() {
		$screen        = get_current_screen();
		$valid_screens = self::get_critical_screen_ids();

		if ( null === $screen || ! in_array( $screen->id, $valid_screens, true ) ) {
			return;
		}

		$update_action_link = '';
		if ( current_user_can( 'update_core' ) ) {
			// translators: %s is the URL for the page where users can go to update WordPress.
			$update_action_link = ' ' . sprintf( __( 'Please <a href="%s">update WordPress</a> to avoid issues.', 'wccb' ), esc_url( self_admin_url( 'update-core.php' ) ) );
		}

		echo '<div class="error">';
		echo '<p>' . wp_kses_post( __( '<strong>Woocommerce Class Booking</strong> requires a more recent version of WordPress.', 'wccb' ) . $update_action_link ) . '</p>';
		echo '</div>';
	}

	/**
	 * Add admin notice when WP upgrade is required.
	 *
	 * @access private
	 *
	 * @param array $actions Actions to show in WordPress admin's plugin list.
	 * @return array
	 */
	public static function wp_version_plugin_action_notice( $actions ) {
		if ( ! current_user_can( 'update_core' ) ) {
			$actions[] = '<strong style="color: red">' . esc_html__( 'WordPress Update Required', 'wccb' ) . '</strong>';
		} else {
			$actions[] = '<a href="' . esc_url( self_admin_url( 'update-core.php' ) ) . '" style="color: red">' . esc_html__( 'WordPress Update Required', 'wccb' ) . '</a>';
		}
		return $actions;
	}

	/**
	 * Returns the screen IDs where dependency notices should be displayed.
	 *
	 * @return array
	 */
	private static function get_critical_screen_ids() {
		return array( 'dashboard', 'plugins', 'plugins-network' );
	}
}