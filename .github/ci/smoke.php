<?php
/**
 * Runtime smoke test (dev/CI only — never shipped: .github is in .distignore).
 *
 * Lint and unit tests don't execute the plugin inside WordPress, so a PHP
 * version can pass both and still raise deprecations or fatals at runtime.
 * This loads the active plugin and, as an administrator, calls every
 * read-only REST route and every read-only ability it owns, then fails on any
 * PHP notice, warning or deprecation raised from the plugin's own files, or
 * on a server error.
 *
 *   wp eval-file .github/ci/smoke.php
 *
 * Only GET routes and abilities annotated readonly are called, never an
 * action that changes the site.
 *
 * @package NhrrobCiSmoke
 */

// phpcs:ignoreFile -- dev tooling, not part of the plugin.

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Only ever runs inside WordPress via `wp eval-file`.
}

$nhrsmoke_dir    = realpath( getenv( 'SMOKE_PLUGIN_DIR' ) ?: dirname( __DIR__, 2 ) );
$nhrsmoke_errors = [];
$nhrsmoke_failed = [];
$nhrsmoke_calls  = 0;

set_error_handler(
	function ( $no, $message, $file, $line ) use ( &$nhrsmoke_errors, $nhrsmoke_dir ) {
		if ( 0 === strpos( (string) realpath( $file ), $nhrsmoke_dir . DIRECTORY_SEPARATOR ) ) {
			$nhrsmoke_errors[ $message . ' @ ' . substr( $file, strlen( $nhrsmoke_dir ) + 1 ) . ':' . $line ] = true;
		}
		return true;
	},
	E_ALL
);

/**
 * Whether a callback is defined inside the plugin under test.
 *
 * @param mixed  $callback Callback.
 * @param string $dir      Plugin directory.
 * @return bool
 */
function nhrsmoke_owned( $callback, $dir ) {
	try {
		if ( is_array( $callback ) && 2 === count( $callback ) ) {
			$ref = new ReflectionMethod( is_object( $callback[0] ) ? get_class( $callback[0] ) : $callback[0], $callback[1] );
		} elseif ( $callback instanceof Closure || ( is_string( $callback ) && false === strpos( $callback, '::' ) ) ) {
			$ref = new ReflectionFunction( $callback );
		} else {
			return false;
		}
		return 0 === strpos( (string) realpath( (string) $ref->getFileName() ), $dir . DIRECTORY_SEPARATOR );
	} catch ( ReflectionException $e ) {
		return false;
	}
}

$nhrsmoke_admins = get_users(
	[
		'role'   => 'administrator',
		'number' => 1,
	]
);
wp_set_current_user( $nhrsmoke_admins[0]->ID );

// Every plugin-owned GET route without a path parameter. A 4xx is a route
// asking for a required parameter; only a server error is a failure.
foreach ( rest_get_server()->get_routes() as $route => $endpoints ) {
	if ( false !== strpos( $route, '(' ) ) {
		continue;
	}
	foreach ( $endpoints as $endpoint ) {
		if ( empty( $endpoint['methods']['GET'] ) || ! nhrsmoke_owned( $endpoint['callback'], $nhrsmoke_dir ) ) {
			continue;
		}
		++$nhrsmoke_calls;
		$status = rest_do_request( new WP_REST_Request( 'GET', $route ) )->get_status();
		if ( $status >= 500 ) {
			$nhrsmoke_failed[] = "GET $route => $status";
		}
	}
}

// Every plugin-owned read-only ability that needs no input (WordPress 6.9+).
$nhrsmoke_abilities = 0;
foreach ( function_exists( 'wp_get_abilities' ) ? wp_get_abilities() : [] as $ability ) {
	$callback = new ReflectionProperty( 'WP_Ability', 'execute_callback' );
	if ( PHP_VERSION_ID < 80100 ) {
		$callback->setAccessible( true );
	}
	if ( ! nhrsmoke_owned( $callback->getValue( $ability ), $nhrsmoke_dir ) ) {
		continue;
	}
	++$nhrsmoke_abilities;
	$meta   = $ability->get_meta();
	$schema = $ability->get_input_schema();
	if ( empty( $meta['annotations']['readonly'] ) || ! empty( $schema['required'] ) ) {
		continue;
	}
	++$nhrsmoke_calls;
	$result = $ability->execute();
	if ( is_wp_error( $result ) ) {
		$nhrsmoke_failed[] = $ability->get_name() . ' => ' . $result->get_error_message();
	}
}

restore_error_handler();

printf(
	"PHP %s, WordPress %s: %d calls (%d abilities registered), %d failed, %d PHP notices from the plugin\n",
	PHP_VERSION,
	get_bloginfo( 'version' ),
	$nhrsmoke_calls,
	$nhrsmoke_abilities,
	count( $nhrsmoke_failed ),
	count( $nhrsmoke_errors )
);
foreach ( array_merge( $nhrsmoke_failed, array_keys( $nhrsmoke_errors ) ) as $nhrsmoke_line ) {
	echo "  FAIL $nhrsmoke_line\n";
}

// Nothing called means the plugin did not load: that is a failure too. A
// plugin with no REST route and no ability sets SMOKE_ALLOW_EMPTY=1; it must
// still be active.
$nhrsmoke_active = false;
foreach ( wp_get_active_and_valid_plugins() as $nhrsmoke_file ) {
	$nhrsmoke_active = $nhrsmoke_active || 0 === strpos( (string) realpath( $nhrsmoke_file ), $nhrsmoke_dir . DIRECTORY_SEPARATOR );
}
if ( ! $nhrsmoke_active ) {
	echo "  FAIL the plugin is not active\n";
}
if ( $nhrsmoke_failed || $nhrsmoke_errors || ! $nhrsmoke_active || ( 0 === $nhrsmoke_calls && ! getenv( 'SMOKE_ALLOW_EMPTY' ) ) ) {
	exit( 1 );
}
