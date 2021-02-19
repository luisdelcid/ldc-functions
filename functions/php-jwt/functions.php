<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_jwt_decode')){
    function ldc_jwt_decode($jwt = '', $key = '', $allowed_algs = []){
        $dir = plugin_dir_path(__FILE__) . 'php-jwt-5.2.1/';
        if(!class_exists('Firebase\JWT\BeforeValidException')){
			require_once($dir . 'src/BeforeValidException.php');
		}
		if(!class_exists('Firebase\JWT\ExpiredException')){
			require_once($dir . 'src/ExpiredException.php');
		}
		if(!class_exists('Firebase\JWT\JWK')){
			require_once($dir . 'src/JWK.php');
		}
		if(!class_exists('Firebase\JWT\JWT')){
			require_once($dir . 'src/JWT.php');
		}
		if(!class_exists('Firebase\JWT\SignatureInvalidException')){
			require_once($dir . 'src/SignatureInvalidException.php');
		}
        return Firebase\JWT\JWT::decode($jwt, $key, $allowed_algs);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_jwt_encode')){
    function ldc_jwt_encode($payload = [], $key = '', $alg = 'HS256', $keyId = null, $head = null){
        $dir = plugin_dir_path(__FILE__) . 'php-jwt-5.2.1/';
        if(!class_exists('Firebase\JWT\BeforeValidException')){
			require_once($dir . 'src/BeforeValidException.php');
		}
		if(!class_exists('Firebase\JWT\ExpiredException')){
			require_once($dir . 'src/ExpiredException.php');
		}
		if(!class_exists('Firebase\JWT\JWK')){
			require_once($dir . 'src/JWK.php');
		}
		if(!class_exists('Firebase\JWT\JWT')){
			require_once($dir . 'src/JWT.php');
		}
		if(!class_exists('Firebase\JWT\SignatureInvalidException')){
			require_once($dir . 'src/SignatureInvalidException.php');
		}
        return Firebase\JWT\JWT::encode($payload, $key, $alg, $keyId, $head);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
