<?php

namespace VLT\Helper\Modules\Features;

use VLT\Helper\Modules\BaseModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kirki Custom Fonts Module
 *
 * Integrates custom fonts with Kirki Customizer Framework
 * Adds support for Custom Fonts and TypeKit fonts in Kirki
 */
class KirkiCustomFonts extends BaseModule {

	/**
	 * Module name
	 *
	 * @var string
	 */
	protected $name = 'kirki_custom_fonts';

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
		add_action( 'init', [ $this, 'prepare_custom_fonts' ] );
		add_filter( 'vlthemes_fonts_list', [ $this, 'add_custom_fonts' ], 20 );
		add_filter( 'vlthemes_fonts_list', [ $this, 'add_typekit_fonts' ], 20 );
		add_filter( 'vlthemes_fonts_choices', [ $this, 'kirki_fonts_choices' ] );
	}

	/**
	 * Prepare custom fonts from Bsf Custom Fonts plugin
	 */
	public function prepare_custom_fonts() {
		// Check if Bsf Custom Fonts plugin is active
		if ( ! class_exists( 'Bsf_Custom_Fonts_Render' ) ) {
			return;
		}

		$fonts        = \Bsf_Custom_Fonts_Render::get_instance()->get_existing_font_posts();
		$custom_fonts = [];

		if ( ! empty( $fonts ) ) {
			foreach ( $fonts as $post_id ) {
				$font_family_name                = get_the_title( $post_id );
				$custom_fonts[ $font_family_name ] = $font_family_name;
			}
		}

		update_option( 'vlthemes-custom-fonts', $custom_fonts );
	}

	/**
	 * Add custom fonts to fonts list
	 *
	 * @param array $fonts Existing fonts.
	 * @return array Modified fonts list.
	 */
	public function add_custom_fonts( $fonts ) {
		$custom_fonts = get_option( 'vlthemes-custom-fonts', [] );

		if ( empty( $custom_fonts ) ) {
			return $fonts;
		}

		// Initialize families array if not exists
		if ( ! isset( $fonts['families'] ) ) {
			$fonts['families'] = [];
		}

		// Initialize variants array if not exists
		if ( ! isset( $fonts['variants'] ) ) {
			$fonts['variants'] = [];
		}

		// Add custom fonts group
		$fonts['families']['custom_fonts'] = [
			'text'     => esc_html__( 'Custom Fonts', 'vlt-helper' ),
			'children' => [],
		];

		// Add each custom font
		foreach ( $custom_fonts as $font => $key ) {
			$fonts['families']['custom_fonts']['children'][] = [
				'id'   => $font,
				'text' => $font,
			];

			// Add all font weights
			$fonts['variants'][ $font ] = [ '100', '200', '300', 'regular', '500', '600', '700', '800', '900' ];
		}

		return $fonts;
	}

	/**
	 * Add TypeKit fonts to fonts list
	 *
	 * @param array $fonts Existing fonts.
	 * @return array Modified fonts list.
	 */
	public function add_typekit_fonts( $fonts ) {
		$typekit_option = get_option( 'custom-typekit-fonts', [] );
		$typekit_fonts  = isset( $typekit_option['custom-typekit-font-details'] ) ? $typekit_option['custom-typekit-font-details'] : [];

		if ( empty( $typekit_fonts ) ) {
			return $fonts;
		}

		// Initialize families array if not exists
		if ( ! isset( $fonts['families'] ) ) {
			$fonts['families'] = [];
		}

		// Initialize variants array if not exists
		if ( ! isset( $fonts['variants'] ) ) {
			$fonts['variants'] = [];
		}

		// Add TypeKit fonts group
		$fonts['families']['typekit_fonts'] = [
			'text'     => esc_html__( 'TypeKit Fonts', 'vlt-helper' ),
			'children' => [],
		];

		// Add each TypeKit font
		foreach ( $typekit_fonts as $font ) {
			$font_id = $font['slug'];

			$fonts['families']['typekit_fonts']['children'][] = [
				'id'   => $font['slug'],
				'text' => $font['family'],
			];

			// Add font weights
			$fonts['variants'][ $font_id ] = isset( $font['weights'] ) ? $font['weights'] : [];
		}

		return $fonts;
	}

	/**
	 * Add custom fonts support to Kirki
	 *
	 * @param array $settings Existing Kirki font settings.
	 * @return array Modified font settings.
	 */
	public function kirki_fonts_choices( $settings = [] ) {
		// Get custom fonts list from filter
		$fonts_list = apply_filters( 'vlthemes_fonts_list', [] );

		// If no custom fonts, return original settings
		if ( empty( $fonts_list ) ) {
			return $settings;
		}

		// Prepare fonts settings for Kirki
		$fonts_settings = [
			'fonts' => [
				'google'   => [],
				'families' => isset( $fonts_list['families'] ) ? $fonts_list['families'] : [],
				'variants' => isset( $fonts_list['variants'] ) ? $fonts_list['variants'] : [],
			],
		];

		// Merge with existing settings
		$fonts_settings = array_merge( (array) $settings, (array) $fonts_settings );

		return apply_filters( 'vlt_helper_kirki_fonts_choices', $fonts_settings );
	}
}
