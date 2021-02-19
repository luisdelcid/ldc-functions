<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_tgmpa_register')){
    function ldc_tgmpa_register($plugin = []){
        if(!isset($GLOBALS['ldc_tgmpa_plugins'])){
            $GLOBALS['ldc_tgmpa_plugins'] = [];
        }
        $md5 = ldc_md5($plugin);
        if(!array_key_exists($md5, $GLOBALS['ldc_tgmpa_plugins'])){
            $GLOBALS['ldc_tgmpa_plugins'][$md5] = $plugin;
        }
        if(!class_exists('TGM_Plugin_Activation')){
            require_once(plugin_dir_path(__FILE__) . 'tgm-plugin-activation-2.6.1/class-tgm-plugin-activation.php');
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
