<?php
/**
 * Endpoint authorization probe (dev/CI only — never shipped: .github is in .distignore).
 *
 * Mirrors what WordPress.org's automated release security review looks for:
 * AJAX / REST endpoints (and abilities) a low-privilege or anonymous user can reach. Every
 * nonce is forced valid (see the --exec in probe.sh), so a handler that relies
 * on a nonce alone — with no capability check — shows up here.
 *
 * Runs inside WordPress via WP-CLI (no HTTP), one target per process so a
 * handler calling exit can't take the probe down:
 *
 *   wp --user=<admin> eval-file endpoint-probe.php list
 *   wp --user=<admin> eval-file endpoint-probe.php run <ajax|rest|ability> <target> <method> <subscriber|anonymous> <subscriber_id>
 *
 * Every INSERT/UPDATE/DELETE/REPLACE/ALTER/DROP/TRUNCATE the handler attempts
 * is recorded and blocked, so the site is never modified.
 *
 * @package NhrrobSecurityProbe
 */

// phpcs:ignoreFile -- dev tooling, not part of the plugin.

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Only ever runs inside WordPress via `wp eval-file`.
}

$nhrprobe_args   = isset( $args ) ? $args : [];
$nhrprobe_mode   = isset( $nhrprobe_args[0] ) ? $nhrprobe_args[0] : 'list';
$nhrprobe_plugin = realpath( getenv( 'PROBE_PLUGIN_DIR' ) ?: dirname( __DIR__, 2 ) );

/**
 * Whether a callback is defined inside the plugin under test.
 *
 * @param mixed  $callback Callback.
 * @param string $dir      Plugin directory.
 * @return bool
 */
function nhrprobe_owned( $callback, $dir ) {
	try {
		if ( is_array( $callback ) && 2 === count( $callback ) ) {
			$ref = new ReflectionMethod( is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0], $callback[1] );
		} elseif ( is_string( $callback ) && false !== strpos( $callback, '::' ) ) {
			$ref = new ReflectionMethod( $callback );
		} elseif ( $callback instanceof Closure || is_string( $callback ) ) {
			$ref = new ReflectionFunction( $callback );
		} elseif ( is_object( $callback ) && method_exists( $callback, '__invoke' ) ) {
			$ref = new ReflectionMethod( $callback, '__invoke' );
		} else {
			return false;
		}
		$file = realpath( (string) $ref->getFileName() );
		return $file && 0 === strpos( $file, $dir . DIRECTORY_SEPARATOR );
	} catch ( ReflectionException $e ) {
		return false;
	}
}

/**
 * Plugin-owned AJAX actions, REST routes and abilities.
 *
 * @param string $dir Plugin directory.
 * @return array
 */
function nhrprobe_targets( $dir ) {
	global $wp_filter;
	$targets = [];

	foreach ( $wp_filter as $hook => $obj ) {
		if ( 0 !== strpos( $hook, 'wp_ajax_' ) ) {
			continue;
		}
		foreach ( $obj->callbacks as $callbacks ) {
			foreach ( $callbacks as $cb ) {
				if ( nhrprobe_owned( $cb['function'], $dir ) ) {
					$nopriv    = 0 === strpos( $hook, 'wp_ajax_nopriv_' );
					$targets[] = [
						'type'   => 'ajax',
						'target' => $nopriv ? substr( $hook, 15 ) : substr( $hook, 8 ),
						'method' => 'POST',
						'nopriv' => $nopriv,
					];
					break 2;
				}
			}
		}
	}

	foreach ( rest_get_server()->get_routes() as $route => $endpoints ) {
		foreach ( $endpoints as $endpoint ) {
			$owned = nhrprobe_owned( $endpoint['callback'], $dir )
				|| ( isset( $endpoint['permission_callback'] ) && nhrprobe_owned( $endpoint['permission_callback'], $dir ) );
			if ( ! $owned ) {
				continue;
			}
			foreach ( array_keys( $endpoint['methods'] ) as $method ) {
				$targets[] = [
					'type'   => 'rest',
					'target' => $route,
					'method' => $method,
					'nopriv' => false,
				];
			}
		}
	}

	// Abilities (WordPress 6.9+): reachable by AI agents and MCP clients.
	foreach ( function_exists( 'wp_get_abilities' ) ? wp_get_abilities() : [] as $ability ) {
		$callback = new ReflectionProperty( 'WP_Ability', 'execute_callback' );
		if ( PHP_VERSION_ID < 80100 ) {
			$callback->setAccessible( true );
		}
		if ( nhrprobe_owned( $callback->getValue( $ability ), $dir ) ) {
			$targets[] = [
				'type'   => 'ability',
				'target' => $ability->get_name(),
				'method' => 'RUN',
				'nopriv' => true,
			];
		}
	}

	// De-duplicate (a nopriv + priv pair registers the same action twice).
	$seen = [];
	return array_values(
		array_filter(
			$targets,
			function ( $t ) use ( &$seen ) {
				$key = $t['type'] . '|' . $t['target'] . '|' . $t['method'];
				if ( isset( $seen[ $key ] ) ) {
					return false;
				}
				$seen[ $key ] = true;
				return true;
			}
		)
	);
}

/**
 * Generic, hostile-looking request parameters most handlers will accept.
 *
 * @return array
 */
function nhrprobe_params() {
	return [
		'id'               => '1',
		'ids'              => [ '1' ],
		'name'             => 'siteurl',
		'names'            => [ 'siteurl' ],
		'key'              => 'siteurl',
		'value'            => 'http://probe.invalid',
		'option_name'      => 'siteurl',
		'option_value'     => 'http://probe.invalid',
		'option_id'        => '1',
		'new_option_name'  => 'nhrprobe_option',
		'new_option_value' => 'probe',
		'meta_key'         => 'wp_capabilities',
		'meta_value'       => 'a:1:{s:13:"administrator";b:1;}',
		'umeta_id'         => '1',
		'user_id'          => '1',
		'post_id'          => '1',
		'prefix'           => 'wp_',
		'search'           => 'a',
		'replace'          => 'b',
		'dry_run'          => '0',
		'autoload'         => 'no',
		'label'            => 'probe',
		'backup_id'        => '1',
		'history_id'       => '1',
		'scope'            => 'all',
		'type'             => 'options',
		'enabled'          => 'true',
		'settings'         => [ 'probe' => '1' ],
		'data'             => '{}',
		'json'             => '{}',
		'page'             => '1',
		'per_page'         => '10',
		'nonce'            => 'probe',
		'_wpnonce'         => 'probe',
		'security'         => 'probe',
	];
}

if ( 'list' === $nhrprobe_mode ) {
	echo wp_json_encode( nhrprobe_targets( $nhrprobe_plugin ) );
	return;
}

// ---- run <type> <target> <method> <role> ----------------------------------
list( , $nhrprobe_type, $nhrprobe_target, $nhrprobe_method, $nhrprobe_role, $nhrprobe_uid ) = array_pad( $nhrprobe_args, 6, '' );

// Record + block every write query from here on.
$GLOBALS['nhrprobe_writes'] = [];
add_filter(
	'query',
	function ( $query ) {
		if ( preg_match( '/^\s*(INSERT|UPDATE|DELETE|REPLACE|ALTER|DROP|TRUNCATE|CREATE)\b/i', $query ) ) {
			$GLOBALS['nhrprobe_writes'][] = substr( preg_replace( '/\s+/', ' ', $query ), 0, 160 );
			return '';
		}
		return $query;
	},
	PHP_INT_MAX
);

// Low-privilege identity.
if ( 'subscriber' === $nhrprobe_role ) {
	wp_set_current_user( (int) $nhrprobe_uid );
} else {
	wp_set_current_user( 0 );
}

$nhrprobe_result = [
	'type'   => $nhrprobe_type,
	'target' => $nhrprobe_target,
	'method' => $nhrprobe_method,
	'role'   => $nhrprobe_role,
];

if ( 'ajax' === $nhrprobe_type ) {
	$params            = nhrprobe_params() + [ 'action' => $nhrprobe_target ];
	$_GET              = $params;
	$_POST             = $params;
	$_REQUEST          = $params;
	$_SERVER['REQUEST_METHOD'] = 'POST';

	add_filter(
		'wp_die_ajax_handler',
		function () {
			return function () {
				throw new RuntimeException( 'nhrprobe_die' );
			};
		}
	);
	add_filter(
		'wp_die_handler',
		function () {
			return function () {
				throw new RuntimeException( 'nhrprobe_die' );
			};
		}
	);

	$hook = ( 'anonymous' === $nhrprobe_role ? 'wp_ajax_nopriv_' : 'wp_ajax_' ) . $nhrprobe_target;
	ob_start();
	try {
		do_action( $hook );
	} catch ( Throwable $e ) {
		// wp_send_json*() / wp_die() land here.
	}
	$out = trim( (string) ob_get_clean() );

	// Match the envelope directly: a big payload (or a stray notice before it)
	// can make the full output fail json_decode() while still being a leak.
	$nhrprobe_result['output'] = substr( $out, 0, 200 );
	$nhrprobe_result['leak']   = (bool) preg_match( '/"success"\s*:\s*true/', $out );
} elseif ( 'ability' === $nhrprobe_type ) {
	// The permission check alone: execute() validates the input first, and a
	// validation error would hide a missing capability check.
	$allowed = function_exists( 'wp_get_ability' ) ? wp_get_ability( $nhrprobe_target )->check_permissions( nhrprobe_params() ) : true;

	$nhrprobe_result['output'] = is_wp_error( $allowed ) ? $allowed->get_error_code() : wp_json_encode( $allowed );
	$nhrprobe_result['leak']   = true === $allowed;
} else {
	$route = preg_replace( '/\(\?P<[^>]+>[^)]*\)/', '1', $nhrprobe_target );
	$req   = new WP_REST_Request( $nhrprobe_method, $route );
	$req->set_query_params( nhrprobe_params() );
	$req->set_body_params( nhrprobe_params() );
	$res = rest_do_request( $req );

	$nhrprobe_result['output'] = $res->get_status() . ' ' . substr( wp_json_encode( $res->get_data() ), 0, 160 );
	$nhrprobe_result['leak']   = $res->get_status() < 400;
}

$nhrprobe_result['writes'] = $GLOBALS['nhrprobe_writes'];
$nhrprobe_result['fail']   = $nhrprobe_result['leak'] || ! empty( $nhrprobe_result['writes'] );
echo "\nNHRPROBE_RESULT " . wp_json_encode( $nhrprobe_result ) . "\n";
