<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_do')){
    function ldc_do($tag = '', ...$args){
        return do_action($tag, ...$args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_off')){
    function ldc_off($tag = '', $function_to_add = '', $priority = 10){
        return remove_filter($tag, $function_to_add, $priority);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_on')){
    function ldc_on($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1){
        add_filter($tag, $function_to_add, $priority, $accepted_args);
    	return _wp_filter_build_unique_id($tag, $function_to_add, $priority);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_one')){
    function ldc_one($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1){
		static $hooks = [];
		$idx = _wp_filter_build_unique_id($tag, $function_to_add, $priority);
		if($function_to_add instanceof Closure){
			$md5 = ldc_md5_closure($function_to_add);
            if(is_wp_error($md5)){
                $md5 = md5($idx);
            }
		} else {
			$md5 = md5($idx);
		}
		if(!isset($hooks[$tag])){
			$hooks[$tag] = [];
		}
		if(!in_array($md5, $hooks[$tag])){
			$hooks[$tag][] = $md5;
			return ldc_on($tag, $function_to_add, $priority, $accepted_args);
		} else {
			return $idx;
		}
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
