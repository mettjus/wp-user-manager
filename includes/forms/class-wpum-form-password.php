<?php
/**
 * WP User Manager Forms: Password Recovery Form
 *
 * @package     wp-user-manager
 * @author      Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Form_Password Class
 *
 * @since 1.0.0
 */
class WPUM_Form_Password extends WPUM_Form {

	public static $form_name = 'password';

	/**
	 * Init the form.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {

		add_action( 'wp', array( __CLASS__, 'process' ) );

		// Validate username field
		add_filter( 'wpum_password_form_validate_fields', array( __CLASS__, 'validate_username' ), 10, 3 );

	}

	/**
	 * Define password recovery form fields
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function get_password_fields() {

		if ( self::$fields ) {
			return;
		}

		self::$fields = apply_filters( 'wpum_password_fields', array(
			'user' => array(
				'username_email' => array(
					'label'       => __( 'Username or email' ),
					'type'        => 'text',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1
				),
			),
			'password' => array(
				'password_1' => array(
					'label'       => __( 'Password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 1
				),
				'password_2' => array(
					'label'       => __( 'Repeat Password' ),
					'type'        => 'password',
					'required'    => true,
					'placeholder' => '',
					'priority'    => 2
				),
			),
		) );

		// Temporarily remove fields if not into password reset form
		if( !isset( $_GET['password-reset'] ) ) :
			unset( self::$fields['password']['password_1'] );
			unset( self::$fields['password']['password_2'] );
		endif;

	}

	/**
	 * Get submitted fields values.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return $values array of data from the fields.
	 */
	protected static function get_posted_fields() {

		// Get fields
		self::get_password_fields();

		$values = array();

		foreach ( self::$fields as $group_key => $group_fields ) {
			foreach ( $group_fields as $key => $field ) {
				// Get the value
				$field_type = str_replace( '-', '_', $field['type'] );

				if ( method_exists( __CLASS__, "get_posted_{$field_type}_field" ) ) {
					$values[ $group_key ][ $key ] = call_user_func( __CLASS__ . "::get_posted_{$field_type}_field", $key, $field );
				} else {
					$values[ $group_key ][ $key ] = self::get_posted_field( $key, $field );
				}

				// Set fields value
				self::$fields[ $group_key ][ $key ]['value'] = $values[ $group_key ][ $key ];
			}
		}

		return $values;
	}

	/**
	 * Goes through fields and sanitizes them.
	 *
	 * @access public
	 * @param array|string $value The array or string to be sanitized.
	 * @since 1.0.0
	 * @return array|string $value The sanitized array (or string from the callback)
	 */
	public static function sanitize_posted_field( $value ) {
		// Decode URLs
		if ( is_string( $value ) && ( strstr( $value, 'http:' ) || strstr( $value, 'https:' ) ) ) {
			$value = urldecode( $value );
		}

		// Santize value
		$value = is_array( $value ) ? array_map( array( __CLASS__, 'sanitize_posted_field' ), $value ) : sanitize_text_field( stripslashes( trim( $value ) ) );

		return $value;
	}

	/**
	 * Get the value of submitted fields.
	 *
	 * @access protected
	 * @param  string $key
	 * @param  array $field
	 * @since 1.0.0
	 * @return array|string content of the submitted field
	 */
	protected static function get_posted_field( $key, $field ) {
		return isset( $_POST[ $key ] ) ? self::sanitize_posted_field( $_POST[ $key ] ) : '';
	}

	/**
	 * Validate the posted fields
	 *
	 * @return bool on success, WP_ERROR on failure
	 */
	protected static function validate_fields( $values ) {

		foreach ( self::$fields as $group_key => $group_fields ) {
			foreach ( $group_fields as $key => $field ) {
				if ( $field['required'] && empty( $values[ $group_key ][ $key ] ) ) {
					return new WP_Error( 'validation-error', sprintf( __( '%s is a required field' ), $field['label'] ) );
				}
			}
		}

		return apply_filters( 'wpum_password_form_validate_fields', true, self::$fields, $values );

	}

	/**
	 * Validate username field.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function validate_username( $passed, $fields, $values ) {

		$username = $values['user'][ 'username_email' ];

		if( is_email( $username ) && !email_exists( $username ) || !is_email( $username ) && !username_exists( $username ) )
			return new WP_Error( 'username-validation-error', __( 'This user could not be found.' ) );

		return $passed;

	}

	/**
	 * Process the submission.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function process() {

		// Get fields
		self::get_password_fields();

		// Get posted values
		$values = self::get_posted_fields();

		if ( empty( $_POST['wpum_submit_form'] ) && empty( $_POST['wpum_password_form_status'] ) ) {
			return;
		}

		// Validate required
		if ( is_wp_error( ( $return = self::validate_fields( $values ) ) ) ) {
			self::add_error( $return->get_error_message() );
			return;
		}

		// Check what the form status we should process
		if ( !empty( $_POST['wpum_password_form_status'] ) && $_POST['wpum_password_form_status'] == 'recover' ) {
			
			self::retrieve_password( $values['user'][ 'username_email' ] );
			self::add_confirmation( __('Check your e-mail for the confirmation link.') );

		}

	}

	/**
	 * Handles sending password retrieval email to user.
	 * Based on retrieve_password() in core wp-login.php
	 *
	 * @access public
	 * @uses $wpdb WordPress Database object
	 * @return bool True: when finish. False: on error
	 */
	public static function retrieve_password( $username ) {

		global $wpdb, $wp_hasher;

		// Check on username first, as users can use emails as usernames.
		$login = trim( $username );
		$user_data = get_user_by( 'login', $login );

		// If no user found, check if it login is email and lookup user based on email.
		if ( ! $user_data && is_email( $username ) && apply_filters( 'wpum_get_username_from_email', true ) ) {
			$user_data = get_user_by( 'email', trim( $username ) );
		}

		do_action( 'lostpassword_post' );

		if ( ! $user_data ) {
			self::add_error( __( 'Invalid username or e-mail.' ) );
			return false;
		}

		if ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
			self::add_error( __( 'Invalid username or e-mail.' ) );
			return false;
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow ) {

			self::add_error( __( 'Password reset is not allowed for this user' ) );
			return false;

		} elseif ( is_wp_error( $allow ) ) {

			self::add_error( __( 'Password reset is not allowed for this user' ) );
			return false;
		}

		$key = wp_generate_password( 20, false );

		do_action( 'retrieve_password_key', $user_login, $key );

		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}

		$hashed = $wp_hasher->HashPassword( $key );

		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

		/* == Send Email == */
			
		$site_url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');

		// Check if email exists first
		if( wpum_email_exists('password') ) {

			// Retrieve the email from the database
			$password_email = wpum_get_email('password');

			$message = wpautop( $password_email['message'] );
			$message = wpum_do_email_tags( $message, $user_data->ID, $key );

			WPUM()->emails->send( $user_email, $password_email['subject'], $message );

		} else {

			return false;

		}

		return true;

	}

	/**
	 * Output the form.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public static function output( $atts = array() ) {
		
		// Get fields
		self::get_password_fields();

		// Show errors from fields
		self::show_errors();

		// Show confirmation messages
		self::show_confirmations();

		// Display template
		get_wpum_template( 'password-form.php', 
			array(
				'atts'            => $atts,
				'form'            => self::$form_name,
				'user_fields'     => self::get_fields( 'user' ),
				'password_fields' => self::get_fields( 'password' ),
			)
		);

	}

}