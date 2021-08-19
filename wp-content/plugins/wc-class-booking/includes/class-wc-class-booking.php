<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Class_Booking {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.26.0
	 */
	private static $instance = null;
	
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->includes();
		$this->init_hooks();	
	}
	
	public function includes(){
		require_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/class-wccb-install.php';
		//require_once WC_CLASS_BOOKING_PLUGIN_DIR . '/includes/functions.php';
	}
	
	public function init_hooks(){
		register_activation_hook( WC_CLASS_BOOKING_PLUGIN_FILE, array( 'WCCB_Install', 'install' ) );
		register_deactivation_hook( WC_CLASS_BOOKING_PLUGIN_FILE, array( 'WCCB_Install', 'uninstall' ) );
		
		//Load text domain
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		
		//Enqueue custom javasacript
		add_action( 'wp_enqueue_scripts', array( $this, 'load_javascript' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_javascript' ) );

		//Enqueue custom css
		add_action( 'wp_enqueue_scripts', array($this ,  'load_css') );
		add_action( 'admin_enqueue_scripts', array($this , 'load_css') );		
		
		// Shortcodes.
	}
	
	/**
	 * Loads textdomain for plugin.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wccb', false, WC_CLASS_BOOKING_PLUGIN_FOLDER.'/languages/' );
	}
	
	public function load_javascript(){
		wp_enqueue_script(
			"wccb-script-js",
			WC_CLASS_BOOKING_PLUGIN_URL ."/assets/js/wccb-scripts.js",
			array( 'jquery' ),
			1,
			1
		);

		$script_config 	= array( 
			'ajax_url' => admin_url("admin-ajax.php") 
		);
		wp_localize_script(
			'wccb-script-js',
			'wccb_config',
			$script_config
		);

	}

	//Enqueue css
	public function load_css()
	{
		wp_enqueue_style("wccb-styles",WSTDL_URL."/assets/css/wccb-styles.css",array(),1,"all");
	}
	
	public static function log($variable) {
		$upload_dir = wp_upload_dir();
		$base_dir 	= $upload_dir['basedir'];
		$base_url 	= $upload_dir['baseurl'];
		$upload_dir = $base_dir . "/wccb/";
		if (! is_dir($upload_dir)) {
			mkdir( $upload_dir, 0755 );
		}
		
		$log_file = $upload_dir.date_i18n('d-m-Y').'-log.txt';
		$myfile = fopen($log_file, "a") or die("Unable to open file!");
		ob_start();
		echo $variable.PHP_EOL;
		$txt = ob_get_clean();
		fwrite($myfile, $txt);
		fclose($myfile);
	}
}