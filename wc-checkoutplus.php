<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://pluginette.com
 * @since             0.1
 * @package           CheckoutPlus
 *
 * @wordpress-plugin
 * Plugin Name:       CheckoutPlus - WooCommerce Checkout Fields Builder
 * Plugin URI:        https://pluginette.com
 * Description:       The all-in-one WooCommerce checkout field editor
 * Version:           0.1.11
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            David Towoju
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wc-checkoutplus
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CHECKOUTPLUS_VERSION', '0.1.10' );
define( 'CHECKOUTPLUS_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'CHECKOUTPLUS_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'CHECKOUTPLUS_BASE', plugin_basename( __FILE__ ) );
define( 'CHECKOUTPLUS_PARTIALS', CHECKOUTPLUS_DIR_PATH . 'partials' );
define( 'CHECKOUTPLUS_ASSETS', CHECKOUTPLUS_DIR_URL . 'assets/' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
if ( ! file_exists( CHECKOUTPLUS_DIR_PATH . '/vendor/autoload.php' ) ) {
	return;
}

require CHECKOUTPLUS_DIR_PATH . '/vendor/autoload.php';
require CHECKOUTPLUS_DIR_PATH . '/includes/Helper.php';

$wc_checkoutplus = new CheckoutPlus\Core();

register_activation_hook( __FILE__, array( $wc_checkoutplus, 'activate_checkoutplus' ) );
register_deactivation_hook( __FILE__, array( $wc_checkoutplus, 'deactivate_checkoutplus' ) );

$wc_checkoutplus->register();
