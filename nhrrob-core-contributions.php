<?php
/**
 * Plugin Name: NHR Core Contributions
 * Plugin URI: http://wordpress.org/plugins/nhrrob-core-contributions/
 * Description: Display Core Contributions stat in your own website
 * Author: Nazmul Hasan Robin
 * Author URI: https://profiles.wordpress.org/nhrrob/
 * Version: 1.1.6
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
final class Nhrcc_Core_Contributions {

    use Nhrcc\CoreContributions\Traits\GlobalTrait;

    /**
     * Plugin version
     *
     * @var string
     */
    const nhrcc_version = '1.1.6';

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
     * @return \Nhrcc_Core_Contributions
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
        define( 'NHRCC_VERSION', self::nhrcc_version );
        define( 'NHRCC_FILE', __FILE__ );
        define( 'NHRCC_PATH', __DIR__ );
        define( 'NHRCC_URL', plugins_url( '', NHRCC_FILE ) );
        define( 'NHRCC_ASSETS', NHRCC_URL . '/assets' );
        define( 'NHRCC_PLUGIN_DIR', plugin_dir_path( NHRCC_FILE ) );
        define( 'NHRCC_INCLUDES_PATH', NHRCC_PATH . '/includes' );
        define( 'NHRCC_VIEWS_PATH', NHRCC_INCLUDES_PATH . '/views' );
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init_plugin() {

        $assetObj = new Nhrcc\CoreContributions\Assets();
        $apiObj = new Nhrcc\CoreContributions\API();
        $blocksObj = new Nhrcc\CoreContributions\Blocks();

        $assetObj->init();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new Nhrcc\CoreContributions\Ajax();
        }

        if ( is_admin() ) {
            new Nhrcc\CoreContributions\Admin();
        } else {
            new Nhrcc\CoreContributions\Frontend();
        }

        $apiObj->init();
        
        $blocksObj->init();
    }

    /**
     * Do stuff upon plugin activation
     *
     * @return void
     */
    public function activate() {
        $installer = new Nhrcc\CoreContributions\Installer();
        $installer->run();
    }
}

/**
 * Initializes the main plugin
 *
 * @return \Nhrcc_Core_Contributions
 */
function nhrcc_core_contributions() {
    return Nhrcc_Core_Contributions::init();
}

// Call the plugin
nhrcc_core_contributions();

// Dispatch actions
Nhrcc\CoreContributions\Admin::dispatch_actions();