<?php

/**
 * Plugin Name: Quantity Plus Minus Button for WooCommerce
 * Requires Plugins: woocommerce
 * Plugin URI: https://demo.wooproducttable.com/product/beanie/
 * Description: Easily add plus,minus button for WooCommerce Quantity Input box in everywhere. Such: Single Page, In Loop Quantity input, Cart page etc. 
 * Author: CodeAstrology Team
 * Author URI: https://codeastrology.com/
 *
 * Version: 2.0.0
 * Requires at least:    4.0.0
 * Tested up to:         6.8
 * WC requires at least: 3.7
 * WC tested up to: 	 9.8.5
 * 
 * Text Domain: wc-quantity-plus-minus-button
 * Domain Path: /languages
 * 
 * License: GPL3+
 * License URI: http://www.gnu.org/licenses/gpl.html
 */
if ( !defined( 'ABSPATH' ) ) {
    die;
}
if ( !defined( 'WQPMB_VERSION' ) ) {
    define( 'WQPMB_VERSION', '2.0.0.0' );
}
if ( !defined( 'WQPMB_NAME' ) ) {
    define( 'WQPMB_NAME', 'Quantity Plus Minus Button for WooCommerce' );
}
if ( !defined( 'WQPMB_BASE_NAME' ) ) {
    define( 'WQPMB_BASE_NAME', plugin_basename( __FILE__ ) );
}
if ( !defined( 'WQPMB_MENU_SLUG' ) ) {
    // define( 'WQPMB_MENU_SLUG', 'ua-quanity-plus-minus-button' );
    define( 'WQPMB_MENU_SLUG', 'wqpmb-settings' );
}
if ( !defined( 'WQPMB_MENU_NAME' ) ) {
    define( 'WQPMB_MENU_NAME', __( 'Plus Minus Button', 'wc-quantity-plus-minus-button' ) );
}
if ( !defined( 'WQPMB_BASE_URL' ) ) {
    define( "WQPMB_BASE_URL", plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) ) . '/' );
}
if ( !defined( 'WQPMB_MAIN_FILE' ) ) {
    define( "WQPMB_MAIN_FILE", __FILE__ );
}
if ( !defined( 'WQPMB_BASE_DIR' ) ) {
    define( "WQPMB_BASE_DIR", str_replace( '\\', '/', dirname( __FILE__ ) ) );
}
include_once ABSPATH . 'wp-admin/includes/plugin.php';
/**
 * Freemius integration
 * 
 */
if ( function_exists( 'wqpmb_fs' ) ) {
    wqpmb_fs()->set_basename( false, __FILE__ );
} else {
    /**
     * DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE
     * `function_exists` CALL ABOVE TO PROPERLY WORK.
     */
    if ( !function_exists( 'wqpmb_fs' ) ) {
        // Create a helper function for easy SDK access.
        function wqpmb_fs() {
            global $wqpmb_fs;
            if ( !isset( $wqpmb_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';
                $wqpmb_fs = fs_dynamic_init( array(
                    'id'              => '21188',
                    'slug'            => 'wc-quantity-plus-minus-button',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_b1d0dd3dec8813c5bc4918992d07a',
                    'is_premium'      => false,
                    'has_addons'      => false,
                    'has_paid_plans'  => true,
                    'trial'           => array(
                        'days'               => 10,
                        'is_require_payment' => true,
                    ),
                    'has_affiliation' => 'selected',
                    'menu'            => array(
                        'slug'       => 'wqpmb-settings',
                        'first-path' => 'admin.php?page=wqpmb-settings',
                    ),
                    'is_live'         => true,
                ) );
            }
            return $wqpmb_fs;
        }

        // Init Freemius.
        wqpmb_fs();
        // Signal that SDK was initiated.
        do_action( 'wqpmb_fs_loaded' );
    }
    // ... Your plugin's main file logic ...
}
WQPMB_Button::getInstance();
class WQPMB_Button {
    /**
     * Core singleton class
     * @var self - pattern realization
     */
    private static $_instance;

    /**
     * Option names Array, We have used to option key for WP Option table
     *
     * @var type Array
     */
    public static $option = array(
        'option' => 'wqpmb_configs',
        'css'    => 'wqpmb_css',
    );

    /**
     * CSS selector for Plus Minus Button tag
     *
     * @var type String
     */
    public static $css_selector = '.qib-button-wrapper button.qib-button';

    //,.qib-button-wrapper .quantity input.input-text.qty.text
    public static $input_css_selector = '.qib-button-wrapper .quantity input.input-text.qty.text';

    /**
     * Trying to commit and push something
     * Minimum PHP Version
     *
     * @since 1.0.0
     *
     * @var string Minimum PHP version required to run the plugin.
     */
    const MINIMUM_PHP_VERSION = '5.6';

    /*
     * List of Path
     * 
     * @since 1.0.0
     * @var array
     */
    protected $paths = array();

    /**
     * Set like Constant static array
     * Get this by getPath() method
     * Set this by setConstant() method
     *  
     * @var type array
     */
    private static $constant = array();

    /**
     * Set Path
     * 
     * @param type $path_array
     * 
     * @since 1.0.0
     */
    public function setPath( $path_array ) {
        $this->paths = $path_array;
    }

    /**
     * 
     * @param type $contanst_array
     */
    private function setConstant( $contanst_array ) {
        self::$constant = $this->paths;
    }

    /**
     * Set Path as like Constant Will Return Full Path
     * Name should like Constant and full Capitalize
     * 
     * @param type $name
     * @return string
     */
    public function path( $name, $_complete_full_file_path = false ) {
        $path = $this->paths[$name] . $_complete_full_file_path;
        return $path;
    }

    /**
     * To Get Full path to Anywhere based on Constant
     * 
     * @param type $constant_name
     * @return type String
     */
    public static function getPath( $constant_name = false ) {
        $path = self::$constant[$constant_name];
        return $path;
    }

    /**
     * Getting full Plugin data. We have used __FILE__ for the main plugin file.
     * 
     * @since V 1.5
     * @return Array Returnning Array of full Plugin's data for This Woo Product Table plugin
     */
    public static function getPluginData() {
        return get_plugin_data( __FILE__ );
    }

    /**
     * Getting Version by this Function/Method
     * 
     * @return type static String
     */
    public static function getVersion() {
        $data = self::getPluginData();
        return $data['Version'];
    }

    /**
     * Getting Version by this Function/Method
     * 
     * @return type static String
     */
    public static function getName() {
        $data = self::getPluginData();
        return $data['Name'];
    }

    /**
     * Create instance
     */
    public static function getInstance() {
        if ( !self::$_instance instanceof self ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        // Declare compatibility with custom order tables for WooCommerce.
        add_action( 'before_woocommerce_init', function () {
            if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
            }
        } );
        $is_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );
        if ( !$is_woocommerce ) {
            add_action( 'admin_notices', [$this, 'admin_notice_missing_main_plugin'] );
            return;
        }
        // Check for required PHP version
        if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', [$this, 'admin_notice_minimum_php_version'] );
            return;
        }
        add_action( 'plugins_loaded', function () {
            load_plugin_textdomain( 'wc-quantity-plus-minus-button', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        } );
        $dir = dirname( __FILE__ );
        include_once $dir . '/autoloader.php';
        /**
         * See $path_args for Set Path and set Constant
         * 
         * @since 1.0.0
         */
        $path_args = array(
            'PLUGIN_BASE_FOLDER' => plugin_basename( $dir ),
            'PLUGIN_BASE_FILE'   => plugin_basename( __FILE__ ),
            'BASE_URL'           => plugins_url() . '/' . plugin_basename( $dir ) . '/',
            'BASE_DIR'           => str_replace( '\\', '/', $dir . '/' ),
        );
        /**
         * Set Path Full with Constant as Array
         * 
         * @since 1.0.0
         */
        $this->setPath( $path_args );
        /**
         * Set Constant
         * 
         * @since 1.0.0
         */
        $this->setConstant( $path_args );
        include_once $this->path( 'BASE_DIR', 'includes/functions.php' );
        include_once $this->path( 'BASE_DIR', 'includes/admin-menu.php' );
        include_once $this->path( 'BASE_DIR', 'includes/load-scripts.php' );
        if ( is_admin() ) {
            $admin_page = new \WQPMB\Admin\Page_Loader();
            $admin_page->run();
        }
        $pro_file = dirname( __FILE__ ) . '/premium/premium-loader.php';
        if ( wqpmb_fs()->can_use_premium_code__premium_only() && file_exists( $pro_file ) ) {
            require_once $pro_file;
        }
        WQPMB\Includes\Feature_Loader::run();
    }

    public function admin_notice_missing_main_plugin() {
        if ( isset( $_GET['activate'] ) ) {
            unset($_GET['activate']);
        }
        $message = sprintf( 
            /* translators: 1: Plugin name 2: Plugin name */
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'wc-quantity-plus-minus-button' ),
            '<strong>' . WQPMB_NAME . '</strong>',
            '<strong><a href="' . esc_url( 'https://wordpress.org/plugins/woocommerce/' ) . '" target="_blank">' . esc_html__( 'WooCommerce', 'wc-quantity-plus-minus-button' ) . '</a></strong>'
         );
        printf( '<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $message );
    }

    public function admin_notice_minimum_php_version() {
        if ( isset( $_GET['activate'] ) ) {
            unset($_GET['activate']);
        }
        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'wc-quantity-plus-minus-button' ),
            '<strong>' . WQPMB_NAME . '</strong>',
            '<strong>' . esc_html__( 'PHP', 'wc-quantity-plus-minus-button' ) . '</strong>',
            self::MINIMUM_PHP_VERSION
        );
        printf( '<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $message );
    }

    public static function defaultDatas() {
        $default_data = array(
            'on_off'              => 'on',
            'css'                 => false,
            'quantiy_box_archive' => 1,
        );
        return $default_data;
    }

    /**
     * Activation Hook for WordPress
     */
    public static function install() {
        $default_data = self::defaultDatas();
        $option_key = self::$option['option'];
        $css_key = self::$option['css'];
        $saved_data = get_option( $option_key );
        if ( empty( $saved_data ) ) {
            update_option( $option_key, $default_data );
        }
    }

    /**
     * Deactivation Hook for WordPress
     */
    public static function uninstall() {
        //Nothing for now
        return;
    }

}

/**
* Plugin Install and Uninstall
*/
register_activation_hook( __FILE__, array('WQPMB_Button', 'install') );
register_deactivation_hook( __FILE__, array('WQPMB_Button', 'uninstall') );