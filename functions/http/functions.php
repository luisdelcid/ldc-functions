<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_prepare')){
    function ldc_prepare(...$args){
        global $wpdb;
        if(!$args){
            return '';
        }
        if(strpos($args[0], '%') !== false and count($args) > 1){
            return str_replace("'", '', $wpdb->remove_placeholder_escape($wpdb->prepare(...$args)));
        } else {
            return $args[0];
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_request')){
    function ldc_request($url = '', $args = []){
        if(class_exists('\LDC_Request')){
            require_once(plugin_dir_path(__FILE__) . 'class-ldc-request.php');
        }
        return new LDC_Request($url, $args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_response')){
    function ldc_response($response = null){
        if(class_exists('\LDC_Response')){
            require_once(plugin_dir_path(__FILE__) . 'class-ldc-response.php');
        }
        return new LDC_Response($response);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_response_error')){
    function ldc_response_error($message = '', $code = 500, $data = ''){
        if(!$message){
            $message = get_status_header_desc($code);
        }
        $success = false;
        return ldc_response(compact('code', 'data', 'message', 'success'));
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_response_success')){
    function ldc_response_success($data = '', $code = 200, $message = ''){
        if(!$message){
            $message = get_status_header_desc($code);
        }
        $success = true;
        return ldc_response(compact('code', 'data', 'message', 'success'));
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_sanitize_timeout')){
    function ldc_sanitize_timeout($timeout = 0){
        $timeout = absint($timeout);
        $max_execution_time = absint(ini_get('max_execution_time'));
        if($max_execution_time){
            if(!$timeout or $timeout > $max_execution_time){
                $timeout = $max_execution_time; // Prevents timeout error
            }
        }
        if(ldc_seems_cloudflare()){
            if(!$timeout or $timeout > 90){
                $timeout = 90; // Prevents error 524: https://support.cloudflare.com/hc/en-us/articles/115003011431#524error
            }
        }
        return $timeout;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_seems_json')){
    function ldc_seems_json($str = ''){
        return (is_string($str) and (preg_match('/^\{\".*\"\:.*\}$/', $str) or preg_match('/^\[.*\]$/', $str)));
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_seems_response')){
    function ldc_seems_response($response = []){
        return ldc_array_keys_exist(['code', 'data', 'message', 'success'], $response);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_seems_successful')){
    function ldc_seems_successful($code = 0){
        if(!is_numeric($code)){
            if($code instanceof \LDC_Response){
                $code = $code->code;
            } else {
                return false;
            }
        } else {
            $code = absint($code);
        }
        return ($code >= 200 and $code < 300);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_seems_wp_http_requests_response')){
    function ldc_seems_wp_http_requests_response($response = []){
        return (ldc_array_keys_exist(['headers', 'body', 'response', 'cookies', 'filename', 'http_response'], $response) and ($response['http_response'] instanceof \WP_HTTP_Requests_Response));
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_support_authorization_header')){
    function ldc_support_authorization_header(){
        ldc_one('mod_rewrite_rules', function($rules){
            return str_replace("RewriteEngine On\n", "RewriteEngine On\nRewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n", $rules);
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~