<?php
/**
 * Plugin Name: Adaptive Learning with LearnDash
 * Plugin URI: http://wooninjas.com/
 * Description: Adaptive Learning with LearnDash
 * Version: 1.4
 * Author: Wooninjas
 * Author URI: http://wooninjas.com/
 * Text Domain: ld-adaptive-learning
 * @package Wooninjas
 */

namespace AL;

if ( !defined ( "ABSPATH" ) ) exit;
/**
 * Check if LearnDash is enabled
 */
function require_dependency ( ) {
    if ( !is_plugin_active ( "sfwd-lms/sfwd_lms.php" ) ) {
        deactivate_plugins ( plugin_basename( __FILE__ ) );
        $class = "error";
        $message = __( "Adaptive Learning with LearnDash requires <a href='https://www.learndash.com'>LearnDash</a> plugin to be activated.", "ld-adaptive-learning" );
        printf ( "<div class='%s'> <p>%s</p></div>", $class, $message );
    }
}
add_action ( "admin_notices", __NAMESPACE__ . "\\require_dependency" );

// Directory
define ( "AL\DIR", plugin_dir_path ( __FILE__ ) );
define ( "AL\DIR_FILE", DIR . basename ( __FILE__ ) );
define ( "AL\INCLUDES_DIR", trailingslashit ( DIR . "includes" ) );

// URLS
define ( "AL\URL", trailingslashit ( plugins_url ( "", __FILE__ ) ) );
define ( "AL\ASSETS_URL", trailingslashit ( URL . "assets" ) );

// Autoload classes for the plugin
spl_autoload_register ( __NAMESPACE__ . "\Main::autoloader" );
require_once INCLUDES_DIR . "functions.php";
//require_once INCLUDES_DIR . "AL_Core_Adaptive_Learning.php";

/**
 * Class Main for plugin initiation
 *
 * @since 1.0.0
 */
final class Main {
    public static $version = "1.4";

    // Main instance
    protected static $_instance = null;

    protected function __construct () {
        register_activation_hook ( __FILE__, array ( $this, "activation" ) );
        register_deactivation_hook ( __FILE__, array ( $this, "deactivation" ) );

        // Upgrade
        add_action ( "plugins_loaded", array ( $this, "upgrade" ) );

        add_action ( "admin_enqueue_scripts", array ( $this, "admin_enqueue_scripts" ) );
        add_action ( "wp_enqueue_scripts", array ( $this, "enqueue_scripts" ) );

        new AL_Core_Adaptive_Learning();
        new AL_Post_Types();
        new AL_Localize_Script();
        new AL_Install();
    }

    public static function autoloader ( $class ) {
        $class = str_replace ( __NAMESPACE__ . "\\" , "" , $class );
        if ( file_exists ( INCLUDES_DIR  . $class . ".php" ) ) {
            include INCLUDES_DIR  . $class . ".php";
        } elseif ( file_exists( INCLUDES_DIR  . "admin" . DIRECTORY_SEPARATOR . $class . ".php" ) ) {
            include INCLUDES_DIR  . "admin/" . $class . ".php";
        }
    }

    /**
     * @return $this
     */
    public static function instance () {
        if ( is_null ( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Activation function hook
     *
     * @return void
     */
    public static function activation () {
        if ( !current_user_can ( "activate_plugins" ) )
            return;

        update_option ( "al_version", self::$version );
    }

    /**
     * Deactivation function hook
     * No used in this plugin
     *
     * @return void
     */
    public static function deactivation () {}

    public static function upgrade () {
        if ( get_option ( "al_version" ) != self::$version ) {
            al_upgrade();
        }
    }

    /**
     * Enqueue scripts on admin
     */
    public static function admin_enqueue_scripts () {

        wp_enqueue_style ( "al-admin-css", ASSETS_URL . "css/al-admin.css", array(), self::$version );

        $deps = array (
            "jquery",
            "jquery-ui-core",
            "backbone",
            "editor"
        );
        wp_enqueue_script ( "al-admin-js", ASSETS_URL . "js/al-admin.js", $deps, self::$version, true );
    }

    /**
     * Enqueue scripts on frontend
     */
    public static function enqueue_scripts () {
        wp_enqueue_style ( "al-css", ASSETS_URL . "css/al.css", array(), self::$version );

        $deps = array (
            "jquery",
            "jquery-ui-core",
            "backbone",
            "editor"
        );
        wp_enqueue_script ( "al-js", ASSETS_URL . "js/al.js", $deps, self::$version, true );
    }
}

/**
 * Main instance
 *
 * @return Main
 */
function AL() {
    return Main::instance();
}

AL();