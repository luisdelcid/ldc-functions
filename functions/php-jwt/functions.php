<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_jwt_decode')){
    function ldc_jwt_decode($jwt = '', $key = '', $allowed_algs = []){
        $use = ldc_use_php_jwt();
        if(is_wp_error($use)){
            return $use;
        }
        return Firebase\JWT\JWT::decode($jwt, $key, $allowed_algs);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_jwt_encode')){
    function ldc_jwt_encode($payload = [], $key = '', $alg = 'HS256', $keyId = null, $head = null){
        $use = ldc_use_php_jwt();
        if(is_wp_error($use)){
            return $use;
        }
        return Firebase\JWT\JWT::encode($payload, $key, $alg, $keyId, $head);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_php_jwt')){
    function ldc_use_php_jwt(){
        if(class_exists('Firebase\JWT\JWT')){
            return true;
        }
        $dir = ldc_upload_basedir() . '/github/firebase/php-jwt/5.2.1';
        $expected = $dir . '/php-jwt-5.2.1';
        if(is_dir($expected)){
            require_once($expected . '/src/BeforeValidException.php');
            require_once($expected . '/src/ExpiredException.php');
            require_once($expected . '/src/JWK.php');
            require_once($expected . '/src/JWT.php');
            require_once($expected . '/src/SignatureInvalidException.php');
            return true;
        }
        $url = 'https://github.com/firebase/php-jwt/archive/v5.2.1.zip';
        $result = ldc_download_and_unzip($dir, $url);
        if(is_wp_error($result)){
            return $result;
        }
        require_once($expected . '/src/BeforeValidException.php');
        require_once($expected . '/src/ExpiredException.php');
        require_once($expected . '/src/JWK.php');
        require_once($expected . '/src/JWT.php');
        require_once($expected . '/src/SignatureInvalidException.php');
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
