<?php
/**
 * VLT Helper Main Class
 *
 * @package VLT Helper
 */

namespace VLT\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Helper class
 */
class Helper {

	/**
	 * Instance
	 *
	 * @var Helper
	 */
	private static $instance = null;

	/**
	 * Modules
	 *
	 * @var array
	 */
	private $modules = array();

	/**
	 * Get instance
	 *
	 * @return Helper
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->load_textdomain();
		$this->load_base_module();
		$this->init_modules();
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Enqueue admin scripts
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
		add_action( 'customize_controls_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script(
			'vlt-helper-admin',
			VLT_HELPER_URL . 'assets/js/admin.js',
			[], // 'customize-controls'
			VLT_HELPER_VERSION,
			true
		);

		wp_enqueue_style(
			'vlt-helper-admin',
			VLT_HELPER_URL . 'assets/css/admin.css',
			[],
			VLT_HELPER_VERSION
		);
	}

	/**
	 * Load plugin text domain
	 */
	private function load_textdomain() {
		load_plugin_textdomain(
			'vlt-helper',
			false,
			dirname( plugin_basename( VLT_HELPER_FILE ) ) . '/languages/'
		);
	}

	/**
	 * Load base module class
	 */
	private function load_base_module() {
		require_once VLT_HELPER_PATH . 'includes/Modules/BaseModule.php';
	}

	/**
	 * Initialize modules
	 */
	private function init_modules() {
		$modules = array(
			// Core feature modules
			'Features\\PostTypes',
			'Features\\UploadMimes',
			'Features\\Widgets',
			'Features\\KirkiCustomFonts',
			'Features\\SocialIcons',
			'Features\\PostViews',
			// Integrations
			'Integrations\\Elementor',
			'Integrations\\ContactForm7',
			'Integrations\\VisualPortfolio',
			'Integrations\\WooCommerce',
			'Integrations\\ACF',
		);

		foreach ( $modules as $module ) {
			$this->load_module( $module );
		}

		do_action( 'vlt_helper/modules_loaded' );
	}

	/**
	 * Load module
	 *
	 * @param string $module Module class name.
	 */
	private function load_module( $module ) {
		$class_name = 'VLT\\Helper\\Modules\\' . $module;
		$file_path  = VLT_HELPER_PATH . 'includes/Modules/' . str_replace( '\\', '/', $module ) . '.php';

		if ( file_exists( $file_path ) ) {
			require_once $file_path;

			if ( class_exists( $class_name ) ) {
				$this->modules[ $module ] = $class_name::instance();
			}
		}
	}

	/**
	 * Get module
	 *
	 * @param string $module Module name.
	 * @return object|null
	 */
	public function get_module( $module ) {
		return isset( $this->modules[ $module ] ) ? $this->modules[ $module ] : null;
	}
}
