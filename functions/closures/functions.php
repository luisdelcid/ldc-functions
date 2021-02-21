<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_md5_closure')){
    function ldc_md5_closure($data = '', $spl_object_hash = false){
        if($data instanceof Closure){
			$serialized = ldc_serialize_closure($data);
            if(is_wp_error($serialized)){
                return $serialized;
            }
			if(!$spl_object_hash){
				$serialized = str_replace(spl_object_hash($data), 'spl_object_hash', $serialized);
			}
			return md5($serialized);
        }
		return '';
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_serialize_closure')){
    function ldc_serialize_closure($data = ''){
        $use = ldc_use_closure();
        if(is_wp_error($use)){
            return $use;
        }
        if($data instanceof Closure){
            $wrapper = new Opis\Closure\SerializableClosure($data);
            return serialize($wrapper);
        }
		return '';
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_closure')){
    function ldc_use_closure(){
        if(class_exists('Opis\Closure\SerializableClosure')){
            return true;
        }
        $dir = ldc_upload_basedir() . '/github/opis/closure/3.6.1';
        $expected = $dir . '/closure-3.6.1';
        if(is_dir($expected)){
            require_once($expected . '/autoload.php');
            return true;
        }
        $url = 'https://github.com/opis/closure/archive/3.6.1.zip';
        $result = ldc_download_and_unzip($dir, $url);
        if(is_wp_error($result)){
            return $result;
        }
        require_once($expected . '/autoload.php');
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
