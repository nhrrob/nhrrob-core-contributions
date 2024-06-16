<?php
/**
 * Plugin Name: NHR Core Contributions
 * Plugin URI: http://wordpress.org/plugins/nhrrob-core-contributions/
 * Description: Display Core Contributions stat in your own website
 * Author: Nazmul Hasan Robin
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: nhrrob-core-contributions
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main plugin class
 */
final class Nhrrob_Core_Contributions {

    /**
     * Plugin version
     *
     * @var string
     */
    const version = '1.0.0';

    /**
     * Class construcotr
     */
    private function __construct() {
        $this->define_constants();

        register_activation_hook( __FILE__, [ $this, 'activate' ] );

        add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
    }

    /**
     * Initialize a singleton instance
     *
     * @return \Nhrrob_Core_Contributions
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Define the required plugin constants
     *
     * @return void
     */
    public function define_constants() {
        define( 'NHRROB_CORE_CONTRIBUTIONS_VERSION', self::version );
        define( 'NHRROB_CORE_CONTRIBUTIONS_FILE', __FILE__ );
        define( 'NHRROB_CORE_CONTRIBUTIONS_PATH', __DIR__ );
        define( 'NHRROB_CORE_CONTRIBUTIONS_URL', plugins_url( '', NHRROB_CORE_CONTRIBUTIONS_FILE ) );
        define( 'NHRROB_CORE_CONTRIBUTIONS_ASSETS', NHRROB_CORE_CONTRIBUTIONS_URL . '/assets' );
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin() {

        new NhrrobCoreContributions\Assets();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new NhrrobCoreContributions\Ajax();
        }

        if ( is_admin() ) {
            new NhrrobCoreContributions\Admin();
        } else {
            new NhrrobCoreContributions\Frontend();
        }

        new NhrrobCoreContributions\API();
    }

    /**
     * Do stuff upon plugin activation
     *
     * @return void
     */
    public function activate() {
        $installer = new NhrrobCoreContributions\Installer();
        $installer->run();
    }
}

/**
 * Initializes the main plugin
 *
 * @return \Nhrrob_Core_Contributions
 */
function nhrrob_core_contributions() {
    return Nhrrob_Core_Contributions::init();
}

//call the plugin
nhrrob_core_contributions();
