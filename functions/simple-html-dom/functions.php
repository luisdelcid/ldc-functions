<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_simple_html_dom')){
    function ldc_use_simple_html_dom(){
        if(class_exists('simple_html_dom')){
            return true;
        }
        $dir = ldc_upload_basedir() . '/github/simple-html-dom/1.9.1';
        $file = $dir . '/simplehtmldom-1.9.1/simple_html_dom.php';
        if(file_exists($file)){
            require_once($file);
            return true;
        }
        $url = 'https://github.com/simplehtmldom/simplehtmldom/archive/1.9.1.zip';
        $result = ldc_download_and_unzip($dir, $url);
        if(is_wp_error($result)){
            return $result;
        }
        require_once($file);
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_file_get_html')){
    function ldc_file_get_html(...$args){
        /*if(!class_exists('simple_html_dom')){
            require_once(plugin_dir_path(__FILE__) . 'simple-html-dom-1.9.1/simple_html_dom.php');
		}*/
        ldc_use_simple_html_dom();
        return file_get_html(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_str_get_html')){
    function ldc_str_get_html(...$args){
        /*if(!class_exists('simple_html_dom')){
            require_once(plugin_dir_path(__FILE__) . 'simple-html-dom-1.9.1/simple_html_dom.php');
		}*/
        ldc_use_simple_html_dom();
        return str_get_html(...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
