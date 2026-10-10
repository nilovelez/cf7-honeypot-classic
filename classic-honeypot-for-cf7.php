<?php
/**
 * Plugin Name:       Classic Honeypot for CF7
 * Plugin URI:        https://github.com/nilovelez/cf7-honeypot-classic
 * Description:       Adds a honeypot anti-spam field to Contact Form 7 forms. A lean fork of Honeypot for Contact Form 7 2.1.1 by Nocean.
 * Version:           1.0.0
 * Requires at least: 6.0
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
define( 'CHONEYPOT4CF7_PLUGIN_NAME', trim( dirname( CHONEYPOT4CF7_PLUGIN_BASENAME ), '/' ) );
define( 'CHONEYPOT4CF7_PLUGIN_DIR', untrailingslashit( dirname( CHONEYPOT4CF7_PLUGIN ) ) );
define( 'CHONEYPOT4CF7_PLUGIN_DIR_URL', untrailingslashit( plugin_dir_url( CHONEYPOT4CF7_PLUGIN ) ) );
define( 'CHONEYPOT4CF7_DEP_PLUGIN', 'contact-form-7/wp-contact-form-7.php' );

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
 * Import the settings of the original plugin the first time this plugin runs.
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

	$imported = array();

	$cf7apps_settings = get_option( 'cf7apps_settings' );
	$legacy_config    = get_option( 'honeypot4cf7_config' );

	if ( is_array( $cf7apps_settings ) && ! empty( $cf7apps_settings['honeypot'] ) && is_array( $cf7apps_settings['honeypot'] ) ) {
		$settings = $cf7apps_settings['honeypot'];
		$as_flag  = function ( $key ) use ( $settings ) {
			return ( ! empty( $settings[ $key ] ) && 1 == $settings[ $key ] ) ? array( 'true' ) : array( 'false' );
		};

		$imported = array(
			'store_honeypot'         => empty( $settings['store_value'] ) ? 0 : 1,
			'placeholder'            => isset( $settings['global_placeholder'] ) ? (string) $settings['global_placeholder'] : '',
			'accessibility_message'  => isset( $settings['accessibility_msg'] ) ? (string) $settings['accessibility_msg'] : '',
			'w3c_valid_autocomplete' => $as_flag( 'standard_auto_complete' ),
			'move_inline_css'        => $as_flag( 'inline_css' ),
			'nomessage'              => $as_flag( 'accessibility_label' ),
			'timecheck_enabled'      => $as_flag( 'enable_time_check' ),
			'timecheck_value'        => isset( $settings['time_check'] ) ? (int) $settings['time_check'] : 4,
		);
	} elseif ( is_array( $legacy_config ) ) {
		$keys     = array(
			'store_honeypot',
			'placeholder',
			'accessibility_message',
			'w3c_valid_autocomplete',
			'move_inline_css',
			'nomessage',
			'timecheck_enabled',
			'timecheck_value',
		);
		$imported = array_intersect_key( $legacy_config, array_flip( $keys ) );
	}

	if ( ! empty( $imported ) ) {
		add_option( 'choneypot4cf7_config', $imported );
	}
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

	if ( defined( 'WPCF7_VERSION' ) ) {
		define( 'CHONEYPOT4CF7_WPCF7_VERSION', WPCF7_VERSION );
	} else {
		$path_to_cf7 = WP_PLUGIN_DIR . '/' . CHONEYPOT4CF7_DEP_PLUGIN;
		if ( file_exists( $path_to_cf7 ) ) {
			$cf7_plugin_data = get_file_data( $path_to_cf7, array( 'Version' => 'Version' ), 'plugin' );
		}

		if ( ! empty( $cf7_plugin_data['Version'] ) ) {
			define( 'CHONEYPOT4CF7_WPCF7_VERSION', $cf7_plugin_data['Version'] );
		} else {
			define( 'CHONEYPOT4CF7_WPCF7_VERSION', '0.0.0' );
		}
	}

	choneypot4cf7_maybe_import_settings();

	require_once CHONEYPOT4CF7_PLUGIN_DIR . '/includes/admin.php';
	require_once CHONEYPOT4CF7_PLUGIN_DIR . '/includes/choneypot4cf7.php';
}
add_action( 'plugins_loaded', 'choneypot4cf7_bootstrap' );

/**
 * Delete the plugin configuration on uninstall.
 *
 * Defined here rather than in includes/admin.php because WordPress only
 * loads the main plugin file when uninstalling.
 */
function choneypot4cf7_uninstall() {
	delete_option( 'choneypot4cf7_config' );
}
register_uninstall_hook( CHONEYPOT4CF7_PLUGIN, 'choneypot4cf7_uninstall' );
