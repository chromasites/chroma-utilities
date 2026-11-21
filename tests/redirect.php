<?php
/** Isolated redirect integration checks. Run: php tests/redirect.php [rest|cli|success] */
error_reporting( E_ALL );
set_error_handler( function ( $severity, $message ) {
	if ( error_reporting() & $severity ) { throw new RuntimeException( $message ); }
	return false;
} );
define( 'ABSPATH', __DIR__ . '/' );
$mode = isset( $argv[1] ) ? $argv[1] : '';
if ( 'rest' === $mode ) { define( 'REST_REQUEST', true ); }
if ( 'cli' === $mode ) { define( 'WP_CLI', true ); }
$hooks = $settings = $pages = $errors = $redirects = array();
$home = 'https://client.chroma.site';
$site = 'https://client.chroma.site/wp';
$options = array( 'chroma_ssr_url' => 'https://live.example.com/landing/?a=1#section' );
$admin = $ajax = $cron = false;
$can_manage = true;
$no_cache = 0;
$_SERVER['HTTP_HOST'] = 'client.chroma.site';
function check( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
}
function add_action( $hook, $callback, $priority = 10 ) {
	global $hooks; $hooks[$hook][$priority][] = $callback;
}
function do_action( $hook ) {
	global $hooks;
	if ( ! isset( $hooks[$hook] ) ) { return; }
	$groups = $hooks[$hook]; ksort( $groups );
	foreach ( $groups as $group ) { foreach ( $group as $callback ) { call_user_func( $callback ); } }
}
function home_url() { global $home; return $home; }
function site_url() { global $site; return $site; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function get_option( $key, $default = false ) { global $options; return isset( $options[$key] ) ? $options[$key] : $default; }
// These checks exercise plugin validation; URL escaping itself belongs to WordPress.
function esc_url_raw( $value, $protocols ) {
	return preg_match( '/^(?:https?):\/\//i', $value ) ? $value : '';
}
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function add_settings_error( $setting, $code, $message ) { global $errors; $errors[] = $message; }
function register_setting( $group, $key, $args ) { global $settings; $settings[$key] = $args; }
function add_options_page( $title, $label, $capability, $slug, $callback ) {
	global $pages; $pages[$slug] = array( $capability, $callback );
}
function current_user_can( $capability ) { global $can_manage; return $can_manage; }
function settings_errors() {}
function settings_fields( $group ) { echo '<input type="hidden" name="option_page" value="' . esc_attr( $group ) . '">'; }
function submit_button() { echo '<button>Save Changes</button>'; }
function is_admin() { global $admin; return $admin; }
function wp_doing_ajax() { global $ajax; return $ajax; }
function wp_doing_cron() { global $cron; return $cron; }
function nocache_headers() { global $no_cache; $no_cache++; }
function wp_redirect( $url, $status, $by ) {
	global $redirects, $mode;
	$redirects[] = array( $url, $status, $by );
	if ( 'success' === $mode ) { echo json_encode( end( $redirects ) ) . "\n"; return true; }
	return false; // Allow assertions without exiting the test runner.
}
require dirname( __DIR__ ) . '/includes/site-redirect.php';

if ( '' !== $mode ) {
	do_action( 'template_redirect' );
	check( 'success' !== $mode, 'A successful redirect must exit before rendering the page.' );
	check( empty( $redirects ) && 0 === $no_cache, 'REST and CLI must bypass redirects.' );
	echo 'Passed: ' . $mode . " bypass.\n";
	return;
}

// Eligibility uses Site Address, not the admin URL, a query string, or a suffix lookalike.
$cases = array(
	'https://client.chroma.site' => true,
	'https://Client.CHROMA.SITE.:8443/subdirectory' => true,
	'https://a.b.chroma.site' => true,
	'https://chroma.site' => false,
	'https://notchroma.site' => false,
	'https://client.chroma.site.evil.example' => false,
	'https://client-chroma.site' => false,
	'https://live.example.com/?old=client.chroma.site' => false,
	'https://-client.chroma.site' => false,
	'https://client..chroma.site' => false,
);
foreach ( $cases as $url => $eligible ) {
	$home = $url;
	check( chroma_utilities_redirect_site_eligible() === $eligible, 'Wrong eligibility: ' . $url );
	$settings = $pages = $redirects = array(); $no_cache = 0;
	do_action( 'admin_init' ); do_action( 'admin_menu' );
	check( isset( $settings['chroma_ssr_url'] ) === $eligible, 'Settings must follow eligibility.' );
	check( isset( $pages['chroma-ssr'] ) === $eligible, 'Menu must follow eligibility.' );
	if ( ! $eligible ) {
		do_action( 'template_redirect' );
		check( empty( $redirects ) && 0 === $no_cache, 'Ineligible sites must not redirect with a saved destination.' );
	}
}
$home = 'https://client.chroma.site';
$destination = $options['chroma_ssr_url'];
do_action( 'admin_init' ); do_action( 'admin_menu' );
check( 'manage_options' === $pages['chroma-ssr'][0], 'Settings require administrator capability.' );
$sanitize = $settings['chroma_ssr_url']['sanitize_callback'];
check( call_user_func( $sanitize, '  ' ) === '', 'Blank must disable redirects.' );
check( call_user_func( $sanitize, ' https://other.example.com/page?q=1#x ' ) === 'https://other.example.com/page?q=1#x', 'Complete destination must survive trimming.' );
foreach ( array( array(), null, 'javascript:alert(1)', 'ftp://example.com', '//example.com', 'https://', 'https://user:pass@example.com', 'https://CLIENT.CHROMA.SITE.:8443/path', 'https://client.chroma.site/wp' ) as $invalid ) {
	$count = count( $errors );
	check( call_user_func( $sanitize, $invalid ) === $destination, 'Invalid destination must preserve the saved option.' );
	check( count( $errors ) === $count + 1, 'Invalid destination needs an error.' );
}
$site = 'https://admin.example.com/wp';
check( chroma_utilities_redirect_valid_url( 'https://admin.example.com/login' ) === '', 'Admin domain must not become a loop destination.' );
$site = 'https://client.chroma.site/wp';

foreach ( array( 'client.chroma.site', 'CLIENT.CHROMA.SITE:443', 'client.chroma.site.' ) as $request_host ) {
	$_SERVER['HTTP_HOST'] = $request_host; $redirects = array(); $no_cache = 0;
	$_SERVER['REQUEST_URI'] = '/some/page?ignored=1';
	do_action( 'template_redirect' );
	check( $redirects === array( array( $destination, 302, 'Chroma Utilities' ) ), 'Existing destination must receive an exact temporary redirect.' );
	check( 1 === $no_cache, 'Redirect must send no-cache headers.' );
}
foreach ( array( 'live.example.com', 'other.chroma.site', 'client.chroma.site.evil.example', 'client.chroma.site@evil.example', "client.chroma.site\n", '' ) as $request_host ) {
	$_SERVER['HTTP_HOST'] = $request_host; $redirects = array(); $no_cache = 0;
	do_action( 'template_redirect' );
	check( empty( $redirects ) && 0 === $no_cache, 'Aliases and malformed hosts must not redirect.' );
}
unset( $_SERVER['HTTP_HOST'] );
do_action( 'template_redirect' );
check( empty( $redirects ), 'Missing request host must not redirect.' );
$_SERVER['HTTP_HOST'] = 'client.chroma.site';
foreach ( array( 'admin', 'ajax', 'cron' ) as $context ) {
	$$context = true; $redirects = array(); $no_cache = 0;
	do_action( 'template_redirect' );
	check( empty( $redirects ) && 0 === $no_cache, 'Must bypass ' . $context );
	$$context = false;
}
foreach ( array( '', array(), 'javascript:alert(1)', 'https://client.chroma.site/loop' ) as $stored ) {
	$options['chroma_ssr_url'] = $stored; $redirects = array(); $no_cache = 0;
	do_action( 'template_redirect' );
	check( empty( $redirects ) && 0 === $no_cache, 'Disabled, invalid and looping stored values must not redirect.' );
}
// A cloned site can inherit its own new hostname as the old destination.
$home = 'https://new.chroma.site'; $_SERVER['HTTP_HOST'] = 'new.chroma.site';
$options['chroma_ssr_url'] = $home;
do_action( 'template_redirect' );
check( empty( $redirects ), 'Domain changes must not create a self-redirect loop.' );
$home = 'https://client.chroma.site';
$options['chroma_ssr_url'] = $destination;
ob_start(); chroma_utilities_redirect_settings_page(); $html = ob_get_clean();
check( false !== strpos( $html, esc_attr( $destination ) ), 'Settings must show the existing escaped destination.' );
check( false !== strpos( $html, 'action="options.php"' ), 'Use the WordPress settings submission flow.' );
$can_manage = false;
ob_start(); chroma_utilities_redirect_settings_page(); $html = ob_get_clean();
check( '' === $html, 'Non-administrators must not see the form.' );
$can_manage = true; $home = 'https://live.example.com';
ob_start(); chroma_utilities_redirect_settings_page(); $html = ob_get_clean();
check( '' === $html, 'Settings must stay unavailable on custom domains.' );
echo "Passed: domain restrictions, aliases, destination migration/validation, request bypasses, settings access, and 302 redirects.\n";
