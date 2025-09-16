<?php if ( !defined('WP_UNINSTALL_PLUGIN') ) {
    exit;
}

delete_option('the_preloader_settings');
delete_option('the_preloader_first_use');