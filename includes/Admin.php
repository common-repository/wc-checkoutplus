<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://pluginette.com
 * @since      1.0.0
 *
 * @package    CheckoutPlus
 * @subpackage CheckoutPlus/admin
 */

namespace CheckoutPlus;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CheckoutPlus
 * @subpackage CheckoutPlus/admin
 * @author     David Towoju <hello@pluginette.com>
 */
class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The collection of billing, shipping & additional fields
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $fields    The fields collection.
	 */
	private $fields;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		if ( ! defined( 'PLUGINETTE_ENV' ) ) {
			$app_src          = CHECKOUTPLUS_DIR_URL . 'assets/admin/css/app.css';
			$chunk_vendor_src = CHECKOUTPLUS_DIR_URL . 'assets/admin/css/chunk-vendors.css';
			wp_enqueue_style( 'checkoutplus-css-admin', $chunk_vendor_src, array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name, $app_src, array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		/**
		 *  Non-production environment
		 */
		if ( defined( 'PLUGINETTE_ENV' ) ) {
			$chunk_vendor_src = 'http://localhost:8080/js/chunk-vendors.js';
			$app_src          = 'http://localhost:8080/js/app.js';
		} else {
			$chunk_vendor_src = CHECKOUTPLUS_DIR_URL . 'assets/admin/js/chunk-vendors.js';
			$app_src          = CHECKOUTPLUS_DIR_URL . 'assets/admin/js/app.js';
		}

		wp_enqueue_script(
			'field-builder-vendors',
			$chunk_vendor_src,
			array(),
			'1.0.1',
			true
		);

		wp_enqueue_script(
			'field-builder-app',
			$app_src,
			array( 'wp-i18n' ),
			'1.0.1',
			true
		);

		do_action( 'checkoutplus_after_admin_enqueue' );

		wp_localize_script( 'field-builder-app', 'chxi18n', i18n::load_translations() );
	}

	/**
	 * Adds a submenu under Restrict menu
	 *
	 * @since    1.0.0
	 */
	public function add_submenu() {
		// remove_all_actions( 'admin_notices' );

		add_submenu_page(
			'woocommerce',
			esc_html__( 'Checkout Fields', 'wc-checkoutplus' ),  // The title
			esc_html__( 'Checkout Fields', 'wc-checkoutplus' ),  // The text to be displayed for this menu item
			'manage_options',                       // Which type of users can see this menu item
			'checkoutplus_fields',                     // The unique ID - that is, the slug - for this menu item
			array( $this, 'render_submenu_fields' )   // The name of the function to call
		);
	}

	/**
	 * Renders the submenu created above
	 *
	 * @since    1.0.0
	 */
	public function render_submenu_fields() {
		$this->enqueue_styles();
		$this->enqueue_scripts();
		require_once CHECKOUTPLUS_PARTIALS . '/field-builder.php';
	}

}
