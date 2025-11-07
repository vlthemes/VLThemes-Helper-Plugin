<?php

namespace VLT\Helper\Modules\Integrations;

use VLT\Helper\Modules\BaseModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Module
 */
class WooCommerce extends BaseModule {

	/**
	 * Module name
	 *
	 * @var string
	 */
	protected $name = 'woocommerce';

	/**
	 * Module version
	 *
	 * @var string
	 */
	protected $version = '1.0.0';

	/**
	 * Check if module should load
	 *
	 * @return bool
	 */
	protected function can_register() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Register module
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_scripts' ], 100 );
	}

	/**
	 * Dequeue unnecessary WooCommerce scripts
	 */
	public function dequeue_scripts() {
		// Dequeue selectWoo script
		wp_dequeue_script( 'selectWoo' );
		wp_deregister_script( 'selectWoo' );

		// Allow themes/plugins to dequeue additional scripts
		do_action( 'vlt_helper_woocommerce_dequeue_scripts' );
	}
}
