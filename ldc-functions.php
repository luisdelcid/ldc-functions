<?php
/*
Author: Luis del Cid
Author URI: https://github.com/luisdelcid
Description: A collection of useful functions for your WordPress theme's functions.php.
Domain Path:
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Network:
Plugin Name: LDC Functions
Plugin URI: https://github.com/luisdelcid/ldc-functions
Text Domain: ldc-functions
Version: 0.1.18.2
*/

if(defined('ABSPATH')){
    foreach(glob(plugin_dir_path(__FILE__) . 'functions/*', GLOB_ONLYDIR) as $ldc_dir){
        $ldc_file = $ldc_dir . '/functions.php';
        if(file_exists($ldc_file)){
            require_once($ldc_file);
        }
    }
    unset($ldc_dir);
    unset($ldc_file);
    ldc_build_update_checker('https://github.com/luisdelcid/ldc-functions', __FILE__, 'ldc-functions');
    ldc_on('after_setup_theme', function(){
        $file = get_stylesheet_directory() . '/ldc-functions.php';
        if(file_exists($file)){
            require_once($file);
        }
    });
    ldc_on('plugins_loaded', function(){
        do_action('ldc_functions');
    });
}
