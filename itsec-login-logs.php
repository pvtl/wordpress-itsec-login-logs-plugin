<?php
/**
 * Plugin Name:  iThemes Security - Log Logins
 * Plugin URI:   https://github.com/pvtl/wordpress-itsec-login-logs-plugin
 * Description:  Adds an entry into the iThemes Security (free) logs (notices), for each successful user login.
 * Author:       Pivotal Agency
 * Author URI:   https://pvtl.io/
 * Text Domain:  pvtl-itsec-login-logs
 * Domain Path:  /languages
 * Version:      1.0.3
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
	// iThemes Security plugin doesn't exist or isn't active, so don't continue.
	if ( ! class_exists( 'ITSEC_Log' ) ) {
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
			'User successfully logged in', // "user-logged-in::user-{$user->id}", // Code.
			array(
				'details'  => ITSEC_Lib::get_login_details(),
				'user'     => $user,
				'username' => $user->user_login,
				'user_id'  => $user->id,
				'SERVER'   => ITSEC_Lib::get_server_snapshot(),
			),
			array( 'user_id' => $user->id ) // Overrides.
		);
	} catch ( Exception $e ) {
		// Ignore - likely the iThemes Security plugin has just changed.
		return $user;
	}

	// Continue on your merry way after the log was added.
	return $user;
}

add_action( 'authenticate', 'pvtl_itsec_log_logins', 99, 1 );
