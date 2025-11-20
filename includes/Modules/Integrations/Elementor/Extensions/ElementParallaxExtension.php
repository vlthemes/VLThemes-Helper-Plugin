<?php

namespace VLT\Toolkit\Modules\Integrations\Elementor\Extensions;

use VLT\Toolkit\Modules\Integrations\Elementor\BaseExtension;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element Parallax Extension
 *
 * Adds parallax effects to Elementor elements using GSAP ScrollTrigger
 */
class ElementParallaxExtension extends BaseExtension {


	/**
	 * Extension name
	 *
	 * @var string
	 */
	protected $name = 'element_parallax';

	/**
	 * Initialize extension
	 */
	protected function init() {
		// Extension initialization
	}

	/**
	 * Register extension scripts
	 */
	protected function register_scripts() {
		wp_enqueue_script(
			'vlt-element-parallax-extension',
			plugin_dir_url( __FILE__ ) . 'js/ElementParallaxExtension.js',
			array( 'gsap', 'scrolltrigger' ),
			VLT_TOOLKIT_VERSION,
			true
		);
	}

	/**
	 * Register WordPress hooks
	 */
	protected function register_hooks() {
		// Register controls for containers
		add_action( 'elementor/element/container/section_layout/after_section_end', array( $this, 'register_controls' ), 10, 2 );

		// Register controls for common widgets
		add_action( 'elementor/element/common/_section_style/after_section_end', array( $this, 'register_controls' ), 10, 2 );

		// Render for containers
		add_action( 'elementor/frontend/container/before_render', array( $this, 'render_attributes' ) );

		// Render for common widgets
		add_action( 'elementor/frontend/widget/before_render', array( $this, 'render_attributes' ) );
	}

	/**
	 * Register Element Parallax controls
	 *
	 * Adds parallax controls to Elementor containers and widgets
	 * Controls are defined here, but functionality is in ElementParallax module
	 *
	 * @param object $element Elementor element instance.
	 * @param array  $args    Element arguments.
	 */
	public function register_controls( $element, $args ) {
		$element->start_controls_section(
			'vlt_section_element_parallax',
			array(
				'label' => esc_html__( 'VLT Element Parallax', 'vlthemes-toolkit' ),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			)
		);

		$element->add_control(
			'vlt_parallax_enabled',
			array(
				'label'        => esc_html__( 'Enable Parallax', 'vlthemes-toolkit' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		// Horizontal Scroll Popover
		$element->add_control(
			'vlt_parallax_horizontal_popover',
			array(
				'label'     => esc_html__( 'Horizontal Scroll', 'vlthemes-toolkit' ),
				'type'      => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'condition' => array(
					'vlt_parallax_enabled' => 'yes',
				),
			)
		);

		$element->start_popover();

		$element->add_control(
			'vlt_parallax_x',
			array(
				'label'       => esc_html__( 'Parallax X (px)', 'vlthemes-toolkit' ),
				'description' => esc_html__( 'Distance to move horizontally during scroll', 'vlthemes-toolkit' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => -500,
						'max'  => 500,
						'step' => 5,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 0,
				),
			)
		);

		$element->end_popover();

		// Vertical Scroll Popover
		$element->add_control(
			'vlt_parallax_vertical_popover',
			array(
				'label'     => esc_html__( 'Vertical Scroll', 'vlthemes-toolkit' ),
				'type'      => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'condition' => array(
					'vlt_parallax_enabled' => 'yes',
				),
			)
		);

		$element->start_popover();

		$element->add_control(
			'vlt_parallax_y',
			array(
				'label'       => esc_html__( 'Parallax Y (px)', 'vlthemes-toolkit' ),
				'description' => esc_html__( 'Distance to move vertically during scroll', 'vlthemes-toolkit' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => -500,
						'max'  => 500,
						'step' => 5,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 0,
				),
			)
		);

		$element->end_popover();

		// Transparency Popover
		$element->add_control(
			'vlt_parallax_opacity_popover',
			array(
				'label'     => esc_html__( 'Transparency', 'vlthemes-toolkit' ),
				'type'      => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'condition' => array(
					'vlt_parallax_enabled' => 'yes',
				),
			)
		);

		$element->start_popover();

		$element->add_control(
			'vlt_parallax_opacity_start',
			array(
				'label'       => esc_html__( 'Opacity Start', 'vlthemes-toolkit' ),
				'description' => esc_html__( 'Starting opacity value (0-1)', 'vlthemes-toolkit' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 1,
				'step'        => 0.1,
			)
		);

		$element->add_control(
			'vlt_parallax_opacity_end',
			array(
				'label'       => esc_html__( 'Opacity End', 'vlthemes-toolkit' ),
				'description' => esc_html__( 'Ending opacity value (0-1)', 'vlthemes-toolkit' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 1,
				'step'        => 0.1,
			)
		);

		$element->end_popover();

		// Scale Popover
		$element->add_control(
			'vlt_parallax_scale_popover',
			array(
				'label'     => esc_html__( 'Scale', 'vlthemes-toolkit' ),
				'type'      => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'condition' => array(
					'vlt_parallax_enabled' => 'yes',
				),
			)
		);

		$element->start_popover();

		$element->add_control(
			'vlt_parallax_scale_start',
			array(
				'label'       => esc_html__( 'Scale Start', 'vlthemes-toolkit' ),
				'description' => esc_html__( 'Starting scale value (0.1-5)', 'vlthemes-toolkit' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 0.1,
				'max'         => 5,
				'step'        => 0.1,
			)
		);

		$element->add_control(
			'vlt_parallax_scale_end',
			array(
				'label'       => esc_html__( 'Scale End', 'vlthemes-toolkit' ),
				'description' => esc_html__( 'Ending scale value (0.1-5)', 'vlthemes-toolkit' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 0.1,
				'max'         => 5,
				'step'        => 0.1,
			)
		);

		$element->end_popover();

		// Parent Selector
		$element->add_control(
			'vlt_parallax_parent',
			array(
				'label'       => esc_html__( 'Parent Selector', 'vlthemes-toolkit' ),
				'description' => esc_html__( 'CSS selector of parent element to use as trigger (e.g., .parent-class)', 'vlthemes-toolkit' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'separator'   => 'before',
				'condition'   => array(
					'vlt_parallax_enabled' => 'yes',
				),
			)
		);

		$element->end_controls_section();
	}

	/**
	 * Render Element Parallax attributes
	 *
	 * @param object $widget Elementor widget instance.
	 */
	public function render_attributes( $widget ) {
		$settings = $widget->get_settings_for_display();

		if ( empty( $settings['vlt_parallax_enabled'] ) || $settings['vlt_parallax_enabled'] !== 'yes' ) {
			return;
		}

		// Add parallax class
		$widget->add_render_attribute( '_wrapper', 'class', 'vlt-element-parallax' );

		// Parent selector
		if ( ! empty( $settings['vlt_parallax_parent'] ) ) {
			$widget->add_render_attribute( '_wrapper', 'data-element-parallax-parent', $settings['vlt_parallax_parent'] );
		}

		// Parallax X and Y
		$y = isset( $settings['vlt_parallax_y']['size'] ) && is_numeric( $settings['vlt_parallax_y']['size'] ) ? $settings['vlt_parallax_y']['size'] : 0;
		$x = isset( $settings['vlt_parallax_x']['size'] ) && is_numeric( $settings['vlt_parallax_x']['size'] ) ? $settings['vlt_parallax_x']['size'] : 0;

		if ( $y !== 0 || $x !== 0 ) {
			$widget->add_render_attribute( '_wrapper', 'data-element-parallax', "{$y} {$x}" );
		}

		// Opacity
		$opacity_start = $settings['vlt_parallax_opacity_start'] ?? null;
		$opacity_end   = $settings['vlt_parallax_opacity_end'] ?? null;

		if ( is_numeric( $opacity_start ) || is_numeric( $opacity_end ) ) {
			$opacity_val = '';
			if ( is_numeric( $opacity_start ) && is_numeric( $opacity_end ) ) {
				$opacity_val = "{$opacity_start} {$opacity_end}";
			} elseif ( is_numeric( $opacity_start ) ) {
				$opacity_val = (string) $opacity_start;
			} elseif ( is_numeric( $opacity_end ) ) {
				$opacity_val = (string) $opacity_end;
			}

			if ( $opacity_val !== '' ) {
				$widget->add_render_attribute( '_wrapper', 'data-element-opacity', $opacity_val );
			}
		}

		// Scale
		$scale_start = $settings['vlt_parallax_scale_start'] ?? null;
		$scale_end   = $settings['vlt_parallax_scale_end'] ?? null;

		if ( is_numeric( $scale_start ) || is_numeric( $scale_end ) ) {
			$scale_val = '';
			if ( is_numeric( $scale_start ) && is_numeric( $scale_end ) ) {
				$scale_val = "{$scale_start} {$scale_end}";
			} elseif ( is_numeric( $scale_start ) ) {
				$scale_val = (string) $scale_start;
			} elseif ( is_numeric( $scale_end ) ) {
				$scale_val = (string) $scale_end;
			}

			if ( $scale_val !== '' ) {
				$widget->add_render_attribute( '_wrapper', 'data-element-scale', $scale_val );
			}
		}
	}
}
