<?php
/*
 * Plugin Name: LDC Functions
 * Plugin URI: https://luisdelcid.com
 * Description: A personal collection of useful functions for WordPress plugins and themes.
 * Version: 0.1.15
 * Requires at least: 5.6
 * Requires PHP: 5.6
 * Author: Luis del Cid
 * Author URI: https://github.com/luisdelcid
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ldc-functions
 * Network: true
 * Update URI: https://github.com/luisdelcid/ldc-functions
 */

/*
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

// Make sure we don't expose any info if called directly.
if(!defined('ABSPATH')){
    echo "Hi there! I'm just a plugin, not much I can do when called directly.";
    exit;
}

// Wait for the `plugins_loaded` action hook.
add_action('plugins_loaded', function(){

    // Include PHP classes and functions.
    $plugin = plugin_dir_path(__FILE__);
    require_once $plugin . 'singleton.php';
    require_once $plugin . 'functions.php';

    // Check for updates.
    $checker = ldc_plugin_update_checker(__FILE__);
    if(is_wp_error($checker)){
        ldc_add_admin_notice($checker->get_error_message(), 'error');
    }

    // Enqueue JavaScript classes and functions.
    ldc_enqueue_functions();

    // Wait for the `after_setup_theme` action hook.
    add_action('after_setup_theme', function(){

        // Load the functions for the active theme, for both parent and child theme if applicable.
        foreach(wp_get_active_and_valid_themes() as $theme){
        	if(file_exists($theme . '/ldc-functions.php')){
        		include_once $theme . '/ldc-functions.php';
        	}
        }

        // Fires once the theme has loaded.
        do_action('after_ldc_functions');

    }, 0); // Highest priority.

    // Fires once the plugin has loaded.
    do_action('ldc_functions');

}, 0); // Highest priority.
