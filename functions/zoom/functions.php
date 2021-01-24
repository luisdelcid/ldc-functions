<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_generate_zoom_jwt')){
	function ldc_generate_zoom_jwt($api_key = '', $api_secret = ''){
		if(!$api_key){
			$api_key = (defined('LDC_ZOOM_API_KEY') ? LDC_ZOOM_API_KEY : '');
		}
		if(!$api_secret){
			$api_secret = (defined('LDC_ZOOM_API_SECRET') ? LDC_ZOOM_API_SECRET : '');
		}
        if(!$api_key or !$api_secret){
            return '';
        }
        $payload = [
            'iss' => $api_key,
            'exp' => time() + DAY_IN_SECONDS, // GMT time
        ];
        ldc_support_php_jwt();
        return \Firebase\JWT\JWT::encode($payload, $api_secret);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
