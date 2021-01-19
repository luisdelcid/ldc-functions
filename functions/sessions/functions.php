<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// You shoud use in conjunction with the WordPress Native PHP Sessions plugin by Pantheon.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_support_sessions')){
	function ldc_support_sessions($defaults = true){
		ldc_one('init', function() use($defaults){
			if(!session_id()){
        		session_start();
        	}
			if($defaults){
				if(empty($_SESSION['ldc_current_user_id'])){
	        		$_SESSION['ldc_current_user_id'] = get_current_user_id();
	        	}
	            if(empty($_SESSION['ldc_utm'])){
	        		$_SESSION['ldc_utm'] = [];
	                foreach($_GET as $key => $value){
	                    if(substr($key, 0, 4) == 'utm_'){
	                        $_SESSION['ldc_utm'][$key] = $value;
	                    }
	                }
	        	}
			}
		}, 9);
		ldc_one('wp_login', function($user_login, $user) use($defaults){
			if($defaults){
				$_SESSION['ldc_current_user_id'] = $user->ID;
			}
		}, 10, 2);
		ldc_one('wp_logout', function(){
			if(session_id()){
        		session_destroy();
        	}
		});
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
