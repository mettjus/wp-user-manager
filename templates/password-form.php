<?php
/**
 * WPUM Template: Password Form Template.
 *
 * Displays password recovery form.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Define the form status
$form_status = 'recover'; 
if( isset($_GET['password-reset']) )
	$form_status = 'reset';

?>
<div id="wpum-form-password-<?php echo $atts['form_id'];?>" class="wpum-password-form-wrapper">

	<?php do_action( 'wpum_before_password_form_template', $atts ); ?>

	<p class="wpum-message wpum-info wpum-lost-psw-message">
		<?php echo apply_filters( 'wpum_lost_password_message', __( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.' ) ); ?>
	</p>

	<form action="#" method="post" id="wpum-password-<?php echo $atts['form_id'];?>" class="wpum-password-form" name="wpum-password-<?php echo $atts['form_id'];?>">

		<?php do_action( 'wpum_before_inside_password_form_template', $atts ); ?>

		<?php if( isset($_GET['password-reset']) ) : ?>

			<!-- Start Password Replace Fields -->
			<?php foreach ( $password_fields as $key => $field ) : ?>
				<fieldset class="fieldset-<?php esc_attr_e( $key ); ?>">
					<label for="<?php esc_attr_e( $key ); ?>"><?php echo $field['label']; ?></label>
					<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
						<?php get_wpum_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field ) ); ?>
					</div>
				</fieldset>
			<?php endforeach; ?>
			<!-- End Password Replace Fields -->

		<?php else : ?>

			<!-- Start Password User Fields -->
			<?php foreach ( $user_fields as $key => $field ) : ?>
				<fieldset class="fieldset-<?php esc_attr_e( $key ); ?>">
					<label for="<?php esc_attr_e( $key ); ?>"><?php echo $field['label']; ?></label>
					<div class="field <?php echo $field['required'] ? 'required-field' : ''; ?>">
						<?php get_wpum_template( 'form-fields/' . $field['type'] . '-field.php', array( 'key' => $key, 'field' => $field ) ); ?>
					</div>
				</fieldset>
			<?php endforeach; ?>
			<!-- End Password User Fields -->

		<?php endif; ?>

		<?php do_action( 'wpum_after_inside_password_form_template', $atts ); ?>

		<?php wp_nonce_field( 'wpum-password-nonce', 'security' ); ?>

		<p class="wpum-submit">
			<input type="hidden" name="wpum_submit_form" value="<?php echo $form; ?>" />
			<input type="hidden" name="wpum_password_form_status" value="<?php echo $form_status; ?>" />
			<input type="submit" id="submit_wpum_password" name="submit_wpum_password" class="button" value="<?php _e('Reset Password'); ?>" />
		</p>

	</form>

	<?php do_action( 'wpum_after_password_form_template', $atts ); ?>

</div>