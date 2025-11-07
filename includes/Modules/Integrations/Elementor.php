<?php

namespace VLT\Helper\Modules\Integrations;

use VLT\Helper\Modules\BaseModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Integration Module
 *
 * Handles Elementor widgets registration and integration
 */
class Elementor extends BaseModule {

	/**
	 * Module name
	 *
	 * @var string
	 */
	protected $name = 'elementor';

	/**
	 * Module version
	 *
	 * @var string
	 */
	protected $version = '1.0.0';

	/**
	 * Assets URL
	 *
	 * @var string
	 */
	private $assets_url;

	/**
	 * Check if module should load
	 *
	 * @return bool
	 */
	protected function can_register() {
		return defined( 'ELEMENTOR_VERSION' );
	}

	/**
	 * Initialize module
	 */
	protected function init() {
		$this->assets_url  = VLT_HELPER_URL . 'assets/';
	}

	/**
	 * Register module
	 */
	public function register() {
		add_action( 'elementor/init', [ $this, 'init_elementor' ] );
	}

	/**
	 * Initialize Elementor integration
	 */
	public function init_elementor() {

		// Register widgets - support both old and new Elementor versions
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );

		// Register other hooks
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'editor_styles' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );
		add_action( 'elementor/theme/register_locations', [ $this, 'register_locations' ] );
		add_filter( 'elementor/icons_manager/additional_tabs', [ $this, 'add_icon_tabs' ] );
	}

	/**
	 * Include widget files
	 *
	 * Widget files should be loaded from theme using the action hook.
	 * Theme manages all widget file paths and loading.
	 */
	private function include_widget_files() {
		// Fire action to allow theme to load widget files from theme directory
		do_action( 'vlt_helper_elementor_register_widgets' );
	}

	/**
	 * Register widgets
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager = null ) {
		$this->include_widget_files();

		// Get widget manager
		if ( ! $widgets_manager ) {
			$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
		}

		// Get widget classes
		$widgets = $this->get_widget_classes();

		// Register each widget
		foreach ( $widgets as $widget_class ) {
			if ( class_exists( $widget_class ) ) {
				$widgets_manager->register( new $widget_class() );
			}
		}

		do_action( 'vlt_helper_elementor_widgets_registered' );
	}

	/**
	 * Get widget classes
	 *
	 * Returns empty array by default. Use 'vlt_helper_elementor_widget_classes' filter
	 * in theme to register widget classes.
	 *
	 * @return array
	 */
	private function get_widget_classes() {
		/**
		 * Filter Elementor widget classes
		 *
		 * Allows themes to specify which widget classes to register.
		 *
		 * @param array $widget_classes Array of widget class names.
		 */
		return apply_filters( 'vlt_helper_elementor_widget_classes', [] );
	}

	/**
	 * Register Elementor categories
	 *
	 * @param object $elements_manager Elementor elements manager.
	 */
	public function register_categories( $elements_manager ) {
		$elements_manager->add_category(
			'vlthemes-elements',
			[
				'title' => esc_html__( 'VLThemes Elements', 'vlt-helper' ),
				'icon'  => 'fa fa-plug',
			]
		);

		$elements_manager->add_category(
			'vlthemes-showcase',
			[
				'title' => esc_html__( 'VLThemes Showcase', 'vlt-helper' ),
				'icon'  => 'fa fa-image',
			]
		);

		$elements_manager->add_category(
			'vlthemes-woo',
			[
				'title' => esc_html__( 'VLThemes WooCommerce', 'vlt-helper' ),
				'icon'  => 'fa fa-shopping-cart',
			]
		);
	}

	/**
	 * Register Elementor theme locations
	 *
	 * @param object $elementor_theme_manager Elementor theme manager.
	 */
	public function register_locations( $elementor_theme_manager ) {
		$elementor_theme_manager->register_location( 'header' );
		$elementor_theme_manager->register_location( 'footer' );
		$elementor_theme_manager->register_location( '404' );
	}

	/**
	 * Enqueue editor styles
	 */
	public function editor_styles() {
		// Enqueue main editor CSS
		wp_enqueue_style(
			'vlt-helper-elementor',
			$this->assets_url . 'css/elementor.css',
			[],
			VLT_HELPER_VERSION
		);

		// Add inline CSS for badge customization
		$this->add_badge_styles();
	}

	/**
	 * Add badge styles to editor
	 */
	private function add_badge_styles() {
		$theme = wp_get_theme();
		$theme_name = $theme->get( 'Name' );

		$badge_config = apply_filters( 'vlt_helper_elementor_badge', [
			'text' => $theme_name,
		] );

		if ( empty( $badge_config['text'] ) ) {
			return;
		}

		$custom_css = sprintf(
			'#elementor-panel-elements-wrapper .elementor-element .icon i[class*="-badge"]::after,
			#elementor-panel-elements-wrapper .elementor-element .icon .vlt-badge::after {
				content: "%s";
			}',
			esc_attr( $badge_config['text'] )
		);

		wp_add_inline_style( 'vlt-helper-elementor', $custom_css );
	}

	/**
	 * Add custom icon tabs
	 *
	 * @param array $settings Icon settings.
	 * @return array
	 */
	public function add_icon_tabs( $settings ) {
		$icon_sets = $this->get_icon_sets();

		foreach ( $icon_sets as $key => $icon_set ) {
			// Check if icon set files exist before adding
			$css_path = str_replace( VLT_HELPER_URL, VLT_HELPER_PATH, $icon_set['url'] );
			if ( file_exists( $css_path ) ) {
				$settings[ $key ] = $icon_set;
			}
		}

		return apply_filters( 'vlt_helper_elementor_icon_tabs', $settings );
	}

	/**
	 * Get icon sets configuration
	 *
	 * @return array
	 */
	private function get_icon_sets() {
		return [
			// Socicons
			'socicons' => [
				'name'          => 'socicons',
				'label'         => esc_html__( 'Socicons', 'vlt-helper' ),
				'url'           => $this->assets_url . 'fonts/socicons/socicons.css',
				'enqueue'       => [ $this->assets_url . 'fonts/socicons/socicons.css' ],
				'prefix'        => 'socicon-',
				'displayPrefix' => false,
				'labelIcon'     => 'socicon-twitter',
				'fetchJson'     => $this->assets_url . 'fonts/socicons/elementor.json',
				'native'        => false,
				'ver'           => VLT_HELPER_VERSION,
			],
			// ET-Line Icons
			'etline' => [
				'name'          => 'etline',
				'label'         => esc_html__( 'ET-Line', 'vlt-helper' ),
				'url'           => $this->assets_url . 'fonts/etline/etl.css',
				'enqueue'       => [ $this->assets_url . 'fonts/etline/etl.css' ],
				'prefix'        => 'etl-',
				'displayPrefix' => false,
				'labelIcon'     => 'etl-desktop',
				'fetchJson'     => $this->assets_url . 'fonts/etline/elementor.json',
				'native'        => false,
				'ver'           => VLT_HELPER_VERSION,
			],
			// Icomoon
			'icomoon' => [
				'name'          => 'icomoon',
				'label'         => esc_html__( 'Icomoon', 'vlt-helper' ),
				'url'           => $this->assets_url . 'fonts/icomoon/icnm.css',
				'enqueue'       => [ $this->assets_url . 'fonts/icomoon/icnm.css' ],
				'prefix'        => 'icnm-',
				'displayPrefix' => false,
				'labelIcon'     => 'icnm-barcode',
				'fetchJson'     => $this->assets_url . 'fonts/icomoon/elementor.json',
				'native'        => false,
				'ver'           => VLT_HELPER_VERSION,
			],
			// Iconsmind
			'iconsmind' => [
				'name'          => 'iconsmind',
				'label'         => esc_html__( 'Iconsmind', 'vlt-helper' ),
				'url'           => $this->assets_url . 'fonts/iconsmind/iconsmind.css',
				'enqueue'       => [ $this->assets_url . 'fonts/iconsmind/iconsmind.css' ],
				'prefix'        => 'icnmd-',
				'displayPrefix' => false,
				'labelIcon'     => 'icnmd-ATM',
				'fetchJson'     => $this->assets_url . 'fonts/iconsmind/elementor.json',
				'native'        => false,
				'ver'           => VLT_HELPER_VERSION,
			],
			// Linearicons
			'linearicons' => [
				'name'          => 'linearicons',
				'label'         => esc_html__( 'Linearicons', 'vlt-helper' ),
				'url'           => $this->assets_url . 'fonts/linearicons/lnr.css',
				'enqueue'       => [ $this->assets_url . 'fonts/linearicons/lnr.css' ],
				'prefix'        => 'lnr-',
				'displayPrefix' => false,
				'labelIcon'     => 'lnr-book',
				'fetchJson'     => $this->assets_url . 'fonts/linearicons/elementor.json',
				'native'        => false,
				'ver'           => VLT_HELPER_VERSION,
			],
			// Elusive Icons
			'elusiveicons' => [
				'name'          => 'elusiveicons',
				'label'         => esc_html__( 'Elusive Icons', 'vlt-helper' ),
				'url'           => $this->assets_url . 'fonts/elusiveicons/el.css',
				'enqueue'       => [ $this->assets_url . 'fonts/elusiveicons/el.css' ],
				'prefix'        => 'el-',
				'displayPrefix' => false,
				'labelIcon'     => 'el-address-book',
				'fetchJson'     => $this->assets_url . 'fonts/elusiveicons/elementor.json',
				'native'        => false,
				'ver'           => VLT_HELPER_VERSION,
			],
			// Icofont
			'icofont' => [
				'name'          => 'icofont',
				'label'         => esc_html__( 'Icofont', 'vlt-helper' ),
				'url'           => $this->assets_url . 'fonts/icofont/icofont.css',
				'enqueue'       => [ $this->assets_url . 'fonts/icofont/icofont.css' ],
				'prefix'        => 'icofont-',
				'displayPrefix' => false,
				'labelIcon'     => 'icofont-cop',
				'fetchJson'     => $this->assets_url . 'fonts/icofont/elementor.json',
				'native'        => false,
				'ver'           => VLT_HELPER_VERSION,
			],
		];
	}

	/**
	 * Static helper methods for Elementor widgets
	 */

	/**
	 * Get post names by post type
	 *
	 * @param string $post_type Post type.
	 * @return array Posts list.
	 */
	public static function get_post_name( $post_type = 'post' ) {
		$options = [];

		$all_post = [
			'posts_per_page' => -1,
			'post_type'      => $post_type,
		];

		$post_terms = get_posts( $all_post );

		if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {
			foreach ( $post_terms as $term ) {
				$options[ $term->ID ] = $term->post_title;
			}
		}

		return $options;
	}

	/**
	 * Get post types
	 *
	 * @param array $args Arguments.
	 * @return array Post types list.
	 */
	public static function get_post_types( $args = [] ) {
		$post_type_args = [
			'show_in_nav_menus' => true,
		];

		if ( ! empty( $args['post_type'] ) ) {
			$post_type_args['name'] = $args['post_type'];
		}

		$_post_types = get_post_types( $post_type_args, 'objects' );

		$post_types = [];
		foreach ( $_post_types as $post_type => $object ) {
			$post_types[ $post_type ] = $object->label;
		}

		return $post_types;
	}

	/**
	 * Get all sidebars
	 *
	 * @return array Sidebars list.
	 */
	public static function get_all_sidebars() {
		global $wp_registered_sidebars;

		$options = [];

		if ( ! $wp_registered_sidebars ) {
			$options[''] = esc_html__( 'No sidebars were found', 'vlt-helper' );
		} else {
			$options[''] = esc_html__( 'Choose Sidebar', 'vlt-helper' );

			foreach ( $wp_registered_sidebars as $sidebar_id => $sidebar ) {
				$options[ $sidebar_id ] = $sidebar['name'];
			}
		}

		return $options;
	}

	/**
	 * Get all types of posts
	 *
	 * @return array Posts list.
	 */
	public static function get_all_types_post() {
		$posts = get_posts( [
			'post_type'      => 'any',
			'post_style'     => 'all_types',
			'post_status'    => 'publish',
			'posts_per_page' => '-1',
		] );

		if ( ! empty( $posts ) ) {
			return wp_list_pluck( $posts, 'post_title', 'ID' );
		}

		return [];
	}

	/**
	 * Get post type categories
	 *
	 * @param string $type Type of value to return (term_id, slug, etc).
	 * @return array Categories list.
	 */
	public static function get_post_type_categories( $type = 'term_id' ) {
		$options = [];

		$terms = get_terms( [
			'taxonomy'   => 'category',
			'hide_empty' => true,
		] );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->{$type} ] = $term->name;
			}
		}

		return $options;
	}

	/**
	 * Get taxonomies
	 *
	 * @param string $taxonomy Taxonomy name.
	 * @return array Taxonomies list.
	 */
	public static function get_taxonomies( $taxonomy = 'category' ) {
		$options = [];

		$terms = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		] );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}

		return $options;
	}

	/**
	 * Get available menus
	 *
	 * @return array Menus list.
	 */
	public static function get_available_menus() {
		$options = [];
		$menus   = wp_get_nav_menus();

		foreach ( $menus as $menu ) {
			$options[ $menu->slug ] = $menu->name;
		}

		return $options;
	}

	/**
	 * Get Elementor templates
	 *
	 * @param string|null $type Template type.
	 * @return array Templates list.
	 */
	public static function get_elementor_templates( $type = null ) {
		$args = [
			'post_type'      => 'elementor_library',
			'posts_per_page' => -1,
		];

		if ( $type ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'elementor_library_type',
					'field'    => 'slug',
					'terms'    => $type,
				],
			];
		}

		$page_templates = get_posts( $args );

		$options[0] = esc_html__( 'Select a Template', 'vlt-helper' );

		if ( ! empty( $page_templates ) && ! is_wp_error( $page_templates ) ) {
			foreach ( $page_templates as $post ) {
				$options[ $post->ID ] = $post->post_title;
			}
		} else {
			$options[0] = esc_html__( 'Create a Template First', 'vlt-helper' );
		}

		return $options;
	}

	/**
	 * Render Elementor template
	 *
	 * @param int $template_id Template ID to render.
	 * @return string Rendered template HTML.
	 */
	public static function render_template( $template_id ) {
		if ( ! $template_id || ! class_exists( '\Elementor\Frontend' ) ) {
			return '';
		}

		// Only render published templates
		if ( 'publish' !== get_post_status( $template_id ) ) {
			return '';
		}

		// Get rendered template content
		$content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id, false );

		// Force enqueue Elementor styles for proper rendering
		\Elementor\Plugin::$instance->frontend->enqueue_styles();

		return $content;
	}

}
