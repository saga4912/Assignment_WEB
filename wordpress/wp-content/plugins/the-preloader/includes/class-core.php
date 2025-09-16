<?php

if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * Core class handles plugin initialization and manages main components (Output and Settings).
 * Uses singleton pattern to ensure single instance throughout the application.
 * 
 * @author   Alobaidi
 * @since    2.0.0
 */

class The_Preloader_Core {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     */
    private function __construct() {
        // No initialization here.
    }
    
    public function initialize() {
        // Run components
        if ( !is_admin() && !class_exists('The_Preloader_Output') ) {
            require_once THE_PRELOADER_PLUGIN_PATH . 'includes/class-output.php';
            $The_Preloader_Output = The_Preloader_Output::get_instance();
            $The_Preloader_Output->run();
        }

        if ( is_admin() && !class_exists('The_Preloader_Settings') ) {
            require_once THE_PRELOADER_PLUGIN_PATH . 'includes/class-settings.php';
            $The_Preloader_Settings = The_Preloader_Settings::get_instance();
            $The_Preloader_Settings->run();
        }
    }
}