<?php

/**
 * Dashboard Activate Theme Template
 *
 * @author VLThemes
 * @version 1.0
 */

if (! defined('ABSPATH')) {
	exit;
}

?>

<div class="vlt-masonry-grid">
	<div class="vlt-masonry-sizer"></div>

	<div class="vlt-masonry-item">

		<?php
		/**
		 * Theme License Activation Form
		 * Hook for theme activation form
		 */
		do_action('vlt_dashboard_print_form');
		?>

	</div>

	<div class="vlt-masonry-item">
		<div class="vlt-widget">

			<div class="vlt-widget__title">
				<mark class="elements"><?php echo esc_html_e('Envato Elements Customer?', 'vlt-helper'); ?></mark>
			</div>

			<div class="vlt-widget__content">
				<p>
					<?php
					echo wp_kses(
						__('The theme activation is possible only for items purchased from <strong>Themeforest</strong> when you have the purchase code.', 'vlt-helper'),
						array(
							'strong' => array() // разрешаем только <strong>
						)
					);
					?>
				</p>

				<p class="mt-sm">
					<?php
					echo wp_kses(
						__('You can <strong>skip the activation</strong> step if you don\'t have a purchase code. The core features of the theme are fully functional without activation.', 'vlt-helper'),
						array(
							'strong' => array()
						)
					);
					?>
				</p>
				<div class="notice notice-info inline mt-sm">
					<p>
						<?php echo esc_html_e('Please be aware that item support is not provided for products obtained through Envato Elements.', 'vlt-helper'); ?>
					</p>
				</div>

			</div>

		</div>
		<!-- /.vlt-widget -->
	</div>

</div>