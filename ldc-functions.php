<?php
/*
Author: Luis del Cid
Author URI: https://github.com/luisdelcid
Description: A collection of useful functions for your WordPress plugins or theme's functions.php.
Domain Path:
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Network: true
Plugin Name: LDC Functions
Plugin URI: https://github.com/luisdelcid/ldc-functions
Text Domain: ldc-functions
Version: 0.2.19.6
*/

if(defined('ABSPATH')){
    require_once(plugin_dir_path(__FILE__) . 'functions/functions.php');
    register_activation_hook(__FILE__, 'ldc_activation');
    register_deactivation_hook(__FILE__, 'ldc_deactivation');
    $ldc_file = trailingslashit(WPMU_PLUGIN_DIR) . 'ldc-preloader.php';
    if(file_exists($ldc_file)){
        ldc_build_update_checker('https://github.com/luisdelcid/ldc-functions', __FILE__, 'ldc-functions');
        ldc_enqueue_functions('admin');
        ldc_enqueue_functions('front-end');
        ldc_on('after_setup_theme', function(){
            $file = get_stylesheet_directory() . '/ldc-functions.php';
            if(file_exists($file)){
                require_once($file);
            }
        });
        ldc_on('plugins_loaded', function(){
            ldc_do('ldc_functions_loaded');
        });
    }
    unset($ldc_file);
}
