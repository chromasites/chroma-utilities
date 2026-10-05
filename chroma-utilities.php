<?php
/**
 * Plugin Name: Chroma Utilities
 * Plugin URI: https://chromasites.com
 * Description: Miscellaneous utility functions and standardizations for Chroma Sites managed websites.
 * Version: 1.1
 * Author: Chroma Sites
 * Author URI: https://chromasites.com
 * License: GPL-2.0-or-later
 * Text Domain: chroma-utilities
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable WordPress automatic update notification emails.
 */
add_filter( 'auto_core_update_send_email', '__return_false' );
add_filter( 'auto_plugin_update_send_email', '__return_false' );
add_filter( 'auto_theme_update_send_email', '__return_false' );

/**
 * Prefill the lost-password form from the email query parameter.
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
