<?php
/**
 * Plugin Name:  iThemes Security - Log Logins
 * Plugin URI:   https://github.com/pvtl/wordpress-itsec-login-logs-plugin
 * Description:  Adds an entry into the iThemes Security (free) logs (notices), for each successful user login.
 * Author:       Pivotal Agency
 * Author URI:   https://pvtl.io/
 * Text Domain:  pvtl-itsec-login-logs
 * Domain Path:  /languages
 * Version:      1.2.1
 * License:      MIT License
 *
 * @package      PVTL_ITSEC_Logs
 */

/**
 * Check if ITSec is enabled
 *
 * @return bool
 */
function pvtl_itsec_is_enabled() {
	return (
		class_exists( 'ITSEC_Log' )
		&& class_exists( 'ITSEC_Lib' )
		&& method_exists( 'ITSEC_Log', 'add_notice' )
		&& method_exists( 'ITSEC_Lib', 'get_login_details' )
		&& method_exists( 'ITSEC_Lib', 'get_server_snapshot' )
	) ? true : false;
}

/**
 * Log the successful login
 *
 * @param null|WP_User|WP_Error $user - the user object if successful.
 */
function pvtl_itsec_log_logins( $user ) {
	// Only continue when ITSec plugin is enabled.
	if ( ! pvtl_itsec_is_enabled() ) {
		return $user;
	}

	// Login wasn't attempted or wasn't successful - so don't continue.
	// Note that unsuccessful logins are logged elsewhere.
	if ( null === $user || is_wp_error( $user ) ) {
		return $user;
	}

	// Login was successful - log it.
	try {
		$request_scheme = ( ! empty( $_SERVER['REQUEST_SCHEME'] ) ) ? sanitize_text_field( $_SERVER['REQUEST_SCHEME'] ) : 'http';
		$http_host = ( ! empty( $_SERVER['HTTP_HOST'] ) ) ? sanitize_text_field( $_SERVER['HTTP_HOST'] ) : 'localhost';
		$request_uri = ( ! empty( $_SERVER['REQUEST_URI'] ) ) ? explode( '?', sanitize_text_field( $_SERVER['REQUEST_URI'] ), 2 )[0] : '/';

		ITSEC_Log::add_notice(
			'Login', // 'user_logging', // Module.
			'User successfully logged in', // "user-logged-in::user-{$user->ID}", // Code.
			array(
				'details'  => ITSEC_Lib::get_login_details(),
				'user'     => $user,
				'username' => $user->user_login,
				'user_id'  => $user->ID,
				'SERVER'   => ITSEC_Lib::get_server_snapshot(),
			),
			array(
				'user_id' => $user->ID,
				// URL without query params - which can expose the likes of SSO tokens.
				'url' => "{$request_scheme}://{$http_host}{$request_uri}",
			) // Overrides.
		);
	} catch ( Exception $e ) {
		// Ignore - likely the iThemes Security plugin has just changed.
		return $user;
	}

	// Continue on your merry way after the log was added.
	return $user;
}

add_action( 'authenticate', 'pvtl_itsec_log_logins', 99, 1 );

/**
 * Reduce the time in which a user remains logged in.
 * By making a user log in once every X mins (and therefore logging each login), it increases the
 * validity of our logs - i.e. we can more accurately determine:
 * eg. User: X logged in with IP: Y, within 90mins of this action being taken - it was more than likely User: X
 *
 * @param int $length - the WP-defined default login length (14 days).
 * @return int
 */
function pvtl_cookie_expiration( $length ) {
	return ( pvtl_itsec_is_enabled() ) ? ( 8 * HOUR_IN_SECONDS ) : $length;
}

add_filter( 'auth_cookie_expiration', 'pvtl_cookie_expiration', 99 );
