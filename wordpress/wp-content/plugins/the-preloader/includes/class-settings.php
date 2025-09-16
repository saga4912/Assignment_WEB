<?php

if ( !defined('ABSPATH') ) {
    exit;
}

/**
 * Handles all plugin settings functionality including admin page, tabs, options,
 * and sanitization. Uses singleton pattern to ensure single instance.
 * 
 * @author   Alobaidi
 * @since    2.0.0
 */

class The_Preloader_Settings {
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

    // Add settings link before activate/deactivate plugin links.
    public function custom_plugin_action_links($actions, $plugin_file){
        $plugin = THE_PRELOADER_PLUGIN_BASENAME;
            
        if ($plugin == $plugin_file) {
            $custom_link = '<a href="' . admin_url('admin.php?page=' . THE_PRELOADER_PLUGIN_ID) . '">'.esc_html__('Settings', 'the-preloader').'</a>';
            $actions = array_merge(array($custom_link), $actions);
        }

        return $actions;
    }

    public function run() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'add_menu_page'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('admin_head', array($this, 'first_use'));
        add_filter('plugin_action_links', array($this, 'custom_plugin_action_links'), 10, 5);
    }

    public function admin_scripts($hook) {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- This is a read-only check to enqueue assets, not processing form input.
        if ( isset($_GET['page']) && $_GET['page'] === THE_PRELOADER_PLUGIN_ID ) {

            // phpcs:enable WordPress.Security.NonceVerification.Recommended

            wp_enqueue_style(
                THE_PRELOADER_PLUGIN_ID . '-admin-style',
                THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/css/admin-style.css',
                array(),
                THE_PRELOADER_PLUGIN_VERSION
            );

            wp_enqueue_style(
                THE_PRELOADER_PLUGIN_ID . '-preview-templates',
                THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/css/preview-templates.css',
                array(THE_PRELOADER_PLUGIN_ID . '-admin-style'),
                THE_PRELOADER_PLUGIN_VERSION
            );

            wp_enqueue_style(
                THE_PRELOADER_PLUGIN_ID . '-color-scheme',
                THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/css/color-scheme.css',
                array(THE_PRELOADER_PLUGIN_ID . '-admin-style'),
                THE_PRELOADER_PLUGIN_VERSION
            );

            wp_enqueue_script(
                THE_PRELOADER_PLUGIN_ID . '-admin-tabs',
                THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/js/admin-tabs.js',
                array(),
                THE_PRELOADER_PLUGIN_VERSION,
                false
            );

            wp_enqueue_script(
                THE_PRELOADER_PLUGIN_ID . '-template-selector',
                THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/js/template-selector.js',
                array(),
                THE_PRELOADER_PLUGIN_VERSION,
                false
            );

            wp_enqueue_script(
                THE_PRELOADER_PLUGIN_ID . '-preloader-image-preview',
                THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/js/preloader-image-preview.js',
                array(),
                THE_PRELOADER_PLUGIN_VERSION,
                true
            );

            wp_enqueue_script(
                THE_PRELOADER_PLUGIN_ID . '-display-locations',
                THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/js/display-locations.js',
                array(),
                THE_PRELOADER_PLUGIN_VERSION,
                true
            );

            wp_enqueue_script(
                THE_PRELOADER_PLUGIN_ID . '-color-field',
                THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/js/color-field.js',
                array(),
                THE_PRELOADER_PLUGIN_VERSION,
                true
            );

            wp_enqueue_script('jquery');
            wp_enqueue_media();

            if ( !get_option('the_preloader_first_use') ) {
                update_option('the_preloader_first_use', 'used');
            }
        }
    }

    public function first_use() {
        if ( !get_option('the_preloader_first_use') ) {
            ?>
                <style>
                    li.toplevel_page_the_preloader > a.toplevel_page_the_preloader{
                        background-color: #6f36d6 !important;
                    }
                </style>
            <?php
        }
    }

    /**
     * SVG Menu icon
     * @since    2.0.1
     */
    private function menu_icon_svg() {
        return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="256" height="256" viewBox="0 0 256 256"><path fill="#A7AAAD" d="M200.706 38.3244C203.921 37.7633 207.707 38.5486 210.535 40.1675C214.003 42.1137 216.532 45.3832 217.545 49.2281C218.433 52.6028 218.128 57.2186 216.329 60.2273C215.519 61.5532 214.599 62.8083 213.578 63.9791C212.683 64.9998 211.669 66.0627 210.681 67.0168C203.847 73.6171 197.43 80.8297 190.217 86.9995C188.698 88.2985 185.929 89.1341 184.011 89.6187C171.236 91.7614 161.42 77.0072 169.12 66.2913C173.153 61.5394 177.91 56.5868 182.471 52.3313C187.937 47.2327 193.109 39.5917 200.706 38.3244Z"/><path fill="#A7AAAD" d="M203.045 113.1C204.994 112.819 212.516 112.879 214.968 112.923C222.27 113.055 230.264 112.522 237.45 113.455C239.132 113.673 241.833 115.15 243.239 116.211C246.366 118.541 248.417 122.038 248.924 125.905C250.211 135.286 243.831 142.058 234.825 143.237C227.032 143.457 219.091 143.175 211.282 143.297C207.907 143.349 203.52 143.475 200.289 142.749C197.919 142.213 195.713 141.113 193.859 139.541C190.836 136.947 188.969 133.256 188.67 129.283C188.369 125.316 189.654 121.391 192.243 118.369C195.18 114.924 198.596 113.45 203.045 113.1Z"/><path fill="#A7AAAD" d="M127.269 188.591C130.615 188.332 134.25 189.673 136.966 191.493C143.788 196.065 143.478 203.952 143.29 211.186C143.238 213.197 143.322 215.211 143.269 217.19C142.929 229.918 146.818 246.238 129.688 249.03C121.061 249.248 113.912 244.437 113.167 235.193C112.521 227.181 113.198 218.855 112.93 210.801C112.83 207.797 112.842 203.564 113.417 200.671C113.864 198.313 114.889 196.103 116.399 194.238C119.156 190.837 122.958 189.031 127.269 188.591Z"/><path fill="#A7AAAD" d="M125.898 7.29992C129.724 6.97614 132.868 7.43681 136.216 9.46808C146.155 15.5985 142.699 28.3146 143.27 38.0506C143.526 42.4213 143.238 47.3853 143.273 51.8185C143.337 59.8461 138.307 65.7506 130.526 67.458C122.223 68.1273 114.826 63.9727 113.362 55.0601C112.83 49.5059 112.758 43.1605 112.959 37.5783C113.21 30.6229 111.549 19.4003 115.693 13.6019C118.119 10.2255 121.793 7.95697 125.898 7.29992Z"/><path fill="#A7AAAD" d="M21.5012 113.099C21.608 113.084 21.7149 113.069 21.8218 113.054C29.8072 112.849 37.8132 112.907 45.8029 112.929C51.8349 112.946 57.0136 112.474 62.0207 116.421C65.0998 118.871 67.0802 122.443 67.5267 126.352C67.9798 130.36 66.8218 134.383 64.3077 137.537C61.5729 140.935 57.8305 142.706 53.5597 143.176C52.0669 143.265 50.5151 143.318 48.9927 143.278C38.9991 143.014 28.6954 143.887 18.7748 142.794C17.0424 142.603 14.0259 140.857 12.6735 139.766C9.62413 137.322 7.68883 133.752 7.30526 129.863C6.35687 120.676 12.5345 113.922 21.5012 113.099Z"/><path fill="#A7AAAD" d="M72.9578 166.572C76.3674 166.102 80.1108 167.179 82.9717 169.038C86.3558 171.211 88.7032 174.674 89.4678 178.622C90.2497 182.545 89.4424 186.361 87.2404 189.678C84.8367 193.299 64.9876 213.261 61.1669 215.749C59.2922 216.97 57.1543 217.516 54.9885 217.948C51.2247 218.345 47.4471 217.336 44.3824 215.116C36.5301 209.427 36.1438 198.681 42.898 191.963C49.9019 184.997 56.7883 177.882 63.8784 170.999C66.4662 168.486 69.4236 167.101 72.9578 166.572Z"/><path fill="#A7AAAD" d="M179.693 166.572C183.287 166.309 186.687 166.939 189.734 168.931C193.077 171.117 213.257 191.378 215.703 194.826C216.749 196.3 217.394 198.139 217.748 199.907C218.56 203.896 217.729 208.044 215.444 211.413C212.756 215.374 209.247 217.08 204.692 217.944C199.209 218.369 193.861 215.987 190.481 211.851C184.747 204.835 168.485 192.979 166.766 184.48C165.982 180.569 166.792 176.508 169.017 173.197C171.626 169.311 175.228 167.454 179.693 166.572Z"/></svg>';
    }

    public function add_menu_page() {
        $menu_icon = 'dashicons-admin-generic'; // default menu icon

        $svg_icon  =  $this->menu_icon_svg();
        $menu_icon = 'data:image/svg+xml;base64,' . base64_encode($svg_icon);

        add_menu_page(
            esc_html__('Preloader Settings', 'the-preloader'),
            esc_html__('Preloader', 'the-preloader'),
            'manage_options',
            THE_PRELOADER_PLUGIN_ID,
            array($this, 'render_page'),
            $menu_icon
        );
    }

    public function get_default_settings() {
        return array(
                'enable_preloader' => 0,
                'image_url' => '',
                'wrap_bg_color' => '#f8f9fa',
                'image_width' => 64,
                'image_height' => 64,

                'preloader_target' => 'all',
                'display_locations' => array('home', 'front', 'posts'),

                'template' => 'image',
                'background_color' => '#f8f9fa',
                'fill_color' => '#3498db',
                'scale' => 1,

                'manually' => 0
            );
    }

    public function register_settings() {
        // phpcs:disable PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic
        // This setting uses a proper sanitize_callback method defined in the class: sanitize_settings()
        // The callback ensures all user input is properly sanitized.
        register_setting(
            'the_preloader_options',
            'the_preloader_settings',
            array(
                'sanitize_callback' => array($this, 'sanitize_settings')
            )
        );
        // phpcs:enable PluginCheck.CodeAnalysis.SettingSanitization.register_settingDynamic
        
        if ( !get_option('the_preloader_settings') ) {
            $default_settings = $this->get_default_settings();

            if ( get_option('wptpreloader_image') ) {
                $default_settings['image_url'] = esc_url_raw(get_option('wptpreloader_image')); // Get image url from old version settings.
                delete_option('wptpreloader_image');
            }else{
                $default_settings['image_url'] = esc_url_raw(THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/images/preloader.gif');
            }

            if ( get_option('wptpreloader_bg_color') ) {
                // Get wrap background color from old version settings.
                $default_settings['wrap_bg_color'] = sanitize_text_field(get_option('wptpreloader_bg_color'));
                delete_option('wptpreloader_bg_color');
            }
            
            add_option('the_preloader_settings', $default_settings);
        }
    }

    private function is_valid_int_or_float($value) {
        $value = trim($value);
        
        if (empty($value) || !is_numeric($value)) {
            return false;
        }

        // Convert to float for comparison
        $floatVal = floatval($value);
        
        if ($floatVal <= 0) {
            return false;
        }

        // Check if it's a whole number (like 50.0)
        if (floor($floatVal) == $floatVal) {
            // If it's written with decimal point
            if (strpos($value, '.') !== false) {
                return false; // Reject numbers like 50.0
            }
            return true; // Accept whole numbers like 50
        }
        
        // For decimal numbers, make sure they have meaningful decimal places
        $decimal = $floatVal - floor($floatVal);
        return $decimal > 0;
    }

    public function sanitize_settings($input) {
        $sanitized = array();
        $defaults = $this->get_default_settings();

        // Verify nonce
        if ( !isset($_POST['the_preloader_settings_nonce']) || 
            !wp_verify_nonce(
                sanitize_key($_POST['the_preloader_settings_nonce']), 
                'the_preloader_settings_action'
            )) {
            // Add error message
            add_settings_error(
                'the_preloader_settings',
                'settings_error',
                esc_html__('Security check failed. Please try saving the settings again. If the problem persists, log out then log back in and try again.', 'the-preloader'),
                'error'
            );
            // Return existing settings
            return get_option('the_preloader_settings', $defaults);
        }

        /* Sanitize General Settings */
        $sanitized['enable_preloader'] = isset($input['enable_preloader']) ? 1 : 0;

        if ( isset($input['image_url']) ) {
            $default_image_url = esc_url_raw(THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/images/preloader.gif');
            $sanitized['image_url'] = !empty( trim($input['image_url']) ) ? esc_url_raw($input['image_url']) : $default_image_url;
        }

        if ( isset($input['wrap_bg_color']) ) {
            $sanitized['wrap_bg_color'] = sanitize_hex_color(trim($input['wrap_bg_color']));
        }

        if ( isset($input['image_width']) ) {
            $is_valid_number = $this->is_valid_int_or_float($input['image_width']);
            $sanitized['image_width'] = $is_valid_number === true && trim($input['image_width']) > 0 ? floatval($input['image_width']) : 64;
        }

        if ( isset($input['image_height']) ) {
            $is_valid_number = $this->is_valid_int_or_float($input['image_height']);
            $sanitized['image_height'] = $is_valid_number === true && trim($input['image_height']) > 0 ? floatval($input['image_height']) : 64;
        }

        /* Sanitize Display Settings */
        // Define valid display locations
        $valid_locations = array(
            'entire', 'entire_ex_woo', 'home', 'front', 'posts', 'pages', 'woo_all', 'cats', 'tags', 
            'attach', 'error', 'search'
        );

        // Initialize display_locations array
        $sanitized['display_locations'] = array();

        // Check if display_locations is set and is array
        if (isset($input['display_locations']) && is_array($input['display_locations'])) {
            foreach ($input['display_locations'] as $location) {
                // Only add valid locations
                if (in_array($location, $valid_locations)) {
                    $sanitized['display_locations'][] = $location;
                }
            }
        }

        // If no valid locations selected, set default
        if (empty($sanitized['display_locations'])) {
            $sanitized['display_locations'] = array('home', 'front', 'posts');
        }

        $valid_targets = array('all', 'visitors', 'users');
        $sanitized['preloader_target'] = isset($input['preloader_target']) && in_array($input['preloader_target'], $valid_targets) 
            ? $input['preloader_target'] 
            : $defaults['preloader_target'];

        /* Sanitize Template Settings */        
        if ( isset($input['background_color']) ) {
            $sanitized['background_color'] = sanitize_hex_color(trim($input['background_color']));
        }
        
        if ( isset($input['fill_color']) ) {
            $sanitized['fill_color'] = sanitize_hex_color(trim($input['fill_color']));
        }

        if ( isset($input['scale']) ) {
            $scale = floatval($input['scale']);
            $sanitized['scale'] = max(0.3, min(2, $scale)); // Limit between 0.3 and 2
        }else{
            $sanitized['scale'] = 1;
        }

        if ( isset($input['template']) ) {
            $sanitized['template'] = sanitize_text_field($input['template']);
        }

        /* Sanitize Integration Settings */
        $sanitized['manually'] = isset($input['manually']) ? 1 : 0;
        
        // Add success message
        add_settings_error(
            'the_preloader_settings',
            'settings_updated',
            esc_html__('Settings saved! If changes are not showing on your site, please clear your cache. Read the answer to question #1 in the FAQ tab.', 'the-preloader'),
            'updated'
        );

        // Merge with defaults to ensure all settings exist
        return wp_parse_args($sanitized, $defaults);
    }

    public function render_page() {
        ?>
        <div class="wrap preloader-settings-wrap">
            <h1><?php esc_html_e('Preloader Settings', 'the-preloader'); ?></h1>

            <?php settings_errors('the_preloader_settings'); ?>
            
            <div class="preloader-tabs">
                <button class="tab-button thp-tab-active" data-tab="tab-general"><?php esc_html_e('General', 'the-preloader'); ?></button>

                <button class="tab-button" data-tab="tab-display"><?php esc_html_e('Display', 'the-preloader'); ?></button>

                <button class="tab-button" data-tab="tab-cookie"><?php esc_html_e('Cookie', 'the-preloader'); ?></button>

                <button class="tab-button" data-tab="tab-templates"><?php esc_html_e('Templates', 'the-preloader'); ?></button>

                <button class="tab-button" data-tab="tab-integration"><?php esc_html_e('Integration', 'the-preloader'); ?></button>

                <button class="tab-button tab-faq-button" data-tab="tab-faq"><?php esc_html_e('FAQ', 'the-preloader'); ?></button>

                <button class="tab-button tab-upgrade-button" data-tab="tab-upgrade"><?php esc_html_e('Upgrade to Premium', 'the-preloader'); ?></button>
            </div>
    
            <form action="options.php" method="post">
                <div id="tp-admin-preloader">
                    <div class="tp-admin-preloader-wrap">
                        <?php
                            // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
                            // This is a static preloader GIF image bundled with the plugin itself (not user input).
                            // Since the image is located within the plugin's directory and is not a dynamic or external source,
                            // it is safe to embed it directly using the <img> tag without wp_get_attachment_image().
                        ?>
                        <img src="<?php echo esc_url(THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/images/admin-preloader.gif'); ?>" 
                             width="32" 
                             height="32" 
                             alt="<?php esc_attr_e('Loading settings... Please wait...', 'the-preloader'); ?>">
                        <p style="text-align: center; margin: 12px 0 0 0; color: #333; padding: 0; font-size: 12px;">
                            <?php esc_html_e('Loading settings... Please wait...', 'the-preloader'); ?>
                        </p>
                        <?php
                            // phpcs:enable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage 
                        ?>
                    </div>
                </div>

                <?php 
                    settings_fields('the_preloader_options'); 
                    wp_nonce_field('the_preloader_settings_action', 'the_preloader_settings_nonce');
                ?>
                
                <div id="tab-content-wrap" style="display: none;">
                    <div class="tab-content thp-tab-active" id="tab-general">
                        <?php $this->render_tab('general'); ?>
                    </div>
        
                    <div class="tab-content" id="tab-display">
                        <?php $this->render_tab('display'); ?>
                    </div>

                    <div class="tab-content" id="tab-cookie">
                        <?php $this->render_tab('cookie'); ?>
                    </div>
        
                    <div class="tab-content" id="tab-templates">
                        <?php $this->render_tab('template'); ?>
                    </div>
        
                    <div class="tab-content" id="tab-integration">
                        <?php $this->render_tab('integration'); ?>
                    </div>
        
                    <div class="tab-content" id="tab-faq">
                        <?php $this->render_tab('faq'); ?>
                    </div>

                    <div class="tab-content" id="tab-upgrade">
                        <?php $this->render_tab('upgrade'); ?>
                    </div>
                </div>
    
                <div id="tp-submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr__('Save Changes', 'the-preloader'); ?>"></div>

                <p class="tp-tagline" style="margin-top: 15px; color:#777; font-size: 13px;">
                    <strong><?php esc_html_e('For any issues you encounter, we recommend reading the FAQ section as it\'s specifically designed to help you quickly resolve issues.', 'the-preloader'); ?></strong>
                </p>
            </form>
        </div>
        <?php
        $this->settings_page_script();
    }

    private function settings_page_script() {
        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        $reset_cookie_alert = esc_js( __("Preloader cookie deleted successfully on this browser!", 'the-preloader') );
        $no_cookie_alert = esc_js( __("No preloader cookie found on this browser.", 'the-preloader') );
        ?>
        <script>
            jQuery(document).ready(function($) {
                var preloaderUploaderFrame;
                $('#preloader_upload_btn').on('click', function(e) {
                    e.preventDefault();
        
                    if (preloaderUploaderFrame) {
                        preloaderUploaderFrame.open();
                        return;
                    }
        
                    preloaderUploaderFrame = wp.media({
                        title: '<?php echo esc_js( __('Select or Upload Preloader Image', 'the-preloader') ); ?>',
                        button: {
                            text: '<?php echo esc_js( __('Use this image', 'the-preloader') ); ?>'
                        },
                        multiple: false
                    });
        
                    preloaderUploaderFrame.on('select', function() {
                        var attachment = preloaderUploaderFrame.state().get('selection').first().toJSON();
                        $('#preloader_image_url').val(attachment.url);
                        $('#preloader_preview_image').attr('src', attachment.url);
                        $('#preloader_preview_wrap').show();
                        $('#preloader_remove_btn').show();
                    });
        
                    preloaderUploaderFrame.open();
                });
        
                $('#preloader_remove_btn').on('click', function() {
                    $('#preloader_image_url').val('');
                    $('#preloader_preview_image').attr('src', '');
                    $('#preloader_preview_wrap').hide();
                    $(this).hide();
                });

                $('#reset_preloader_cookie').on('click', function(e) {
                    // Check if cookie exists
                    if (document.cookie.indexOf('preloader_shown=') !== -1) {
                        document.cookie = "preloader_shown=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;"; // Delete cookie
                        alert('<?php echo $reset_cookie_alert; ?>');
                    } else {
                        alert('<?php echo $no_cookie_alert; ?>');
                    }
                });
            });
        </script>

        <script>
            window.addEventListener('load', function() {

                setTimeout(function() {

                    const tp_admin_preloader = document.getElementById('tp-admin-preloader');
                    if ( tp_admin_preloader ) {
                        tp_admin_preloader.remove();
                    }

                    const tp_tab_content_wrap = document.getElementById('tab-content-wrap');
                    if ( tp_tab_content_wrap ) {
                        tp_tab_content_wrap.style.display = 'block';
                    }

                }, 600);

            });
        </script>

        <noscript>
            <style>
                #tp-admin-preloader { display: none !important; }
                #tab-content-wrap, #tp-submit { display: block !important; }
            </style>
        </noscript>
        <?php
        // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
    }
    
    private function general_tab_options() {
        $options = get_option('the_preloader_settings', $this->get_default_settings());
        $default_image_url = esc_url(THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/images/preloader.gif');
        $image_url = isset($options['image_url']) && !empty($options['image_url']) ? esc_url($options['image_url']) : $default_image_url;

        if ( !isset($options['enable_preloader']) ) {
            $options['enable_preloader'] = 0;
        }

        if ( !isset($options['wrap_bg_color']) ) {
            $options['wrap_bg_color'] = '#f8f9fa';
        }

        if ( !isset($options['image_width']) ) {
            $options['image_width'] = 64;
        }

        if ( !isset($options['image_height']) ) {
            $options['image_height'] = 64;
        }

        ?>
        <h2><?php esc_html_e('General Settings', 'the-preloader'); ?></h2>

        <?php
            if ( isset($options['enable_preloader']) && $options['enable_preloader'] == 0 ) {
                ?>
                    <div class="tp-display-notice">
                        <p class="tp-tagline" style="color:#555;"><?php esc_html_e('Note: Preloader is disabled. Enable it using the option below.', 'the-preloader'); ?></p>
                    </div>
                <?php
            }
        ?>
    
        <div class="tp-form-field">
            <label>
                <input type="checkbox" 
                    name="the_preloader_settings[enable_preloader]" 
                    value="1" 
                    <?php checked($options['enable_preloader'], 1); ?>> <?php esc_html_e('Enable Preloader.', 'the-preloader'); ?></label>
            <p class="tp-tagline"><?php esc_html_e("There's no need to deactivate the plugin if you just want to disable the preloader. You can enable or disable the preloader at any time using this option.", 'the-preloader'); ?></p>
        </div>
    
        <div class="tp-form-field">
            <label for="preloader_image_url"><?php esc_html_e('Preloader Image URL:', 'the-preloader'); ?></label>
            <div class="image-upload-wrap">
                <input type="text" 
                       name="the_preloader_settings[image_url]" 
                       id="preloader_image_url" 
                       placeholder="<?php esc_attr_e('Enter a GIF image URL here...', 'the-preloader'); ?>" 
                       value="<?php echo esc_attr($image_url); ?>">
                <button type="button" 
                        class="button" 
                        id="preloader_upload_btn"><?php esc_html_e('Select or Upload Image', 'the-preloader'); ?></button>
                <button type="button" 
                        class="button" 
                        id="preloader_remove_btn" 
                        style="<?php echo empty($image_url) ? 'display: none;' : ''; ?>"><?php esc_html_e('Remove Image', 'the-preloader'); ?></button>
            </div>
            <p class="tp-tagline">
                <?php
                    printf(
                        // translators: %1$s is opening link tag, %2$s is closing link tag
                        esc_html__('Select or upload or enter a GIF image URL. Only GIF format is supported, such as %1$sthis image%2$s.', 'the-preloader'),
                        '<a href="' . esc_url(THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/images/preloader.gif') . '" target="_blank">',
                        '</a>'
                    );
                ?>
            </p>
        </div>
    
        <div class="tp-form-field">
            <label for="preloader_bg_color"><?php esc_html_e('Background Color:', 'the-preloader'); ?></label>
            <input type="color" 
                id="preloader_bg_color" 
                style="max-width: 100px;" 
                name="the_preloader_settings[wrap_bg_color]" 
                title="<?php esc_attr_e('Double-click to switch the field type.', 'the-preloader'); ?>" 
                value="<?php echo esc_attr($options['wrap_bg_color']); ?>">
            <p class="tp-tagline"><?php esc_html_e('Click on the color field to choose a color using the color picker. To enter a custom HEX color code, double-click on the color field to make it editable, then enter your HEX color code. To switch back to the color picker, double-click the field again. Default background color is "#f8f9fa". For more details, read the answer to question #3 in the FAQ tab.', 'the-preloader'); ?></p>
        </div>
    
        <div class="tp-form-field">
            <label for="preloader_width"><?php esc_html_e('Image Width:', 'the-preloader'); ?></label>
            <input type="text" 
                   id="preloader_width" 
                   style="max-width: 100px;" 
                   name="the_preloader_settings[image_width]" 
                   min="1" 
                   step="1" 
                   value="<?php echo esc_attr($options['image_width']); ?>">

            <p class="tp-tagline"><?php esc_html_e('Width value is in pixels. Only whole or decimal numbers allowed, without "px".', 'the-preloader'); ?></p>
        </div>
    
        <div class="tp-form-field">
            <label for="preloader_height"><?php esc_html_e('Image Height:', 'the-preloader'); ?></label>
            <input type="text" 
                   id="preloader_height" 
                   style="max-width: 100px;" 
                   name="the_preloader_settings[image_height]" 
                   min="1" 
                   step="1" 
                   value="<?php echo esc_attr($options['image_height']); ?>">
            <p class="tp-tagline"><?php esc_html_e('Height value is in pixels. Only whole or decimal numbers allowed, without "px".', 'the-preloader'); ?></p>
        </div>

        <?php
            // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
            // The image URL is directly inputted by the user, and it is a URL of an image already uploaded to the WordPress media library.
            // Therefore, we are using the <img> tag directly without wp_get_attachment_image(), as the image is safely hosted within WordPress.
        ?>
        <div id="preloader_preview_wrap" class="tp-form-field" style="<?php echo empty($image_url) ? 'display: none;' : ''; ?>">
            <div><?php esc_html_e('Preview', 'the-preloader'); ?></div>
            <img src="<?php echo esc_attr($image_url); ?>" 
                 id="preloader_preview_image">
        </div>

        <?php
        // phpcs:enable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
        ?>

        <div class="tp-general-tip">
            <h3><?php esc_html_e('Best Image Size Tip', 'the-preloader'); ?></h3>
            <p class="tp-tagline"><?php esc_html_e('Just take your image width and height size, divide it by "2", and use that. This helps your image show sharp on high-resolution screens (such as Apple\'s Retina displays). So, for example, if your image is "256x256", just set the width and height to "128". If your image is "180x90", just set the width to "90" and the height to "45". Easy as that. For more details, read the answer to question #4 in the FAQ tab.', 'the-preloader'); ?></p>
        </div>

        <div class="tp-general-tip">
            <h3><?php esc_html_e('Preloader Templates vs Preloader Image', 'the-preloader'); ?></h3>
            <p class="tp-tagline"><?php esc_html_e('Using an animated image as a preloader is considered somewhat outdated. Animated images are usually large in file size and not easy to customize. In contrast, templates are lightweight (no more than 2KB), image-free, built using HTML/CSS3, and easy to customize their color and scale. To use the Preloader Templates, click on the "Templates" tab at the top.', 'the-preloader'); ?></p>
        </div>
        <?php
    }

    private function display_tab_options(){
        $options = get_option('the_preloader_settings', $this->get_default_settings());
        $display_locations = isset($options['display_locations']) ? $options['display_locations'] : array('home', 'front', 'posts');

        if ( !isset($options['preloader_target']) ) {
            $options['preloader_target'] = 'all';
        }
        ?>

            <h2><?php esc_html_e('Display Settings', 'the-preloader'); ?></h2>

            <div class="tp-form-field">
                <label for="preloader_target"><?php esc_html_e('Show Preloader To:', 'the-preloader'); ?></label>
                <select name="the_preloader_settings[preloader_target]" id="preloader_target">
                    <option value="all" <?php selected($options['preloader_target'], 'all'); ?>><?php esc_html_e('Everyone', 'the-preloader'); ?></option>
                    <option value="visitors" <?php selected($options['preloader_target'], 'visitors'); ?>><?php esc_html_e('Visitors only', 'the-preloader'); ?></option>
                    <option value="users" <?php selected($options['preloader_target'], 'users'); ?>><?php esc_html_e('Logged in users only', 'the-preloader'); ?></option>
                </select>
                <p class="tp-tagline">
                    <?php esc_html_e('Choose who the preloader shows to. This lets you control whether the preloader shows for everyone, visitors only, or logged in users only.', 'the-preloader'); ?>
                </p>
            </div>

            <div class="tp-form-field display-locations-grid">

                <label><?php esc_html_e('Display Locations:', 'the-preloader'); ?></label>

                <p class="tp-tagline"><?php esc_html_e('Select where you want to show the preloader. You must select at least one location. To understand the difference between Blog Page and Homepage, read the answer to question #5 in the FAQ tab.', 'the-preloader'); ?></p>
        
                <div class="locations-group">
                    <h4><?php esc_html_e('General', 'the-preloader'); ?></h4>
                    <label>
                        <input type="checkbox" 
                            name="the_preloader_settings[display_locations][]" 
                            value="entire"
                            <?php checked(in_array('entire', $display_locations)); ?>>
                        <?php esc_html_e('Entire Site', 'the-preloader'); ?>
                    </label>
                    <label>
                        <input type="checkbox" 
                            name="the_preloader_settings[display_locations][]" 
                            value="entire_ex_woo"
                            <?php checked(in_array('entire_ex_woo', $display_locations)); ?>>
                        <?php esc_html_e('Entire Site (excluding WooCommerce pages - if installed)', 'the-preloader'); ?>
                    </label>
                    <label>
                        <input type="checkbox" 
                            name="the_preloader_settings[display_locations][]" 
                            value="home"
                            <?php checked(in_array('home', $display_locations)); ?>>
                        <?php 
                        printf(
                            // translators: %1$s is opening link tag, %2$s is closing link tag
                            esc_html__('Blog Page (will show the preloader on the page that displays your latest posts, or on the page set in %1$sSettings > Reading%2$s > Posts page)', 'the-preloader'),
                            '<a href="' . esc_url( admin_url('options-reading.php') ) . '" target="_blank">',
                            '</a>'
                        );
                        ?>
                    </label>
                    <label>
                        <input type="checkbox" 
                            name="the_preloader_settings[display_locations][]" 
                            value="front"
                            <?php checked(in_array('front', $display_locations)); ?>>
                        <?php 
                        printf(
                            // translators: %1$s is opening link tag, %2$s is closing link tag
                            esc_html__('Homepage (will show the preloader on the static page you set in %1$sSettings > Reading%2$s > Homepage)', 'the-preloader'),
                            '<a href="' . esc_url( admin_url('options-reading.php') ) . '" target="_blank">',
                            '</a>'
                        );
                        ?>
                    </label>
                    <label>
                        <input type="checkbox" 
                            name="the_preloader_settings[display_locations][]" 
                            value="posts"
                            <?php checked(in_array('posts', $display_locations)); ?>>
                        <?php esc_html_e('Posts', 'the-preloader'); ?>
                    </label>
                    <label>
                        <input type="checkbox" 
                            name="the_preloader_settings[display_locations][]" 
                            value="pages"
                            <?php checked(in_array('pages', $display_locations)); ?>>
                        <?php esc_html_e('Pages', 'the-preloader'); ?>
                    </label>
                    <label>
                        <input type="checkbox" 
                            name="the_preloader_settings[display_locations][]" 
                            value="woo_all"
                            <?php checked(in_array('woo_all', $display_locations)); ?>>
                        <?php esc_html_e('All WooCommerce Pages', 'the-preloader'); ?>
                    </label>
                    <label>
                        <input type="checkbox" 
                            name="the_preloader_settings[display_locations][]" 
                            value="cats"
                            <?php checked(in_array('cats', $display_locations)); ?>>
                        <?php esc_html_e('Categories', 'the-preloader'); ?>
                    </label>
                    <label>
                        <input type="checkbox" 
                            name="the_preloader_settings[display_locations][]" 
                            value="tags"
                            <?php checked(in_array('tags', $display_locations)); ?>>
                        <?php esc_html_e('Tags', 'the-preloader'); ?>
                    </label>
                    <label>
                        <input type="checkbox" 
                            name="the_preloader_settings[display_locations][]" 
                            value="attach"
                            <?php checked(in_array('attach', $display_locations)); ?>>
                        <?php esc_html_e('Attachments', 'the-preloader'); ?>
                    </label>
                    <label>
                        <input type="checkbox" 
                            name="the_preloader_settings[display_locations][]" 
                            value="error"
                            <?php checked(in_array('error', $display_locations)); ?>>
                        <?php esc_html_e('404 Error Page', 'the-preloader'); ?>
                    </label>
                    <label>
                        <input type="checkbox" 
                            name="the_preloader_settings[display_locations][]" 
                            value="search"
                            <?php checked(in_array('search', $display_locations)); ?>>
                        <?php esc_html_e('Search Results', 'the-preloader'); ?>
                    </label>
                </div>

                <div class="locations-group">
                    <h4 class="tp-premium-feature"><?php esc_html_e('Specific WooCommerce Pages & Custom Post Types (Premium)', 'the-preloader'); ?></h4>
                    <p class="tp-tagline">
                        <?php
                            printf(
                                // translators: %1$s is opening link tag, %2$s is closing link tag
                                esc_html__('With the %1$spremium version%2$s, you can show the preloader on specific WooCommerce pages only (shop, products, cart, checkout, account pages, product categories, product tags), without showing it on other pages. There\'s also a single option to show the preloader on any available public custom post type.', 'the-preloader'),
                                '<a href="https://wp-plugins.in/PreloaderPremium" target="_blank">',
                                '</a>'
                            );
                        ?>
                    </p>
                </div>
            </div>

            <div class="tp-form-field">
                <label class="tp-premium-feature"><?php esc_html_e('Always Show Preloader on these IDs (Premium):', 'the-preloader'); ?></label>
                <p class="tp-tagline">
                    <?php
                        printf(
                            // translators: %1$s is opening link tag, %2$s is closing link tag
                            esc_html__('With the premium version, you can force show the preloader on specific content by their IDs, regardless of the display location settings above. You can also uncheck all display locations and use IDs to show the preloader on specific content only. Want to show the preloader on specific content only? %1$sUpgrade to Premium%2$s.', 'the-preloader'),
                            '<a href="https://wp-plugins.in/PreloaderPremium" target="_blank">',
                            '</a>'
                        );
                    ?>
                </p>
            </div>

            <div class="tp-form-field">
                <label class="tp-premium-feature"><?php esc_html_e('Never Show Preloader on these IDs (Premium):', 'the-preloader'); ?></label>
                <p class="tp-tagline">
                    <?php
                        esc_html_e('With the premium version, you can force hide the preloader on specific content by their IDs, regardless of the display location settings above.', 'the-preloader');
                    ?>
                </p>
            </div>

            <div class="tp-form-field">
                <label class="tp-premium-feature"><?php esc_html_e('Delay Before Fade (Premium):', 'the-preloader'); ?></label>
                <p class="tp-tagline">
                    <?php
                        esc_html_e('With the premium version, you can add a delay (in seconds) after page has finished loading, before the preloader starts to fade out. For example: setting value to "3" means the preloader will stay visible for 3 seconds after page has finished loading, then start fading out!', 'the-preloader');
                    ?>
                </p>
            </div>

            <div class="tp-form-field">
                <label class="tp-premium-feature"><?php esc_html_e('Fade Speed (Premium):', 'the-preloader'); ?></label>
                <p class="tp-tagline">
                    <?php
                        esc_html_e('With the premium version, you can control how long the fade out takes (in seconds). For example: setting value to "1" means once the fade begins (after page loads and after any delay set above), it will take 1 second to completely fade out.', 'the-preloader');
                    ?>
                </p>
            </div>

            <div class="tp-form-field">
                <label class="tp-premium-feature"><?php esc_html_e('Hide Preloader on Specific Screen Width (Premium):', 'the-preloader'); ?></label>
                <p class="tp-tagline">
                    <?php
                        esc_html_e('With the premium version, you can hide the preloader based on screen width. For example: setting value to "960" will hide the preloader on most tablets and phones (screens 960px or smaller), or setting value to "480" will hide the preloader on phones only.', 'the-preloader');
                    ?>
                </p>
            </div>

        <?php
    }

    private function cookie_tab_options(){
        ?>

            <h2 class="tp-premium-feature"><?php esc_html_e('Cookie-based Display (Premium)', 'the-preloader'); ?></h2>

            <p class="tp-tagline" style="margin: 0; font-size: 16px; color: #555;">
                <?php
                printf(
                    // translators: %1$s and %2$s are image link tags, %3$s and %4$s are upgrade link tags
                    esc_html__('Instead of showing the preloader every time a visitor opens your website, show it just once for each visitor or user, then again after a set number of days for the same visitor or user! A smart way to improve user experience and reduce annoyance. 3 Options. GDPR compliant. See the cookie-based display settings in this %1$simage%2$s or a %3$slive demo%4$s. %5$sUpgrade to Premium%6$s.', 'the-preloader'),
                    '<a href="' . esc_url(THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/images/cookie-feature.png') . '" target="_blank">',
                    '</a>',
                    '<a href="https://wp-plugins.in/Preloader-Cookie-basedDisplay" target="_blank">',
                    '</a>',
                    '<a href="https://wp-plugins.in/PreloaderPremium" target="_blank">',
                    '</a>'
                );
                ?>
            </p>
        <?php
    }

    private function template_tab_options() {
        $options = get_option('the_preloader_settings', $this->get_default_settings());
        $templates = array( 
                        'image' => __('Image', 'the-preloader'),
                        'classic-loader' => __('Classic Loader', 'the-preloader'),
                        'infinity-loader' => __('Infinity Loader', 'the-preloader')
                    );
        $current_template = $options['template'] ?? 'image';

        $default_image_url = THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/images/preloader.gif';
        $preloader_image_url = isset($options['image_url']) && !empty($options['image_url']) ? $options['image_url'] : $default_image_url;
        $wrap_bg_color = isset($options['wrap_bg_color']) && !empty($options['wrap_bg_color']) ? $options['wrap_bg_color'] : '#f8f9fa';

        if ( !isset($options['background_color']) ) {
            $options['background_color'] = '#f8f9fa';
        }

        if ( !isset($options['fill_color']) ) {
            $options['fill_color'] = '#3498db';
        }

        if ( !isset($options['scale']) ) {
            $options['scale'] = 1;
        }
        
        // Load all template styles for preview
        foreach ($templates as $key => $label) {
            $css_file = THE_PRELOADER_PLUGIN_URL . 'css/templates/' . $key . '.css';
            wp_enqueue_style(
                'preloader-template-' . $key,
                $css_file,
                array(),
                THE_PRELOADER_PLUGIN_VERSION
            );
        }
        ?>
        <h2><?php esc_html_e('Preloader Templates', 'the-preloader'); ?></h2>

        <p class="tp-tagline" style="margin-bottom: 15px; color: #555; font-size: 16px;"><?php 
            printf(
                // translators: %1$s and %2$s are first link tags, %3$s and %4$s are second link tags
                esc_html__('Preloader templates are ready-made loading animations built with HTML and CSS3, no more than 2KB in size compared to GIF images, which means better website performance and user experience. We\'ve included two free templates with customization options below. There are %1$s30+ professional templates%2$s in the %3$sPremium Version%4$s.', 'the-preloader'),
                '<a href="https://wp-plugins.in/PreloaderTemplates" target="_blank">',
                '</a>',
                '<a href="https://wp-plugins.in/PreloaderPremium" target="_blank">',
                '</a>'
            );
            ?></p>

        <p class="tp-tagline" style="margin-bottom: 7px;">
            <?php esc_html_e('Click on a template to choose it.', 'the-preloader'); ?>
        </p>

        <p class="tp-tagline" style="margin-bottom: 7px;">
            <?php esc_html_e('The customization options shown below do not apply to the GIF image that was uploaded in the General tab; they only apply to templates. If you choose "Your GIF Image", your GIF image that was uploaded in the General tab will be used as the preloader, and the background color and image sizes (width and height) set in the General tab will be applied.', 'the-preloader'); ?>
        </p>

        <p class="tp-tagline" style="margin-bottom: 7px;">
            <?php esc_html_e('Click on the color fields to choose colors using the color picker, or double-click to enter custom HEX color code. To switch back to the color picker, double-click the field again. For more details, read the answer to question #3 in the FAQ tab.', 'the-preloader'); ?>
        </p>

        <p class="tp-tagline" style="margin-bottom: 7px;">
            <?php esc_html_e('To restore template customization options to their default values, click "Reset".', 'the-preloader'); ?>
        </p>

        <p class="tp-tagline" style="margin: 15px 0; color: #555; padding: 12px; border-radius: 5px; background: #f0f0f0;">
            <strong class="tp-premium-feature"><?php esc_html_e('Typing Effect Template:', 'the-preloader'); ?></strong> <?php 
            printf(
                // translators: %1$s is opening link tag, %2$s is closing link tag
                esc_html__('How about showing a message or promoting a coupon on your website while page is loading? With the Typing Effect Template for Preloader, you can turn loading time into a clever opportunity to engage your visitors. See a %1$slive demo%2$s.', 'the-preloader'),
                '<a href="https://wp-plugins.in/TypingEffectTemplate" target="_blank">',
                '</a>'
            );
            ?>
        </p>

        <div class="template-colors">
            <div class="color-field">
                <label for="background-color-picker"><?php esc_html_e('Background Color', 'the-preloader'); ?></label>
                <input type="color" 
                    class="background-color-picker" 
                    id="background-color-picker" 
                    title="<?php esc_attr_e('Double-click to switch the field type.', 'the-preloader'); ?>" 
                    name="the_preloader_settings[background_color]" 
                    value="<?php echo esc_attr($options['background_color']); ?>">
            </div>
            <div class="color-field">
                <label for="fill-color-picker"><?php esc_html_e('Fill Color', 'the-preloader'); ?></label>
                <input type="color" 
                    class="fill-color-picker" 
                    id="fill-color-picker" 
                    title="<?php esc_attr_e('Double-click to switch the field type.', 'the-preloader'); ?>" 
                    name="the_preloader_settings[fill_color]" 
                    value="<?php echo esc_attr($options['fill_color']); ?>">
            </div>
            <div class="color-field">
                <label for="scale-range"><?php esc_html_e('Scale', 'the-preloader'); ?></label>
                <input type="range" 
                    class="scale-range"
                    id="scale-range"
                    name="the_preloader_settings[scale]" 
                    min="0.3"
                    max="2"
                    step="0.1"
                    value="<?php echo esc_attr($options['scale']); ?>">
                <span class="scale-value" title="<?php esc_attr_e('Scale value', 'the-preloader'); ?>"><?php echo esc_html($options['scale']); ?></span>
            </div>
            <button type="button" class="button reset-colors"><?php esc_html_e('Reset', 'the-preloader'); ?></button>
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr__('Save Changes', 'the-preloader'); ?>">
        </div>

        <div class="templates-grid">
            <?php foreach ($templates as $key => $label) : ?>
                <?php
                    $template_class = $key == 'image' ? 'template-preview image-tp-template' : 'template-preview';
                    $label = $key == 'image' ? __('Your GIF Image', 'the-preloader') : $label;
                ?>
                <label class="template-item <?php echo ($current_template === $key) ? 'thp-tab-active' : ''; ?>">
                    <input type="radio" 
                           name="the_preloader_settings[template]" 
                           value="<?php echo esc_attr($key); ?>"
                           <?php checked($current_template, $key); ?>>
                    <div class="<?php echo esc_attr($template_class); ?>">
                        <?php if ( $key == 'image' ) : ?>

                            <?php if ( $preloader_image_url ) : ?>
                                <style type="text/css">
                                    .template-preview.image-tp-template{
                                        background: <?php echo esc_attr($wrap_bg_color); ?> !important;
                                    }
                                </style>
                                <div><img 
                                    src="<?php echo esc_url($preloader_image_url); ?>" 
                                    style="display: block; margin:0 auto; padding:0; border:none; width: 64px; height: 64px;"></div>
                            <?php else : ?>
                                <div><?php esc_html_e('Upload a GIF image in the General tab.', 'the-preloader'); ?></div>
                            <?php endif; ?>
                        <?php elseif ( $key == 'infinity-loader' ) : ?>
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
                    <span class="template-name"><?php echo esc_html($label); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function integration_tab_options(){
        $options = get_option('the_preloader_settings', $this->get_default_settings());

        if ( !isset($options['manually']) ) {
            $options['manually'] = 0;
        }
        ?>
            <h2><?php esc_html_e('Manual Integration', 'the-preloader'); ?></h2>

            <div class="tp-form-field">

                <label>
                    <input type="checkbox" 
                        name="the_preloader_settings[manually]" 
                        value="1" 
                        <?php checked($options['manually'], 1); ?>> <?php esc_html_e('Insert preloader element manually.', 'the-preloader'); ?></label>

                <p class="tp-tagline">
                    <?php
                        $theme = wp_get_theme();
                        $header_link = sprintf(
                            '<a href="%s" target="_blank">header.php</a>',
                            esc_url( admin_url('theme-editor.php?file=header.php&theme=' . $theme->get_stylesheet()) )
                        );

                        printf(
                            // translators: %s is a link to the theme's header.php file.
                            esc_html__('The preloader element is automatically added to your theme using the "wp_body_open" hook. However, if your theme doesn\'t support this hook or doesn\'t display the preloader correctly, enable this option, then go to your theme\'s %s file and add the following code right after the "<body>" tag:', 'the-preloader'),
                            wp_kses(
                                $header_link,
                                array(
                                    'a' => array(
                                        'href' => array(),
                                        'target' => array()
                                    )
                                )
                            )
                        );
                    ?>
                    <br><code><?php echo esc_html('<?php echo the_preloader_element(); ?>'); ?></code>
                </p>

                <p class="tp-tagline" style="margin-top: 15px;">
                    <?php
                        printf(
                            // translators: %s is a link to wp_body_open image
                            esc_html__('To check if your theme supports "wp_body_open" hook, look at this image: %s', 'the-preloader'),
                            '<a href="' . esc_url(THE_PRELOADER_PLUGIN_URL . 'includes/admin-assets/images/wp_body_open.png') . '" target="_blank">view</a>'
                        );
                    ?>
                </p>

                <p class="tp-tagline" style="margin-top: 15px;">
                    <?php esc_html_e('Can\'t find the header.php file in your theme? Or found it but can\'t edit it? In this case, your theme likely supports the "wp_body_open" hook, but contact your theme developer to ask about "wp_body_open" hook support or how to access and edit the header.php file. For more details, read the answers to questions #11, #12, and #13 in the FAQ tab.', 'the-preloader'); ?>
                </p>

            </div>
        <?php
    }

    private function faq_tab_content() {
    ?>
        <h2><?php esc_html_e('Frequently Asked Questions', 'the-preloader'); ?></h2>

        <div class="faq-item">
            <h3><?php esc_html_e('1. I saved the changes but they\'re not showing on the site. What\'s the solution?', 'the-preloader'); ?></h3>
            <p><?php esc_html_e('This means your site is likely using a caching plugin. Caching plugins store a static copy of your pages to improve loading speed. This means visitors see the cached version of your pages instead of generating them new each time, which means visitors will see the old content that doesn\'t include your recent changes.', 'the-preloader'); ?></p>
            
            <p><?php esc_html_e('When you make any changes to the preloader settings (enabling/disabling preloader, changing background color, etc.), you need to clear your entire site cache to apply these changes.', 'the-preloader'); ?></p>

            <p><?php esc_html_e('If you\'re using a caching plugin like WP Super Cache, W3 Total Cache, or similar, we recommend:', 'the-preloader'); ?></p>
            
            <ol>
                <li><?php esc_html_e('Set up all preloader settings at once.', 'the-preloader'); ?></li>
                <li><?php esc_html_e('Save the changes.', 'the-preloader'); ?></li>
                <li><?php esc_html_e('Clear your entire site cache once.', 'the-preloader'); ?></li>
            </ol>
            
            <p><?php esc_html_e('Remember, if you make any changes to the preloader settings later, you must clear the cache again.', 'the-preloader'); ?></p>
        </div>

        <div class="faq-item">
            <h3><?php esc_html_e('2. The preloader shows but never fades out! What are the possible causes and solutions?', 'the-preloader'); ?></h3>
            
            <p><?php esc_html_e('This is a rare issue. The cause is usually a conflict with either:', 'the-preloader'); ?></p>
            
            <ul>
                <li><?php esc_html_e('One of your installed plugins.', 'the-preloader'); ?></li>
                <li><?php esc_html_e('JavaScript files used in your theme.', 'the-preloader'); ?></li>
            </ul>

            <p><?php 
                printf(
                    // translators: %1$s is opening link tag, %2$s is closing link tag
                    esc_html__('To help us investigate, open the page where the issue occurs in Google Chrome, right-click anywhere on the page, select "Inspect", then click on the "Console" tab. Take a screenshot of the console, then %1$scontact us%2$s and provide the screenshot. We\'ll help you if the issue is related to our plugin.', 'the-preloader'),
                    '<a href="https://wp-plugins.in/Contact" target="_blank">',
                    '</a>'
                );
            ?></p>
        </div>

        <div class="faq-item">
            <h3><?php esc_html_e('3. How can I enter a custom HEX color code?', 'the-preloader'); ?></h3>
            <p><?php esc_html_e('Double-click on the color field to make it editable, then enter your HEX color code. To switch back to the color picker, double-click the field again. Only HEX color codes are allowed (starts with # followed by 6 characters). For example: #0000FF for blue. RGB and RGBA colors are not supported. The following color fields support double-click editing:', 'the-preloader'); ?></p>

            <ol>
                <li><?php esc_html_e('Background Color (in General tab) - Default color: #f8f9fa', 'the-preloader'); ?></li>
                <li><?php esc_html_e('Background Color (in Templates tab) - Default color: #f8f9fa', 'the-preloader'); ?></li>
                <li><?php esc_html_e('Fill Color (in Templates tab) - Default color: #3498db', 'the-preloader'); ?></li>
            </ol>
        </div>

        <div class="faq-item">
            <h3><?php esc_html_e('4. Why should I divide my image size by 2 for better display?', 'the-preloader'); ?></h3>
            <p><?php esc_html_e('Modern devices often have high-resolution screens (like Retina displays) that have twice as many pixels as standard screens. By using half the actual image dimensions, your preloader image will show crisp on these high-resolution displays while maintaining a proper size on standard screens. For example, if your image is 128x128 pixels, setting the width and height to 64 will ensure optimal display across all devices. Note: Preloader templates will always show sharp on all screens, whether high or low resolution, because templates use only HTML/CSS3 and don\'t rely on images.', 'the-preloader'); ?></p>
        </div>

        <div class="faq-item">
            <h3><?php esc_html_e('5. What is the difference between "Blog Page" and "Homepage" in Display Locations?', 'the-preloader'); ?></h3>
            <p>
                <?php 
                printf(
                    // translators: %1$s is opening link tag, %2$s is closing link tag
                    esc_html__('In WordPress, when you go to %1$sSettings > Reading%2$s, you have two options for your site\'s front page setup:', 'the-preloader'),
                    '<a href="' . esc_url( admin_url('options-reading.php') ) . '" target="_blank">',
                    '</a>'
                );
                ?>
            </p>
            <ul>
                <li><?php esc_html_e('Show your latest posts.', 'the-preloader'); ?></li>
                <li><?php esc_html_e('Set a static page as Homepage and another page for your Posts page.', 'the-preloader'); ?></li>
            </ul>
            <p><?php esc_html_e('In Display Locations:', 'the-preloader'); ?></p>
            <ul>
                <li><?php esc_html_e('"Blog Page" will show the preloader on the page that displays your latest posts (or the page you set in Settings > Reading > Posts page).', 'the-preloader'); ?></li>
                <li><?php esc_html_e('"Homepage" will show the preloader on the static page you set in Settings > Reading > Homepage.', 'the-preloader'); ?></li>
            </ul>
        </div>

        <div class="faq-item">
            <h3><?php esc_html_e('6. How can I control where to show the preloader?', 'the-preloader'); ?></h3>
            <p>
                <?php
                printf(
                    // translators: %1$s is opening link tag, %2$s is closing link tag
                    esc_html__('With the free version, you can select locations where you want to show the preloader. With the premium version, you get more control with ID settings that let you force show/hide the preloader on specific content regardless of the display location settings. For example, you can show the preloader on specific posts only, or show it everywhere except specific pages. %1$sUpgrade to Premium%2$s.', 'the-preloader'),
                    '<a href="https://wp-plugins.in/PreloaderPremium" target="_blank">',
                    '</a>'
                );
                ?>
            </p>
        </div>

        <div class="faq-item">
            <h3><?php esc_html_e('7. What is the difference between "Entire Site" and "Entire Site (excluding WooCommerce pages)" in Display Locations options?', 'the-preloader'); ?></h3>
            <p>
                <?php 
                printf(
                    // translators: %1$s is opening link tag, %2$s is closing link tag
                    esc_html__('When you select "Entire Site", the preloader will show on any page on your site including WooCommerce pages (shop, products, cart, checkout, account pages, etc). But if you select "Entire Site (excluding WooCommerce pages)", the preloader will show on any page except WooCommerce pages. If WooCommerce is not installed and you select "Entire Site (excluding WooCommerce pages)", there will be no issues - the preloader will simply show on the entire site. With the premium version, you can show the preloader on specific WooCommerce pages only without showing it on other pages. For example, you can show the preloader on WooCommerce product pages only. The premium version offers more flexible display options. %1$sUpgrade to Premium%2$s.', 'the-preloader'),
                    '<a href="https://wp-plugins.in/PreloaderPremium" target="_blank">',
                    '</a>'
                );
                ?>
            </p>
        </div>

        <div class="faq-item">
            <h3><?php esc_html_e('8. Can I add a delay before the preloader fades out and control how long the fade-out takes?', 'the-preloader'); ?></h3>
            <p>
                <?php
                    esc_html_e('With the premium version, you can add a delay before the fade-out starts and control how long the fade-out takes. For example, you can make the preloader wait 4 seconds after your page has finished loading before starting to fade out, and control the fade-out speed.', 'the-preloader');
                ?>
            </p>
        </div>

        <div class="faq-item">
            <h3><?php esc_html_e('9. Can I disable the preloader on mobile devices?', 'the-preloader'); ?></h3>
            <p>
                <?php
                    esc_html_e('With the premium version, you can disable the preloader based on screen width. For example, you can disable it on tablets and phones (screens 960px or smaller) while keeping it active on larger screens.', 'the-preloader');
                ?>
            </p>
        </div>

        <div class="faq-item">
            <h3><?php esc_html_e('10. What are preloader templates?', 'the-preloader'); ?></h3>
            <p><?php 
                printf(
                    // translators: %1$s and %2$s are first link tags, %3$s and %4$s are second link tags
                    esc_html__('Preloader templates are ready-made loading animations built with HTML and CSS3, no more than 2KB in size compared to GIF images, which means better website performance and user experience. In the Templates tab, you\'ll find two free templates with customization options. If you prefer to use your GIF image instead, simply choose "Your GIF Image" from the templates list. There are %1$s30+ professional templates%2$s in the %3$spremium version%4$s.', 'the-preloader'),
                    '<a href="https://wp-plugins.in/PreloaderTemplates" target="_blank">',
                    '</a>',
                    '<a href="https://wp-plugins.in/PreloaderPremium" target="_blank">',
                    '</a>'
                );
            ?></p>
        </div>

        <div class="faq-item">
            <h3><?php esc_html_e('11. My theme doesn\'t show the preloader or I can\'t find/edit header.php. What should I do?', 'the-preloader'); ?></h3>
            
            <p><?php esc_html_e('If the preloader doesn\'t show, or you can\'t find/edit header.php, follow these steps:', 'the-preloader'); ?></p>

            <ol>
                <li><?php esc_html_e('First, keep using automatic integration (default setting) as most modern themes support it.', 'the-preloader'); ?></li>
                <li><?php esc_html_e('If the preloader still doesn\'t show, contact your theme developer to:', 'the-preloader'); ?>
                    <ul>
                        <li><?php esc_html_e('Ask about "wp_body_open" hook support.', 'the-preloader'); ?></li>
                        <li><?php esc_html_e('Ask how to access and edit the header.php file or its equivalent.', 'the-preloader'); ?></li>
                    </ul>
                </li>
                <li><?php esc_html_e('If manual integration is needed, go to the "Integration" tab and follow the steps there.', 'the-preloader'); ?></li>
            </ol>
        </div>

        <div class="faq-item">
            <h3><?php esc_html_e('12. The preloader shows after a delay instead of immediately. What\'s the solution?', 'the-preloader'); ?></h3>
            
            <p><?php esc_html_e('If there is a delay before the preloader shows (instead of showing immediately when the page starts loading), this usually happens when multiple plugins use the "wp_body_open" hook. In this case:', 'the-preloader'); ?></p>
            
            <ol>
                <li><?php esc_html_e('Go to the "Integration" tab.', 'the-preloader'); ?></li>
                <li><?php esc_html_e('Enable "Insert preloader element manually".', 'the-preloader'); ?></li>
                <li><?php esc_html_e('Follow the instructions to add the preloader code.', 'the-preloader'); ?></li>
            </ol>
            
            <p><?php esc_html_e('This will ensure the preloader shows immediately when the page starts loading, and there\'s no need to disable wp_body_open hook. You can clearly use manual integration even if your theme supports "wp_body_open", but remember you\'ll need to add the code to header.php file after each theme update if you choose manual integration.', 'the-preloader'); ?></p>
        </div>

        <div class="faq-item">
            <h3><?php esc_html_e('13. Which is better: Automatic or Manual integration for Preloader element?', 'the-preloader'); ?></h3>
            <p><?php esc_html_e('The best choice depends on your theme:', 'the-preloader'); ?></p>

            <p><strong><?php esc_html_e('Automatic Integration (Default):', 'the-preloader'); ?></strong></p>
            <ul>
                <li><?php esc_html_e('Perfect when your theme supports "wp_body_open" hook.', 'the-preloader'); ?></li>
                <li><?php esc_html_e('No action required after each update to your current theme.', 'the-preloader'); ?></li>
            </ul>

            <p><strong><?php esc_html_e('Manual Integration:', 'the-preloader'); ?></strong></p>
            <ul>
                <li><?php esc_html_e('Required if you encounter issues mentioned in questions #11 or #12, or if the preloader doesn\'t show for any other reason.', 'the-preloader'); ?></li>
                <li><?php esc_html_e('Must manually add the preloader element code to header.php file after the "<body>" tag after each update of your current theme.', 'the-preloader'); ?></li>
            </ul>

            <p><?php esc_html_e('We recommend automatic integration unless you encounter specific issues that require manual integration.', 'the-preloader'); ?></p>
        </div>

        <div class="faq-item">
            <h3><?php esc_html_e('Need help or support?', 'the-preloader'); ?></h3>
            
            <p><?php esc_html_e('For support and help:', 'the-preloader'); ?></p>

            <ul>
                <li>
                    <?php 
                        printf(
                            // translators: %s is a link to a website.
                            esc_html__('Contact form on our %s', 'the-preloader'),
                            '<a href="https://wp-plugins.in/Contact" target="_blank">website</a>.'
                        );
                    ?>
                </li>
                <li>
                    <?php 
                        printf(
                            // translators: %s is a link to a website.
                            esc_html__('%1$sPlugin Reference%2$s.', 'the-preloader'),
                            '<a href="https://wp-plugins.in/PreloaderPlugin" target="_blank">',
                            '</a>'
                        );
                    ?>
                </li>
            </ul>
        </div>

    <?php
    }

    private function upgrade_tab_content() {
        ?>
            <h2><?php esc_html_e('Upgrade to Premium', 'the-preloader'); ?></h2>
            
            <p class="tp-tagline" style="margin-bottom: 15px; color: #555; font-size: 16px;">
                <?php 
                printf(
                    // translators: %1$s and %2$s are first link tags, %3$s and %4$s are second link tags, %5$s and %6$s are third link tags
                    esc_html__('Take your preloader to the next level with our %1$sPremium Version%2$s. Get advanced display controls, add a delay after loading before fade out, control fade speed, hide preloader on mobile devices, Cookie-based Display feature, and choosing from %3$s30+ professional preloader templates%4$s — including the awesome %5$sTyping Effect Template%6$s to show a message or promote a coupon while page is loading. Show/hide the preloader on specific content by their IDs and on specific WooCommerce pages. There\'s also a single option to show the preloader on any available public custom post type.', 'the-preloader'),
                    '<a href="https://wp-plugins.in/PreloaderPremium" target="_blank">',
                    '</a>',
                    '<a href="https://wp-plugins.in/PreloaderTemplates" target="_blank">',
                    '</a>',
                    '<a href="https://wp-plugins.in/TypingEffectTemplate" target="_blank">',
                    '</a>'
                );
                ?>
            </p>

            <p class="tp-tagline" style="margin-bottom: 15px; color: #555; font-size: 16px;">
                <?php
                printf(
                    // translators: %1$s is opening link tag, %2$s is closing link tag
                    esc_html__('No subscription, pay once, get more! %1$sUpgrade to Premium%2$s.', 'the-preloader'),
                    '<a href="https://wp-plugins.in/PreloaderPremium" target="_blank">',
                    '</a>'
                );
                ?>
            </p>

            <p class="tp-tagline" style="margin-bottom: 15px; color: #555; font-size: 14px;">
                <?php 
                esc_html_e('We offer a money-back guarantee on your purchase. No questions asked!', 'the-preloader');
                ?>
            </p>

            <p class="tp-tagline" style="color: #555; font-size: 14px;">
                <?php 
                printf(
                    // translators: %1$s is opening link tag, %2$s is closing link tag
                    esc_html__('See %1$sscreenshots%2$s of premium version features.', 'the-preloader'),
                    '<a href="https://wp-plugins.in/PreloaderPremiumScreenshots" target="_blank">',
                    '</a>'
                );
                ?>
            </p>
        <?php
    }

    private function render_tab($tab) {
        switch ($tab) {
            case 'general':
                $this->general_tab_options();
                break;

            case 'display':
                $this->display_tab_options();
                break;

            case 'cookie':
                $this->cookie_tab_options();
                break;
                
            case 'template':
                $this->template_tab_options();
                break;

            case 'integration':
                $this->integration_tab_options();
                break;

            case 'faq':
                $this->faq_tab_content();
                break;

            case 'upgrade':
                $this->upgrade_tab_content();
                break;
        }
    }
}