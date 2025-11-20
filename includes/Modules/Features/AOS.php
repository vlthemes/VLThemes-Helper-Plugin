<?php

namespace VLT\Toolkit\Modules\Features;

use VLT\Toolkit\Modules\BaseModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AOS (Animate On Scroll) Module
 *
 * Provides scroll-based animations using AOS library
 * Integrates with Elementor for element animations
 */
class AOS extends BaseModule {


	/**
	 * Module name
	 *
	 * @var string
	 */
	protected $name = 'aos';

	/**
	 * Module version
	 *
	 * @var string
	 */
	protected $version = '1.0.0';

	/**
	 * Register module
	 */
	public function register() {
		// Enqueue AOS assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue AOS CSS and JS
	 */
	public function enqueue_assets() {
		wp_enqueue_style( 'aos' );
		wp_enqueue_script( 'aos' );
	}

	/**
	 * Get all available animations
	 *
	 * @return array Array of animation options.
	 */
	public static function get_animations() {
		$custom_animations = array();

		$custom_animations = apply_filters( 'vlt_toolkit_aos_animations', $custom_animations );

		$default_animations = array(
			'fade'            => esc_html__( 'Fade', 'vlthemes-toolkit' ),
			'fade-up'         => esc_html__( 'Fade Up', 'vlthemes-toolkit' ),
			'fade-down'       => esc_html__( 'Fade Down', 'vlthemes-toolkit' ),
			'fade-left'       => esc_html__( 'Fade Left', 'vlthemes-toolkit' ),
			'fade-right'      => esc_html__( 'Fade Right', 'vlthemes-toolkit' ),
			'fade-up-right'   => esc_html__( 'Fade Up Right', 'vlthemes-toolkit' ),
			'fade-up-left'    => esc_html__( 'Fade Up Left', 'vlthemes-toolkit' ),
			'fade-down-right' => esc_html__( 'Fade Down Right', 'vlthemes-toolkit' ),
			'fade-down-left'  => esc_html__( 'Fade Down Left', 'vlthemes-toolkit' ),

			'flip-up'         => esc_html__( 'Flip Up', 'vlthemes-toolkit' ),
			'flip-down'       => esc_html__( 'Flip Down', 'vlthemes-toolkit' ),
			'flip-left'       => esc_html__( 'Flip Left', 'vlthemes-toolkit' ),
			'flip-right'      => esc_html__( 'Flip Right', 'vlthemes-toolkit' ),

			'slide-up'        => esc_html__( 'Slide Up', 'vlthemes-toolkit' ),
			'slide-down'      => esc_html__( 'Slide Down', 'vlthemes-toolkit' ),
			'slide-left'      => esc_html__( 'Slide Left', 'vlthemes-toolkit' ),
			'slide-right'     => esc_html__( 'Slide Right', 'vlthemes-toolkit' ),

			'zoom-in'         => esc_html__( 'Zoom In', 'vlthemes-toolkit' ),
			'zoom-in-up'      => esc_html__( 'Zoom In Up', 'vlthemes-toolkit' ),
			'zoom-in-down'    => esc_html__( 'Zoom In Down', 'vlthemes-toolkit' ),
			'zoom-in-left'    => esc_html__( 'Zoom In Left', 'vlthemes-toolkit' ),
			'zoom-in-right'   => esc_html__( 'Zoom In Right', 'vlthemes-toolkit' ),
			'zoom-out'        => esc_html__( 'Zoom Out', 'vlthemes-toolkit' ),
			'zoom-out-up'     => esc_html__( 'Zoom Out Up', 'vlthemes-toolkit' ),
			'zoom-out-down'   => esc_html__( 'Zoom Out Down', 'vlthemes-toolkit' ),
			'zoom-out-left'   => esc_html__( 'Zoom Out Left', 'vlthemes-toolkit' ),
			'zoom-out-right'  => esc_html__( 'Zoom Out Right', 'vlthemes-toolkit' ),
		);

		$all_animations = array_merge( $custom_animations, $default_animations );

		$result = array( 'none' => esc_html__( 'None', 'vlthemes-toolkit' ) );

		return array_merge( $result, $all_animations );
	}

	/**
	 * Get AOS data attributes as array
	 *
	 * @param string $animation Animation name.
	 * @param array  $args      Additional arguments (duration, delay, offset, once, etc.).
	 * @return array Data attributes array.
	 */
	public static function get_render_attrs( $animation, $args = array() ) {
		if ( empty( $animation ) || $animation === 'none' ) {
			return array();
		}

		$defaults = array(
			'duration' => '',
			'delay'    => '',
			'offset'   => '',
			'once'     => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$attrs = array(
			'data-aos' => esc_attr( $animation ),
		);

		if ( ! empty( $args['duration'] ) ) {
			$attrs['data-aos-duration'] = esc_attr( $args['duration'] * 1000 );
		}

		if ( ! empty( $args['delay'] ) ) {
			$attrs['data-aos-delay'] = esc_attr( $args['delay'] * 1000 );
		}

		if ( ! empty( $args['offset'] ) ) {
			$attrs['data-aos-offset'] = esc_attr( $args['offset'] );
		}

		if ( ! empty( $args['once'] ) ) {
			$attrs['data-aos-once'] = esc_attr( $args['once'] );
		}

		return $attrs;
	}

	/**
	 * Build AOS data attributes string
	 *
	 * @param string $animation Animation name.
	 * @param array  $args      Additional arguments (duration, delay, offset, once, etc.).
	 * @return string Data attributes string.
	 */
	public static function render_attrs( $animation, $args = array() ) {
		$attrs = self::get_render_attrs( $animation, $args );

		if ( empty( $attrs ) ) {
			return '';
		}

		$output = array();
		foreach ( $attrs as $key => $value ) {
			$output[] = sprintf( '%s="%s"', $key, $value );
		}

		return implode( ' ', $output );
	}
}
