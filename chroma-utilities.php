<?php
/**
 * Plugin Name: Chroma Utilities
 * Plugin URI: https://chromasites.com
 * Description: Miscellaneous utility functions and standardizations for Chroma Sites managed websites.
 * Version: 2.0.1
 * Requires at least: 5.8
 * Requires PHP: 5.6.20
 * Author: Chroma Sites
 * Author URI: https://chromasites.com
 * License: GPL-2.0-or-later
 * Text Domain: chroma-utilities
 * Update URI: https://github.com/chromasites/chroma-utilities
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for published GitHub releases using the bundled updater.
 * Each release must include an installable asset named chroma-utilities.zip.
 * Drafts, prereleases, standalone tags, and branch commits are not updates.
 * Automatic installation follows the site's WordPress auto-update setting.
 */
add_action( 'plugins_loaded', function () {
	require_once __DIR__ . '/lib/plugin-update-checker/plugin-update-checker.php';

	$update_checker = \YahnisElsts\PluginUpdateChecker\v5p7\PucFactory::buildUpdateChecker(
		'https://github.com/chromasites/chroma-utilities',
		__FILE__,
		'chroma-utilities'
	);
	$update_checker->setBranch( 'main' );
	$update_checker->getVcsApi()->enableReleaseAssets(
		'/^chroma-utilities\.zip$/i',
		\YahnisElsts\PluginUpdateChecker\v5p7\Vcs\Api::REQUIRE_RELEASE_ASSETS
	);
	$update_checker->addFilter( 'vcs_update_detection_strategies', function ( $strategies ) {
		return array_intersect_key( $strategies, array( 'latest_release' => true ) );
	} );
} );

/**
 * Disable WordPress automatic update notification emails.
 */
add_filter( 'auto_core_update_send_email', '__return_false' );
add_filter( 'auto_plugin_update_send_email', '__return_false' );
add_filter( 'auto_theme_update_send_email', '__return_false' );

/**
 * Set Public Post Preview's nonce lifetime to four weeks (28 days).
 * Link validity varies with nonce time windows (approximately 14–28 days).
 */
add_filter( 'ppp_nonce_life', function () {
	return 28 * DAY_IN_SECONDS;
} );

/**
 * Use the configured Postmark sender for all Gravity Forms notifications.
 * Preserve the notification's sender if Postmark settings are missing or invalid.
 * Official Postmark settings are JSON; older array settings are also supported.
 */
add_filter( 'gform_notification', function ( $notification, $form, $entry ) {
	$postmark_settings = get_option( 'postmark_settings' );
	if ( is_string( $postmark_settings ) ) {
		$postmark_settings = json_decode( $postmark_settings, true );
	}

	if ( ! is_array( $postmark_settings ) || ! isset( $postmark_settings['sender_address'] ) ) {
		return $notification;
	}

	$postmark_sender = $postmark_settings['sender_address'];
	if ( is_string( $postmark_sender ) && is_email( $postmark_sender ) ) {
		$notification['from'] = $postmark_sender;
	}

	return $notification;
}, 10, 3 );

/**
 * Enable scrolling to Gravity Forms validation errors and confirmations.
 */
add_filter( 'gform_confirmation_anchor', '__return_true' );

/**
 * Set the viewport content for the Hello Elementor theme.
 */
add_filter( 'hello_elementor_viewport_content', function () {
	return 'width=device-width, initial-scale=1.0, maximum-scale=1.0';
} );

/**
 * Prefill the lost-password form from the URL's email query parameter.
 *
 * Link to the site's lost-password page with a URL-encoded email address:
 * https://yoursite.com/wp-login.php?action=lostpassword&email=person%40example.com
 * Replace yoursite.com with the site's domain and person%40example.com with
 * the recipient's URL-encoded email address. Existing field values are preserved.
 */
add_action( 'login_footer', function () {
	?>
	<script>
	const form = document.getElementById('lostpasswordform');
	const email = new URLSearchParams(window.location.search).get('email');

	if (form && email) {
		const field = form.querySelector('#user_login');
		if (field && !field.value) {
			field.value = email;
		}
	}
	</script>
	<?php
} );
