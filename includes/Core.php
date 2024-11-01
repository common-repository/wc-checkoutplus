<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://pluginette.com
 * @since      1.0.0
 *
 * @package    CheckoutPlus
 * @subpackage CheckoutPlus/includes
 */

namespace CheckoutPlus;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    CheckoutPlus
 * @subpackage CheckoutPlus/includes
 * @author     David Towoju <hello@pluginette.com>
 */
class Core {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->plugin_name = 'wc-checkoutplus';
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function register() {
		// Japa if oga Woo no dey visible
		$this->load_dependencies();
		$this->set_locale();
		$this->define_hooks();
		$this->loader->run();
	}

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-wc-checkoutplus-activator.php
	 */
	public function activate_checkoutplus() {
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-wc-checkoutplus-deactivator.php
	 */
	public function deactivate_checkoutplus() {
	}


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		$this->loader = new Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin & public area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_hooks() {
		$plugin_admin = new Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_submenu', 100 );

		$plugin_fields = new Fields();
		$this->loader->add_action( 'init', $plugin_fields, 'save', 10 );
		$this->loader->add_action( 'init', $plugin_fields, 'register', 10 );
		$this->loader->add_action( 'checkoutplus_after_admin_enqueue', $plugin_fields, 'localize_fields', 10 );

		$plugin_checkout = new Checkout( $this->get_plugin_name(), $this->get_version(), $plugin_fields );
		$this->loader->add_filter( 'woocommerce_billing_fields', $plugin_checkout, 'prepare_billing_fields', 1 );
		$this->loader->add_filter( 'woocommerce_shipping_fields', $plugin_checkout, 'prepare_shipping_fields', 1 );
		$this->loader->add_filter( 'woocommerce_checkout_fields', $plugin_checkout, 'prepare_order_fields', 999 );
		$this->loader->add_filter( 'woocommerce_form_field', $plugin_checkout, 'checkout_fields_config', 10, 4 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_checkout, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_checkout, 'enqueue_scripts' );
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_checkout, 'save_data', 10, 2 );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
