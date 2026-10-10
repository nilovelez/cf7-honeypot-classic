<?php
/**
 * Delete the plugin settings when Classic Honeypot for CF7 is uninstalled.
 *
 * The settings of the original "Honeypot for Contact Form 7" plugin are left
 * untouched.
 *
 * @package Classic_Honeypot_For_CF7
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'choneypot4cf7_config' );
