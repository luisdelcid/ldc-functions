<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_build_update_checker')){
    function ldc_build_update_checker(...$args){
        $use = ldc_use_plugin_update_checker();
        if(is_wp_error($use)){
            return $use;
        }
        return Puc_v4_Factory::buildUpdateChecker(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_plugin_update_checker')){
    function ldc_use_plugin_update_checker(){
        if(class_exists('Puc_v4_Factory')){
            return true;
        }
        $dir = ldc_upload_basedir() . '/github/YahnisElsts/plugin-update-checker/4.10';
        $expected = $dir . '/plugin-update-checker-4.10';
        if(is_dir($expected)){
            require_once($expected . '/plugin-update-checker.php');
            return true;
        }
        $url = 'https://github.com/YahnisElsts/plugin-update-checker/archive/v4.10.zip';
        $result = ldc_download_and_unzip($dir, $url);
        if(is_wp_error($result)){
            return $result;
        }
        require_once($expected . '/plugin-update-checker.php');
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
