<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_tgmpa_register')){
    function ldc_tgmpa_register($plugin = []){
        $use = ldc_use_tgm_plugin_activation();
        if(is_wp_error($use)){
            return $use;
        }
        if(!isset($GLOBALS['ldc_tgmpa_plugins'])){
            $GLOBALS['ldc_tgmpa_plugins'] = [];
        }
        $md5 = ldc_md5($plugin);
        if(!array_key_exists($md5, $GLOBALS['ldc_tgmpa_plugins'])){
            $GLOBALS['ldc_tgmpa_plugins'][$md5] = $plugin;
        }
        ldc_one('tgmpa_register', function(){
            if($GLOBALS['ldc_tgmpa_plugins']){
                tgmpa($GLOBALS['ldc_tgmpa_plugins'], [
                    'id' => 'ldc-functions',
                ]);
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_tgm_plugin_activation')){
    function ldc_use_tgm_plugin_activation(){
        if(class_exists('TGM_Plugin_Activation')){
            return true;
        }
        $dir = ldc_upload_basedir() . '/github/TGMPA/TGM-Plugin-Activation/2.6.1';
        $expected = $dir . '/TGM-Plugin-Activation-2.6.1';
        if(is_dir($expected)){
            require_once($expected . '/class-tgm-plugin-activation.php');
            return true;
        }
        $url = 'https://github.com/TGMPA/TGM-Plugin-Activation/archive/2.6.1.zip';
        $result = ldc_download_and_unzip($dir, $url);
        if(is_wp_error($result)){
            return $result;
        }
        require_once($expected . '/class-tgm-plugin-activation.php');
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
