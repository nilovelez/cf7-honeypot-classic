<?php
/**
 * Settings for Classic Honeypot for CF7.
 *
 * Classic Honeypot for CF7 is a fork of "Honeypot for Contact Form 7" 2.1.1
 * by Nocean and contributors, released under the GPLv2 or later.
 * This file was modified by Nilo Vélez in October 2026. See the changelog in
 * readme.txt and the git history for details of every change.
 *
 * @package Classic_Honeypot_For_CF7
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get the default settings.
 *
 * @return array
 */
function choneypot4cf7_get_defaults() {
	return array(
		'store_honeypot'         => false,
		'placeholder'            => '',
		'accessibility_message'  => '',
		'w3c_valid_autocomplete' => false,
		'move_inline_css'        => false,
		'nomessage'              => false,
		'timecheck_enabled'      => false,
		'timecheck_value'        => 4,
	);
}

/**
 * Get the current settings, filled in with the defaults.
 *
 * @return array
 */
function choneypot4cf7_get_config() {
	$config = get_option( 'choneypot4cf7_config', array() );

	if ( ! is_array( $config ) ) {
		$config = array();
	}

	return wp_parse_args( $config, choneypot4cf7_get_defaults() );
}

/**
 * Add a "Settings" link to the plugin row on the Plugins screen.
 *
 * @param array $links Plugin action links.
 * @return array
 */
function choneypot4cf7_settings_link( $links ) {
	$url = add_query_arg( 'page', 'choneypot4cf7', admin_url( 'admin.php' ) );

	$links[] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'classic-honeypot-for-cf7' ) . '</a>';

	return $links;
}
add_filter( 'plugin_action_links_' . CHONEYPOT4CF7_PLUGIN_BASENAME, 'choneypot4cf7_settings_link' );

/**
 * Register the settings page under the Contact Form 7 menu.
 */
function choneypot4cf7_admin_menu() {
	add_submenu_page(
		'wpcf7',
		__( 'Classic Honeypot for CF7', 'classic-honeypot-for-cf7' ),
		__( 'Honeypot', 'classic-honeypot-for-cf7' ),
		'manage_options',
		'choneypot4cf7',
		'choneypot4cf7_admin_page'
	);
}
add_action( 'admin_menu', 'choneypot4cf7_admin_menu' );

/**
 * Load the settings page stylesheet, only on the settings page.
 *
 * @param string $hook Current admin page hook suffix.
 */
function choneypot4cf7_admin_enqueues( $hook ) {
	if ( false === strpos( $hook, 'choneypot4cf7' ) ) {
		return;
	}

	wp_enqueue_style( 'choneypot4cf7-admin-css', plugins_url( 'includes/css/styles.css', CHONEYPOT4CF7_PLUGIN ), array(), CHONEYPOT4CF7_VERSION );
}
add_action( 'admin_enqueue_scripts', 'choneypot4cf7_admin_enqueues' );

/**
 * Save or reset the settings when the settings form is submitted.
 *
 * @return string Message to show to the user, or an empty string.
 */
function choneypot4cf7_handle_settings_form() {
	if ( empty( $_POST['save'] ) && empty( $_POST['clear'] ) ) {
		return '';
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return '';
	}

	check_admin_referer( 'choneypot4cf7-submit', 'choneypot4cf7_nonce' );

	if ( ! empty( $_POST['clear'] ) ) {
		// Store the defaults instead of deleting the option, so the settings
		// of the original plugin are not imported again on the next request.
		update_option( 'choneypot4cf7_config', choneypot4cf7_get_defaults() );
		return __( 'The settings have been reset to their defaults.', 'classic-honeypot-for-cf7' );
	}

	$placeholder = '';
	if ( isset( $_POST['choneypot4cf7_placeholder'] ) ) {
		$placeholder = sanitize_text_field( wp_unslash( $_POST['choneypot4cf7_placeholder'] ) );
	}

	$accessibility_message = '';
	if ( isset( $_POST['choneypot4cf7_accessibility_message'] ) ) {
		$accessibility_message = sanitize_text_field( wp_unslash( $_POST['choneypot4cf7_accessibility_message'] ) );
	}

	$timecheck_value = 0;
	if ( isset( $_POST['choneypot4cf7_timecheck_value'] ) ) {
		$timecheck_value = absint( $_POST['choneypot4cf7_timecheck_value'] );
	}
	if ( $timecheck_value < 1 ) {
		$defaults        = choneypot4cf7_get_defaults();
		$timecheck_value = $defaults['timecheck_value'];
	}

	$config = array(
		'store_honeypot'         => isset( $_POST['choneypot4cf7_store'] ),
		'placeholder'            => $placeholder,
		'accessibility_message'  => $accessibility_message,
		'w3c_valid_autocomplete' => isset( $_POST['choneypot4cf7_w3c_valid_autocomplete'] ),
		'move_inline_css'        => isset( $_POST['choneypot4cf7_move_inline_css'] ),
		'nomessage'              => isset( $_POST['choneypot4cf7_nomessage'] ),
		'timecheck_enabled'      => isset( $_POST['choneypot4cf7_timecheck_enabled'] ),
		'timecheck_value'        => $timecheck_value,
	);

	update_option( 'choneypot4cf7_config', $config );

	return __( 'The changes have been saved.', 'classic-honeypot-for-cf7' );
}

/**
 * Render the settings page.
 */
function choneypot4cf7_admin_page() {
	$message = choneypot4cf7_handle_settings_form();
	$config  = choneypot4cf7_get_config();

	// HTML allowed in the translatable descriptions below.
	$allowed_html = array(
		'a'  => array(
			'href'   => array(),
			'target' => array(),
		),
		'em' => array(),
	);
	?>
	<div class="wrap choneypot4cf7-admin" id="choneypot4cf7-admin-page">
		<h1 class="choneypot4cf7-admin__title">
			<?php esc_html_e( 'Classic Honeypot for CF7', 'classic-honeypot-for-cf7' ); ?> <span><?php echo esc_html( 'v' . CHONEYPOT4CF7_VERSION ); ?></span>
		</h1>

		<?php if ( $message ) : ?>
			<div id="message" class="notice notice-success"><p><?php echo esc_html( $message ); ?></p></div>
		<?php endif; ?>

		<div class="choneypot4cf7-admin__primary">
			<div class="choneypot4cf7-admin__box">
				<form action="" method="post" id="choneypot4cf7_options_form" name="choneypot4cf7_options_form">
					<?php wp_nonce_field( 'choneypot4cf7-submit', 'choneypot4cf7_nonce' ); ?>
					<h3><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Honeypot Settings', 'classic-honeypot-for-cf7' ); ?></h3>
					<p class="choneypot4cf7-admin__introduction"><?php esc_html_e( 'Below are global settings for the Honeypot plugin. Many of these settings can be overridden when inserting the Honeypot field shortcode when creating your CF7 contact form.', 'classic-honeypot-for-cf7' ); ?></p>
					<table class="form-table">
						<tbody>
							<tr>
								<th><label for="choneypot4cf7__store-honeypot"><?php esc_html_e( 'Store Honeypot Value', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="checkbox" name="choneypot4cf7_store" id="choneypot4cf7__store-honeypot" value="1" <?php checked( $config['store_honeypot'] ); ?>>
								</td>
							</tr>
							<tr>
								<td class="description" colspan="2">
									<?php
									$flamingo_link = '<a href="https://wordpress.org/plugins/flamingo/" target="_blank">' . esc_html__( 'Flamingo', 'classic-honeypot-for-cf7' ) . '</a>';
									echo wp_kses(
										sprintf(
											/* translators: %1$s: Link to the Flamingo plugin page. */
											__( '(Recommended) By default the Honeypot field is not stored with other fields in form-saving plugins like %1$s. However, saving the field can be useful to see what spam bots are leaving behind to help you improve your spam stopping superpowers. If you\'d like to store the value of the field, simply check this box (and install %1$s).', 'classic-honeypot-for-cf7' ),
											$flamingo_link
										),
										$allowed_html
									);
									?>
								</td>
							</tr>

							<tr>
								<th><label for="choneypot4cf7__placeholder"><?php esc_html_e( 'Global Placeholder', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="text" class="regular-text" name="choneypot4cf7_placeholder" id="choneypot4cf7__placeholder" value="<?php echo esc_attr( $config['placeholder'] ); ?>">
								</td>
							</tr>
							<tr>
								<td class="description" colspan="2"><?php esc_html_e( 'If using placeholders on other fields, this can help honeypot mimic a "real" field. This can be overridden in the contact form. If you\'re unsure, leave blank.', 'classic-honeypot-for-cf7' ); ?></td>
							</tr>

							<tr>
								<th><label for="choneypot4cf7__accessibility_message"><?php esc_html_e( 'Accessibility Message', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="text" class="regular-text" name="choneypot4cf7_accessibility_message" id="choneypot4cf7__accessibility_message" value="<?php echo esc_attr( $config['accessibility_message'] ); ?>">
								</td>
							</tr>
							<tr>
								<td class="description" colspan="2">
									<?php
									echo wp_kses(
										sprintf(
											/* translators: %s: Default accessibility message. */
											__( 'You can customize the (hidden) accessibility message, or just leave it the default value: %s', 'classic-honeypot-for-cf7' ),
											'<em>' . esc_html__( 'Please leave this field empty.', 'classic-honeypot-for-cf7' ) . '</em>'
										),
										$allowed_html
									);
									?>
								</td>
							</tr>

							<tr>
								<th><label for="choneypot4cf7__w3c-valid-autocomplete"><?php esc_html_e( 'Use Standard Autocomplete Value', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="checkbox" name="choneypot4cf7_w3c_valid_autocomplete" id="choneypot4cf7__w3c-valid-autocomplete" value="1" <?php checked( $config['w3c_valid_autocomplete'] ); ?>>
								</td>
							</tr>
							<tr>
								<td class="description" colspan="2"><?php esc_html_e( 'To assure the honeypot isn\'t auto-completed by a browser, we add an atypical "autocomplete" attribute value. If you have any problems with this, you can switch it to the more standard (but less effective) "off" value. If you\'re unsure, leave this unchecked.', 'classic-honeypot-for-cf7' ); ?></td>
							</tr>

							<tr>
								<th><label for="choneypot4cf7__move_inline_css"><?php esc_html_e( 'Move Inline CSS', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="checkbox" name="choneypot4cf7_move_inline_css" id="choneypot4cf7__move_inline_css" value="1" <?php checked( $config['move_inline_css'] ); ?>>
								</td>
							</tr>
							<tr>
								<td class="description" colspan="2"><?php esc_html_e( 'By default Honeypot uses inline CSS on the honeypot field to hide it. Checking this box moves that CSS to the footer of the page. It may help confuse bots.', 'classic-honeypot-for-cf7' ); ?></td>
							</tr>

							<tr>
								<th><label for="choneypot4cf7__nomessage"><?php esc_html_e( 'Disable Accessibility Label', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="checkbox" name="choneypot4cf7_nomessage" id="choneypot4cf7__nomessage" value="1" <?php checked( $config['nomessage'] ); ?>>
								</td>
							</tr>
							<tr>
								<td class="description" colspan="2"><?php esc_html_e( 'If checked, the accessibility label will not be generated. This is not recommended, but may improve spam blocking. If you\'re unsure, leave this unchecked.', 'classic-honeypot-for-cf7' ); ?></td>
							</tr>

							<tr>
								<th><label for="choneypot4cf7__timecheck_enabled"><?php esc_html_e( 'Enable Time Check', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="checkbox" name="choneypot4cf7_timecheck_enabled" id="choneypot4cf7__timecheck_enabled" value="1" <?php checked( $config['timecheck_enabled'] ); ?>>&nbsp;&nbsp;<input type="number" class="small-text" name="choneypot4cf7_timecheck_value" id="choneypot4cf7__timecheck_value" value="<?php echo esc_attr( $config['timecheck_value'] ); ?>" step="1" min="1"> <?php esc_html_e( 'seconds', 'classic-honeypot-for-cf7' ); ?>
								</td>
							</tr>
							<tr>
								<td class="description" colspan="2"><?php esc_html_e( 'If enabled, this will perform an additional check for spam bots using the time it takes to submit the form under the idea that bots submit forms faster than people. The value is set to 4 seconds by default, but adjust based on your needs. If you\'re not sure, leave this unchecked.', 'classic-honeypot-for-cf7' ); ?></td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<input name="save" id="save" class="button button-primary" value="<?php esc_attr_e( 'Save', 'classic-honeypot-for-cf7' ); ?>" type="submit" />
						<input name="clear" id="reset" class="button" value="<?php esc_attr_e( 'Reset to Defaults', 'classic-honeypot-for-cf7' ); ?>" type="submit" />
					</p>
				</form>
			</div>
		</div>
	</div>
	<?php
}
