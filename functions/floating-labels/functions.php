<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_floating_labels')){
    function ldc_use_floating_labels($bootstrap = 4){
        ldc_one('wp_enqueue_scripts', function() use($bootstrap){
            switch($bootstrap){
                case 4:
                    $ver = filemtime(plugin_dir_path(__FILE__) . 'floating-labels.css');
                    wp_enqueue_style('ldc-floating-labels', plugin_dir_url(__FILE__) . 'floating-labels.css', [], $ver);
                    $ver = filemtime(plugin_dir_path(__FILE__) . 'floating-labels.js');
                    wp_enqueue_script('ldc-floating-labels', plugin_dir_url(__FILE__) . 'floating-labels.js', ['jquery'], $ver, true);
                    break;
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
