<?php

namespace VLT\Toolkit\Modules\Integrations\Elementor\Extensions;

use VLT\Toolkit\Modules\Integrations\Elementor\BaseExtension;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AOS Animation Extension
 *
 * Handles AOS (Animate On Scroll) animations
 */
class AosExtension extends BaseExtension {


	/**
	 * Extension name
	 *
	 * @var string
	 */
	protected $name = 'aos';

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
		wp_enqueue_style( 'aos' );
		wp_enqueue_script(
			'vlt-aos-extension',
			plugin_dir_url( __FILE__ ) . 'js/AosExtension.js',
			array( 'aos' ),
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
	 * Register AOS animation controls
	 *
	 * @param object $element Elementor element.
	 * @param array  $args    Element arguments.
	 */
	public function register_controls( $element, $args ) {
		$element->start_controls_section(
			'vlt_section_aos_animation',
			array(
				'label' => esc_html__( 'VLT Entrance Animation', 'vlthemes-toolkit' ),
				'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED,
			)
		);

		$element->add_control(
			'vlt_aos_animation',
			array(
				'label'   => esc_html__( 'Entrance Animation', 'vlthemes-toolkit' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->get_aos_animations(),
				'default' => 'none',
			)
		);

		$element->add_control(
			'vlt_aos_settings_popover',
			array(
				'label'     => esc_html__( 'Animation Settings', 'vlthemes-toolkit' ),
				'type'      => \Elementor\Controls_Manager::POPOVER_TOGGLE,
				'condition' => array( 'vlt_aos_animation!' => 'none' ),
			)
		);

		$element->start_popover();

		$element->add_control(
			'vlt_aos_duration',
			array(
				'label'       => esc_html__( 'Duration (seconds)', 'vlthemes-toolkit' ),
				'description' => esc_html__( 'Animation duration in seconds', 'vlthemes-toolkit' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 3,
						'step' => 0.1,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 1,
				),
			)
		);

		$element->add_control(
			'vlt_aos_delay',
			array(
				'label'       => esc_html__( 'Delay (seconds)', 'vlthemes-toolkit' ),
				'description' => esc_html__( 'Delay before animation starts in seconds', 'vlthemes-toolkit' ),
				'type'        => \Elementor\Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min'  => 0,
						'max'  => 3,
						'step' => 0.1,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 0,
				),
			)
		);

		$element->add_control(
			'vlt_aos_offset',
			array(
				'label'       => esc_html__( 'Offset (px)', 'vlthemes-toolkit' ),
				'description' => esc_html__( 'Distance from bottom of viewport to start', 'vlthemes-toolkit' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => -500,
				'max'         => 500,
				'step'        => 10,
			)
		);

		$element->add_control(
			'vlt_aos_once',
			array(
				'label'       => esc_html__( 'Animate Once', 'vlthemes-toolkit' ),
				'description' => esc_html__( 'Animate only once while scrolling down', 'vlthemes-toolkit' ),
				'type'        => \Elementor\Controls_Manager::SWITCHER,
				'default'     => 'yes',
			)
		);

		$element->end_popover();

		$element->end_controls_section();

		// Allow themes to add custom AOS controls
		do_action( 'vlt_toolkit_elementor_aos_controls', $element, $args );
	}

	/**
	 * Render AOS attributes
	 *
	 * @param object $widget Elementor widget instance.
	 */
	public function render_attributes( $widget ) {
		$settings = $widget->get_settings_for_display();

		if ( empty( $settings['vlt_aos_animation'] ) || $settings['vlt_aos_animation'] === 'none' ) {
			return;
		}

		// Add animation
		$widget->add_render_attribute( '_wrapper', 'data-aos', $settings['vlt_aos_animation'] );

		// Add duration (convert seconds to milliseconds)
		if ( ! empty( $settings['vlt_aos_duration']['size'] ) ) {
			$duration_ms = $settings['vlt_aos_duration']['size'] * 1000;
			$widget->add_render_attribute( '_wrapper', 'data-aos-duration', $duration_ms );
		}

		// Add delay (convert seconds to milliseconds)
		if ( ! empty( $settings['vlt_aos_delay']['size'] ) ) {
			$delay_ms = $settings['vlt_aos_delay']['size'] * 1000;
			$widget->add_render_attribute( '_wrapper', 'data-aos-delay', $delay_ms );
		}

		// Add offset
		if ( isset( $settings['vlt_aos_offset'] ) && $settings['vlt_aos_offset'] !== '' ) {
			$widget->add_render_attribute( '_wrapper', 'data-aos-offset', $settings['vlt_aos_offset'] );
		}

		// Add once
		if ( ! empty( $settings['vlt_aos_once'] ) ) {
			$once_value = $settings['vlt_aos_once'] === 'yes' ? 'true' : 'false';
			$widget->add_render_attribute( '_wrapper', 'data-aos-once', $once_value );
		}
	}

	/**
	 * Get AOS animations list
	 *
	 * @return array Array of animations.
	 */
	private function get_aos_animations() {
		// Check if AOS module is loaded
		if ( ! class_exists( 'VLT\Toolkit\Modules\Features\AOS' ) ) {
			return array( 'none' => esc_html__( 'None', 'vlthemes-toolkit' ) );
		}

		return \VLT\Toolkit\Modules\Features\AOS::get_animations();
	}
}
