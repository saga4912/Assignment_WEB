<?php

if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * Handles all preloader output functionality including display conditions,
 * template rendering, styles, and scripts management.
 * Uses singleton pattern to ensure single instance throughout the application.
 * 
 * @author   Alobaidi
 * @since    2.0.0
 */

class The_Preloader_Output {
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

    public function run() {
        add_action('wp_head', array($this, 'noscript'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'inline_styles'));
        add_action('wp_body_open', array($this, 'insert_preloader_element_automatically'), 0);
    }

    private function get_preloader_options(){
        $options = get_option('the_preloader_settings', array());
        $has_options = is_array($options) && !empty($options) ? true : false;

        if ( $has_options === true ) {
            return $options;
        }

        return false;
    }

    private function is_manually(){
        $options = $this->get_preloader_options();

        $is_manually = $options && isset($options['manually']) && $options['manually'] == 1 ? true : false;

        if ( $is_manually ) {
            return true;
        }

        return false;
    }

    private function is_enabled(){
        $options = $this->get_preloader_options();

        $is_preloader_enabled = $options && isset($options['enable_preloader']) && $options['enable_preloader'] == 1 ? true : false;

        if ( $is_preloader_enabled ) {
            return true;
        }

        return false;
    }

    private function display_locations() {
        $options = $this->get_preloader_options();
        $locations = $options && isset($options['display_locations']) && is_array($options['display_locations']) && !empty($options['display_locations']) 
            ? $options['display_locations'] 
            : array('home', 'front', 'posts');

        if ( empty($locations) ) {
            return false;
        }

        // If 'entire' is selected, return true (show the preloader on all site pages, includes woocommerce pages)
        if ( in_array('entire', $locations) ) {
            return true;
        }

        $is_woocommerce_page = function_exists('is_woocommerce') && ( is_product() || is_shop() || is_cart() || is_checkout() || is_account_page() || is_view_order_page() || is_product_category() || is_product_tag() || is_woocommerce() ) ? true : false;

        // If 'entire_ex_woo' site is selected, return true (show the preloader on all site pages, except woocommerce pages)
        if ( in_array('entire_ex_woo', $locations) && !$is_woocommerce_page ) {
            return true;
        }

        if ( in_array('woo_all', $locations) && $is_woocommerce_page ) {
            return true;
        }

        // Check individual locations
        foreach ($locations as $location) {

            switch ($location) {
                case 'home':
                // case 'home': will show the preloader on the page that displays latest posts, or on the page set in Settings > Reading > Posts page.
                    if ( is_home() ) return true;
                    break; // end (locations foreach) loop.

                case 'front':
                // case 'front': will show the preloader on the static page set in Settings > Reading > Homepage.
                    if ( is_front_page() ) return true; // the homepage in "Settings > Reading > Homepage".
                    break; // end (locations foreach) loop.

                case 'posts':
                    if ( is_singular('post') ) return true;
                    break; // end (locations foreach) loop.

                case 'pages':
                    if ( is_singular('page') && !is_front_page() && !$is_woocommerce_page ) return true;
                    break; // end (locations foreach) loop.

                case 'cats':
                    if ( is_category() ) return true;
                    break; // end (locations foreach) loop.

                case 'tags':
                    if ( is_tag() ) return true;
                    break; // end (locations foreach) loop.

                case 'attach':
                    if ( is_attachment() ) return true;
                    break; // end (locations foreach) loop.

                case 'error':
                    if ( is_404() ) return true;
                    break; // end (locations foreach) loop.

                case 'search':
                    if ( is_search() ) return true;
                    break; // end (locations foreach) loop.
            }

        }

        return false;
    }

    private function check_preloader_target() {
        $options = $this->get_preloader_options();
        
        // Get target setting, default to 'all' if not set
        $target = isset($options['preloader_target']) && !empty($options['preloader_target']) ? $options['preloader_target'] : 'all';
        
        // Always show if target is 'all'
        if ($target === 'all') {
            return true;
        }
        
        // Check if user is logged in
        $is_logged_in = is_user_logged_in();
        
        // Show for visitors only
        if ($target === 'visitors' && !$is_logged_in) {
            return true;
        }
        
        // Show for Logged in users only
        if ($target === 'users' && $is_logged_in) {
            return true;
        }
        
        return false;
    }

    private function should_display() {
        // 1. Check preloader target first
        if ( !$this->check_preloader_target() ) {
            return false;
        }

        // 2. Check display locations
        return $this->display_locations();
    }

    public function noscript() {
        ?>
        <noscript>
            <style>
                #the-preloader-element { display: none !important; }
            </style>
        </noscript>
        <?php
    }

    public function enqueue_scripts() {
        $options = $this->get_preloader_options();

        if ( !$options ) {
            return;
        }

        if ( !$this->is_enabled() ) {
            return;
        }

        if ( !$this->should_display() ) {
            return;
        }

        $template = isset($options['template']) && !empty($options['template']) ? $options['template'] : 'image';
        
        // Enqueue main styles and scripts
        wp_enqueue_style(
            THE_PRELOADER_PLUGIN_ID . '_template', 
            THE_PRELOADER_PLUGIN_URL . "css/templates/$template.css",
            array(),
            THE_PRELOADER_PLUGIN_VERSION
        );
        
        wp_enqueue_script(
            THE_PRELOADER_PLUGIN_ID . '_main_script',
            THE_PRELOADER_PLUGIN_URL . 'js/thePreloader.js',
            array(),
            THE_PRELOADER_PLUGIN_VERSION,
            true
        );
    }

    public function inline_styles() {
        $options = $this->get_preloader_options();

        if ( !$options ) {
            return;
        }

        if ( !$this->is_enabled() ) {
            return;
        }

        if ( !$this->should_display() ) {
            return;
        }

        $template = isset($options['template']) && !empty($options['template']) ? $options['template'] : 'image';
        if ( $template == 'image' ) {
            return;
        }

        $fill_color = isset($options['fill_color']) && !empty($options['fill_color']) ? esc_attr($options['fill_color']) : '#3498db';
        $scale = isset($options['scale']) && !empty($options['scale']) ? floatval($options['scale']) : 1;
        $scale = esc_attr($scale);

        if ( $template == 'infinity-loader' ) {
            $template_custom_css = ".infinity-loader_preloader-template:before, .infinity-loader_preloader-template:after { border-color: {$fill_color}; }";
        }
        else{
            $template_custom_css = ".classic-loader_preloader-template div { background: {$fill_color}; }";
        }

        $template_custom_css .= " #the-preloader-element > div { transform: scale({$scale}); }";
        
        wp_add_inline_style(THE_PRELOADER_PLUGIN_ID . '_template', $template_custom_css);
    }

    private function render_preloader($manually = false) {
        $options = $this->get_preloader_options();

        if ( !$options ) {
            return;
        }

        if ( !$this->is_enabled() ) {
            return;
        }

        if ( !$this->should_display() ) {
            return;
        }

        $template_name = isset($options['template']) && !empty($options['template']) ? $options['template'] : 'image';
        $template_bg_color = isset($options['background_color']) && !empty($options['background_color']) ? $options['background_color'] : '#f8f9fa';

        $wrap_bg_color = isset($options['wrap_bg_color']) && !empty($options['wrap_bg_color']) ? $options['wrap_bg_color'] : '#f8f9fa';
        $background_color = $template_name == 'image' ? $wrap_bg_color : $template_bg_color;

        $default_image_url = esc_url(THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/images/preloader.gif');
        $preloader_image_url = isset($options['image_url']) && !empty($options['image_url']) ? esc_url($options['image_url']) : $default_image_url;

        $image_width = isset($options['image_width']) && !empty($options['image_width']) ? floatval($options['image_width']) : 64;
        $image_height = isset($options['image_height']) && !empty($options['image_height']) ? floatval($options['image_height']) : 64;
        ?>
        <div id="the-preloader-element" style="background-color: <?php echo esc_attr($background_color); ?>;">
            <?php if ( $template_name == 'image' ) : ?>
                <div class="the-preloader-image" 
                    style="background: url(<?php echo esc_attr($preloader_image_url); ?>) no-repeat 50%;
                            background-size: 100% 100%;
                            width: <?php echo esc_attr($image_width); ?>px;
                            height: <?php echo esc_attr($image_height); ?>px;">
                </div>
            <?php elseif ( $template_name == 'infinity-loader' ) : ?>
                <div><div class="infinity-loader_preloader-template"></div></div>
            <?php else : ?>
                <div>
                    <div class="classic-loader_preloader-template">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function insert_preloader_element_automatically(){
        if ( !$this->is_manually() ) {
            return $this->render_preloader();
        }
    }

    public function insert_preloader_element_manually(){
        if ( $this->is_manually() ) {
            return $this->render_preloader();
        }
    }
}

if ( class_exists('The_Preloader_Output') && !function_exists('the_preloader_element') ) {
    function the_preloader_element(){
        return The_Preloader_Output::get_instance()->insert_preloader_element_manually();
    }
}