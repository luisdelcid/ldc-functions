<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/*if(!function_exists('ldc_require_simple_html_dom')){
    function ldc_require_simple_html_dom(){
        if(!class_exists('simple_html_dom')){
            $dir = ldc_upload_basedir() . '/github/simplehtmldom/simplehtmldom-1.9.1';
            if(is_dir($dir)){
                require_once($dir . '/simple_html_dom.php');
                return true;
            } else {
                $url = 'https://github.com/simplehtmldom/simplehtmldom/archive/1.9.1.zip';
                return ldc_download_and_unzip($url, $dir);
            }
		}
        return ldc_error();
    }
}*/

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_file_get_html')){
    function ldc_file_get_html(...$args){
        if(!class_exists('simple_html_dom')){
            require_once(plugin_dir_path(__FILE__) . 'simple-html-dom-1.9.1/simple_html_dom.php');
		}
        return file_get_html(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_str_get_html')){
    function ldc_str_get_html(...$args){
        if(!class_exists('simple_html_dom')){
            require_once(plugin_dir_path(__FILE__) . 'simple-html-dom-1.9.1/simple_html_dom.php');
		}
        return str_get_html(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
