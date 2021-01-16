<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_md5_closure')){
    function ldc_md5_closure($data = '', $spl_object_hash = false){
        if($data instanceof \Closure){
			ldc_use_closure_serialization();
			$wrapper = new \Opis\Closure\SerializableClosure($data);
			$serialized = serialize($wrapper);
			if(!$spl_object_hash){
				$serialized = str_replace(spl_object_hash($data), 'spl_object_hash', $serialized);
			}
			return md5($serialized);
        }
		return '';
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_closure_serialization')){
    function ldc_use_closure_serialization(){
        if(!class_exists('\Opis\Closure\SerializableClosure')){
            require_once(plugin_dir_path(__FILE__) . 'closure-3.6.1/autoload.php');
        }
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
