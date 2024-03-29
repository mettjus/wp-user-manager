<?php
/**
 * Handles Emails Functions
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gets list of registered emails.
 *
 * @since 1.0.0
 * @return array $emails - list of emails.
 */
function wpum_get_emails() {

	return apply_filters( 'wpum/get_emails', array() );

}

/**
 * Run this function to reset/install registered emails.
 * This function should be used of plugin installation
 * or on addons installation if the addon adds new emails.
 *
 * @since 1.0.0
 * @return void.
 */
function wpum_register_emails() {

	$emails = wpum_get_emails();
	$default_emails = array();

	foreach ( $emails as $id => $settings ) {

		$default_emails[ $id ] = array(
			'subject' => $settings['subject'],
			'message' => $settings['message'],
		);

	}

	update_option( 'wpum_emails', $default_emails );

}

if ( ! function_exists( 'wp_new_user_notification' ) && !is_admin() ) :
/**
 * Replaces the default wp_new_user_notification function of the core.
 *
 * Email login credentials to a newly-registered user.
 * A new user registration notification is also sent to admin email.
 *
 * @since 1.0.0
 * @access public
 * @return void
 */
function wp_new_user_notification( $user_id, $plaintext_pass ) {
	$user = get_userdata( $user_id );
	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	// Send notification to admin if not disabled.
	if ( !wpum_get_option( 'disable_admin_register_email' ) ) {
		$message  = sprintf( __( 'New user registration on your site %s:', 'wpum' ), $blogname ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', 'wpum' ), $user->user_login ) . "\r\n\r\n";
		$message .= sprintf( __( 'E-mail: %s', 'wpum' ), $user->user_email ) . "\r\n";
		wp_mail( get_option( 'admin_email' ), sprintf( __( '[%s] New User Registration', 'wpum' ), $blogname ), $message );
	}
	/* == Send notification to the user now == */
	if ( empty( $plaintext_pass ) )
		return;
	// Check if email exists first
	if ( wpum_email_exists( 'register' ) ) {
		// Retrieve the email from the database
		$register_email = wpum_get_email( 'register' );
		$message = wpautop( $register_email['message'] );
		$message = wpum_do_email_tags( $message, $user_id, $plaintext_pass );
		WPUM()->emails->__set( 'heading', __( 'Your account', 'wpum' ) );
		WPUM()->emails->send( $user->user_email, $register_email['subject'], $message );
	}
}
endif;
