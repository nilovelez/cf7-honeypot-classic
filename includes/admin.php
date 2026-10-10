<?php
/**
 * Admin functions for Classic Honeypot for CF7.
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
 *
 * Check if CF7 is installed and activated.
 * 		Deliver a message to install CF7 if not.
 * 
 */
add_action( 'admin_init', 'choneypot4cf7_init' );
function choneypot4cf7_init() {
	// Get Options
	$choneypot4cf7_config = choneypot4cf7_get_config();

	// CF7 is not enabled
	if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( CHONEYPOT4CF7_DEP_PLUGIN ) && empty( $choneypot4cf7_config['honeypot_cf7_req_msg_dismissed'] ) ) {
		add_action( 'admin_notices', 'choneypot4cf7_nocf7_notice' );
	}

	// CF7 is enabled, but it is OOOOOLD. Display message and deactivate Honeypot.
	if ( is_admin() && current_user_can( 'activate_plugins' ) && is_plugin_active( CHONEYPOT4CF7_DEP_PLUGIN ) && defined('WPCF7_VERSION') && version_compare(WPCF7_VERSION, '3.0', '<' ) ) {
		add_action( 'admin_notices', 'choneypot4cf7_oldcf7_notice' );
		deactivate_plugins( CHONEYPOT4CF7_PLUGIN_BASENAME );
	}

	// This resets dismissed notice
	if ( is_plugin_active( CHONEYPOT4CF7_DEP_PLUGIN ) && $choneypot4cf7_config['honeypot_cf7_req_msg_dismissed'] === 1 ) {
		$choneypot4cf7_config['honeypot_cf7_req_msg_dismissed'] = 0;
		update_option( 'choneypot4cf7_config', $choneypot4cf7_config );
	}
}

function choneypot4cf7_nocf7_notice() { 
	?>
	<div class="notice error choneypot4cf7-notice is-dismissible">
		<p>
			<?php 
			printf(
				/* translators: %s: Link to Contact Form 7 plugin page. */
				__('%s must be installed and activated for the Classic Honeypot for CF7 plugin to work', 'classic-honeypot-for-cf7'),
				'<a href="'.admin_url('plugin-install.php?tab=search&s=contact+form+7').'">'.__('Contact Form 7','classic-honeypot-for-cf7').'</a>'
			); 
			?>
		</p>
	</div>
	<?php
}

function choneypot4cf7_oldcf7_notice() { 
	?>
	<div class="notice error">
		<p>
			<?php 
			printf(
				/* translators: %s: Link to Contact Form 7 plugin page. */
				__('The version of %s that is installed will not work with this version of Classic Honeypot for CF7.', 'classic-honeypot-for-cf7'),
				'<a href="'.admin_url('plugin-install.php?tab=search&s=contact+form+7').'">'.__('Contact Form 7','classic-honeypot-for-cf7').'</a>'
			); 
			?>
		</p>
	</div>
	<?php
}


add_action( 'wp_ajax_choneypot4cf7_dismiss_notice', 'choneypot4cf7_dismiss_notice' );
function choneypot4cf7_dismiss_notice() {
	$choneypot4cf7_config = choneypot4cf7_get_config();
	$choneypot4cf7_config['honeypot_cf7_req_msg_dismissed'] = 1;
	update_option( 'choneypot4cf7_config', $choneypot4cf7_config );
}

add_filter( 'plugin_action_links_'.CHONEYPOT4CF7_PLUGIN_BASENAME, 'choneypot4cf7_settings_link' );
function choneypot4cf7_settings_link( $links ) {
	$url = esc_url( add_query_arg(
		'page',
		'choneypot4cf7',
		get_admin_url() . 'admin.php'
	) );
	
	$settings_link = "<a href='$url'>" . __( 'Settings', 'classic-honeypot-for-cf7' ) . '</a>';
	
	array_push( $links, $settings_link );

	return $links;
}


/* ***********
*
* Set / Get / Restore Config
*
*********** */
function choneypot4cf7_get_config( $context = false ) {

	$choneypot4cf7_config = get_option( 'choneypot4cf7_config' );	

	$choneypot4cf7_config = array(
		'store_honeypot'					=> ( 'reset' == $context || empty( $choneypot4cf7_config['store_honeypot'] ) ) ?  0 : $choneypot4cf7_config['store_honeypot'],
		'placeholder'						=> ( 'reset' == $context || empty( $choneypot4cf7_config['placeholder'] ) ) ?  '' : $choneypot4cf7_config['placeholder'],
		'accessibility_message'				=> ( 'reset' == $context || empty( $choneypot4cf7_config['accessibility_message'] ) ) ?  '' : $choneypot4cf7_config['accessibility_message'],
		'w3c_valid_autocomplete'			=> ( 'reset' == $context || empty( $choneypot4cf7_config['w3c_valid_autocomplete'] ) ) ?  array( 'false' ) : $choneypot4cf7_config['w3c_valid_autocomplete'],
		'move_inline_css'					=> ( 'reset' == $context || empty( $choneypot4cf7_config['move_inline_css'] ) ) ?  array( 'false' ) : $choneypot4cf7_config['move_inline_css'],
		'nomessage'							=> ( 'reset' == $context || empty( $choneypot4cf7_config['nomessage'] ) ) ?  array( 'false' ) : $choneypot4cf7_config['nomessage'],
		'timecheck_enabled'					=> ( 'reset' == $context || empty( $choneypot4cf7_config['timecheck_enabled'] ) ) ?  array( 'false' ) : $choneypot4cf7_config['timecheck_enabled'],
		'timecheck_value'					=> ( 'reset' == $context || empty( $choneypot4cf7_config['timecheck_value'] ) ) ?  4 : $choneypot4cf7_config['timecheck_value'],
		'honeypot_cf7_req_msg_dismissed'	=> ( 'reset' == $context || empty( $choneypot4cf7_config['honeypot_cf7_req_msg_dismissed'] ) ) ? 0 : $choneypot4cf7_config['honeypot_cf7_req_msg_dismissed'],
		'choneypot4cf7_version'				=> CHONEYPOT4CF7_VERSION,
	);
	
	update_option( 'choneypot4cf7_config', $choneypot4cf7_config );

	return $choneypot4cf7_config;
}


/* ***********
*
* Setup the Options page
*
*********** */

add_action( 'admin_menu', 'choneypot4cf7_admin_menu' );
function choneypot4cf7_admin_menu() {
	add_submenu_page(
		'wpcf7', 
		__('Classic Honeypot for CF7', 'classic-honeypot-for-cf7'),
		__('Honeypot', 'classic-honeypot-for-cf7'),
		'manage_options','choneypot4cf7',
		'choneypot4cf7_admin_page'
	);
}

function choneypot4cf7_admin_page() {
	// Reset Values
	if ( ! empty( $_POST['clear'] ) && check_admin_referer( 'choneypot4cf7-submit', 'choneypot4cf7_nonce' ) && current_user_can( 'manage_options' ) ) {
		$choneypot4cf7_config = choneypot4cf7_get_config( 'reset' );
		echo '<div id="message" class="updated"><p>'.esc_html( __('The settings have been reset to their defaults.', 'classic-honeypot-for-cf7' ) ).'</p></div>';

	} elseif ( ! empty( $_POST['save'] ) && check_admin_referer( 'choneypot4cf7-submit', 'choneypot4cf7_nonce' ) && current_user_can( 'manage_options' ) ) {
		$choneypot4cf7_config = choneypot4cf7_get_config();

		// Validate & Sanitize
		$choneypot4cf7_config_update = array(
			'store_honeypot' 			=> ( isset( $_POST['choneypot4cf7_store'] ) ) ? $_POST['choneypot4cf7_store'] : 0,
			'placeholder' 				=> ( isset( $_POST['choneypot4cf7_placeholder'] ) ) ? $_POST['choneypot4cf7_placeholder'] : '',
			'accessibility_message'		=> ( isset( $_POST['choneypot4cf7_accessibility_message'] ) ) ? $_POST['choneypot4cf7_accessibility_message'] : '',
			'w3c_valid_autocomplete'	=> ( isset( $_POST['choneypot4cf7_w3c_valid_autocomplete'] ) ) ? $_POST['choneypot4cf7_w3c_valid_autocomplete'] : array( 'false' ),
			'move_inline_css'			=> ( isset( $_POST['choneypot4cf7_move_inline_css'] ) ) ? $_POST['choneypot4cf7_move_inline_css'] : array( 'false' ),
			'nomessage'					=> ( isset( $_POST['choneypot4cf7_nomessage'] ) ) ? $_POST['choneypot4cf7_nomessage'] : array( 'false' ),
			'timecheck_enabled'			=> ( isset( $_POST['choneypot4cf7_timecheck_enabled'] ) ) ? $_POST['choneypot4cf7_timecheck_enabled'] : array( 'false' ),
			'timecheck_value'			=> ( isset( $_POST['choneypot4cf7_timecheck_value'] ) ) ? $_POST['choneypot4cf7_timecheck_value'] : 0,
		);
	
		$choneypot4cf7_config = array_replace( $choneypot4cf7_config, $choneypot4cf7_config_update );

		update_option( 'choneypot4cf7_config', $choneypot4cf7_config );

		echo '<div id="message" class="updated"><p>' . esc_html( __( 'The changes have been saved.', 'classic-honeypot-for-cf7' ) ).'</p></div>';
	} else {
		$choneypot4cf7_config = choneypot4cf7_get_config();
	}
	?>
		
	<div class="wrap" class="choneypot4cf7-admin" id="choneypot4cf7-admin-page">
		<h1 class="choneypot4cf7-admin__title">
			<?php esc_html_e( 'Classic Honeypot for CF7', 'classic-honeypot-for-cf7' ); ?> <span><?php echo esc_html( 'v' . CHONEYPOT4CF7_VERSION ); ?>
		</h1>
		<div class="choneypot4cf7-admin__primary">
			<div class="choneypot4cf7-admin__box">
				<form action="" method="post" id="choneypot4cf7_options_form" name="choneypot4cf7_options_form">
					<?php wp_nonce_field( 'choneypot4cf7-submit', 'choneypot4cf7_nonce' ); ?>
					<h3><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Honeypot Settings', 'classic-honeypot-for-cf7' ); ?></h3>
					<p class="choneypot4cf7-admin__introduction"><?php esc_html_e( 'Below are global settings for the Honeypot plugin. Many of these settings can be overridden when inserting the Honeypot field shortcode when creating your CF7 contact form.', 'classic-honeypot-for-cf7' ); ?></p>
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th><label for="choneypot4cf7__store-honeypot"><?php esc_html_e( 'Store Honeypot Value', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="checkbox" name="choneypot4cf7_store" id="choneypot4cf7__store-honeypot" value="1" <?php checked( $choneypot4cf7_config['store_honeypot'], 1 ); ?>>
								</td>
							</tr>
							<tr valign="top">
								<td class="description" colspan="2">
									<?php 
									printf(
										/* translators: 1: Link to Flamingo plugin page */
										__( '(Recommended) By default the Honeypot field is not stored with other fields in form-saving plugins like %1$s. However, saving the field can be useful to see what spam bots are leaving behind to help you improve your spam stopping superpowers. If you\'d like to store the value of the field, simply check this box (and install %1$s).', 'classic-honeypot-for-cf7' ),
										'<a href="https://wordpress.org/plugins/flamingo/" target="_blank">' . __( 'Flamingo', 'classic-honeypot-for-cf7' ) . '</a>'
									); 
									?>
								</td>
							</tr>

							<tr valign="top">
								<th><label for="choneypot4cf7__placeholder"><?php esc_html_e( 'Global Placeholder', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="text" class="regular-text" name="choneypot4cf7_placeholder" id="choneypot4cf7__placeholder" value="<?php echo sanitize_text_field( $choneypot4cf7_config['placeholder'] ); ?>">
								</td>
							</tr>
							<tr valign="top">
								<td class="description" colspan="2"><?php esc_html_e( 'If using placeholders on other fields, this can help honeypot mimic a "real" field. This can be overridden in the contact form. If you\'re unsure, leave blank.', 'classic-honeypot-for-cf7' ); ?></td>
							</tr>

							<tr valign="top">
								<th><label for="choneypot4cf7__accessibility_message"><?php esc_html_e( 'Accessibility Message', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="text" class="regular-text" name="choneypot4cf7_accessibility_message" id="choneypot4cf7__accessibility_message" value="<?php echo sanitize_text_field( $choneypot4cf7_config['accessibility_message'] ); ?>">
								</td>
							</tr>
							<tr valign="top">
								<td class="description" colspan="2">
									<?php 
									printf(
										/* translators: %s: default value */
										__( 'You can customize the (hidden) accessibility message, or just leave it the default value: %s', 'classic-honeypot-for-cf7' ),
										'<em>' . __( 'Please leave this field empty.', 'classic-honeypot-for-cf7' ) . '</em>'
									); 
									?>		
								</td>
							</tr>

							<tr valign="top">
								<th><label for="choneypot4cf7__w3c-valid-autocomplete"><?php esc_html_e( 'Use Standard Autocomplete Value', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="checkbox" name="choneypot4cf7_w3c_valid_autocomplete[]" id="choneypot4cf7__w3c-valid-autocomplete" value="true" <?php checked( $choneypot4cf7_config['w3c_valid_autocomplete'][0], 'true' ); ?>>
								</td>
							</tr>
							<tr valign="top">
								<td class="description" colspan="2"><?php esc_html_e( 'To assure the honeypot isn\'t auto-completed by a browser, we add an atypical "autocomplete" attribute value. If you have any problems with this, you can switch it to the more standard (but less effective) "off" value. If you\'re unsure, leave this unchecked.', 'classic-honeypot-for-cf7' ); ?></td>
							</tr>

							<tr valign="top">
								<th><label for="choneypot4cf7__move_inline_css"><?php esc_html_e( 'Move Inline CSS', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="checkbox" name="choneypot4cf7_move_inline_css[]" id="choneypot4cf7__move_inline_css" value="true" <?php checked( $choneypot4cf7_config['move_inline_css'][0], 'true' ); ?>>
								</td>
							</tr>
							<tr valign="top">
								<td class="description" colspan="2"><?php esc_html_e( 'By default Honeypot uses inline CSS on the honeypot field to hide it. Checking this box moves that CSS to the footer of the page. It may help confuse bots.', 'classic-honeypot-for-cf7' ); ?></td>
							</tr>

							<tr valign="top">
								<th><label for="choneypot4cf7__nomessage"><?php esc_html_e( 'Disable Accessibility Label', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="checkbox" name="choneypot4cf7_nomessage[]" id="choneypot4cf7__nomessage" value="true" <?php checked( $choneypot4cf7_config['nomessage'][0], 'true' ); ?>>
								</td>
							</tr>
							<tr valign="top">
								<td class="description" colspan="2"><?php esc_html_e( 'If checked, the accessibility label will not be generated. This is not recommended, but may improve spam blocking. If you\'re unsure, leave this unchecked.', 'classic-honeypot-for-cf7' ); ?></td>
							</tr>

							<tr valign="top">
								<th><label for="choneypot4cf7__timecheck_enabled"><?php esc_html_e( 'Enable Time Check', 'classic-honeypot-for-cf7' ); ?></label></th>
								<td>
									<input type="checkbox" name="choneypot4cf7_timecheck_enabled[]" id="choneypot4cf7__timecheck_enabled" value="true" <?php checked( $choneypot4cf7_config['timecheck_enabled'][0], 'true' ); ?>>&nbsp;&nbsp;<input type="number" class="small-text" name="choneypot4cf7_timecheck_value" id="choneypot4cf7__timecheck_value" value="<?php echo sanitize_text_field( $choneypot4cf7_config['timecheck_value'] ); ?>" step="1" min="1"> <?php esc_html_e('seconds', 'classic-honeypot-for-cf7'); ?>
								</td>
							</tr>
							<tr valign="top">
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

/* ***********
*
* Add admin page CSS
*
*********** */
function choneypot4cf7_admin_enqueues( $hook ) {
	wp_enqueue_script( 'choneypot4cf7-admin-js', plugins_url( 'includes/js/notice-update.js', CHONEYPOT4CF7_PLUGIN ), array( 'jquery' ), CHONEYPOT4CF7_VERSION, true );
	if ( strpos( $hook, 'choneypot4cf7' ) !== false ) {
		 wp_enqueue_style( 'choneypot4cf7-admin-css', plugins_url( 'includes/css/styles.css', CHONEYPOT4CF7_PLUGIN ), array(), CHONEYPOT4CF7_VERSION, 'all' );
	}
}
add_action( 'admin_enqueue_scripts', 'choneypot4cf7_admin_enqueues' );



