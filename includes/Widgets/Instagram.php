<?php
/**
 * Instagram Widget
 *
 * @package VLT Helper
 */

namespace VLT\Helper\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Instagram Feed Widget
 */
class Instagram extends \WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		$widget_options = [
			'classname'   => 'vlt-widget-instagram',
			'description' => esc_html__( 'Displays Instagram feed using Visual Portfolio.', 'vlt-helper' ),
		];

		parent::__construct(
			'vlt_widget_instagram',
			esc_html__( 'VLThemes: Instagram Feed', 'vlt-helper' ),
			$widget_options
		);
	}

	/**
	 * Output widget content
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $args['before_widget'];

		// Check if ACF is available
		if ( ! function_exists( 'get_field' ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				echo '<p>' . esc_html__( 'Advanced Custom Fields plugin is required for this widget.', 'vlt-helper' ) . '</p>';
			}
			echo $args['after_widget'];
			return;
		}

		$widget_id = $args['widget_id'];
		$instagram_layout = get_field( 'instagram_saved_layout', 'widget_' . $widget_id );

		// Output title
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Output Visual Portfolio layout
		if ( ! empty( $instagram_layout ) ) {
			$layout_id = intval( $instagram_layout );
			echo \VLT\Helper\Modules\Integrations\VisualPortfolio::render_portfolio( $layout_id );
		}

		echo $args['after_widget'];
	}

	/**
	 * Output widget form
	 *
	 * @param array $instance Widget instance.
	 */
	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'vlt-helper' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $title ); ?>"
			/>
		</p>
		<p class="description">
			<?php esc_html_e( 'Configure Instagram layout using Advanced Custom Fields in the widget settings below.', 'vlt-helper' ); ?>
		</p>
		<?php
	}

	/**
	 * Update widget instance
	 *
	 * @param array $new_instance New instance.
	 * @param array $old_instance Old instance.
	 * @return array Updated instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		return $instance;
	}
}
