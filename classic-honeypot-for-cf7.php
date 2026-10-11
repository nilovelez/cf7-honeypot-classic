<?php
/**
 * Plugin Name:       Classic Honeypot for CF7
 * Plugin URI:        https://github.com/nilovelez/classic-honeypot-for-cf7
 * Description:       Adds a honeypot anti-spam field to Contact Form 7 forms. A lean fork of Honeypot for Contact Form 7 2.1.1 by Nocean.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Requires Plugins:  contact-form-7
 * Author:            Nilo Vélez
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       classic-honeypot-for-cf7
 * Domain Path:       /languages
 *
 * Classic Honeypot for CF7 is a fork of "Honeypot for Contact Form 7"
 * version 2.1.1 (https://wordpress.org/plugins/contact-form-7-honeypot/),
 * originally written by Nocean and contributors and released under the
 * GNU General Public License v2 or later.
 *
 * This file was modified by Nilo Vélez in October 2026. See the changelog
 * in readme.txt and the git history for details of every change.
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 2 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 * @package Classic_Honeypot_For_CF7
 */

defined( 'ABSPATH' ) || exit;

define( 'CHONEYPOT4CF7_VERSION', '1.0.0' );
define( 'CHONEYPOT4CF7_PLUGIN', __FILE__ );
define( 'CHONEYPOT4CF7_PLUGIN_BASENAME', plugin_basename( CHONEYPOT4CF7_PLUGIN ) );
define( 'CHONEYPOT4CF7_PLUGIN_DIR', untrailingslashit( dirname( CHONEYPOT4CF7_PLUGIN ) ) );
define( 'CHONEYPOT4CF7_MIN_WPCF7_VERSION', '6.0' );

/**
 * Check whether the original "Honeypot for Contact Form 7" plugin (any
 * version, including the later "CF7 Apps" releases) is loaded.
 *
 * Both plugins register the same [honeypot] form tag and public hooks, so
 * they must never run at the same time.
 *
 * @return bool
 */
function choneypot4cf7_original_plugin_is_active() {
	return defined( 'HONEYPOT4CF7_VERSION' )
		|| defined( 'CF7APPS_VERSION' )
		|| function_exists( 'honeypot4cf7_form_tag_handler' );
}

/**
 * Admin notice shown while the original plugin is active.
 */
function choneypot4cf7_original_plugin_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p>
			<?php
			esc_html_e( 'Classic Honeypot for CF7 is not running because the original "Honeypot for Contact Form 7" (CF7 Apps) plugin is active. Please deactivate it first. Your settings will be imported automatically.', 'classic-honeypot-for-cf7' );
			?>
		</p>
	</div>
	<?php
}

/**
 * Admin notice shown when the installed Contact Form 7 is too old.
 */
function choneypot4cf7_old_cf7_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: Minimum required Contact Form 7 version. */
				esc_html__( 'Classic Honeypot for CF7 is not running because it requires Contact Form 7 version %s or later. Please update Contact Form 7.', 'classic-honeypot-for-cf7' ),
				esc_html( CHONEYPOT4CF7_MIN_WPCF7_VERSION )
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Create the settings the first time this plugin runs, importing the
 * settings of the original plugin if there are any.
 *
 * Versions up to 2.x store them in the "honeypot4cf7_config" option. Version
 * 3.x stores them in "cf7apps_settings" under the "honeypot" key, using
 * different key names, and gives that copy priority, so it is checked first.
 * The original options are left untouched.
 */
function choneypot4cf7_maybe_import_settings() {
	if ( false !== get_option( 'choneypot4cf7_config' ) ) {
		return;
	}

	$cf7apps_settings = get_option( 'cf7apps_settings' );
	$legacy_config    = get_option( 'honeypot4cf7_config' );

	if ( is_array( $cf7apps_settings ) && ! empty( $cf7apps_settings['honeypot'] ) && is_array( $cf7apps_settings['honeypot'] ) ) {
		$old = $cf7apps_settings['honeypot'];

		$old_config = array(
			'store_honeypot'         => isset( $old['store_value'] ) ? $old['store_value'] : false,
			'placeholder'            => isset( $old['global_placeholder'] ) ? $old['global_placeholder'] : '',
			'accessibility_message'  => isset( $old['accessibility_msg'] ) ? $old['accessibility_msg'] : '',
			'w3c_valid_autocomplete' => isset( $old['standard_auto_complete'] ) ? $old['standard_auto_complete'] : false,
			'move_inline_css'        => isset( $old['inline_css'] ) ? $old['inline_css'] : false,
			'nomessage'              => isset( $old['accessibility_label'] ) ? $old['accessibility_label'] : false,
			'timecheck_enabled'      => isset( $old['enable_time_check'] ) ? $old['enable_time_check'] : false,
			'timecheck_value'        => isset( $old['time_check'] ) ? $old['time_check'] : 0,
		);
	} elseif ( is_array( $legacy_config ) ) {
		$old_config = $legacy_config;
	} else {
		// Nothing to import: store the defaults so the import runs only once.
		$old_config = array();
	}

	$defaults   = choneypot4cf7_get_defaults();
	$old_config = wp_parse_args( $old_config, $defaults );

	$timecheck_value = absint( $old_config['timecheck_value'] );
	if ( $timecheck_value < 1 ) {
		$timecheck_value = $defaults['timecheck_value'];
	}

	$config = array(
		'store_honeypot'         => choneypot4cf7_import_flag( $old_config['store_honeypot'] ),
		'placeholder'            => sanitize_text_field( $old_config['placeholder'] ),
		'accessibility_message'  => sanitize_text_field( $old_config['accessibility_message'] ),
		'w3c_valid_autocomplete' => choneypot4cf7_import_flag( $old_config['w3c_valid_autocomplete'] ),
		'move_inline_css'        => choneypot4cf7_import_flag( $old_config['move_inline_css'] ),
		'nomessage'              => choneypot4cf7_import_flag( $old_config['nomessage'] ),
		'timecheck_enabled'      => choneypot4cf7_import_flag( $old_config['timecheck_enabled'] ),
		'timecheck_value'        => $timecheck_value,
	);

	add_option( 'choneypot4cf7_config', $config );
}

/**
 * Convert a flag stored by the original plugin to a boolean.
 *
 * Version 2.x stores flags as array( 'true' ) or array( 'false' ), and 1 or 0
 * for "store_honeypot". Version 3.x stores them as 1 or 0.
 *
 * @param mixed $value Stored value.
 * @return bool
 */
function choneypot4cf7_import_flag( $value ) {
	if ( is_array( $value ) ) {
		$value = reset( $value );
	}

	return ( 'true' === $value || 1 === (int) $value );
}

/**
 * Bootstrap the plugin once all plugins are loaded, so the original plugin
 * can be detected regardless of load order.
 */
function choneypot4cf7_bootstrap() {
	if ( choneypot4cf7_original_plugin_is_active() ) {
		add_action( 'admin_notices', 'choneypot4cf7_original_plugin_notice' );
		return;
	}

	// The "Requires Plugins" header makes sure Contact Form 7 is active.
	// This check only guards against fatal errors if it is not loaded anyway.
	if ( ! defined( 'WPCF7_VERSION' ) ) {
		return;
	}

	if ( version_compare( WPCF7_VERSION, CHONEYPOT4CF7_MIN_WPCF7_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'choneypot4cf7_old_cf7_notice' );
		return;
	}

	require_once CHONEYPOT4CF7_PLUGIN_DIR . '/includes/admin.php';
	require_once CHONEYPOT4CF7_PLUGIN_DIR . '/includes/choneypot4cf7.php';

	choneypot4cf7_maybe_import_settings();
}
add_action( 'plugins_loaded', 'choneypot4cf7_bootstrap' );
