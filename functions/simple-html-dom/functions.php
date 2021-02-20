<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_file_get_html')){
    function ldc_file_get_html(...$args){
        $use = ldc_use_simple_html_dom();
        if(is_wp_error($use)){
            return $use;
        }
        return file_get_html(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_str_get_html')){
    function ldc_str_get_html(...$args){
        $use = ldc_use_simple_html_dom();
        if(is_wp_error($use)){
            return $use;
        }
        return str_get_html(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_simple_html_dom')){
    function ldc_use_simple_html_dom(){
        if(class_exists('simple_html_dom')){
            return true;
        }
        $dir = ldc_upload_basedir() . '/github/simplehtmldom/simplehtmldom/1.9.1';
        $expected = $dir . '/simplehtmldom-1.9.1';
        if(is_dir($expected)){
            require_once($expected . '/simple_html_dom.php');
            return true;
        }
        $url = 'https://github.com/simplehtmldom/simplehtmldom/archive/1.9.1.zip';
        $result = ldc_download_and_unzip($dir, $url);
        if(is_wp_error($result)){
            return $result;
        }
        require_once($expected . '/simple_html_dom.php');
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
