<?php
/**
 * Getting Started Page Class
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Getting_Started Class
 *
 * A general class for About and Credits page.
 *
 * @since 1.0.0
 */
class WPUM_Getting_Started {

	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Get things started
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	/**
	 * Register the Dashboard Pages which are later hidden but these pages
	 * are used to render the Welcome and Credits pages.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function admin_menus() {
		// About Page
		add_dashboard_page(
			__( 'Welcome to WP User Manager', 'wpum' ),
			__( 'Welcome to WP User Manager', 'wpum' ),
			$this->minimum_capability,
			'wpum-about',
			array( $this, 'about_screen' )
		);

		// Getting Started Page
		add_dashboard_page(
			__( 'Getting started with WP User Manager', 'wpum' ),
			__( 'Getting started with WP User Manager', 'wpum' ),
			$this->minimum_capability,
			'wpum-getting-started',
			array( $this, 'getting_started_screen' )
		);

	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'wpum-about' );
		remove_submenu_page( 'index.php', 'wpum-getting-started' );

		// Badge for welcome page
		$badge_url = WPUM_PLUGIN_URL . 'images/badge.png';
		?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/
		.wpum-badge {
			background: url('<?php echo $badge_url; ?>') center 24px/85px 85px no-repeat #404448;
			-webkit-background-size: 85px 85px;
			color: #7ec276;
			font-size: 14px;
			text-align: center;
			font-weight: 600;
			margin: 5px 0 0;
			padding-top: 120px;
			height: 40px;
			display: inline-block;
			width: 150px;
			text-rendering: optimizeLegibility;
			-webkit-box-shadow: 0 1px 3px rgba(0,0,0,.2);
			box-shadow: 0 1px 3px rgba(0,0,0,.2);
		}

		.about-wrap .wpum-badge {
			position: absolute;
			top: 0;
			right: 0;
		}

		.wpum-welcome-screenshots {
			float: right;
			margin-left: 10px!important;
		}

		.about-wrap .feature-section {
			margin-top: 20px;
		}

		/*]]>*/
		</style>
		<?php
	}

	/**
	 * Navigation tabs
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function tabs() {
		$selected = isset( $_GET['page'] ) ? $_GET['page'] : 'wpum-about';
		?>
		<h2 class="nav-tab-wrapper">
			
			<?php if( WPUM_VERSION > 1 ) : ?>
			<a class="nav-tab <?php echo $selected == 'wpum-about' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wpum-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "What's New", 'wpum' ); ?>
			</a>
			<?php endif; ?>
			
			<a class="nav-tab <?php echo $selected == 'wpum-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wpum-getting-started' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Getting Started', 'wpum' ); ?>
			</a>
			
		</h2>
		<?php
	}

	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function about_screen() {
		?>
		<div class="wrap about-wrap">
			
			<h1><?php printf( __( 'Welcome to WP User Manager %s', 'wpum' ), WPUM_VERSION ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! WP User Manager %s is ready to provide improved control over your WordPress users.', 'wpum' ), WPUM_VERSION ); ?></div>
			<div class="wpum-badge"><?php printf( __( 'Version %s', 'wpum' ), WPUM_VERSION ); ?></div>

			<?php $this->tabs(); ?>

			<div class="changelog">
				
				<h3><?php _e( 'Full Changelog', 'wpum' );?></h3>

				<div class="feature-section">
					<?php echo $this->parse_readme(); ?>
				</div>

			</div>

		</div>
		<?php
	}

	/**
	 * Render Getting Started Screen
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function getting_started_screen() {
		?>
		<div class="wrap about-wrap">
			
			<h1><?php printf( __( 'Welcome to WP User Manager %s', 'wpum' ), WPUM_VERSION ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for installing the latest version! WP User Manager %s is ready to provide improved control over your WordPress users.', 'wpum' ), WPUM_VERSION ); ?></div>
			<div class="wpum-badge"><?php printf( __( 'Version %s', 'wpum' ), WPUM_VERSION ); ?></div>

			<?php $this->tabs(); ?>

			<p class="about-description"><?php _e('Use the tips below to get started using WP User Manager. You will be up and running in no time!', 'wpum'); ?></p>

			<div id="welcome-panel" class="welcome-panel" style="padding-top:0px;">
				<div class="welcome-panel-content">
					<div class="welcome-panel-column-container">
						<div class="welcome-panel-column">
							<h4><?php _e( 'Configure WP User Manager', 'wpum' );?></h4>
							<ul>
								<li><a href="<?php echo admin_url( 'users.php?page=wpum-settings' ); ?>#wpum_settings[password_strength]" class="welcome-icon dashicons-lock" target="_blank"><?php _e('Strengthen your passwords', 'wpum'); ?></a></li>
								<li><a href="<?php echo admin_url( 'users.php?page=wpum-settings' ); ?>#wpum_settings[login_method]" class="welcome-icon dashicons-admin-network" target="_blank"><?php _e('Setup login method', 'wpum'); ?></a></li>
								<li><a href="<?php echo admin_url( 'users.php?page=wpum-settings&tab=emails' ); ?>" class="welcome-icon dashicons-email-alt" target="_blank"><?php _e('Customize notifications', 'wpum'); ?></a></li>
							</ul>
						</div>
						<div class="welcome-panel-column">
							<h4><?php _e( 'Customize Profiles', 'wpum' );?></h4>
							<ul>
								<li><a href="<?php echo admin_url( 'users.php?page=wpum-settings&tab=profile' ); ?>" class="welcome-icon dashicons-admin-users" target="_blank"><?php _e('Customize profiles', 'wpum'); ?></a></li>
								<li><a href="<?php echo admin_url( 'users.php?page=wpum-profile-fields' ); ?>" class="welcome-icon dashicons-admin-settings" target="_blank"><?php _e('Customize fields', 'wpum'); ?></a></li>
								<li><a href="<?php echo admin_url( 'edit.php?post_type=wpum_directory' ); ?>" class="welcome-icon dashicons-groups" target="_blank"><?php _e('Create user directories', 'wpum'); ?></a></li>
							</ul>
						</div>
						<div class="welcome-panel-column welcome-panel-last">
							<h4><?php _e('Documentation', 'wpum'); ?></h4>
							<p class="welcome-icon welcome-learn-more"><?php echo sprintf( __( 'Looking for help? <a href="%s" target="_blank">WP User Manager documentation</a> has got you covered.', 'wpum' ), 'http://documentation.wpusermanager.com' ); ?> <br/><br/><a href="http://documentation.wpusermanager.com" class="button" target="_blank"><?php _e('Read documentation', 'wpum') ;?></a></p>
						</div>
					</div>
				</div>
			</div>

			<div class="changelog under-the-hood feature-list">

				<div class="feature-section col two-col">

					<div>
						<h3><?php _e('Looking for help ?', 'wpum'); ?></h3>
						<p><?php echo sprintf( __('We do all we can to provide every user with the best support possible. If you encounter a problem or have a question, please <a href="%s" target="_blank">contact us.</a> Make sure you <a href="%s">read the documentation</a> first.', 'wpum'), 'http://wpusermanager.com/contacts', 'http://documentation.wpusermanager.com' ); ?></p>
					</div>
					
					<div class="last-feature">
						<h3><?php _e('Get Notified of Extension Releases', 'wpum'); ?></h3>
						<p><?php echo sprintf( __('New extensions that make WP User Manager even more powerful will be released soon. Subscribe to the newsletter to stay up to date with our latest releases. Signup now to ensure you do not miss a release!', 'wpum'), '#' ); ?></p>
						<a href="http://wpusermanager.com/newsletter" class="button"><?php _e('Signup Now', 'wpum'); ?> &raquo;</a>
					</div>

					<hr>

					<div class="return-to-dashboard">
						<a href="<?php echo admin_url( 'users.php?page=wpum-settings' ); ?>"><?php _e('Go To WP User Manager &rarr; Settings', 'wpum'); ?></a>
					</div>
				
				</div>
			</div>

		</div>

		<?php
	}

	/**
	 * Parse the readme.txt file
	 *
	 * @since 1.0.1
	 * @return string $readme HTML formatted readme file
	 */
	public function parse_readme() {
		$file = file_exists( WPUM_PLUGIN_DIR . 'readme.txt' ) ? WPUM_PLUGIN_DIR . 'readme.txt' : null;
		if ( ! $file ) {
			$readme = '<p>' . __( 'No valid changlog was found.', 'wpum' ) . '</p>';
		} else {
			$readme = file_get_contents( $file );
			$readme = nl2br( esc_html( $readme ) );
			$readme = explode( '== Changelog ==', $readme );
			$readme = end( $readme );
			$readme = preg_replace( '/`(.*?)`/', '<code>\\1</code>', $readme );
			$readme = preg_replace( '/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $readme );
			$readme = preg_replace( '/[\040]\*(.*?)\*/', ' <em>\\1</em>', $readme );
			$readme = preg_replace( '/= (.*?) =/', '<h4>\\1</h4>', $readme );
			$readme = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $readme );
		}
		return $readme;
	}

	/**
	 * Sends user to the Welcome page on first activation of WPUM as well as each
	 * time WPUM is upgraded to a new version
	 *
	 * @access public
	 * @since 1.0
	 * @global $wpum_options Array of all the WPUM Options
	 * @return void
	 */
	public function welcome() {

		global $wpum_options;

		$major_wpum_version = substr( WPUM_VERSION, 0, strrpos( WPUM_VERSION, '.' ) );

		// Bail if no activation redirect
		if ( ! get_transient( '_wpum_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_wpum_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		$upgrade = get_option( 'wpum_version_upgraded_from' );

		if( ! $upgrade ) { // First time install
			wp_safe_redirect( admin_url( 'index.php?page=wpum-getting-started' ) ); exit;
		} else if( version_compare( get_option( 'wpum_version_upgraded_from' ), $major_wpum_version, '<' ) ) { // Update
			wp_safe_redirect( admin_url( 'index.php?page=wpum-about' ) ); exit;
		}
	}

}

new WPUM_Getting_Started();