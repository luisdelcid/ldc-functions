<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_serialize_closure')){
    function ldc_serialize_closure($data = ''){
        if($data instanceof Closure){
            if(!class_exists('Opis\Closure\SerializableClosure')){
                require_once(plugin_dir_path(__FILE__) . 'closure-3.6.1/autoload.php');
            }
			$wrapper = new Opis\Closure\SerializableClosure($data);
			return serialize($wrapper);
        }
		return '';
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_md5_closure')){
    function ldc_md5_closure($data = '', $spl_object_hash = false){
        if($data instanceof Closure){
			$serialized = ldc_serialize_closure($data);
			if(!$spl_object_hash){
				$serialized = str_replace(spl_object_hash($data), 'spl_object_hash', $serialized);
			}
			return md5($serialized);
        }
		return '';
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
