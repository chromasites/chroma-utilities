<?php
/**
 * Isolated integration checks using the real bundled updater and mocked
 * WordPress services. Run: php tests/smoke.php
 * This does not replace testing an installed plugin on a WordPress site.
 */
error_reporting( E_ALL );
set_error_handler( function ( $severity, $message ) {
	if ( error_reporting() & $severity ) {
		throw new RuntimeException( $message );
	}
	return false;
} );
define( 'ABSPATH', __DIR__ . '/' );
define( 'WP_DEBUG', false );
define( 'DAY_IN_SECONDS', 86400 );
define( 'WP_PLUGIN_DIR', dirname( dirname( __DIR__ ) ) );
define( 'WPMU_PLUGIN_DIR', ABSPATH . 'mu-plugins' );
$hooks = array();
$options = array();
$actions = array();
$http_responses = array();
$http_requests = array();
$scheduled = array();

function callback_id( $callback ) {
	if ( is_string( $callback ) ) { return $callback; }
	if ( is_array( $callback ) ) {
		return ( is_object( $callback[0] ) ? spl_object_hash( $callback[0] ) : $callback[0] ) . '::' . $callback[1];
	}
	return spl_object_hash( $callback );
}
function add_filter( $hook, $callback, $priority = 10, $accepted = 1 ) {
	global $hooks;
	$hooks[$hook][$priority][callback_id( $callback )] = array( $callback, $accepted );
}
function add_action( $hook, $callback, $priority = 10, $accepted = 1 ) {
	add_filter( $hook, $callback, $priority, $accepted );
}
function apply_filters( $hook, $value, ...$args ) {
	global $hooks;
	$groups = isset( $hooks[$hook] ) ? $hooks[$hook] : array();
	ksort( $groups );
	foreach ( $groups as $group ) {
		foreach ( $group as $item ) {
			$value = call_user_func_array( $item[0], array_slice( array_merge( array( $value ), $args ), 0, $item[1] ) );
		}
	}
	return $value;
}
function do_action( $hook, ...$args ) {
	global $hooks, $actions;
	$actions[$hook] = did_action( $hook ) + 1;
	$groups = isset( $hooks[$hook] ) ? $hooks[$hook] : array();
	ksort( $groups );
	foreach ( $groups as $group ) {
		foreach ( $group as $item ) {
			call_user_func_array( $item[0], array_slice( $args, 0, $item[1] ) );
		}
	}
}
function did_action( $hook ) { global $actions; return isset( $actions[$hook] ) ? $actions[$hook] : 0; }
function get_option( $key, $default = false ) { global $options; return isset( $options[$key] ) ? $options[$key] : $default; }
function get_site_option( $key, $default = false ) { return get_option( $key, $default ); }
function update_site_option( $key, $value ) { global $options; $options[$key] = $value; }
function is_email( $email ) { return filter_var( $email, FILTER_VALIDATE_EMAIL ); }
function __return_true() { return true; }
function is_admin() { return false; }
function wp_doing_cron() { return false; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function plugin_basename( $path ) { return basename( dirname( $path ) ) . '/' . basename( $path ); }
function wp_next_scheduled( $hook ) { return false; }
function wp_schedule_event( $time, $schedule, $hook ) { global $scheduled; $scheduled[$hook] = $schedule; }
function register_deactivation_hook( $file, $callback ) {}
function __( $text, $domain = '' ) { return $text; }
function _cleanup_header_comment( $text ) { return trim( $text ); }
function get_plugin_data( $file, $markup = true, $translate = true ) {
	return get_file_data( $file, array( 'Name' => 'Plugin Name', 'Version' => 'Version', 'TextDomain' => 'Text Domain' ) );
}
function get_file_data( $file, $headers, $context = '' ) {
	$content = file_get_contents( $file );
	$result = array();
	foreach ( $headers as $key => $label ) {
		$result[$key] = preg_match( '/^[ \t\/*#@]*' . preg_quote( $label, '/' ) . ':(.*)$/mi', $content, $matches ) ? trim( $matches[1] ) : '';
	}
	return $result;
}
class WP_Error {
	public $code;
	public function __construct( $code, $message = '' ) { $this->code = $code; }
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function wp_remote_get( $url, $args = array() ) {
	global $http_requests, $http_responses;
	$http_requests[] = $url;
	if ( !isset( $http_responses[$url] ) ) { throw new RuntimeException( 'Unexpected HTTP request: ' . $url ); }
	return $http_responses[$url];
}
function wp_remote_retrieve_response_code( $response ) { return $response['response']['code']; }
function wp_remote_retrieve_body( $response ) { return $response['body']; }
function check( $condition, $message ) {
	if ( !$condition ) { throw new RuntimeException( $message ); }
}
function mock_release( $release, $code = 200 ) {
	global $http_responses, $http_requests;
	$http_requests = array();
	$http_responses['https://api.github.com/repos/chromasites/chroma-utilities/releases/latest'] = array(
		'response' => array( 'code' => $code ),
		'body' => json_encode( $release ),
	);
}

require dirname( __DIR__ ) . '/chroma-utilities.php';
do_action( 'plugins_loaded' );
$checker = null;
foreach ( $hooks['site_transient_update_plugins'][10] as $item ) {
	if ( is_array( $item[0] ) && $item[0][0] instanceof \YahnisElsts\PluginUpdateChecker\v5p7\Vcs\PluginUpdateChecker ) {
		$checker = $item[0][0];
		break;
	}
}
check( $checker !== null, 'Real update checker must initialize.' );
check( $checker->getInstalledVersion() === '2.0', 'Installed version must be 2.0.' );
check( $checker->getVcsApi()->getRepositoryUrl() === 'https://github.com/chromasites/chroma-utilities', 'Updater repository must match the moved repository.' );
check( $scheduled['puc_cron_check_updates-chroma-utilities'] === 'twicedaily', 'Updater must schedule periodic checks.' );
$plugin_key = 'chroma-utilities/chroma-utilities.php';
$updates = $checker->injectUpdate( null );
check( isset( $updates->no_update[$plugin_key] ), 'Auto-update controls must be available even when no update exists.' );
$cached_update = new \YahnisElsts\PluginUpdateChecker\v5p7\Plugin\Update();
$cached_update->slug = 'chroma-utilities';
$cached_update->filename = $plugin_key;
$cached_update->version = '2.1';
$cached_update->download_url = 'https://example.com/chroma-utilities.zip';
$checker->getUpdateState()->setUpdate( $cached_update );
$updates = $checker->injectUpdate( null );
check( $updates->response[$plugin_key]->new_version === '2.1', 'Newer releases must reach WordPress update data.' );
check( $updates->response[$plugin_key]->package === $cached_update->download_url, 'WordPress must receive the installable ZIP URL.' );
check( $updates->response[$plugin_key]->autoupdate === false, 'Updater must not force automatic installation.' );
$cached_update->version = '2.0';
check( $checker->getUpdate() === null, 'Same-version releases must not be offered as updates.' );
$cached_update->version = '1.1';
check( $checker->getUpdate() === null, 'Older releases must not be offered as downgrades.' );
$checker->getUpdateState()->setUpdate( null );

$release = array(
	'tag_name' => 'v2.1', 'draft' => false, 'prerelease' => false,
	'zipball_url' => 'https://example.com/source.zip', 'created_at' => '2026-10-05T00:00:00Z',
	'assets' => array( array( 'name' => 'chroma-utilities.zip', 'browser_download_url' => 'https://example.com/chroma-utilities.zip', 'download_count' => 1 ) ),
);
mock_release( $release );
$reference = $checker->getVcsApi()->chooseReference( 'main' );
check( $reference && $reference->version === '2.1', 'Stable release must be detected.' );
check( $reference->downloadUrl === 'https://example.com/chroma-utilities.zip', 'Updater must use the installable asset.' );
check( count( $http_requests ) === 1, 'Only the latest-release endpoint should be requested.' );
foreach ( array( 'draft', 'prerelease', 'missing_asset', 'wrong_asset' ) as $case ) {
	$invalid = $release;
	if ( $case === 'missing_asset' ) { $invalid['assets'] = array(); }
	elseif ( $case === 'wrong_asset' ) { $invalid['assets'][0]['name'] = 'other.zip'; }
	else { $invalid[$case] = true; }
	mock_release( $invalid );
	check( $checker->getVcsApi()->chooseReference( 'main' ) === null, 'Must skip ' . $case );
	check( count( $http_requests ) === 1, 'Must not fall back to tags or branch commits.' );
}
mock_release( array(), 404 );
check( $checker->getVcsApi()->chooseReference( 'main' ) === null, 'No published release must mean no update.' );
check( count( $http_requests ) === 1, 'No-release response must not trigger tag or branch fallback.' );

$notification = array( 'from' => 'original@example.com', 'subject' => 'Keep this', 'replyTo' => 'reply@example.com' );
foreach ( array( json_encode( array( 'sender_address' => 'sender@example.com' ) ), array( 'sender_address' => 'sender@example.com' ) ) as $settings ) {
	$options['postmark_settings'] = $settings;
	$expected = $notification;
	$expected['from'] = 'sender@example.com';
	check( apply_filters( 'gform_notification', $notification, array(), array() ) === $expected, 'Postmark must change only the sender.' );
}
foreach ( array( false, '', 'invalid JSON', 'null', '42', '[]', '{}', array(), array( 'sender_address' => null ), array( 'sender_address' => array() ), array( 'sender_address' => '' ), array( 'sender_address' => 'invalid' ) ) as $settings ) {
	$options['postmark_settings'] = $settings;
	check( apply_filters( 'gform_notification', $notification, array(), array() ) === $notification, 'Invalid Postmark settings must preserve the notification.' );
}
check( apply_filters( 'ppp_nonce_life', 172800 ) === 2419200, 'Public preview lifetime must be 28 days.' );
check( apply_filters( 'gform_confirmation_anchor', false ) === true, 'Gravity Forms anchor must be enabled.' );
check( apply_filters( 'hello_elementor_viewport_content', 'default' ) === 'width=device-width, initial-scale=1.0, maximum-scale=1.0', 'Viewport must match the requested value.' );
foreach ( array( 'auto_core_update_send_email', 'auto_plugin_update_send_email', 'auto_theme_update_send_email' ) as $hook ) {
	// Core supplies this callback; define it below for this isolated runtime.
	check( apply_filters( $hook, true ) === false, 'Update emails must remain disabled.' );
}
function __return_false() { return false; }
echo "Passed: updater initialization, release selection, WordPress update data, asset requirements, Postmark fallbacks, and utility filters.\n";
