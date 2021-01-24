<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_enqueue_select2_full')){
    function ldc_mb_enqueue_select2_full(){
        ldc_one('rwmb_enqueue_scripts', function(){
            if(wp_script_is('rwmb-select2', 'enqueued')){
				wp_dequeue_script('rwmb-select2');
			}
			if(wp_script_is('rwmb-select2', 'registered')){
				wp_deregister_script('rwmb-select2');
			}
			wp_register_script('rwmb-select2', plugin_dir_url(__FILE__) . 'select2.full.min.js', ['jquery'], '4.0.10', true);
			wp_enqueue_script('rwmb-select2');
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_support_bootstrap')){
    function ldc_mb_support_bootstrap($bootstrap = 0){
        ldc_one('rwmb_enqueue_scripts', function() use($bootstrap){
            if(!$bootstrap){
                $bootstrap = ldc_bb_get_bootstrap();
            }
            switch($bootstrap){
                case 4:
                    wp_register_style('select2-bootstrap', plugin_dir_url(__FILE__) . 'select2-bootstrap.min.css', ['rwmb-select2'], '1.0.0');
                    wp_enqueue_style('select2-bootstrap');
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
