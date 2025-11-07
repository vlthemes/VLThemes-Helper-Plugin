<?php
/**
 * Dashboard License Activation Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="vlt-wrap">

	<h1 class="vlt-theme-dashboard__title">
		<?php echo esc_html_e( 'Activate your Licence', 'vlt-framework'); ?>
	</h1>

	<p class="vlt-theme-dashboard__subtitle">
		<?php echo sprintf( esc_html__( 'Enter your license key here, to activate the %s, and get full feature updates and premium support.', 'vlt-framework' ), vlt-framework_helper_plugin()->theme_name );?>
	</p>

	<div class="vlt-row">

		<div class="vlt-col-6">

			<div class="vlt-widget">

				<div class="vlt-widget__title">
					<?php if ( true ) { ?>
						<mark class="true"><?php esc_html_e( 'Theme Activated', 'vlt-framework' ); ?></mark>
						<span class="badge true"><?php esc_html_e( 'No Problems', 'vlt-framework' ); ?></span>
					<?php } else { ?>
						<mark class="false"><?php esc_html_e( 'Theme Not Activated', 'vlt-framework' ); ?></mark>
						<span class="badge false"><?php esc_html_e( 'Some Problems', 'vlt-framework' ); ?></span>
					<?php } ?>
				</div>

				<div class="vlt-widget__content">
					<?php do_action( 'vlt-framework_dashboard_print_form' ); ?>
				</div>

			</div>
			<!-- /.vlt-widget -->

		</div>

		<div class="vlt-col-4">

			<div class="vlt-widget">

				<div class="vlt-widget__title">
					<mark class="elements"><?php echo esc_html_e('Envato Elements Customer?', 'vlt-framework'); ?></mark>
				</div>

				<div class="vlt-widget__content">
					<p><?php echo esc_html_e( 'The theme activation is possible only for items purchased from Themeforest when you have the purchase code.', 'vlt-framework'); ?></p>
					<p><?php echo esc_html_e( 'You can skip the activation step if you don\'t have a purchase code. The core features of the theme are fully functional without activation, except for automatic updates.', 'vlt-framework'); ?></p>

					<div class="notice notice-info is-dismissible mt-sm">
						<p>
							<?php echo esc_html_e( 'Please be aware that item support is not provided for products obtained through Envato Elements.', 'vlt-framework'); ?>
						</p>
					</div>

				</div>

			</div>
			<!-- /.vlt-widget -->

		</div>

	</div>

</div>
<!-- /.vlt-wrap -->