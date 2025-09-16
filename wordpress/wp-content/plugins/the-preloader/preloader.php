<?php
/**
 * Plugin Name: Preloader
 * Version: 2.0.2
 * Description: The ultimate Preloader plugin for WordPress. Smart, flexible, and made for easy control. Add a preloader to your website easily in only 3 steps.
 * Author: Alobaidi
 * Author URI: https://wp-plugins.in/PreloaderPlugin
 * Plugin URI: https://wp-plugins.in/PreloaderPlugin
 * Text Domain: the-preloader
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

if ( !defined('ABSPATH') ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('THE_PRELOADER_PLUGIN_ID', 'the_preloader');
define('THE_PRELOADER_PLUGIN_VERSION', '2.0.2');
define('THE_PRELOADER_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('THE_PRELOADER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('THE_PRELOADER_PLUGIN_BASENAME', plugin_basename(__FILE__));


// Add custom link to plugin meta row.
function the_preloader_plugin_row_meta($links, $file) {
    $plugin_file = THE_PRELOADER_PLUGIN_BASENAME;

    if ( $file == $plugin_file ) {
            
        $custom_link = array(
                '<a style="font-weight:bold;" href="https://wp-plugins.in/PreloaderPlugin" target="_blank">' . esc_html__('Plugin Reference', 'the-preloader') . '</a>',
                '<a style="font-weight:bold;" href="https://wp-plugins.in/VideoPopupPMR" target="_blank">' . esc_html__('Video Popup Plugin', 'the-preloader') . '</a>'
        );
            
        $links = array_merge($links, $custom_link);
    }

    return $links;
}
add_filter('plugin_row_meta', 'the_preloader_plugin_row_meta', 10, 2);


// Plugin initialization
if ( !class_exists('The_Preloader_Core') ) {
    require_once THE_PRELOADER_PLUGIN_PATH . 'includes/class-core.php';
    $The_Preloader_Main = The_Preloader_Core::get_instance();
    $The_Preloader_Main->initialize();
}