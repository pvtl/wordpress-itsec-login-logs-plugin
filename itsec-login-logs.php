<?php
/**
 * Plugin Name:  iThemes Security - Log Logins
 * Plugin URI:   https://github.com/pvtl/wordpress-itsec-login-logs-plugin
 * Description:  Adds an entry into the iThemes Security (free) logs (notices), for each successful user login.
 * Author:       Pivotal Agency
 * Author URI:   https://pvtl.io/
 * Text Domain:  pvtl-itsec-login-logs
 * Domain Path:  /languages
 * Version:      1.0.6
 * License:      MIT License
 *
 * @package      PVTL_ITSEC_Logs
 */

/**
 * Log the successful login
 *
 * @param null|WP_User|WP_Error $user - the user object if successful.
 */
function pvtl_itsec_log_logins( $user ) {
	// Confirm that the iThemes Security plugin exists, is active and the version supports what's needed.
	if (
		! class_exists( 'ITSEC_Log' )
		|| ! class_exists( 'ITSEC_Lib' )
		|| ! method_exists( 'ITSEC_Log', 'add_notice' )
		|| ! method_exists( 'ITSEC_Lib', 'get_login_details' )
		|| ! method_exists( 'ITSEC_Lib', 'get_server_snapshot' )
	) {
		return $user;
	}

	// Login wasn't attempted or wasn't successful - so don't continue.
	if ( null === $user || is_wp_error( $user ) ) {
		return $user;
	}

	// Login was successful - log it.
	try {
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
				'url' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . explode( '?', $_SERVER['REQUEST_URI'], 2 )[0],
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
