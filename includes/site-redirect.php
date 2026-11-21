<?php
/**
 * Optional front-end redirects for Chroma staging subdomains.
 * Reuse Simple Site Redirect's option so existing destinations carry over.
 */
defined( 'ABSPATH' ) || exit;

function chroma_utilities_redirect_host( $url ) {
	return strtolower( rtrim( (string) wp_parse_url( $url, PHP_URL_HOST ), '.' ) );
}

function chroma_utilities_redirect_site_eligible() {
	$host = chroma_utilities_redirect_host( home_url() );
	return 1 === preg_match( '/^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+chroma\.site$/D', $host );
}

/**
 * Validate saved values again at request time, including after a domain change.
 */
function chroma_utilities_redirect_valid_url( $value ) {
	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		return '';
	}

	$url   = esc_url_raw( trim( $value ), array( 'http', 'https' ) );
	$parts = wp_parse_url( $url );
	if ( ! $parts || empty( $parts['host'] ) || empty( $parts['scheme'] )
		|| ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true )
		|| isset( $parts['user'] ) || isset( $parts['pass'] ) ) {
		return '';
	}

	$host = chroma_utilities_redirect_host( $url );
	foreach ( array( home_url(), site_url() ) as $site ) {
		if ( $host === chroma_utilities_redirect_host( $site ) ) {
			return '';
		}
	}

	return $url;
}

function chroma_utilities_redirect_sanitize_url( $value ) {
	if ( is_string( $value ) && '' === trim( $value ) ) {
		return '';
	}
	$url = chroma_utilities_redirect_valid_url( $value );
	if ( '' === $url ) {
		add_settings_error( 'chroma_ssr_url', 'invalid_url', 'Enter a complete http:// or https:// destination URL on another domain.' );
		return get_option( 'chroma_ssr_url', '' );
	}
	return $url;
}

add_action( 'admin_init', function () {
	if ( ! chroma_utilities_redirect_site_eligible() ) {
		return;
	}
	register_setting( 'chroma_ssr', 'chroma_ssr_url', array(
		'type'              => 'string',
		'default'           => '',
		'sanitize_callback' => 'chroma_utilities_redirect_sanitize_url',
	) );
} );

add_action( 'admin_menu', function () {
	if ( chroma_utilities_redirect_site_eligible() ) {
		add_options_page( 'Site Redirect', 'Site Redirect', 'manage_options', 'chroma-ssr', 'chroma_utilities_redirect_settings_page' );
	}
} );

function chroma_utilities_redirect_settings_page() {
	if ( ! current_user_can( 'manage_options' ) || ! chroma_utilities_redirect_site_eligible() ) {
		return;
	}
	?>
	<div class="wrap">
		<h1>Site Redirect</h1>
		<p>Send front-end visitors to one destination. Redirects only run on this site's .chroma.site subdomain and turn off automatically when its WordPress Site Address changes to another domain. Your WordPress admin and login remain accessible.</p>
		<?php settings_errors(); ?>
		<form action="options.php" method="post">
			<?php settings_fields( 'chroma_ssr' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="chroma_ssr_url">Destination URL</label></th>
					<td>
						<input type="url" class="regular-text" id="chroma_ssr_url" name="chroma_ssr_url" value="<?php echo esc_attr( get_option( 'chroma_ssr_url', '' ) ); ?>" placeholder="https://example.com/" aria-describedby="chroma_ssr_help">
						<p class="description" id="chroma_ssr_help">Use a URL on another domain. Leave blank to turn redirects off. All front-end paths go to this exact URL using a temporary (302) redirect.</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

// template_redirect does not run for wp-admin or wp-login.php.
add_action( 'template_redirect', function () {
	if ( ! chroma_utilities_redirect_site_eligible()
		|| is_admin() || wp_doing_ajax() || wp_doing_cron()
		|| ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		|| ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}

	// Also reject requests on custom-domain aliases, even with stale WP settings.
	$host = chroma_utilities_redirect_host( home_url() );
	if ( ! isset( $_SERVER['HTTP_HOST'] ) || ! is_string( $_SERVER['HTTP_HOST'] )
		|| ! preg_match( '/^' . preg_quote( $host, '/' ) . '\.?(?::[0-9]+)?$/iD', $_SERVER['HTTP_HOST'] ) ) {
		return;
	}

	$url = chroma_utilities_redirect_valid_url( get_option( 'chroma_ssr_url', '' ) );
	if ( '' === $url ) {
		return;
	}

	nocache_headers();
	// External redirects are intentional; only administrators can save this URL.
	if ( wp_redirect( $url, 302, 'Chroma Utilities' ) ) {
		exit;
	}
}, 0 );
