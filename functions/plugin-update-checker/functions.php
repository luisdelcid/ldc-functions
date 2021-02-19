<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_build_update_checker')){
    function ldc_build_update_checker(...$args){
        if(!class_exists('Puc_v4_Factory')){
			require_once(plugin_dir_path(__FILE__) . 'plugin-update-checker-4.10/plugin-update-checker.php');
		}
        return Puc_v4_Factory::buildUpdateChecker(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
