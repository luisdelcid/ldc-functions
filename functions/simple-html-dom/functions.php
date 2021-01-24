<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_support_simple_html_dom')){
    function ldc_support_simple_html_dom(){
        if(!class_exists('simple_html_dom')){
            require_once(plugin_dir_path(__FILE__) . 'simple-html-dom-1.9.1/simple_html_dom.php');
		}
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
