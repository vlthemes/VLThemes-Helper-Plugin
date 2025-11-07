<?php
/**
 * Socials Widget
 *
 * @package VLT Helper
 */

namespace VLT\Helper\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Links Widget
 */
class Socials extends \WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		$widget_options = [
			'classname'   => 'vlt-widget-socials',
			'description' => esc_html__( 'Displays social media links with icons.', 'vlt-helper' ),
		];

		parent::__construct(
			'vlt_widget_socials',
			esc_html__( 'VLThemes: Social Links', 'vlt-helper' ),
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
		$social_style = get_field( 'social_style', 'widget_' . $widget_id );

		// Output title
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Output social links
		if ( have_rows( 'social_list', 'widget_' . $widget_id ) ) {
			while ( have_rows( 'social_list', 'widget_' . $widget_id ) ) {
				the_row();

				$social_icon = get_sub_field( 'social_icon' );
				$social_url = get_sub_field( 'social_url' );

				if ( empty( $social_icon ) || empty( $social_url ) ) {
					continue;
				}

				$class = 'vlt-social-icon';
				$class .= ' vlt-social-icon--' . esc_attr( $social_style );
				$class .= ' ' . str_replace( 'socicon-', '', $social_icon );

				printf(
					'<a class="%s" href="%s" target="_blank" rel="noopener noreferrer"><i class="%s"></i></a>',
					esc_attr( $class ),
					esc_url( $social_url ),
					esc_attr( $social_icon )
				);
			}
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
			<?php esc_html_e( 'Configure social links and style using Advanced Custom Fields in the widget settings below.', 'vlt-helper' ); ?>
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
