<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_support_floating_labels')){
    function ldc_support_floating_labels($bootstrap = 0){
        ldc_one('wp_enqueue_scripts', function() use($bootstrap){
            if(!$bootstrap){
                $bootstrap = ldc_bb_get_bootstrap();
            }
            switch($bootstrap){
                case 4:
                    $ver = filemtime(plugin_dir_path(__FILE__) . 'bootstrap-4.css');
                    wp_enqueue_style('ldc-floating-labels-bootstrap-4', plugin_dir_url(__FILE__) . 'bootstrap-4.css', [], $ver);
                    $ver = filemtime(plugin_dir_path(__FILE__) . 'bootstrap-4.js');
                    wp_enqueue_script('ldc-floating-labels-bootstrap-4', plugin_dir_url(__FILE__) . 'bootstrap-4.js', ['jquery'], $ver, true);
                    break;
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
