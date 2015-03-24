<?php
/**
 * WPUM Template: Message.
 * Displays a given message and sets the class to the div for styling purposes.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpum-message <?php echo esc_attr( $type ); ?>">

	<p><?php esc_html( $text ); ?></p>

</div>