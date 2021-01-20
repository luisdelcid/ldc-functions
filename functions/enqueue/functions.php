<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_enqueue_bootstrap')){
    function ldc_enqueue_bootstrap($ver = 4, $bundle = true){
        ldc_one('wp_enqueue_scripts', function() use($ver, $bundle){
            switch($ver){
                case 4:
                    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css', [], '4.5.3');
                    if($bundle){
                        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js', ['jquery'], '4.5.3', true);
                    } else {
                        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js', ['jquery'], '4.5.3', true);
                    }
                    break;
                case 5:
                    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css', [], '5.0.0-beta1');
                    if($bundle){
                        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js', [], '5.0.0-beta1', true);
                    } else {
                        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.min.js', [], '5.0.0-beta1', true);
                    }
                    break;
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_enqueue_fontawesome')){
    function ldc_enqueue_fontawesome($ver = 5, $pro = false){
        ldc_one('wp_enqueue_scripts', function() use($ver, $pro){
            switch($ver){
                case 3:
                    wp_enqueue_style('fontawesome', 'https://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css', [], '3.2.1');
                    break;
                case 4:
                    wp_enqueue_style('fontawesome', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', [], '4.7.0');
                    break;
                case 5:
                    if($pro){
                        wp_enqueue_style('fontawesome', 'https://pro.fontawesome.com/releases/v5.15.1/css/all.css', [], '5.15.1');
                    } else {
                        wp_enqueue_style('fontawesome', 'https://use.fontawesome.com/releases/v5.15.1/css/all.css', [], '5.15.1');
                    }
                    break;
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_enqueue_fontawesome_kit')){
    function ldc_enqueue_fontawesome_kit($kit = ''){
        ldc_one('wp_enqueue_scripts', function() use($kit){
            if($kit){
                $url = wp_http_validate_url($kit);
                if($url){
                    wp_enqueue_script('fontawesome', $url);
                } else {
                    wp_enqueue_script('fontawesome', 'https://kit.fontawesome.com/' . rtrim($kit, '.js') . '.js');
                }
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_enqueue_functions')){
    function ldc_enqueue_functions($admin = false){
        $file = plugin_dir_path(__FILE__) . 'functions.js';
        $ver = filemtime($file);
        if($admin){
            ldc_one('admin_enqueue_scripts', function() use($ver){
                wp_enqueue_script('ldc-functions', plugin_dir_url(__FILE__) . 'functions.js', ['jquery'], $ver, true);
            });
        } else {
            ldc_one('wp_enqueue_scripts', function() use($ver){
                wp_enqueue_script('ldc-functions', plugin_dir_url(__FILE__) . 'functions.js', ['jquery'], $ver, true);
            });
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_enqueue_jquery')){
    function ldc_enqueue_jquery(){
        ldc_one('wp_enqueue_scripts', function(){
            if(!wp_script_is('jquery')){
				wp_enqueue_script('jquery');
			}
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_enqueue_popper')){
    function ldc_enqueue_popper($ver = 1){
        ldc_one('wp_enqueue_scripts', function() use($ver){
            switch($ver){
                case 1:
                    wp_enqueue_script('popper', 'https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js', [], '1.16.1', true);
                    break;
                case 2:
                    wp_enqueue_script('popper', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js', [], '2.5.4', true);
                    break;
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_enqueue_stylesheet')){
    function ldc_enqueue_stylesheet(){
        ldc_one('wp_enqueue_scripts', function(){
            $file = get_stylesheet_directory() . '/style.css';
            $ver = filemtime($file);
            wp_enqueue_style(get_stylesheet(), get_stylesheet_uri(), [], $ver);
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
