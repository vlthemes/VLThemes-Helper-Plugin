<?php

/**
 * Popular Posts Widget
 *
 * @package VLT Helper
 */

namespace VLT\Helper\Widgets;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Popular Posts Widget
 */
class PopularPosts extends PostsWidget
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$widget_options = [
			'classname'   => 'vlt-widget-popular-posts',
			'description' => esc_html__('Displays popular posts (by comment count).', 'vlthemes-toolkit'),
		];

		parent::__construct(
			'vlt_widget_popular_posts',
			esc_html__('VLThemes: Popular Posts', 'vlthemes-toolkit'),
			$widget_options
		);
	}

	/**
	 * Output widget content
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget($args, $instance)
	{
		if (! isset($args['widget_id'])) {
			$args['widget_id'] = $this->id;
		}

		// Check ACF availability
		if (! $this->check_acf_availability($args)) {
			return;
		}

		$title = ! empty($instance['title']) ? $instance['title'] : '';
		$title = apply_filters('widget_title', $title, $instance, $this->id_base);

		$widget_id = $args['widget_id'];
		$posts_count = $this->get_acf_field('popular_posts_number_of_posts', $widget_id, 5);
		$layout = $this->get_acf_field('popular_posts_layout', $widget_id, 'list');

		// Query popular posts (by comment count)
		$query_args = [
			'post_type'           => 'post',
			'posts_per_page'      => absint($posts_count),
			'orderby'             => 'comment_count',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'post_status'         => 'publish',
		];

		$query = new \WP_Query($query_args);

		if (! $query->have_posts()) {
			wp_reset_postdata();
			return;
		}

		// Output widget
		echo $args['before_widget'];

		if ($title) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		switch ($layout) {
			case 'list':
			default:
				while ($query->have_posts()) {
					$query->the_post();
					$this->render_list_item($query->post);
				}
				break;

			case 'slider':
?>
				<div class="vlt-widget-post-slider swiper-container swiper" data-tooltip="<?php esc_attr_e('Swipe', 'vlthemes-toolkit'); ?>">
					<div class="swiper-wrapper">
						<?php
						while ($query->have_posts()) {
							$query->the_post();
							$this->render_slider_item($query->post);
						}
						?>
					</div>
					<div class="vlt-slider-controls">
						<div class="vlt-swiper-pagination vlt-swiper-pagination--style-1"></div>
					</div>
				</div>
		<?php
				break;
		}

		echo $args['after_widget'];

		wp_reset_postdata();
	}

	/**
	 * Output widget form
	 *
	 * @param array $instance Widget instance.
	 */
	public function form($instance)
	{
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
				<?php esc_html_e('Title:', 'vlthemes-toolkit'); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo esc_attr($this->get_field_id('title')); ?>"
				name="<?php echo esc_attr($this->get_field_name('title')); ?>"
				type="text"
				value="<?php echo esc_attr($title); ?>" />
		</p>
		<p class="description">
			<?php esc_html_e('Configure layout and posts count using Advanced Custom Fields in the widget settings below.', 'vlthemes-toolkit'); ?>
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
	public function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = sanitize_text_field($new_instance['title']);
		return $instance;
	}
}
