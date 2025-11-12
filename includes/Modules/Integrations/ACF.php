<?php

namespace VLT\Helper\Modules\Integrations;

use VLT\Helper\Modules\BaseModule;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Advanced Custom Fields Integration Module
 *
 * Provides integration hooks for ACF plugin
 * Handles JSON save/load paths and admin visibility
 * Provides static helper methods for dynamic field population (used in themes)
 */
class ACF extends BaseModule
{

	/**
	 * Module name
	 *
	 * @var string
	 */
	protected $name = 'acf';

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
	protected function can_register()
	{
		return class_exists('ACF');
	}

	/**
	 * Register module
	 */
	public function register()
	{
		// Hide ACF in admin if needed
		add_filter('acf/settings/show_admin', [$this, 'show_admin']);

		// Set save/load points for JSON
		add_filter('acf/settings/save_json', [$this, 'save_json']);
		add_filter('acf/settings/load_json', [$this, 'load_json']);
	}

	/**
	 * Control ACF admin visibility
	 *
	 * @param bool $show Whether to show ACF in admin.
	 * @return bool Filtered value.
	 */
	public function show_admin($show)
	{
		return apply_filters('vlt_helper_acf_show_admin', $show);
	}

	/**
	 * Set ACF JSON save path
	 *
	 * @param string $path Default save path.
	 * @return string Filtered save path.
	 */
	public function save_json($path)
	{
		return apply_filters('vlt_helper_acf_save_json', $path);
	}

	/**
	 * Set ACF JSON load paths
	 *
	 * @param array $paths Default load paths.
	 * @return array Filtered load paths.
	 */
	public function load_json($paths)
	{
		return apply_filters('vlt_helper_acf_load_json', $paths);
	}

	/**
	 * Populate field with Elementor templates
	 *
	 * @param array       $field ACF field array.
	 * @param string|null $type  Template type (page, section, widget, etc.).
	 * @return array Modified field with template choices.
	 */
	public static function populate_elementor_templates($field, $type = null)
	{
		// Reset choices
		$field['choices'] = [];

		// Use helper function if available
		if (function_exists('vlt_get_elementor_templates')) {
			$field['choices'] = vlt_get_elementor_templates($type);
			return apply_filters('vlt_helper_acf_elementor_templates', $field, $type);
		}

		$field['choices'][0] = esc_html__('Elementor not available', 'vlt-helper');
		return $field;
	}

	/**
	 * Populate field with Visual Portfolio saved layouts
	 *
	 * @param array $field ACF field array.
	 * @return array Modified field with layout choices.
	 */
	public static function populate_vp_saved_layouts($field)
	{
		// Reset choices
		$field['choices'] = [];

		// Use helper function if available
		if (function_exists('vlt_get_vp_portfolios')) {
			$portfolios = vlt_get_vp_portfolios();

			// Add default option with proper text
			$field['choices'][0] = esc_html__('Select a Layout', 'vlt-helper');

			// Format with ID prefix
			foreach ($portfolios as $id => $title) {
				if ($id > 0) { // Skip the default option from helper
					$field['choices'][$id] = '#' . $id . ' - ' . $title;
				}
			}

			return apply_filters('vlt_helper_acf_vp_layouts', $field);
		}

		$field['choices'][0] = esc_html__('Visual Portfolio not available', 'vlt-helper');
		return $field;
	}

	/**
	 * Populate field with social icons
	 *
	 * @param array $field ACF field array.
	 * @return array Modified field with icon choices.
	 */
	public static function populate_social_icons($field)
	{
		// Reset choices
		$field['choices'] = [];

		// Check if social icons function exists
		if (! function_exists('vlt_get_social_icons')) {
			$field['choices'][0] = esc_html__('No social icons available', 'vlt-helper');
			return $field;
		}

		$social_icons = vlt_get_social_icons();

		// Populate choices
		if (! empty($social_icons)) {
			foreach ($social_icons as $icon_class => $icon_label) {
				$field['choices'][$icon_class] = $icon_label;
			}
		} else {
			$field['choices'][0] = esc_html__('No social icons available', 'vlt-helper');
		}

		return apply_filters('vlt_helper_acf_social_icons', $field);
	}
}
