<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Hardcoded
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_dir')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_dir($subdir = ''){
        $dir = 'ldc-functions'; // Hardcoded.
        $subdir = ltrim($subdir, '/');
	    $subdir = untrailingslashit($subdir);
	    if($subdir){
	        $dir .= '/' . $subdir;
	    }
		return ldc_mkdir($dir);
	}
}

if(!function_exists('ldc_enqueue_asset')){
    /**
     * This function MUST be called inside the 'admin_enqueue_scripts', 'login_enqueue_scripts' or 'wp_enqueue_scripts' action hooks.
     *
     * @return string|WP_Error
     */
    function ldc_enqueue_asset($handle = '', $src = '', $deps = [], $ver = false, $args_media = true){
        global $wp_version;
        if(!doing_action('admin_enqueue_scripts') and !doing_action('login_enqueue_scripts') and !doing_action('wp_enqueue_scripts')){ // Too early or too late.
            $error_msg = translate('Function %1$s was called <strong>incorrectly</strong>. %2$s %3$s');
            $error_msg = sprintf($error_msg, __FUNCTION__, '', '');
            $error_msg = trim($error_msg);
            return ldc_error($error_msg);
        }
        if(!$handle){
            $error_msg = translate('The "%s" argument must be a non-empty string.');
            $error_msg = sprintf($error_msg, 'handle');
            return ldc_error($error_msg);
        }
        $filename = ldc_basename($src);
        $mimes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
        ];
        $filetype = wp_check_filetype($filename, $mimes);
        if(!$filetype['ext']){
            $error_msg = translate('Sorry, you are not allowed to upload this file type.');
            return ldc_error($error_msg);
        }
        if(!is_array($deps)){
            $deps = (array) $deps;
        }
        if('text/css' === $filetype['type']){
            if(is_string($args_media)){
                $media = $args_media; // $media parameter.
            } else {
                $media = 'all'; // All.
            }
            wp_enqueue_style($handle, $src, $deps, $ver, $media);
            return $handle;
        }
		$maybe_autoload = false;
        $path = ldc_url_to_dir($src);
        if($path){ // This WordPress installation.
            $plugin_dir_path = plugin_dir_path(dirname(__FILE__)); // Hardcoded.
            if(!ldc_str_starts_with($path, $plugin_dir_path)){ // Not this plugin.
                $maybe_autoload = true;
                $deps[] = 'ldc-functions'; // Hardcoded.
            }
        }
        $l10n = [];
        if(ldc_is_associative_array($args_media)){
            if(version_compare($wp_version, '6.3', '>=') and (isset($args_media['in_footer']) or isset($args_media['strategy']))){
                $args = $args_media; // As of WordPress 6.3, the new $args parameter – that replaces/overloads the prior $in_footer parameter – can be used to specify a script loading strategy.
            } else {
                $l10n = $args_media;
                $args = true; // In footer.
            }
        } else {
            $args = (bool) $args_media; // $in_footer parameter.
        }
        wp_enqueue_script($handle, $src, $deps, $ver, $args);
        $object_name = ldc_canonicalize($handle);
        if($maybe_autoload){
            $data = "ldc_get_instance('$object_name');"; // Hardcoded.
            wp_add_inline_script($handle, $data);
        }
        if($l10n){
            wp_localize_script($handle, $object_name . '_l10n', $l10n);
        }
        return $handle;
    }
}

if(!function_exists('ldc_enqueue_functions')){
	/**
	 * @return void
	 */
	function ldc_enqueue_functions(){
		ldc_omni_enqueue('stackframe', 'https://cdn.jsdelivr.net/npm/stackframe@1.3.4/stackframe.min.js', [], '1.3.4');
        ldc_omni_enqueue('error-stack-parser', 'https://cdn.jsdelivr.net/npm/error-stack-parser@2.1.4/error-stack-parser.min.js', ['stackframe'], '2.1.4');
		$handle = 'ldc-singleton'; // Hardcoded.
        $file = plugin_dir_path(__FILE__) . 'class-singleton.js'; // Hardcoded.
        ldc_local_omni_enqueue($handle, $file);
        $handle = 'ldc-functions'; // Hardcoded.
        $file = plugin_dir_path(__FILE__) . 'functions.js'; // Hardcoded.
        $deps = ['error-stack-parser', 'jquery', 'ldc-singleton', 'underscore', 'utils', 'wp-api', 'wp-hooks']; // Hardcoded.
        $l10n = [
            'mu_plugins_url' => ldc_dir_to_url(wp_normalize_path(WPMU_PLUGIN_DIR)),
            'plugins_url' => ldc_dir_to_url(wp_normalize_path(WP_PLUGIN_DIR)),
            'site_url' => site_url(),
        ];
        ldc_local_omni_enqueue($handle, $file, $deps, $l10n);
	}
}

if(!function_exists('ldc_error')){
	/**
	 * Alias for new WP_Error::__construct.
	 *
	 * @return WP_Error
	 */
	function ldc_error($message = '', $data = ''){
		if(is_wp_error($message)){
			$data = $message->get_error_data();
			$message = $message->get_error_message();
		}
		if(empty($message)){
			$message = translate('Something went wrong.');
		}
		$code = 'ldc_error'; // Hardcoded.
		return new \WP_Error($code, $message, $data);
	}
}

if(!function_exists('ldc_get_instance')){
	/**
	 * @return LDC_Singleton|WP_Error // Hardcoded.
	 */
	function ldc_get_instance($class = ''){
	    if(!$class){
            $error_msg = translate('The "%s" argument must be a non-empty string.');
            $error_msg = sprintf($error_msg, 'class');
            return ldc_error($error_msg);
	    }
	    if(!class_exists($class)){
            $error_msg = sprintf(translate('Missing parameter(s): %s'), '"' . $class . '"') . '.';
	        return ldc_error($error_msg);
	    }
	    if(!is_subclass_of($class, 'LDC_Singleton')){ // Hardcoded.
            $error_msg = sprintf(translate('Invalid parameter(s): %s'), '"' . $class . '"') . '.';
	        return ldc_error($error_msg);
	    }
	    return call_user_func([$class, 'get_instance']);
	}
}

if(!function_exists('ldc_prefix')){
	/**
	 * @return string
	 */
	function ldc_prefix(){
	    return 'ldc_functions'; // Hardcoded.
	}
}

if(!function_exists('ldc_shortinit_dir')){
	/**
	 * @return string
	 */
	function ldc_shortinit_dir(){
		return plugin_dir_path(dirname(__FILE__)) . 'shortinit'; // Hardcoded.
	}
}

if(!function_exists('ldc_slug')){
	/**
	 * @return string
	 */
	function ldc_slug(){
        return 'ldc-functions'; // Hardcoded.
	}
}

if(!function_exists('ldc_toolbox')){
	/**
	 * @return __Toolbox
	 */
	function ldc_toolbox($atts = []){
        $md5 = ldc_md5($atts);
        if(ldc_isset_array_cache('toolboxes', $md5)){
			return ldc_get_array_cache('toolboxes', $md5, null);
		}
        $toolbox = new \__Toolbox($atts);
        ldc_set_array_cache('toolboxes', $md5, $toolbox);
        return $toolbox;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_context_enqueue')){
    /**
     * This function MUST be called inside the '$context' action hook.
     *
     * @return string|WP_Error
     */
    function ldc_context_enqueue($context = 'wp', $handle = '', $src = '', $deps = [], $ver = false, $args_media = true){
        if(doing_action($context . '_enqueue_scripts')){ // Just in time.
            return ldc_enqueue_asset($handle, $src, $deps, $ver, $args_media);
        }
        if(did_action($context . '_enqueue_scripts')){ // Too late.
            $error_msg = translate('Function %1$s was called <strong>incorrectly</strong>. %2$s %3$s');
            $error_msg = sprintf($error_msg, __FUNCTION__, '', '');
            $error_msg = trim($error_msg);
            return ldc_error($error_msg);
        }
        if(!$handle){
            $error_msg = translate('The "%s" argument must be a non-empty string.');
            $error_msg = sprintf($error_msg, 'handle');
            return ldc_error($error_msg);
        }
        $asset = [
            'args_media' => $args_media,
            'deps' => $deps,
            'handle' => $handle,
            'src' => $src,
            'ver' => $ver,
        ];
        $md5 = ldc_md5($asset);
        ldc_set_array_cache($context . '_assets', $md5, $asset);
        ldc_add_action_once($context . '_enqueue_scripts', 'ldc_maybe_enqueue_' . $context . '_assets'); // Hardcoded.
        return $handle;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Admin notices
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_add_admin_notice')){
	/**
	 * @return void
	 */
	function ldc_add_admin_notice($message = '', $class = 'warning', $is_dismissible = false){
		if(doing_action('admin_notices')){ // Just in time.
	        ldc_echo_admin_notice($message, $class, $is_dismissible);
			return;
	    }
		if(did_action('admin_notices')){ // Too late.
			return;
		}
		$admin_notice = [
			'class' => $class,
			'is_dismissible' => $is_dismissible,
			'message' => $message,
		];
		$md5 = ldc_md5($admin_notice);
		if(ldc_isset_array_cache('admin_notices', $md5)){
            return; // Prevent admin notice being added twice.
        }
		ldc_set_array_cache('admin_notices', $md5, $admin_notice);
		ldc_add_action_once('admin_notices', 'ldc_maybe_add_admin_notices');
	}
}

if(!function_exists('ldc_admin_notice_html')){
	/**
	 * @return string
	 */
	function ldc_admin_notice_html($message = '', $class = 'warning', $is_dismissible = false){
		if(!in_array($class, ['error', 'info', 'success', 'warning'])){
			$class = 'warning';
		}
        if(function_exists('wp_get_admin_notice')){
            $args = [
                'type' => $class,
                'dismissible' => $is_dismissible,
            ];
            $html = wp_get_admin_notice($message, $args);
        }
        // Backward compatibility.
        if($is_dismissible){
			$class .= ' is-dismissible';
		}
		return '<div class="notice notice-' . $class . '"><p>' . $message . '</p></div>';
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_echo_admin_notice')){
	/**
	 * This function MUST be called inside the 'admin_notices' action hook.
	 *
	 * @return void
	 */
	function ldc_echo_admin_notice($message = '', $class = 'warning', $is_dismissible = false){
		if(!doing_action('admin_notices')){ // Too early or too late.
	        return;
	    }
		$html = ldc_admin_notice_html($message, $class, $is_dismissible);
		echo $html;
	}
}

if(!function_exists('ldc_maybe_add_admin_notices')){
	/**
	 * This function MUST be called inside the 'admin_notices' action hook.
	 *
	 * @return void
	 */
	function ldc_maybe_add_admin_notices(){
		if(!doing_action('admin_notices')){ // Too early or too late.
	        return;
	    }
	    $admin_notices = (array) ldc_get_cache('admin_notices', []);
		if(empty($admin_notices)){
			return;
		}
		foreach($admin_notices as $md5 => $admin_notice){
			ldc_echo_admin_notice($admin_notice['message'], $admin_notice['class'], $admin_notice['is_dismissible']);
		}
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Arrays
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_array_keys_exists')){
	/**
	 * @return bool
	 */
	function ldc_array_keys_exists($keys = [], $array = []){
		if(!is_array($keys) or !is_array($array)){
			return false;
		}
		foreach($keys as $key){
			if(!array_key_exists($key, $array)){
				return false;
			}
		}
		return true;
	}
}

if(!function_exists('ldc_is_associative_array')){
	/**
	 * @return bool
	 */
	function ldc_is_associative_array($array = []){
		if(!is_array($array)){
			return false;
		}
		if(empty($array)){
			return false;
		}
		$end = count($array) - 1;
		if(array_keys($array) === range(0, $end)){
			return false;
		}
		return $array;
	}
}

if(!function_exists('ldc_ksort_deep')){
	/**
	 * @return array
	 */
	function ldc_ksort_deep($array = []){
		if(!ldc_is_associative_array($array)){
			return $array;
		}
		ksort($array);
		foreach($array as $key => $value){
			$array[$key] = ldc_ksort_deep($value);
		}
		return $array;
	}
}

if(!function_exists('ldc_list')){
	/**
	 * @return array
	 */
	function ldc_list($list = [], $index_key = ''){
        if(!$index_key){
            return $list;
        }
		$newlist = [];
		foreach($list as $value){
			if(is_object($value)){
				if(isset($value->$index_key)){
					$newlist[$value->$index_key] = $value;
				} else {
					$newlist[] = $value;
				}
			} else {
				if(isset($value[$index_key])){
					$newlist[$value[$index_key]] = $value;
				} else {
					$newlist[] = $value;
				}
			}
		}
		return $newlist;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Authentication
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_signon')){
	/**
	 * @return WP_Error|WP_User
	 */
	function ldc_signon($username_or_email = '', $password = '', $remember = false){
		if(is_user_logged_in()){
			return wp_get_current_user();
		}
		$disable_captcha = !has_filter('wordfence_ls_require_captcha', '__return_false');
		if($disable_captcha){ // Don't filter twice.
			add_filter('wordfence_ls_require_captcha', '__return_false');
		}
	    $user = wp_signon([
	        'remember' => $remember,
	        'user_login' => $username_or_email,
	        'user_password' => $password,
	    ]);
		if($disable_captcha){
			remove_filter('wordfence_ls_require_captcha', '__return_false');
		}
	    if(is_wp_error($user)){
	        return $user;
	    }
	    return wp_set_current_user($user->ID);
	}
}

if(!function_exists('ldc_signon_without_password')){
	/**
	 * @return WP_Error|WP_User
	 */
	function ldc_signon_without_password($username_or_email = '', $remember = false){
		if(is_user_logged_in()){
			return wp_get_current_user();
		}
	    add_filter('authenticate', 'ldc_maybe_authenticate_without_password', 10, 3);
		$disable_captcha = !has_filter('wordfence_ls_require_captcha', '__return_false');
		if($disable_captcha){ // Don't filter twice.
			add_filter('wordfence_ls_require_captcha', '__return_false');
		}
	    $user = wp_signon([
	        'remember' => $remember,
	        'user_login' => $username_or_email,
	        'user_password' => '',
	    ]);
		if($disable_captcha){
			remove_filter('wordfence_ls_require_captcha', '__return_false');
		}
	    remove_filter('authenticate', 'ldc_maybe_authenticate_without_password');
	    if(is_wp_error($user)){
	        return $user;
	    }
	    return wp_set_current_user($user->ID);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_maybe_authenticate_without_password')){
	/**
	 * This function MUST be called inside the 'authenticate' filter hook.
	 *
	 * @return bool|WP_Error|WP_User
	 */
	function ldc_maybe_authenticate_without_password($user = null, $username_or_email = '', $password = ''){
		if(!doing_filter('authenticate')){
	        return $user;
	    }
		if(!is_null($user)){
			return $user;
		}
		if(!empty($password)){
			$message = translate('The link you followed has expired.');
			return ldc_error($message);
		}
		$user = false; // Returning a non-null value will effectively short-circuit the user authentication process.
		if(username_exists($username_or_email)){
			$user = get_user_by('login', $username_or_email);
		} elseif(is_email($username_or_email) and email_exists($username_or_email)){
			$user = get_user_by('email', $username_or_email);
		}
		return $user;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Beaver Builder
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_bb_is_b4')){
    /**
     * @return bool
     */
    function ldc_bb_is_b4(){
        if(ldc_isset_cache('bb_is_b4')){
            return (bool) ldc_get_cache('bb_is_b4', false);
        }
        if(!ldc_is_bb()){
            return false;
        }
        $framework = get_theme_mod('fl-framework', 'base');
    	$bb_is_b4 = ('bootstrap-4' === $framework);
        ldc_set_cache('bb_is_b4', $bb_is_b4);
    	return $bb_is_b4;
    }
}

if(!function_exists('ldc_bb_is_fa5')){
    /**
     * @return bool
     */
    function ldc_bb_is_fa5(){
        if(ldc_isset_cache('bb_is_fa5')){
            return (bool) ldc_get_cache('bb_is_fa5', false);
        }
        if(!ldc_is_bb()){
            return false;
        }
        $awesome = get_theme_mod('fl-awesome', 'none');
    	$bb_is_fa5 = ('fa5' === $awesome);
        ldc_set_cache('bb_is_fa5', $bb_is_fa5);
    	return $bb_is_fa5;
    }
}

if(!function_exists('ldc_is_bb')){
    /**
     * @return bool
     */
    function ldc_is_bb(){
        if(ldc_isset_cache('bb_is')){
            return (bool) ldc_get_cache('bb_is', false);
        }
    	$current_theme = wp_get_theme();
    	$bb_is = ('Beaver Builder Theme' === $current_theme->get('Name') or 'bb-theme' === $current_theme->get('Template'));
    	ldc_set_cache('bb_is', $bb_is);
    	return $bb_is;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Bootstrap
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_alert_html')){
	/**
	 * @return string
	 */
	function ldc_alert_html($message = '', $class = '', $is_dismissible = false){
		if(!in_array($class, ['danger', 'dark', 'info', 'light', 'primary', 'secondary', 'success', 'warning'])){
			$class = 'warning';
		}
		if($is_dismissible){
			$class .= ' alert-dismissible fade show';
		}
		if($is_dismissible){
			$message .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
		}
		return '<div class="alert alert-' . $class . '">' . $message . '</div>';
	}
}

if(!function_exists('ldc_alert_danger')){
	/**
	 * @return string
	 */
	function ldc_alert_danger($message = '', $is_dismissible = false){
		return ldc_alert_html($message, 'danger', $is_dismissible);
	}
}

if(!function_exists('ldc_alert_dark')){
	/**
	 * @return string
	 */
	function ldc_alert_dark($message = '', $is_dismissible = false){
		return ldc_alert_html($message, 'dark', $is_dismissible);
	}
}

if(!function_exists('ldc_alert_info')){
	/**
	 * @return string
	 */
	function ldc_alert_info($message = '', $is_dismissible = false){
		return ldc_alert_html($message, 'info', $is_dismissible);
	}
}

if(!function_exists('ldc_alert_light')){
	/**
	 * @return string
	 */
	function ldc_alert_light($message = '', $is_dismissible = false){
		return ldc_alert_html($message, 'light', $is_dismissible);
	}
}

if(!function_exists('ldc_alert_primary')){
	/**
	 * @return string
	 */
	function ldc_alert_primary($message = '', $is_dismissible = false){
		return ldc_alert_html($message, 'primary', $is_dismissible);
	}
}

if(!function_exists('ldc_alert_secondary')){
	/**
	 * @return string
	 */
	function ldc_alert_secondary($message = '', $is_dismissible = false){
		return ldc_alert_html($message, 'secondary', $is_dismissible);
	}
}

if(!function_exists('ldc_alert_success')){
	/**
	 * @return string
	 */
	function ldc_alert_success($message = '', $is_dismissible = false){
		return ldc_alert_html($message, 'success', $is_dismissible);
	}
}

if(!function_exists('ldc_alert_warning')){
	/**
	 * @return string
	 */
	function ldc_alert_warning($message = '', $is_dismissible = false){
		return ldc_alert_html($message, 'warning', $is_dismissible);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Cache
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_get_array_cache')){
	/**
	 * @return mixed
	 */
	function ldc_get_array_cache($array_key = '', $key = '', $default = null){
		$array = (array) ldc_get_cache($array_key, []);
		return isset($array[$key]) ? $array[$key] : $default;
	}
}

if(!function_exists('ldc_get_cache')){
	/**
	 * @return mixed
	 */
	function ldc_get_cache($key = '', $default = null){
		$group = ldc_prefix();
		$value = wp_cache_get($key, $group, false, $found);
		if($found){
			return $value;
		}
	    return $default;
	}
}

if(!function_exists('ldc_isset_array_cache')){
	/**
	 * @return bool
	 */
	function ldc_isset_array_cache($array_key = '', $key = ''){
		$array = (array) ldc_get_cache($array_key, []);
		return isset($array[$key]);
	}
}

if(!function_exists('ldc_isset_cache')){
	/**
	 * @return bool
	 */
	function ldc_isset_cache($key = ''){
		$group = ldc_prefix();
		$value = wp_cache_get($key, $group, false, $found);
	    return $found;
	}
}

if(!function_exists('ldc_set_array_cache')){
	/**
	 * @return bool
	 */
	function ldc_set_array_cache($array_key = '', $key = '', $data = null){
		$array = (array) ldc_get_cache($array_key, []);
		$array[$key] = $data;
		return ldc_set_cache($array_key, $array);
	}
}

if(!function_exists('ldc_set_cache')){
	/**
	 * @return bool
	 */
	function ldc_set_cache($key = '', $data = null){
		$group = ldc_prefix();
		return wp_cache_set($key, $data, $group);
	}
}

if(!function_exists('ldc_unset_array_cache')){
	/**
	 * @return bool
	 */
	function ldc_unset_array_cache($array_key = '', $key = ''){
		$array = (array) ldc_get_cache($array_key, []);
		if(isset($array[$key])){
			unset($array[$key]);
		}
		return ldc_set_cache($array_key, $array);
	}
}

if(!function_exists('ldc_unset_cache')){
	/**
	 * @return bool
	 */
	function ldc_unset_cache($key = ''){
		$group = ldc_prefix();
		return wp_cache_delete($key, $group);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Cloudinary
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_add_cloudinary_image_size')){
    /**
     * @return void
     */
    function ldc_add_cloudinary_image_size($name = '', $options = []){
        $default_sizes = ['thumbnail', 'medium', 'medium_large', 'large'];
        $size = sanitize_title($name);
    	if(in_array($size, $default_sizes)){
    		return; // Do not overwrite default sizes.
    	}
        /*$additional_sizes = wp_get_additional_image_sizes();
        if(in_array($size, $additional_sizes)){
    		remove_image_size($size); // Do not overwrite additional sizes.
    	}*/
        ldc_add_filter_once('fl_builder_photo_sizes_select', 'ldc_maybe_cloudinary_fl_builder_photo_sizes_select');
        ldc_add_filter_once('image_downsize', 'ldc_maybe_cloudinary_image_downsize', 10, 3);
        ldc_add_filter_once('image_size_names_choose', 'ldc_maybe_cloudinary_image_size_names_choose');
        ldc_set_array_cache('cloudinary_image_sizes', $size, [
    		'name' => $name,
    		'options' => $options,
    	]);
        add_image_size($size); // Fake size.
    }
}

if(!function_exists('ldc_cloudinary_config')){
    /**
     * @return array|WP_Error
     */
    function ldc_cloudinary_config($config = []){
        $cache_key = 'cloudinary_config';
        if(ldc_isset_cache($cache_key)){
            return (array) ldc_get_cache($cache_key, []);
        }
        if(!$config){
            $error_msg = translate('Missing parameter(s): %s');
            $error_msg = sprintf($error_msg, 'Access Keys') . '.';
            return ldc_error($error_msg);
        }
        $lib = ldc_use_cloudinary_php();
        if(is_wp_error($lib)){
            return $lib;
        }
        $config_keys = ['api_key', 'api_secret', 'cloud_name'];
        if(ldc_array_keys_exists($config_keys, $config)){
            $config = \Cloudinary::config($config);
            ldc_set_cache($cache_key, $config);
            return $config;
        }
        if(is_string($config) and preg_match('/^(?:CLOUDINARY_URL=)?(?:cloudinary:\/\/)(\d+):([^:@]+)@([^@]+)$/', $config, $matches)){
            $config = \Cloudinary::config([
                'api_key' => $matches[1],
                'api_secret' => $matches[2],
                'cloud_name' => $matches[3],
            ]);
            ldc_set_cache($cache_key, $config);
            return $config;
        }
        $error_msg = translate('Invalid parameter(s): %s');
        $error_msg = sprintf($error_msg, 'Access Keys') . '.';
        return ldc_error($error_msg);
    }
}

if(!function_exists('ldc_cloudinary_file')){
    /**
     * @return string|WP_Error
     */
    function ldc_cloudinary_file($attachment_id = 0, $options = []){
        $meta_key = ldc_cloudinary_meta_key_with_options('file', $options);
        if(is_wp_error($meta_key)){
            return $meta_key;
        }
        $meta_value = ldc_cloudinary_meta_value($attachment_id, $meta_key);
        if($meta_value){
            if(is_wp_error($meta_value)){
                return $meta_value;
            }
            if(file_exists($meta_value)){
                return $meta_value; // Already downloaded.
            }
            delete_post_meta($attachment_id, $meta_key, $meta_value);
        }
        $url = ldc_cloudinary_url($attachment_id, $options);
        if(is_wp_error($url)){
            return $url;
        }
        $download_dir = ldc_download_dir('cloudinary');
        if(is_wp_error($download_dir)){
            return $download_dir;
        }
        $attachment_file = wp_get_original_image_path($attachment_id);
        $attachment_filename = wp_basename($attachment_file);
        $filename = wp_unique_filename($download_dir, $attachment_filename);
        $args = [
            'filename' => trailingslashit($download_dir) . $filename,
        ];
        $file = ldc_download_url($url, $args);
        if(is_wp_error($file)){
            return $file;
        }
        update_post_meta($attachment_id, $meta_key, $file);
        return $file;
    }
}

if(!function_exists('ldc_cloudinary_file_info')){
    /**
     * @return array|WP_Error
     */
    function ldc_cloudinary_file_info($attachment_id = 0, $options = []){
        $file = ldc_cloudinary_file($attachment_id, $options);
        if(is_wp_error($file)){
            return $file;
        }
        list($width, $height) = wp_getimagesize($file);
        return [
            'file' => $file,
            'filename' => wp_basename($file),
            'height' => $height,
            'url' => ldc_dir_to_url($file),
            'width' => $width,
        ];
    }
}

if(!function_exists('ldc_cloudinary_public_id')){
    /**
     * @return string|WP_Error
     */
    function ldc_cloudinary_public_id($attachment_id = 0){
        $response = ldc_cloudinary_maybe_upload($attachment_id);
        if(is_wp_error($response)){
            return $response;
        }
        return (isset($response['public_id']) ? $response['public_id'] : '');
    }
}

if(!function_exists('ldc_cloudinary_url')){
    /**
     * @return string|WP_Error
     */
    function ldc_cloudinary_url($attachment_id = 0, $options = []){
        $meta_key = ldc_cloudinary_meta_key_with_options('url', $options);
        if(is_wp_error($meta_key)){
            return $meta_key;
        }
        $meta_value = ldc_cloudinary_meta_value($attachment_id, $meta_key);
        if($meta_value){
            if(is_wp_error($meta_value)){
                return $meta_value;
            }
            return $meta_value; // Already transformed.
        }
        $public_id = ldc_cloudinary_public_id($attachment_id);
        if(is_wp_error($public_id)){
            return $public_id;
        }
        try {
            $response = cloudinary_url($public_id, $options);
        } catch(\Throwable $t){
            $response = ldc_error($t->getMessage());
        } catch(\Exception $e){
            $response = ldc_error($e->getMessage());
        }
        if(is_wp_error($response)){
            return $response;
        }
        if(ldc_is_cloudinary_error($response)){
            return ldc_error($response->getMessage());
        }
        update_post_meta($attachment_id, $meta_key, $response);
        return $response;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_cloudinary_file_candidate')){
    /**
     * @return string|WP_Error
     */
    function ldc_cloudinary_file_candidate($attachment_id = 0, $max_file_size = 0){
        if(!wp_attachment_is_image($attachment_id)){
            $error_msg = translate('This file is not an image. Please try another.');
            $error_msg = ldc_first_p($error_msg);
            return ldc_error($error_msg);
        }
        $image_file = get_attached_file($attachment_id);
        if(!file_exists($image_file)){
            $error_msg = translate('The attached file cannot be found.');
            return ldc_error($error_msg);
        }
        $type = wp_check_filetype($image_file);
        if(!$type['ext'] or !$type['type']){
            $error_msg = translate('The uploaded file is not a valid image. Please try again.');
            $error_msg = ldc_first_p($error_msg);
            return ldc_error($error_msg);
        }
        $image_meta = wp_get_attachment_metadata($attachment_id);
        $max_file_size_in_kb = ($max_file_size / KB_IN_BYTES);
        if(!$image_meta){
            $filesize = wp_filesize($image_file);
            if($filesize <= $max_file_size){
                return $image_file;
            }
            $error_msg = translate('This file is too big. Files must be less than %s KB in size.');
            $error_msg = sprintf($error_msg, $max_file_size_in_kb);
            return ldc_error($error_msg);
        }
        if(isset($image_meta['original_image'])){
            $original_image = path_join(dirname($image_file), $image_meta['original_image']); // Alias for wp_get_original_image_path.
            if(!file_exists($original_image)){
                $error_msg = translate('The attached file cannot be found.');
                return ldc_error($error_msg);
            }
            $filesize = wp_filesize($original_image);
            if($filesize <= $max_file_size){
                return $original_image;
            }
        }
        if($image_meta['filesize'] <= $max_file_size){
            return $image_file;
        }
        $sizes = wp_list_pluck($image_meta['sizes'], 'filesize', 'file');
        arsort($sizes);
        $basedir = dirname($file);
        $path = '';
        foreach($sizes as $file => $filesize){
            if($filesize > $max_file_size){
                continue;
            }
            $path = path_join($basedir, $file);
            if(!file_exists($path)){
                continue;
            }
            break;
        }
        if(!$path){
            $error_msg = translate('This file is too big. Files must be less than %s KB in size.');
            $error_msg = sprintf($error_msg, $max_file_size_in_kb);
            return ldc_error($error_msg);
        }
        return $path;
    }
}

if(!function_exists('ldc_cloudinary_maybe_upload')){
    /**
     * @return array|WP_Error
     */
    function ldc_cloudinary_maybe_upload($attachment_id = 0){
        $meta_key = ldc_cloudinary_meta_key('response');
        if(is_wp_error($meta_key)){
            return $meta_key;
        }
        $meta_value = ldc_cloudinary_meta_value($attachment_id, $meta_key);
        if($meta_value){
            if(is_wp_error($meta_value)){
                return $meta_value;
            }
            return (array) $meta_value; // Already uploaded.
        }
        $max_file_size = (10 * MB_IN_BYTES); // TODO: Check for paid plans. https://support.cloudinary.com/hc/en-us/articles/202520592-Do-you-have-a-file-size-limit-
        $file = ldc_cloudinary_file_candidate($attachment_id, $max_file_size);
        if(is_wp_error($file)){
            return $file;
        }
        try {
            $response = \Cloudinary\Uploader::upload($file);
        } catch(\Throwable $t){
            $response = ldc_error($t->getMessage());
        } catch(\Exception $e){
            $response = ldc_error($e->getMessage());
        }
        if(is_wp_error($response)){
            return $response;
        }
        if(ldc_is_cloudinary_error($response)){
            return ldc_error($response->getMessage());
        }
        update_post_meta($attachment_id, $meta_key, $response);
        return $response;
    }
}

if(!function_exists('ldc_cloudinary_meta_key')){
    /**
     * @return string|WP_Error
     */
    function ldc_cloudinary_meta_key($context = ''){
        if(!$context){
            $error_msg = translate('The "%s" argument must be a non-empty string.');
            $error_msg = sprintf($error_msg, 'context');
            return ldc_error($error_msg);
        }
        $config = ldc_cloudinary_config();
        if(is_wp_error($config)){
            return $config;
        }
        $config_hash = ldc_md5($config);
        $meta_key = '_cloudinary_' . $config_hash . '_' . $context;
        return $meta_key;
    }
}

if(!function_exists('ldc_cloudinary_meta_key_with_options')){
    /**
     * @return string|WP_Error
     */
    function ldc_cloudinary_meta_key_with_options($context = '', $options = []){
        $meta_key = ldc_cloudinary_meta_key($context);
        if(is_wp_error($meta_key)){
            return $meta_key;
        }
        if(!$options){
            $error_msg = translate('The %s argument must be an array.');
            $error_msg = sprintf($error_msg, 'options');
            return ldc_error($error_msg);
        }
        $options_hash = ldc_md5($options);
        $meta_key .= '_' . $options_hash;
        return $meta_key;
    }
}

if(!function_exists('ldc_cloudinary_meta_value')){
    /**
     * @return array|string|WP_Error
     */
    function ldc_cloudinary_meta_value($attachment_id = 0, $meta_key = ''){
        if(!$meta_key){
            $error_msg = sprintf(translate('The "%s" argument must be a non-empty string.'), 'meta_key');
            return ldc_error($error_msg);
        }
        if(!wp_attachment_is_image($attachment_id)){
            $error_msg = translate('This file is not an image. Please try another.');
            $error_msg = ldc_first_p($error_msg);
            return ldc_error($error_msg);
        }
        return get_post_meta($attachment_id, $meta_key, true);
    }
}

if(!function_exists('ldc_cloudinary_width_height_ascending_sort')){
    /**
     * @return int
     */
    function ldc_cloudinary_width_height_ascending_sort($a = 0, $b = 0){
        if($a['width'] === $b['width']){
			if($a['height'] === $b['height']){
				return 0;
			}
			if($a['height'] < $b['height']){
				return -1;
			}
			return 1;
		}
		if($a['width'] < $b['width']){
			return -1;
		}
		return 1;
    }
}

if(!function_exists('ldc_is_cloudinary_error')){
    /**
     * @return bool
     */
    function ldc_is_cloudinary_error($thing = null){
        $is_cloudinary_error = ($thing instanceof \Cloudinary\Error);
        return $is_cloudinary_error;
    }
}

if(!function_exists('ldc_maybe_cloudinary_fl_builder_photo_sizes_select')){
	/**
	 * @return array
	 */
	function ldc_maybe_cloudinary_fl_builder_photo_sizes_select($sizes){
        $cache_key = 'cloudinary_image_sizes';
        if(!ldc_isset_cache($cache_key)){
    		return $sizes;
    	}
		if(!isset($sizes['full'])){
			return $sizes;
		}
		$id = ldc_attachment_url_to_postid($sizes['full']['url']);
		if(!$id){
			return $sizes;
		}
        $cloudinary_image_sizes = (array) ldc_get_cache($cache_key, []);
		foreach($cloudinary_image_sizes as $size => $args){
			if(isset($sizes[$size])){
				continue;
			}
            $file_info = ldc_cloudinary_file_info($id, $args['options']);
            if(is_wp_error($file_info)){
                continue;
            }
			$sizes[$size] = [
				'filename' => $file_info['filename'],
				'height' => $file_info['height'],
				'url' => $file_info['url'],
				'width' => $file_info['width'],
			];
		}
		uasort($sizes, 'ldc_cloudinary_width_height_ascending_sort');
		return $sizes;
	}
}

if(!function_exists('ldc_maybe_cloudinary_image_downsize')){
    /**
     * @return array|false
     */
    function ldc_maybe_cloudinary_image_downsize($out, $id, $size){
    	if($out){
    		return $out; // A truthy value from the filter will effectively short-circuit down-sizing the image, returning that value instead.
    	}
        if(!wp_attachment_is_image($id)){
    		return $out;
    	}
        if(!is_scalar($size)){
            return $out; // Discard non-scalars.
        }
        $cache_key = 'cloudinary_image_sizes';
    	if(!ldc_isset_array_cache($cache_key, $size)){
    		return $out;
    	}
        $args = (array) ldc_get_array_cache($cache_key, $size, []);
        $file_info = ldc_cloudinary_file_info($id, $args['options']);
        if(is_wp_error($file_info)){
            return $out;
        }
        return [$file_info['url'], $file_info['width'], $file_info['height'], true];
    }
}

if(!function_exists('ldc_maybe_cloudinary_image_size_names_choose')){
	/**
	 * @return array
	 */
	function ldc_maybe_cloudinary_image_size_names_choose($sizes){
        $cache_key = 'cloudinary_image_sizes';
        if(!ldc_isset_cache($cache_key)){
    		return $sizes;
    	}
        $cloudinary_image_sizes = (array) ldc_get_cache($cache_key, []);
		foreach($cloudinary_image_sizes as $size => $args){
			$sizes[$size] = $args['name'];
		}
		return $sizes;
	}
}

if(!function_exists('ldc_use_cloudinary_php')){
    /**
     * @return string|WP_Error
     */
    function ldc_use_cloudinary_php($ver = '1.20.2'){
    	$key = 'cloudinary-php-' . $ver;
        if(ldc_isset_cache($key)){
            return (string) ldc_get_cache($key, '');
        }
    	$class = 'Cloudinary';
    	if(class_exists($class)){
            return ''; // Already handled outside of this function.
        }
    	$dir = ldc_remote_lib('https://github.com/cloudinary/cloudinary_php/archive/refs/tags/' . $ver . '.zip', 'cloudinary_php-' . $ver);
    	if(is_wp_error($dir)){
    		return $dir;
    	}
    	$file = $dir . '/autoload.php';
    	if(!file_exists($file)){
            $error_msg = translate('File does not exist! Please double check the name and try again.');
            $error_msg = ldc_first_p($error_msg);
            return ldc_error($error_msg);
    	}
    	require_once($file);
        if(!class_exists($class)){
            $error_msg = sprintf(translate('Missing parameter(s): %s'), $class) . '.';
    		return ldc_error($error_msg);
    	}
        $file = $dir . '/src/Helpers.php';
    	if(file_exists($file)){
            require_once($file); // Fallback to legacy autoloader.
    	}
    	ldc_set_cache($key, $dir);
    	return $dir;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Contact Form 7
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_cf7_abort')){
	/**
	 * This function MUST be called inside the 'wpcf7_before_send_mail' action hook.
	 *
	 * @return void
	 */
	function ldc_cf7_abort(&$abort, $message = '', $submission = null){
		if(!doing_action('wpcf7_before_send_mail')){
	        return; // Too early or too late.
	    }
		if($abort){
			return; // Already aborted.
		}
	    $submission = ldc_cf7_submission($submission);
	    if(is_null($submission)){
	        return;
	    }
		if(!$submission->is('init')){
			return; // Avoid conflicts with other statuses.
		}
	    $abort = true; // Avoid mail_sent and mail_failed action hooks.
		$message = ldc_cf7_message($message, 'aborted');
	    $submission->set_response($message);
	    $submission->set_status('aborted');
	}
}

if(!function_exists('ldc_cf7_additional_setting')){
	/**
	 * Alias for WPCF7_ContactForm::pref.
	 *
	 * Differs from WPCF7_ContactForm::pref in that it will always return a string.
	 *
	 * @return string
	 */
	function ldc_cf7_additional_setting($name = '', $contact_form = null){
		$contact_form = ldc_cf7_contact_form($contact_form);
		if(is_null($contact_form)){
			return '';
		}
		return (string) $contact_form->pref($name);
	}
}

if(!function_exists('ldc_cf7_additional_settings')){
	/**
	 * Alias for WPCF7_ContactForm::additional_setting(@param $max = false).
	 *
	 * Differs from WPCF7_ContactForm::additional_setting in that it will always return an array.
	 *
	 * @return array
	 */
	function ldc_cf7_additional_settings($name = '', $contact_form = null){
		$contact_form = ldc_cf7_contact_form($contact_form);
		if(is_null($contact_form)){
			return [];
		}
		return $contact_form->additional_setting($name, false);
	}
}

if(!function_exists('ldc_cf7_contact_form')){
	/**
	 * Alias for wpcf7_contact_form, wpcf7_get_contact_form_by_hash and wpcf7_get_contact_form_by_title.
	 *
	 * Returns the current contact form if the specified setting has a falsey value and restores the current contact form.
	 *
	 * @return null|WPCF7_ContactForm
	 */
	function ldc_cf7_contact_form($contact_form = null){
		$current_contact_form = wpcf7_get_current_contact_form();
		if(empty($contact_form)){ // 0, false, null and other PHP falsey values.
			return $current_contact_form;
		}
		if(ldc_is_cf7($contact_form)){
			return $contact_form;
		}
		$post_id = ldc_cf7_hash_exists($contact_form); // Hash-based contact form identification.
		if($post_id){
			$contact_form = wpcf7_contact_form($post_id); // Avoid wpcf7_get_contact_form_by_hash for backcompat.
		} elseif(is_numeric($contact_form)){
			$contact_form = ldc_cf7_get_contact_form_by('id', $contact_form);
		} elseif(is_string($contact_form)){
	        $contact_form = ldc_cf7_get_contact_form_by('title', $contact_form);
	    } elseif($contact_form instanceof \WP_Post){
			$contact_form = wpcf7_contact_form($contact_form);
		} else {
			return null;
		}
		if(ldc_is_cf7($current_contact_form)){
			wpcf7_contact_form($current_contact_form); // Restores the current contact form.
		}
		return $contact_form;
	}
}

if(!function_exists('ldc_cf7_container_post_id')){
	/**
	 * @return int
	 */
	function ldc_cf7_container_post_id(){
		$submission = ldc_cf7_submission();
        if(ldc_cf7_is_submission($submission)){
            return $submission->get_meta('container_post_id');
        }
        if(!ldc_is_front()){
            return 0;
        }
        if(!in_the_loop()){
            return 0;
        }
        return get_the_ID();
	}
}

if(!function_exists('ldc_cf7_error')){
	/**
	 * @return string
	 */
	function ldc_cf7_error($message = ''){
		if(empty($message)){
			$message = translate('Contact form not found.', 'contact-form-7');
		}
		$error = translate('Error:', 'contact-form-7');
		return sprintf('<p class="wpcf7-contact-form-not-found"><strong>%1$s</strong> %2$s</p>', esc_html($error), esc_html($message));
	}
}

if(!function_exists('ldc_cf7_fake_mail')){
	/**
	 * Skips or sends emails based on user input values and contact form email templates avoiding mail_sent and mail_failed action hooks.
	 *
	 * This function MUST be called inside the 'wpcf7_before_send_mail' action hook.
	 *
	 * @return bool
	 */
	function ldc_cf7_fake_mail($contact_form = null, $submission = null){
		if(!doing_action('wpcf7_before_send_mail')){
	        return; // Too early or too late.
	    }
		$contact_form = ldc_cf7_contact_form($contact_form);
		if(is_null($contact_form)){
			return false;
		}
		$submission = ldc_cf7_submission($submission);
		if(is_null($submission)){
			return false;
		}
		if(!$submission->is('init')){
			return false; // Avoid conflicts with other statuses.
		}
		if(ldc_cf7_skip_mail($contact_form) or ldc_cf7_mail($contact_form)){
			$status = 'mail_sent';
			$message = ldc_cf7_message('', $status);
			$submission->set_response($message);
			$submission->set_status($status);
			return true;
		}
		$status = 'mail_failed';
		$message = ldc_cf7_message('', $status);
		$submission->set_response($message);
		$submission->set_status($status);
		return false;
	}
}

if(!function_exists('ldc_cf7_form_tag_class')){
	/**
	 * Alias for WPCF7_FormTag::get_class_option.
	 *
	 * Differs from WPCF7_FormTag::get_class_option in that it will always return a string.
	 *
	 * @return string
	 */
	function ldc_cf7_form_tag_class($tag = null, $default_classes = ''){
	    if(!ldc_cf7_is_form_tag($tag)){
	        return '';
	    }
		return (string) $tag->get_class_option($default_classes);
	}
}

if(!function_exists('ldc_cf7_form_tag_content')){
	/**
	 * @return string
	 */
	function ldc_cf7_form_tag_content($tag = null, $remove_whitespaces = false){
	    if(!ldc_cf7_is_form_tag($tag)){
	        return false;
	    }
		return ($remove_whitespaces ? ldc_remove_whitespaces($tag->content) : trim($tag->content));
	}
}

if(!function_exists('ldc_cf7_form_tag_content_label')){
	/**
	 * @return string
	 */
	function ldc_cf7_form_tag_content_label($tag = null){
		$content = ldc_cf7_form_tag_content($tag, true);
		if(empty($content)){
			return '';
		}
		if(!in_array($tag->basetype, ['checkbox', 'date', 'email', 'file', 'number', 'password', 'radio', 'range', 'select', 'tel', 'text', 'textarea', 'url'])){
	        return '';
	    }
		if('textarea' === $tag->basetype and $tag->has_option('has_content')){
			return '';
		}
		return $content;
	}
}

if(!function_exists('ldc_cf7_form_tag_content_placeholder_equals')){
	/**
	 * @return bool
	 */
	function ldc_cf7_form_tag_content_placeholder_equals($tag = null){
		$content = ldc_cf7_form_tag_content($tag, true);
		$placeholder = ldc_cf7_form_tag_placeholder($tag);
		return ($content === $placeholder);
	}
}

if(!function_exists('ldc_cf7_form_tag_fa')){
	/**
	 * @return string
	 */
	function ldc_cf7_form_tag_fa($tag = null){
		$class = ldc_cf7_form_tag_fa_class($tag);
		if(!$class){
			return '';
		}
		return '<i class="' . $class . '"></i>';
	}
}

if(!function_exists('ldc_cf7_form_tag_fa_class')){
	/**
	 * @return string
	 */
	function ldc_cf7_form_tag_fa_class($tag = null){
	    $classes = ldc_cf7_form_tag_fa_classes($tag);
	    if(!$classes){
			return '';
		}
		return implode(' ', $classes);
	}
}

if(!function_exists('ldc_cf7_form_tag_fa_classes')){
	/**
	 * @return array
	 */
	function ldc_cf7_form_tag_fa_classes($tag = null){
		if(!ldc_cf7_is_form_tag($tag)){
	        return [];
	    }
		if(!$tag->has_option('fa')){
			return [];
		}
		$classes = [];
		switch(true){
		    case $tag->has_option('fab'):
		        $classes[] = 'fab';
		        break;
		    case $tag->has_option('fad'):
		        $classes[] = 'fad';
		        break;
		    case $tag->has_option('fal'):
		        $classes[] = 'fal';
		        break;
		    case $tag->has_option('far'):
		        $classes[] = 'far';
		        break;
		    case $tag->has_option('fas'):
		        $classes[] = 'fas';
		        break;
		    default:
		        return '';
		}
		$fa = $tag->get_option('fa', 'class', true);
		if(0 !== strpos($fa, 'fa-')){
			$fa = 'fa-' . $fa;
		}
		$classes[] = $fa;
		if($tag->has_option('fw')){
		    $classes[] = 'fa-fw';
		}
	    $rotate = $tag->get_option('rotate', 'int', true);
	    if(in_array($rotate, [90, 180, 270])){
	        $classes[] = 'fa-rotate-' . $rotate;
	    }
	    $flip = $tag->get_option('flip', '', true);
	    if(in_array($flip, ['horizontal', 'vertical', 'both'])){
	        $classes[] = 'fa-flip-' . $flip;
	    }
	    $animate = $tag->get_option('animate', '', true);
	    if(in_array($animate, ['beat', 'fade', 'beat-fade', 'bounce', 'flip', 'shake', 'spin'])){
	        $classes[] = 'fa-' . $animate;
	    }
		return $classes;
	}
}

if(!function_exists('ldc_cf7_form_tag_floating_label')){
	/**
	 * @return string
	 */
	function ldc_cf7_form_tag_floating_label($tag = null){
		$content = ldc_cf7_form_tag_content($tag, true);
		$placeholder = ldc_cf7_form_tag_placeholder($tag);
		if(empty($content) and empty($placeholder)){
			return '';
		}
		if(!in_array($tag->basetype, ['date', 'email', 'file', 'number', 'password', 'select', 'tel', 'text', 'textarea', 'url'])){
	        return '';
	    }
		if($placeholder){
			return $placeholder;
		}
		return wp_strip_all_tags($content);
	}
}

if(!function_exists('ldc_cf7_form_tag_has_data_option')){
	/**
	 * @return bool
	 */
	function ldc_cf7_form_tag_has_data_option($tag = null){
	    if(!ldc_cf7_is_form_tag($tag)){
	        return false;
	    }
		return (bool) $tag->get_data_option();
	}
}

if(!function_exists('ldc_cf7_form_tag_has_content')){
	/**
	 * @return bool
	 */
	function ldc_cf7_form_tag_has_content($tag = null){
	    $content = ldc_cf7_form_tag_content($tag, true);
		return ('' !== $content); // An empty string.
	}
}

if(!function_exists('ldc_cf7_form_tag_has_free_text')){
	/**
	 * @return bool
	 */
	function ldc_cf7_form_tag_has_free_text($tag = null){
	    if(!ldc_cf7_is_form_tag($tag)){
	        return false;
	    }
		return $tag->has_option('free_text');
	}
}

if(!function_exists('ldc_cf7_form_tag_has_pipes')){
	/**
	 * @return bool
	 */
	function ldc_cf7_form_tag_has_pipes($tag = null){
		if(!ldc_cf7_is_form_tag($tag)){
	        return false;
	    }
	    if(!WPCF7_USE_PIPE or !$tag->pipes instanceof \WPCF7_Pipes or $tag->pipes->zero()){
	        return false;
	    }
	    foreach($tag->pipes->to_array() as $pipe){
	        if($pipe[0] !== $pipe[1]){
	            return true;
	        }
	    }
		return false;
	}
}

if(!function_exists('ldc_cf7_form_tag_has_option')){
	/**
	 * Alias for WPCF7_FormTag::has_option.
	 *
	 * @return bool
	 */
	function ldc_cf7_form_tag_has_option($tag = null, $option_name = ''){
	    if(!ldc_cf7_is_form_tag($tag)){
	        return false;
	    }
		return $tag->has_option($option_name);
	}
}

if(!function_exists('ldc_cf7_form_tag_id')){
	/**
	 * Important: Avoid WPCF7_FormTag::get_id_option.
	 *
	 * Differs from WPCF7_FormTag::get_id_option in that it will always return a string.
	 *
	 * @return string
	 */
	function ldc_cf7_form_tag_id($tag = null){
	    if(!ldc_cf7_is_form_tag($tag)){
	        return '';
	    }
		return ldc_cf7_form_tag_option($tag, 'id', 'id');
	}
}

if(!function_exists('ldc_cf7_form_tag_idx')){
	/**
	 * @return string
	 */
	function ldc_cf7_form_tag_idx($tag = null, $contact_form = null){
	    if(!ldc_cf7_is_form_tag($tag)){
	        return '';
	    }
        $uniqid = false;
        if(empty($tag->id)){
            if(empty($tag->name)){
                $id = uniqid($tag->basetype . '_');
                $uniqid = true;
            } else {
                $id = $tag->name;
            }
        } else {
            $id = $tag->id;
        }
        if(wp_doing_ajax()){ // AJAX.
            if(!$uniqid){
                $id .= '-' . uniqid();
            }
        } else {
            if(!$uniqid){
                $o = ldc_cf7_object_number($contact_form);
                if($o){ // Inside the loop.
                    $id .= '-' . $o;
                } else { // Outside the loop.
                    $id .= '-' . uniqid();
                }
            }
        }
        return $id;
	}
}

if(!function_exists('ldc_cf7_form_tag_is_false')){
	/**
	 * Opposite of WPCF7_ContactForm::is_true.
	 *
	 * @return bool
	 */
	function ldc_cf7_form_tag_is_false($tag = null, $option_name = ''){
	    $option_value = ldc_cf7_form_tag_option($tag, $option_name);
	    return ldc_is_false($option_value);
	}
}

if(!function_exists('ldc_cf7_form_tag_is_true')){
	/**
	 * Alias for WPCF7_ContactForm::is_true.
	 *
	 * @return bool
	 */
	function ldc_cf7_form_tag_is_true($tag = null, $option_name = ''){
	    $option_value = ldc_cf7_form_tag_option($tag, $option_name);
	    return ldc_is_true($option_value);
	}
}

if(!function_exists('ldc_cf7_form_tag_option')){
	/**
	 * Alias for WPCF7_FormTag::get_option(@param $single = true).
	 *
	 * Differs from WPCF7_FormTag::get_option in that it will always return a string.
	 *
	 * @return string
	 */
	function ldc_cf7_form_tag_option($tag = null, $option_name = '', $pattern = ''){
	    if(!ldc_cf7_form_tag_has_option($tag, $option_name)){
	        return '';
	    }
	    return (string) $tag->get_option($option_name, $pattern, true);
	}
}

if(!function_exists('ldc_cf7_form_tag_options')){
	/**
	 * Alias for WPCF7_FormTag::get_option(@param $single = false).
	 *
	 * Differs from WPCF7_FormTag::get_option in that it will always return an array.
	 *
	 * @return array
	 */
	function ldc_cf7_form_tag_options($tag = null, $option_name = '', $pattern = ''){
	    if(!ldc_cf7_form_tag_has_option($tag, $option_name)){
	        return '';
	    }
	    return (array) $tag->get_option($option_name, $pattern, false);
	}
}

if(!function_exists('ldc_cf7_form_tag_output')){
	/**
	 * @return void
	 */
	function ldc_cf7_form_tag_output($form_tag = '', $callback = '', $priority = 10, $accepted_args = 1){
        $form_tags = (array) $form_tag;
        foreach($form_tags as $form_tag){
            if('*' === $form_tag){
                $hook_name = 'cf7_form_tag_output';
            } else {
                $hook_name = 'cf7_form_tag_' . $form_tag . '_output';
            }
            $hook = [
                'accepted_args' => $accepted_args,
                'callback' => $callback, // Closure?
                'hook_name' => $hook_name,
                'priority' => $priority,
            ];
            $md5 = ldc_md5($hook);
            if(ldc_isset_array_cache('cf7_hooks', $md5)){
                return; // Prevent hook being added twice.
            }
            ldc_set_array_cache('cf7_hooks', $md5, $hook);
            ldc_add_filter_once($hook_name, $callback, $priority, $accepted_args);
            ldc_add_filter_once('do_shortcode_tag', 'ldc_cf7_maybe_filter_shortcode_tag_output', 10, 4);
        }
	}
}

if(!function_exists('ldc_cf7_form_tag_placeholder')){
	/**
	 * @return string
	 */
	function ldc_cf7_form_tag_placeholder($tag = null){
		if(!ldc_cf7_is_form_tag($tag)){
	        return '';
	    }
		if(!in_array($tag->basetype, ['date', 'email', 'file', 'number', 'password', 'select', 'tel', 'text', 'textarea', 'url'])){
	        return '';
	    }
		if('select' === $tag->basetype){
			if($tag->has_option('include_blank') or empty($tag->values)){
				if(version_compare(WPCF7_VERSION, '5.7', '>=')){
					return translate('&#8212;Please choose an option&#8212;', 'contact-form-7'); // Drop-down menu: Uses more friendly label text. https://contactform7.com/2022/12/10/contact-form-7-57/
				} else {
					return '---';
				}
			} elseif($tag->has_option('first_as_label') and !empty($tag->values)){
				return (string) $tag->values[0];
			} else {
				return '';
			}
		} else {
			if(($tag->has_option('placeholder') or $tag->has_option('watermark')) and !empty($tag->values)){
				return (string) $tag->values[0];
			} else {
				return '';
			}
		}
	}
}

if(!function_exists('ldc_cf7_get_contact_form_by')){
	/**
	 * @return null|WPCF7_ContactForm
	 */
	function ldc_cf7_get_contact_form_by($field = '', $value = null){
	    if('hash' === $field and version_compare(WPCF7_VERSION, '5.8', '<')){ // https://contactform7.com/2023/08/06/contact-form-7-58/#hash-based-contact-form-identification
			return null;
		}
		if('id' === $field){
			$value = ldc_absint($value);
			if(!$value){
				return null;
			}
		}
		if(in_array($field, ['hash', 'title'])){
			if(!is_string($value)){
				return null;
			}
			$value = trim($value);
			if(!$value){
				return null;
			}
		}
		switch($field){
			case 'hash':
				$contact_form = wpcf7_get_contact_form_by_hash($value);
				break;
			case 'id':
				$contact_form = wpcf7_contact_form($value);
				break;
			case 'title':
				$contact_form = wpcf7_get_contact_form_by_title($value);
				break;
			default:
				return null;
		}
		return $contact_form;
	}
}

if(!function_exists('ldc_cf7_get_submission')){
	/**
	 * @return array|WP_Error
	 */
	function ldc_cf7_get_submission($meta_type = '', $object_id = 0){
	    if(!in_array($meta_type, ['post', 'user'])){
			$error_msg = sprintf(translate('Invalid parameter(s): %s'), 'meta_type') . '.';
	        return ldc_error($error_msg);
	    }
	    if('post' === $meta_type){
	        $post = get_post($object_id);
	        if(empty($post)){
				$error_msg = translate('Invalid post ID.');
	            return ldc_error($error_msg);
	        }
	    } elseif('user' === $meta_type){
	        $user = get_userdata($object_id);
	        if(empty($user)){
				$error_msg = translate('Invalid user ID.');
	            return ldc_error($error_msg);
	        }
	    }
        $data = [
            'fields' => [],
            'files' => [],
            'form' => [],
            'meta' => [],
            'submission' => [],
        ];
        $form_keys = ['demo_mode', 'id', 'locale', 'name', 'skip_mail', 'title'];
        $meta_data = get_metadata($meta_type, $object_id);
        $meta_keys = ['container_post_id', 'current_user_id', 'remote_ip', 'remote_port', 'timestamp', 'unit_tag', 'url', 'user_agent'];
        $submission_keys = ['response', 'status'];
        foreach($meta_data as $key => $values){
            if(ldc_str_starts_with('field_', $key)){
                $meta_key = str_replace('field_', '', $key);
                $data['fields'][$meta_key] = $values;
                continue;
            }
            if(ldc_str_starts_with('file_', $key)){
                $meta_key = str_replace('file_', '', $key);
                $data['files'][$meta_key] = $values;
                continue;
            }
            if(ldc_str_starts_with('form_', $key)){
                $meta_key = str_replace('form_', '', $key);
                if(!in_array($meta_key, $form_keys)){
                    continue;
                }
                $data['form'][$meta_key] = $values;
                continue;
            }
            if(ldc_str_starts_with('meta_', $key)){
                $meta_key = str_replace('meta_', '', $key);
                if(!in_array($meta_key, $meta_keys)){
                    continue;
                }
                $data['meta'][$meta_key] = $values;
                continue;
            }
            if(ldc_str_starts_with('submission_', $key)){
                $meta_key = str_replace('submission_', '', $key);
                if(!in_array($meta_key, $submission_keys)){
                    continue;
                }
                $data['submission'][$meta_key] = $values;
                continue;
            }
        }
	    return $data;
	}
}

if(!function_exists('ldc_cf7_has_additional_setting')){
	/**
	 * @return bool
	 */
	function ldc_cf7_has_additional_setting($name = '', $contact_form = null){
		$contact_form = ldc_cf7_contact_form($contact_form);
		if(is_null($contact_form)){
			return false;
		}
		$pref = $contact_form->pref($name);
		return !is_null($pref);
	}
}

if(!function_exists('ldc_cf7_has_posted_data')){
	/**
	 * @return bool
	 */
	function ldc_cf7_has_posted_data($key = ''){
		if(empty($key)){
			return false;
		}
		$data = ldc_cf7_posted_data($key);
		return !is_null($data);
	}
}

if(!function_exists('ldc_cf7_has_shortcode_attr')){
	/**
	 * @return void
	 */
	function ldc_cf7_has_shortcode_attr($name = '', $contact_form = null){
        $contact_form = ldc_cf7_contact_form($contact_form);
		if(is_null($contact_form)){
			return false;
		}
		return !is_null($contact_form->shortcode_attr($name));
	}
}

if(!function_exists('ldc_cf7_hash_exists')){
	/**
	 * Alias for wpcf7_get_contact_form_by_hash.
	 *
	 * Differs from wpcf7_get_contact_form_by_hash in that it will always return an integer.
	 *
	 * @return int
	 */
	function ldc_cf7_hash_exists($hash = ''){
		global $wpdb;
		if(!is_string($hash)){
	        return 0;
	    }
		$hash = trim($hash);
		if(strlen($hash) < 7){
			return 0;
		}
		$like = $wpdb->esc_like($hash) . '%';
		$query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_hash' AND meta_value LIKE %s";
		$query = $wpdb->prepare($query, $like);
		$post_id = $wpdb->get_var($query);
		return (int) $post_id;
	}
}

if(!function_exists('ldc_cf7_insert_update_submission')){
	/**
	 * @return array|WP_Error
	 */
	function ldc_cf7_insert_update_submission($atts = []){
	    $pairs = [
	        'action' => '',
	        'contact_form' => null,
	        'meta_type' => '',
	        'object_id' => 0,
	        'submission' => null,
	        'upload_path' => '',
	    ];
	    $atts = shortcode_atts($pairs, $atts);
	    extract($atts);
	    if(!in_array($action, ['insert', 'update'])){
			$error_msg = sprintf(translate('Invalid parameter(s): %s'), 'action') . '.';
	        return ldc_error($error_msg);
	    }
        $contact_form = ldc_cf7_contact_form($contact_form);
	    if(is_null($contact_form)){
			$error_msg = translate('The requested contact form was not found.', 'contact-form-7');
	        return ldc_error($error_msg);
	    }
	    if(!in_array($meta_type, ['post', 'user'])){
			$error_msg = sprintf(translate('Invalid parameter(s): %s'), 'meta_type') . '.';
	        return ldc_error($error_msg);
	    }
	    if('post' === $meta_type){
	        $post = get_post($object_id);
	        if(empty($post)){
				$error_msg = translate('Invalid post ID.');
	            return ldc_error($error_msg);
	        }
	    } elseif('user' === $meta_type){
	        $user = get_userdata($object_id);
	        if(empty($user)){
				$error_msg = translate('Invalid user ID.');
	            return ldc_error($error_msg);
	        }
	    }
        $submission = ldc_cf7_submission($submission);
	    if(is_null($submission)){
			$error_msg = sprintf(translate('%s (Invalid)'), 'WPCF7_Submission') . '.';
	        return ldc_error($error_msg);
	    }
        if($submission->get_meta('do_not_store')){
            $error_msg = translate('Erase Personal Data') . ': ' . translate('Many plugins may collect or store personal data either in the WordPress database or remotely. Any Erase Personal Data request should delete data from plugins as well.');
	        return ldc_error($error_msg);
        }
        if('post' === $meta_type){
	        $the_post = wp_is_post_revision($object_id);
	        if($the_post){
	            $object_id = $the_post; // Make sure meta is added to the post, not a revision.
	        }
	    }
	    if('insert' === $action){
	        if('post' === $meta_type){
	            ldc_set_cache('cf7_inserted_post_id', $object_id);
	        } elseif('user' === $meta_type){
	            ldc_set_cache('cf7_inserted_user_id', $object_id);
	        }
	    } elseif('update' === $action){
	        if('post' === $meta_type){
	            ldc_set_cache('cf7_updated_post_id', $object_id);
	        } elseif('user' === $meta_type){
	            ldc_set_cache('cf7_updated_user_id', $object_id);
	        }
            if('post' === $meta_type){
	            $postarr = [
	                'ID' => $object_id,
	            ];
	            $field = 'post_content';
	            $content = $submission->get_posted_data($field);
	            if($content){
	                $postarr['post_content'] = $content;
	            }
	            $field = 'post_excerpt';
	            $excerpt = $submission->get_posted_data($field);
	            if($excerpt){
	                $postarr['post_excerpt'] = $excerpt;
	            }
	            $field = 'post_title';
	            $title = $submission->get_posted_data($field);
	            if($title){
	                $postarr['post_title'] = $title;
	            }
				$save_post_revision = !has_filter('wp_save_post_revision_post_has_changed', '__return_true');
				if($save_post_revision){ // Don't filter twice.
					add_filter('wp_save_post_revision_post_has_changed', '__return_true');
				}
	            $post_id = wp_update_post($postarr, true); // Always save a revision.
				if($save_post_revision){
					remove_filter('wp_save_post_revision_post_has_changed', '__return_true');
				}
	            if(is_wp_error($post_id)){
	                ldc_set_cache('cf7_updated_post_id', 0);
	                return $post_id;
	            }
	        } elseif('user' === $meta_type){
	            $user_id = wp_update_user([
	                'ID' => $object_id,
	            ]);
	            if(is_wp_error($user_id)){
	                ldc_set_cache('cf7_updated_user_id', 0);
	                return $user_id;
	            }
	        }
	    }
        $contact_form_data = [
            'demo_mode' => $contact_form->in_demo_mode(),
            'id' => $contact_form->id(),
	        'locale' => $contact_form->locale(),
	        'name' => $contact_form->name(),
	        'skip_mail' => ldc_cf7_skip_mail($contact_form),
            'title' => $contact_form->title(),
        ];
        foreach($contact_form_data as $key => $value){
            $meta_key = 'form_' . $key;
	        update_metadata($meta_type, $object_id, $meta_key, $value);
	    }
        $meta_data = [
            'container_post_id' => $submission->get_meta('container_post_id'),
			'current_user_id' => $submission->get_meta('current_user_id'),
            'remote_ip' => $submission->get_meta('remote_ip'),
			'remote_port' => $submission->get_meta('remote_port'),
            'timestamp' => $submission->get_meta('timestamp'),
			'unit_tag' => $submission->get_meta('unit_tag'),
			'url' => $submission->get_meta('url'),
			'user_agent' => $submission->get_meta('user_agent'),
        ];
        foreach($meta_data as $key => $value){
            $meta_key = 'meta_' . $key;
	        update_metadata($meta_type, $object_id, $meta_key, $value);
	    }
        $submission_data = [
            'response' => $submission->get_response(),
	        'status' => $submission->get_status(),
        ];
        foreach($submission_data as $key => $value){
            $meta_key = 'submission_' . $key;
	        update_metadata($meta_type, $object_id, $meta_key, $value);
	    }
	    $posted_data = $submission->get_posted_data(); // Filtered posted data.
	    foreach($posted_data as $key => $value){
            $meta_key = 'field_' . $key;
	        if(is_array($value)){
	            delete_metadata($meta_type, $object_id, $meta_key);
	            foreach($value as $single){
	                add_metadata($meta_type, $object_id, $meta_key, $single);
	            }
	        } else {
	            update_metadata($meta_type, $object_id, $meta_key, $value);
	        }
	    }
        $uploaded_files = $submission->uploaded_files();
        if(empty($uploaded_files)){
            return ldc_cf7_get_submission($meta_type, $object_id);
        }
        if(empty($upload_path)){
			$upload_path = ldc_download_dir('cf7-uploads');
			if(is_wp_error($upload_path)){
				return $upload_path;
			}
		} else {
	        $upload_path = ldc_check_dir($upload_path);
	        if(is_wp_error($upload_path)){
	            return $upload_path;
	        }
			$upload_path = ldc_check_upload_dir($upload_path);
			if(is_wp_error($upload_path)){
				return $upload_path;
			}
		}
        $fs = ldc_fs_direct();
		if(is_wp_error($fs)){
			return $fs;
		}
        if('post' === $meta_type){
	        $post_id = $object_id;
	    } else {
	        $post_id = 0;
	    }
	    $uploaded_error = new \WP_Error;
	    foreach($uploaded_files as $key => $value){
            $meta_key = 'file_' . $key;
	        foreach((array) $value as $tmp_name){
	            $original_filename = wp_basename($tmp_name);
	            $filename = wp_unique_filename($upload_path, $original_filename);
	            $file = trailingslashit($upload_path) . $filename;
	            if($fs->copy($tmp_name, $file)){
	                $attachment_id = ldc_sideload($file, $post_id, false);
	                if(is_wp_error($attachment_id)){
                        add_metadata($meta_type, $object_id, $meta_key, $attachment_id->get_error_message());
	                    $uploaded_error->merge_from($attachment_id);
	                } else {
	                    add_metadata($meta_type, $object_id, $meta_key, $attachment_id);
	                }
	            } else {
                    $error_msg = translate('The uploaded file could not be moved to %s.');
	                $error_msg = sprintf($error_msg, $file);
	                add_metadata($meta_type, $object_id, $meta_key, $error_msg);
	                $error = ldc_error($error_msg);
	                $uploaded_error->merge_from($error);
	            }
	        }
	        delete_metadata($meta_type, $object_id, $key); // Hash strings.
	    }
	    if($uploaded_error->has_errors()){
            $error_msg = translate('Error:') . ' ' . implode(' ', array_map('ldc_trailingdotit', $uploaded_error->get_error_messages()));
            return ldc_error($error_msg);
	    }
        return ldc_cf7_get_submission($meta_type, $object_id);
	}
}

if(!function_exists('ldc_cf7_invalid_fields')){
	/**
	 * @return array
	 */
	function ldc_cf7_invalid_fields($fields = [], $contact_form = null){
		$contact_form = ldc_cf7_contact_form($contact_form);
		if(is_null($contact_form)){
			return [];
		}
		if(!ldc_is_associative_array($fields)){
			return [];
		}
		$invalid = [];
		$tags = wp_list_pluck($contact_form->scan_form_tags('feature=name-attr'), 'type', 'name');
		foreach($fields as $name => $types){
	        if(!isset($tags[$name])){
	            continue;
	        }
	        if(!in_array($tags[$name], (array) $types)){
	            $invalid[] = $name;
	        }
		}
		return $invalid;
	}
}

if(!function_exists('ldc_cf7_is_false')){
	/**
	 * Opposite of WPCF7_ContactForm::is_true.
	 *
	 * @return bool
	 */
	function ldc_cf7_is_false($name = '', $contact_form = null){
		$pref = ldc_cf7_additional_setting($name, $contact_form);
	    return ldc_is_false($pref);
	}
}

if(!function_exists('ldc_cf7_is_form_tag')){
	/**
	 * @return bool
	 */
	function ldc_cf7_is_form_tag($tag = null){
		return ($tag instanceof \WPCF7_FormTag);
	}
}

if(!function_exists('ldc_cf7_is_submission')){
	/**
	 * @return bool
	 */
	function ldc_cf7_is_submission($submission = null){
		return ($submission instanceof \WPCF7_Submission);
	}
}

if(!function_exists('ldc_cf7_is_true')){
	/**
	 * Alias for WPCF7_ContactForm::is_true.
	 *
	 * @return bool
	 */
	function ldc_cf7_is_true($name = '', $contact_form = null){
		$pref = ldc_cf7_additional_setting($name, $contact_form);
	    return ldc_is_true($pref);
	}
}

if(!function_exists('ldc_cf7_localize')){
	/**
	 * @return WPCF7_ContactForm|WP_Error
	 */
	function ldc_cf7_localize($contact_form = null, $overwrite_messages = false){
		$contact_form = ldc_cf7_contact_form($contact_form);
		if(is_null($contact_form)){
			$message = translate('The requested contact form was not found.', 'contact-form-7');
			return ldc_error($message, [
				'status' => 404,
			]);
		}
		$contact_form_id = $contact_form->id();
		$locale = get_locale();
		if($locale === get_post_meta($contact_form_id, '_locale', true) and !$overwrite_messages){
			return $contact_form;
		}
		$args = [
			'id' => $contact_form_id,
			'locale' => $locale,
		];
		if($overwrite_messages){
	        $messages = wpcf7_messages();
			$args['messages'] = wp_list_pluck($messages, 'default');
		}
		$contact_form = wpcf7_save_contact_form($args);
		if(!$contact_form){
			return ldc_error(translate('There was an error saving the contact form.', 'contact-form-7'), [
				'status' => 500,
			]);
		}
		return $contact_form;
	}
}

if(!function_exists('ldc_cf7_mail')){
	/**
	 * Alias for WPCF7_Submission::mail.
	 *
	 * @return bool
	 */
	function ldc_cf7_mail($contact_form = null){
		$contact_form = ldc_cf7_contact_form($contact_form);
		if(is_null($contact_form)){
			return false;
		}
		$skip_mail = ldc_cf7_skip_mail($contact_form);
		if($skip_mail){
			return true;
		}
		$result = \WPCF7_Mail::send($contact_form->prop('mail'), 'mail');
		if(!$result){
			return false;
		}
		$additional_mail = [];
		if($mail_2 = $contact_form->prop('mail_2') and $mail_2['active']){
			$additional_mail['mail_2'] = $mail_2;
		}
		$additional_mail = apply_filters('wpcf7_additional_mail', $additional_mail, $contact_form);
		foreach($additional_mail as $name => $template){
			\WPCF7_Mail::send($template, $name);
		}
		return true;
	}
}

if(!function_exists('ldc_cf7_message')){
	/**
	 * Alias for WPCF7_ContactForm::filter_message.
	 *
	 * @return string
	 */
	function ldc_cf7_message($message = '', $status = ''){
		$message = wpcf7_mail_replace_tags($message);
		$message = apply_filters('wpcf7_display_message', $message, $status);
		$message = wp_strip_all_tags($message);
		if(!$message){
			$messages = wpcf7_messages();
			switch($status){
				case 'aborted':
					$message = translate('Sending mail has been aborted.', 'contact-form-7');
					break;
				case 'acceptance_missing':
					$message = $messages['accept_terms']['default'];
					break;
				case 'mail_failed':
					$message = $messages['mail_sent_ng']['default'];
					break;
				case 'mail_sent':
					$message = $messages['mail_sent_ok']['default'];
					break;
				case 'spam':
					$message = $messages['spam']['default'];
					break;
				case 'validation_failed':
					$message = $messages['validation_error']['default'];
					break;
				default:
					$message = translate('Unknown action.');
			}
		}
		return $message;
	}
}

if(!function_exists('ldc_cf7_missing_fields')){
	/**
	 * @return array
	 */
	function ldc_cf7_missing_fields($fields = [], $contact_form = null){
		$contact_form = ldc_cf7_contact_form($contact_form);
		if(is_null($contact_form)){
			return [];
		}
	    if(!is_array($fields)){
			return [];
		}
		if(ldc_is_associative_array($fields)){
			$fields = array_keys($fields);
		}
		$missing = [];
		$tags = wp_list_pluck($contact_form->scan_form_tags('feature=name-attr'), 'type', 'name');
		foreach($fields as $name){
			if(isset($tags[$name])){
				continue;
			}
			$missing[] = $name;
		}
		return $missing;
	}
}

if(!function_exists('ldc_cf7_object_number')){
	/**
	 * @return int
	 */
	function ldc_cf7_object_number($contact_form = null){
		$contact_form = ldc_cf7_contact_form($contact_form);
		if(is_null($contact_form)){
			return 0;
		}
		$pattern = '/^wpcf7-f(\d+)-p(\d+)-o(\d+)$/';
		$unit_tag = $contact_form->unit_tag();
		if(preg_match_all($pattern, $unit_tag, $matches)){
			$o = (int) $matches[3][0];
		} else {
			$pattern = '/^wpcf7-f(\d+)-o(\d+)$/';
			if(preg_match_all($pattern, $unit_tag, $matches)){
				$o = (int) $matches[2][0];
			} else {
				$o = 0;
			}
		}
		return $o;
	}
}

if(!function_exists('ldc_cf7_posted_array')){
	/**
	 * @return array
	 */
	function ldc_cf7_posted_array($key = ''){
		if(empty($key)){
			return [];
		}
		$data = (array) ldc_cf7_posted_data($key);
		$data = wpcf7_array_flatten($data);
		return $data;
	}
}

if(!function_exists('ldc_cf7_posted_data')){
	/**
	 * Alias for WPCF7_Submission::get_posted_data.
	 *
	 * Differs from WPCF7_Submission::get_posted_data in that it will avoid filters.
	 *
	 * @return array|null|string
	 */
	function ldc_cf7_posted_data($key = ''){
	    $data = (array) ldc_get_cache('cf7_posted_data', []);
	    if(empty($data) and !empty($_POST)){
            $data = (array) $_POST;
			$data = wp_unslash($data);
	        $data = ldc_cf7_sanitize_posted_data($data);
	        ldc_set_cache('cf7_posted_data', $data);
	    }
		if(empty($key)){
			return $data;
		}
		if(isset($data[$key])){
			return $data[$key];
		}
		return null;
	}
}

if(!function_exists('ldc_cf7_posted_string')){
	/**
	 * Alias for WPCF7_Submission::get_posted_string.
	 *
	 * Differs from WPCF7_Submission::get_posted_string in that it will avoid filters and returns values in a comma separated string.
	 *
	 * @return string
	 */
	function ldc_cf7_posted_string($key = ''){
		$data = ldc_cf7_posted_array($key);
		$data = implode(', ', $data);
		return $data;
	}
}

if(!function_exists('ldc_cf7_sanitize_posted_data')){
	/**
	 * Alias for WPCF7_Submission::sanitize_posted_data.
	 *
	 * @return array|string
	 */
	function ldc_cf7_sanitize_posted_data($value = []){
		if(is_array($value)){
			$value = array_map('ldc_cf7_sanitize_posted_data', $value);
		} elseif(is_string($value)){
			$value = wp_check_invalid_utf8($value);
			$value = wp_kses_no_null($value);
		}
		return $value;
	}
}

if(!function_exists('ldc_cf7_shortcode_attr')){
	/**
	 * Alias for WPCF7_ContactForm::shortcode_attr.
	 *
	 * Differs from WPCF7_ContactForm::shortcode_attr in that it will always return a string.
	 *
	 * @return string
	 */
	function ldc_cf7_shortcode_attr($name = '', $contact_form = null){
		$contact_form = ldc_cf7_contact_form($contact_form);
		if(is_null($contact_form)){
			return '';
		}
		return (string) $contact_form->shortcode_attr($name);
	}
}

if(!function_exists('ldc_cf7_shortcode_attr_is_false')){
	/**
	 * @return void
	 */
	function ldc_cf7_shortcode_attr_is_false($name = '', $contact_form = null){
        $value = ldc_cf7_shortcode_attr($name, $contact_form);
	    return ldc_is_false($value);
	}
}

if(!function_exists('ldc_cf7_shortcode_attr_is_true')){
	/**
	 * @return void
	 */
	function ldc_cf7_shortcode_attr_is_true($name = '', $contact_form = null){
        $value = ldc_cf7_shortcode_attr($name, $contact_form);
	    return ldc_is_true($value);
	}
}

if(!function_exists('ldc_cf7_shortcode_tag_output')){
	/**
	 * @return void
	 */
	function ldc_cf7_shortcode_tag_output($callback = '', $priority = 10, $accepted_args = 1){
        $hook_name = 'cf7_shortcode_tag_output';
        $hook = [
            'accepted_args' => $accepted_args,
            'callback' => $callback, // Closure?
            'hook_name' => $hook_name,
            'priority' => $priority,
        ];
        $md5 = ldc_md5($hook);
        if(ldc_isset_array_cache('cf7_hooks', $md5)){
            return; // Prevent hook being added twice.
        }
        ldc_set_array_cache('cf7_hooks', $md5, $hook);
        ldc_add_filter_once($hook_name, $callback, $priority, $accepted_args);
        ldc_add_filter_once('do_shortcode_tag', 'ldc_cf7_maybe_filter_shortcode_tag_output', 10, 4);
	}
}

if(!function_exists('ldc_cf7_skip_mail')){
	/**
	 * @return bool
	 */
	function ldc_cf7_skip_mail($contact_form = null){
		$contact_form = ldc_cf7_contact_form($contact_form);
		if(is_null($contact_form)){
			return false;
		}
		$skip_mail = ($contact_form->in_demo_mode() or $contact_form->is_true('skip_mail') or !empty($contact_form->skip_mail));
		$skip_mail = apply_filters('wpcf7_skip_mail', $skip_mail, $contact_form);
		return (bool) $skip_mail;
	}
}

if(!function_exists('ldc_cf7_submission')){
	/**
	 * Alias for WPCF7_Submission::get_instance.
	 *
	 * Returns the current submission if the specified setting has a falsey value.
	 *
	 * @return null|WPCF7_Submission
	 */
	function ldc_cf7_submission($submission = null){
		$current_submission = \WPCF7_Submission::get_instance();
		if(empty($submission)){ // 0, false, null and other PHP falsey values.
			return $current_submission;
		}
		if(ldc_cf7_is_submission($submission)){
			return $submission;
		}
		return null;
	}
}

if(!function_exists('ldc_is_cf7')){
	/**
	 * @return bool
	 */
	function ldc_is_cf7($contact_form = null){
		return ($contact_form instanceof \WPCF7_ContactForm);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_cf7_maybe_filter_shortcode_tag_output')){
	/**
	 * This function MUST be called inside the 'do_shortcode_tag' filter hook.
	 *
	 * @return string
	 */
	function ldc_cf7_maybe_filter_shortcode_tag_output($output, $tag, $attr, $m){
		if(!doing_filter('do_shortcode_tag')){ // Too early or too late.
	        return $output;
	    }
		if('contact-form-7' !== $tag){
			return $output;
		}
		$contact_form = ldc_cf7_contact_form();
		if(is_null($contact_form)){
			return ldc_cf7_error();
		}
		if(ldc_is_plugin_active('bb-plugin/fl-builder.php')){
			if(\FLBuilderModel::is_builder_active()){
				$post_type = get_post_type();
				$post_type_object = get_post_type_object($post_type);
				$post_type_name = $post_type_object->labels->singular_name;
				$branding = \FLBuilderModel::get_branding();
				$message = sprintf(_x('%1$s is currently active for this %2$s.', '%1$s branded builder name. %2$s post type name.', 'fl-builder'), $branding, strtolower($post_type_name));
				return ldc_cf7_error($message);
			}
		}
		$html = ldc_str_get_html($output); // Test for simple_html_dom.
		if(is_wp_error($html)){
			$message = $error->get_error_message();
			return ldc_cf7_error($message);
		}
		$errors = new \WP_Error;
	    do_action_ref_array('cf7_shortcode_tag_errors', [&$errors, $contact_form, $attr]);
	    if($errors->has_errors()){
			$messages = $errors->get_error_messages();
			foreach($messages as $index => $message){
				$messages[$index] = rtrim($message, '.') . '.';
			}
			$message = implode(' ', $messages);
			return ldc_cf7_error($message);
	    }
		$comments = $html->find('comment');
		foreach($comments as $comment){
			$comment->remove();
		}
	    $tags = $contact_form->scan_form_tags('feature=name-attr');
	    foreach($tags as $tag){
			$wrapper = $html->find('.wpcf7-form-control-wrap[data-name="' . $tag->name . '"]', 0);
			if(is_null($wrapper)){
				continue;
			}
	        $outertext = $wrapper->outertext;
            $outertext = apply_filters('cf7_form_tag_output', $outertext, $tag, $contact_form); // *
	        $outertext = apply_filters('cf7_form_tag_' . $tag->basetype . '_output', $outertext, $tag, $contact_form);
			$wrapper->outertext = $outertext;
		}
	    $tags = $contact_form->scan_form_tags('type=submit');
		foreach($tags as $idx => $tag){
			$submit = $html->find('.wpcf7-form-control[type="submit"]', $idx);
			if(is_null($submit)){
				continue;
			}
	        $outertext = $submit->outertext;
			$outertext = apply_filters('cf7_form_tag_submit_output', $outertext, $tag, $contact_form);
			$submit->outertext = $outertext;
		}
	    $output = $html->save();
		$output = apply_filters('cf7_shortcode_tag_output', $output, $contact_form, $attr);
		return $output;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Cache
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_set_cookie')){
    /**
     * @return void
     */
    function ldc_set_cookie($name = '', $value = '', $expires = 0){
        if(!$name or !$value or !$expires){
            return;
        }
        $secure = ('https' === parse_url(home_url(), PHP_URL_SCHEME));
        $value = wp_unslash($value);
        $value = esc_attr($value);
        setcookie($name, $value, $expires, COOKIEPATH, COOKIE_DOMAIN, $secure);
    }
}

if(!function_exists('ldc_unset_cookie')){
    /**
     * @return void
     */
    function ldc_unset_cookie($name = ''){
        if(!$name){
            return;
        }
        if(!isset($_COOKIE[$name])){
            return;
        }
        $past = time() - YEAR_IN_SECONDS;
        setcookie($name, ' ', $past, COOKIEPATH, COOKIE_DOMAIN);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Date and time
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_current_time')){
	/**
	 * Alias for current_time.
	 *
	 * Differs from current_time in that it will always return a string.
	 *
	 * If 'offset_or_tz' parameter is an empty string, the output is adjusted with the GMT offset in the WordPress option.
	 *
	 * @return string
	 */
	function ldc_current_time($type = 'U', $offset_or_tz = ''){
		if('timestamp' === $type){
			$type = 'U';
		}
		if('mysql' === $type){
			$type = 'Y-m-d H:i:s';
		}
		$timezone = $offset_or_tz ? ldc_timezone($offset_or_tz) : wp_timezone();
		$datetime = new \DateTime('now', $timezone);
		return $datetime->format($type);
	}
}

if(!function_exists('ldc_date_convert')){
	/**
	 * @return string
	 */
	function ldc_date_convert($string = '', $fromtz = '', $totz = '', $format = 'Y-m-d H:i:s'){
		$datetime = date_create($string, ldc_timezone($fromtz));
		if(false === $datetime){
			return gmdate($format, 0);
		}
		return $datetime->setTimezone(ldc_timezone($totz))->format($format);
	}
}

if(!function_exists('ldc_is_mysql_date')){
	/**
	 * @return bool|string
	 */
	function ldc_is_mysql_date($subject = ''){
		$pattern = '/^\d{4}\-(0[1-9]|1[0-2])\-(0[1-9]|[12]\d|3[01]) ([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/';
		if(!preg_match($pattern, $subject)){
			return false;
		}
		return $subject;
	}
}

if(!function_exists('ldc_offset_or_tz')){
	/**
	 * @param string $offset_or_tz Optional. Default GMT offset or timezone string. Must be either a valid offset (-12 to 14) or a valid timezone string.
	 *
	 * @return array
	 */
	function ldc_offset_or_tz($offset_or_tz = ''){
		if(is_numeric($offset_or_tz)){
			return [
				'gmt_offset' => $offset_or_tz,
				'timezone_string' => '',
			];
		}
		// Map UTC+- timezones to gmt_offsets and set timezone_string to empty.
		if(preg_match('/^UTC[+-]/', $offset_or_tz)){
			return [
				'gmt_offset' => (int) preg_replace('/UTC\+?/', '', $offset_or_tz),
				'timezone_string' => '',
			];
		}
		if(in_array($offset_or_tz, timezone_identifiers_list())){
			return [
				'gmt_offset' => 0,
				'timezone_string' => $offset_or_tz,
			];
		}
		return [
			'gmt_offset' => 0,
			'timezone_string' => 'UTC',
		];
	}
}

if(!function_exists('ldc_timezone')){
	/**
	 * @return DateTimeZone
	 */
	function ldc_timezone($offset_or_tz = ''){
		return new \DateTimeZone(ldc_timezone_string($offset_or_tz));
	}
}

if(!function_exists('ldc_timezone_string')){
	/**
	 * @return string
	 */
	function ldc_timezone_string($offset_or_tz = ''){
		$offset_or_tz = ldc_offset_or_tz($offset_or_tz);
		$timezone_string = $offset_or_tz['timezone_string'];
		if($timezone_string){
			return $timezone_string;
		}
		$offset = (float) $offset_or_tz['gmt_offset'];
		$hours = (int) $offset;
		$minutes = ($offset - $hours);
		$sign = (($offset < 0) ? '-' : '+');
		$abs_hour = abs($hours);
		$abs_mins = abs($minutes * 60);
		$tz_offset = sprintf('%s%02d:%02d', $sign, $abs_hour, $abs_mins);
		return $tz_offset;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Debug
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_debug_backtrace')){
    /**
     * @return array|WP_error
     */
    function ldc_debug_backtrace($index = 0, $options = 2){ // options: 0: provide args and ignore object, 1: provide args and object, 2: ignore args and object, 3: ignore args and provide object.
        $index = ldc_absint($index) + 1;
        $limit = $index + 1;
        $debug_backtrace = debug_backtrace($options, $limit);
        $debug_count = count($debug_backtrace);
        if($limit > $debug_count){
            $error_msg = translate('%1$s must be less than or equal to %2$d');
            $error_msg = sprintf($error_msg, '"index"', $debug_count) . '.';
            return ldc_error($error_msg);
        }
        $atts = $debug_backtrace[$index];
        $pairs = [
            'args' => [],
            'class' => '',
            'file' => '',
            'function' => '',
            'line' => 0,
            'object' => null,
            'type' => '',
        ];
        return shortcode_atts($pairs, $atts);
    }
}

if(!function_exists('ldc_debug_context')){
    /**
     * @return array|WP_error
     */
    function ldc_debug_context($index = 0){
        $index = ldc_absint($index) + 1;
        $debug_backtrace = ldc_debug_backtrace($index);
        if(is_wp_error($debug_backtrace)){
            return $debug_backtrace;
        }
        $context = [
            'file' => $debug_backtrace['file'],
            'name' => '',
            'namespace_name' => '',
            'reflector' => '',
            'short_name' => '',
            'type' => '',
        ];
        if($debug_backtrace['class']){
            $context = ldc_class_context($debug_backtrace['class']);
            if(is_wp_error($context)){
                return $context;
            }
            $context['type'] = 'class';
            return $context;
        }
        if($debug_backtrace['function']){
            if('{closure}' === $debug_backtrace['function']){
                $context['type'] = 'closure';
                return $context;
            }
            $context = ldc_function_context($debug_backtrace['function']);
            if(is_wp_error($context)){
                return $context;
            }
            $context['type'] = 'function';
            return $context;
        }
        return $context;
    }
}

if(!function_exists('ldc_caller_file')){
    /**
     * @return string|WP_error
     */
    function ldc_caller_file($index = 0){
        $index = ldc_absint($index) + 1;
        $context = ldc_debug_context($index);
        if(is_wp_error($context)){
            return $context;
        }
        if('class' === $context['type'] and !$context['file']){
            $error_msg = 'The "%s" class is defined in the PHP core or in a PHP extension.';
            $error_msg = sprintf($error_msg, $context['name']);
            return $error_msg;
        }
        if('function' === $context['type'] and !$context['file']){
            $error_msg = 'The "%s" function is defined in the PHP core or in a PHP extension.';
            $error_msg = sprintf($error_msg, $context['name']);
            return $error_msg;
        }
        if(!$context['file']){
            $error_msg = translate('File does not exist! Please double check the name and try again.');
            $error_msg = ldc_first_p($error_msg);
            return ldc_error($error_msg);
        }
        return $context['file'];
    }
}

if(!function_exists('ldc_class_context')){
    /**
     * @return array|WP_Error
     */
    function ldc_class_context($class = ''){
        if(!$class){
            $error_msg = translate('The "%s" argument must be a non-empty string.');
            $error_msg = sprintf($error_msg, 'class');
            return ldc_error($error_msg);
        }
        if(!class_exists($class)){
            $error_msg = translate('Invalid parameter(s): %s');
            $error_msg = sprintf($error_msg, 'class') . '.';
            return ldc_error($error_msg);
        }
        $reflector = new \ReflectionClass($class);
        return ldc_reflector_context($reflector);
    }
}

if(!function_exists('ldc_function_context')){
    /**
     * @return array|WP_Error
     */
    function ldc_function_context($function = ''){
        if(empty($function)){
            $error_msg = translate('The "%s" argument must be a non-empty string.');
            $error_msg = sprintf($error_msg, 'function');
            return ldc_error($error_msg);
        }
        if(!function_exists($function)){
            $error_msg = translate('Invalid parameter(s): %s');
            $error_msg = sprintf($error_msg, 'function') . '.';
            return ldc_error($error_msg);
        }
        $reflector = new \ReflectionFunction($function);
        return ldc_reflector_context($reflector);
    }
}

if(!function_exists('ldc_reflector_context')){
    /**
     * @return array|WP_Error
     */
    function ldc_reflector_context($reflector = null){
        if(!$reflector instanceof \Reflector){
            $error_msg = translate('Invalid object type.');
            return ldc_error($error_msg);
        }
        $context = [
            'file' => $reflector->getFileName(),
            'name' => $reflector->getName(),
            'namespace_name' => $reflector->getNamespaceName(),
            'reflector' => $reflector,
            'short_name' => $reflector->getShortName(),
        ];
        return $context;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Dependencies
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_admin_enqueue')){
    /**
     * @return string|WP_Error
     */
    function ldc_admin_enqueue($handle = '', $src = '', $deps = [], $ver = false, $args_media = true){
        return ldc_context_enqueue('admin', $handle, $src, $deps, $ver, $args_media);
    }
}

if(!function_exists('ldc_enqueue')){
    /**
     * @return string|WP_Error
     */
    function ldc_enqueue($handle = '', $src = '', $deps = [], $ver = false, $args_media = true){
        return ldc_context_enqueue('wp', $handle, $src, $deps, $ver, $args_media);
    }
}

if(!function_exists('ldc_enqueue_ace')){
    /**
     * This function MUST be called inside the 'admin_enqueue_scripts', 'login_enqueue_scripts' or 'wp_enqueue_scripts' action hooks.
     *
     * @return string|WP_Error
     */
    function ldc_enqueue_ace($deps = [], $version = '1.36.2'){
        $base_path = 'https://cdn.jsdelivr.net/npm/ace-builds@' . $version . '/src-min';
        $handle = ldc_enqueue_asset('ace', $base_path . '/ace.js', $deps, $version);
        if(is_wp_error($handle)){
            return $handle;
        }
		$handle = ldc_enqueue_asset('ace-language-tools', $base_path . '/ext-language_tools.min.js', ['ace'], $version);
        if(is_wp_error($handle)){
            return $handle;
        }
        $data = "if(!_.isUndefined(ace)){ ace.config.set('basePath', '$base_path'); ace.require('ace/ext/language_tools'); }";
        wp_add_inline_script('ace', $data);
        return $handle;
    }
}

if(!function_exists('ldc_enqueue_inputmask')){
    /**
     * This function MUST be called inside the 'admin_enqueue_scripts', 'login_enqueue_scripts' or 'wp_enqueue_scripts' action hooks.
     *
     * @return string|WP_Error
     */
    function ldc_enqueue_inputmask($deps = [], $version = '5.0.9'){
        $base_path = 'https://cdn.jsdelivr.net/npm/inputmask@' . $version . '/dist';
        $handle = ldc_enqueue_asset('inputmask', $base_path . '/query.inputmask.min.js', $deps, $version);
        return $handle;
    }
}

if(!function_exists('ldc_local_admin_enqueue')){
    /**
     * @return string|WP_Error
     */
    function ldc_local_admin_enqueue($handle = '', $file = '', $deps = [], $args_media = true){
        if(!file_exists($file)){
            $error_msg = translate('File does not exist! Please double check the name and try again.');
            return ldc_error($error_msg);
        }
        $src = ldc_dir_to_url($file);
        $ver = filemtime($file);
        return ldc_admin_enqueue($handle, $src, $deps, $ver, $args_media);
    }
}

if(!function_exists('ldc_local_enqueue')){
    /**
     * @return string|WP_Error
     */
    function ldc_local_enqueue($handle = '', $file = '', $deps = [], $args_media = true){
        if(!file_exists($file)){
            $error_msg = translate('File does not exist! Please double check the name and try again.');
            return ldc_error($error_msg);
        }
        $src = ldc_dir_to_url($file);
        $ver = filemtime($file);
        return ldc_enqueue($handle, $src, $deps, $ver, $args_media);
    }
}

if(!function_exists('ldc_local_login_enqueue')){
    /**
     * @return string|WP_Error
     */
    function ldc_local_login_enqueue($handle = '', $file = '', $deps = [], $args_media = true){
        if(!file_exists($file)){
            $error_msg = translate('File does not exist! Please double check the name and try again.');
            return ldc_error($error_msg);
        }
        $src = ldc_dir_to_url($file);
        $ver = filemtime($file);
        return ldc_login_enqueue($handle, $src, $deps, $ver, $args_media);
    }
}

if(!function_exists('ldc_local_omni_enqueue')){
    /**
     * @return string|WP_Error
     */
    function ldc_local_omni_enqueue($handle = '', $file = '', $deps = [], $args_media = true){
        if(!file_exists($file)){
            $error_msg = translate('File does not exist! Please double check the name and try again.');
            return ldc_error($error_msg);
        }
        $src = ldc_dir_to_url($file);
        $ver = filemtime($file);
        return ldc_omni_enqueue($handle, $src, $deps, $ver, $args_media);
    }
}

if(!function_exists('ldc_localize')){
    /**
     * @return string
     */
    function ldc_localize($data = []){
        if(is_string($data)){
            $data = html_entity_decode($data, ENT_QUOTES, 'UTF-8');
        } else {
            foreach((array) $data as $key => $value){
                if(!is_scalar($value)){
                    continue;
                }
                $data[$key] = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
            }
        }
        return wp_json_encode($data);
    }
}

if(!function_exists('ldc_login_enqueue')){
    /**
     * @return string|WP_Error
     */
    function ldc_login_enqueue($handle = '', $src = '', $deps = [], $ver = false, $args_media = true){
        return ldc_context_enqueue('login', $handle, $src, $deps, $ver, $args_media);
    }
}

if(!function_exists('ldc_omni_enqueue')){
    /**
     * @return string|WP_Error
     */
    function ldc_omni_enqueue($handle = '', $src = '', $deps = [], $ver = false, $args_media = true){
        if(!doing_action('admin_enqueue_scripts') and !doing_action('login_enqueue_scripts') and !doing_action('wp_enqueue_scripts')){
            if(did_action('admin_enqueue_scripts') or did_action('login_enqueue_scripts') or did_action('wp_enqueue_scripts')){
                $error_msg = translate('Function %1$s was called <strong>incorrectly</strong>. %2$s %3$s');
                $error_msg = sprintf($error_msg, __FUNCTION__, '', '');
                $error_msg = trim($error_msg);
                return ldc_error($error_msg);
            }
        }
        ldc_context_enqueue('admin', $handle, $src, $deps, $ver, $args_media);
        ldc_context_enqueue('login', $handle, $src, $deps, $ver, $args_media);
        ldc_context_enqueue('wp', $handle, $src, $deps, $ver, $args_media);
        return $handle;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_maybe_enqueue_admin_assets')){
    /**
	 * This function MUST be called inside the 'admin_enqueue_scripts' action hook.
	 *
	 * @return void
	 */
    function ldc_maybe_enqueue_admin_assets(){
        if(!doing_action('admin_enqueue_scripts')){ // Too early or too late.
	        return;
	    }
        $assets = (array) ldc_get_cache('admin_assets', []);
        if(!$assets){
            return;
        }
        foreach($assets as $md5 => $asset){
            ldc_enqueue_asset($asset['handle'], $asset['src'], $asset['deps'], $asset['ver'], $asset['args_media']);
        }
    }
}

if(!function_exists('ldc_maybe_enqueue_wp_assets')){
    /**
	 * This function MUST be called inside the 'wp_enqueue_scripts' action hook.
	 *
	 * @return void
	 */
    function ldc_maybe_enqueue_wp_assets(){
        if(!doing_action('wp_enqueue_scripts')){ // Too early or too late.
	        return;
	    }
        $assets = (array) ldc_get_cache('wp_assets', []);
        if(!$assets){
            return;
        }
        foreach($assets as $md5 => $asset){
            ldc_enqueue_asset($asset['handle'], $asset['src'], $asset['deps'], $asset['ver'], $asset['args_media']);
        }
    }
}

if(!function_exists('ldc_maybe_enqueue_login_assets')){
    /**
	 * This function MUST be called inside the 'login_enqueue_scripts' action hook.
	 *
	 * @return void
	 */
    function ldc_maybe_enqueue_login_assets(){
        if(!doing_action('login_enqueue_scripts')){ // Too early or too late.
	        return;
	    }
        $assets = (array) ldc_get_cache('login_assets', []);
        if(!$assets){
            return;
        }
        foreach($assets as $md5 => $asset){
            ldc_enqueue_asset($asset['handle'], $asset['src'], $asset['deps'], $asset['ver'], $asset['args_media']);
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Errors
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_exit_with_error')){
	/**
	 * @return void
	 */
	function ldc_exit_with_error($message = '', $title = '', $args = []){
		if(is_wp_error($message)){
			$message = $message->get_error_message();
			if($title and !$args){
				$args = $title;
				$title = '';
			}
		}
		if(!$message){
			$message = translate('Error');
		}
        if(is_int($args)){
            $args = [
                'response' => $args,
            ];
        }
        if(is_int($title)){
            if(!isset($args['response'])){
                $args['response'] = $title;
            }
            $title = get_status_header_desc($title);
        }
		if(!$title){
			$title = translate('Something went wrong.');
		}
        $html = '<h1>' . $title . '</h1>';
        $html .= '<p>';
        $html .= $message;
        $html .= '</p>';
        $html .= '<p>';
        $referer = wp_get_referer();
        if($referer){
            $back = translate('Go back');
        } else {
            $back = ldc_go_to(get_bloginfo('title', 'display'));
            $referer = home_url('/');
        }
        $html_link = sprintf('<a href="%s">%s</a>', esc_url($referer), $back);
        $html .= $html_link;
        $html .= '</p>';
        wp_die($html, $title, $args);
	}
}

if(!function_exists('ldc_seems_error')){
	/**
	 * @return bool|WP_Error
	 */
	function ldc_seems_error($data = []){
		if(!ldc_array_keys_exists(['code', 'data', 'message'], $data)){
			return false;
		}
		$count = count($data);
		if((4 === $count and !array_key_exists('additional_errors', $data)) or 3 !== $count){
			return false;
		}
		if(!$data['code']){
			$data['code'] = ldc_str_prefix('error');
		}
		if(!$data['message']){
			$data['message'] = translate('Error');
		}
	    $error = new \WP_Error($data['code'], $data['message'], $data['data']);
	    if(!empty($data['additional_errors'])){
	        foreach($data['additional_errors'] as $err){
	            if(!ldc_array_keys_exists(['code', 'data', 'message'], $err)){
	        		continue;
	        	}
	            $error->add($err['code'], $err['message'], $err['data']);
	        }
	    }
		return $error;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Filesystem
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_basename')){
	/**
	 * @return string
	 */
	function ldc_basename($path = '', $suffix = ''){
		return wp_basename(ldc_remove_query($path), $suffix);
	}
}

if(!function_exists('ldc_check_dir')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_check_dir($dir = ''){
		if(!empty($dir) and (!@is_dir($dir) or !wp_is_writable($dir))){
			$error_msg =  translate('Destination directory for file streaming does not exist or is not writable.');
			return ldc_error($error_msg);
		}
		return $dir;
	}
}

if(!function_exists('ldc_check_upload_dir')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_check_upload_dir($path = ''){
		$path = wp_normalize_path($path);
		$upload_dir = wp_get_upload_dir();
		if($upload_dir['error']){
			return ldc_error($upload_dir['error']);
		}
		$basedir = wp_normalize_path($upload_dir['basedir']);
		if(!ldc_str_starts_with($path, $basedir)){
			$error_msg = sprintf(translate('Unable to locate needed folder (%s).'), translate('The uploads directory'));
			return ldc_error($error_msg);
		}
		return $path;
	}
}

if(!function_exists('ldc_check_upload_size')){
	/**
	 * Alias for WP_REST_Attachments_Controller::check_upload_size.
	 *
	 * @return bool|WP_Error
	 */
	function ldc_check_upload_size($file_size = 0){
		if(!is_multisite()){
			return true;
		}
		if(get_site_option('upload_space_check_disabled')){
			return true;
		}
		$space_left = get_upload_space_available();
		if($space_left < $file_size){
			$error_msg = sprintf(translate('Not enough space to upload. %s KB needed.'), number_format(($file_size - $space_left) / KB_IN_BYTES));
			return ldc_error($error_msg);
		}
		if($file_size > (KB_IN_BYTES * get_site_option('fileupload_maxk', 1500))){
			$error_msg = sprintf(translate('This file is too big. Files must be less than %s KB in size.'), get_site_option('fileupload_maxk', 1500));
			return ldc_error($error_msg);
		}
		if(!function_exists('upload_is_user_over_quota')){
			require_once(ABSPATH . 'wp-admin/includes/ms.php'); // Include multisite admin functions to get access to upload_is_user_over_quota().
		}
		if(upload_is_user_over_quota(false)){
			$error_msg = translate('You have used your space quota. Please delete files before uploading.');
			return ldc_error($error_msg);
		}
		return true;
	}
}

if(!function_exists('ldc_dir_to_url')){
	/**
	 * @return string
	 */
	function ldc_dir_to_url($path = ''){
		return str_replace(wp_normalize_path(ABSPATH), site_url('/'), wp_normalize_path($path));
	}
}

if(!function_exists('ldc_download_dir')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_download_dir($subdir = ''){
        $dir = 'downloads';
        $subdir = ltrim($subdir, '/');
	    $subdir = untrailingslashit($subdir);
	    if($subdir){
	        $dir .= '/' . $subdir;
	    }
		return ldc_dir($dir);
	}
}

if(!function_exists('ldc_fs_direct')){
	/**
	 * @return WP_Filesystem_Base|WP_Error
	 */
	function ldc_fs_direct(){
		global $wp_filesystem;
		if(!function_exists('get_filesystem_method')){
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}
		if('direct' !== get_filesystem_method()){
			return ldc_error(translate('Could not access filesystem.')); // TODO: determine the best way to support other filesystem methods.
		}
		if($wp_filesystem instanceof \WP_Filesystem_Base){
			return $wp_filesystem;
		}
		if(!WP_Filesystem()){
			return ldc_error(translate('Filesystem error.'));
		}
		return $wp_filesystem;
	}
}

if(!function_exists('ldc_get_memory_size')){
	/**
	 * @return int
	 */
	function ldc_get_memory_size(){
		if(!function_exists('exec')){
			$current_limit = ini_get('memory_limit');
			$current_limit_int = wp_convert_hr_to_bytes($current_limit);
			return $current_limit_int;
		}
		exec('free -b', $output);
		$output = sanitize_text_field($output[1]);
		$output = explode(' ', $output);
		return (int) $output[1];
	}
}

if(!function_exists('ldc_handle_file')){
	/**
	 * This function works only if the file was uploaded via HTTP POST.
	 *
	 * @return array|WP_Error
	 */
	function ldc_handle_file($file = [], $dir = '', $mimes = null){
		if(empty($file)){
			$error_msg = translate('No data supplied.');
			return ldc_error($error_msg);
		}
		if(!is_array($file)){
			if(!is_scalar($file)){
				$error_msg = translate('Invalid data provided.');
				return ldc_error($error_msg);
			}
			if(empty($_FILES[$file])){
				$error_msg = translate('File does not exist! Please double check the name and try again.');
				return ldc_error($error_msg);
			}
			$file = $_FILES[$file];
		}
		$keys = ['error', 'name', 'size', 'tmp_name', 'type'];
		foreach($keys as $key){
			$file[$key] = isset($file[$key]) ? (array) $file[$key] : [];
		}
		$count = count($file['tmp_name']);
		$files = [];
		for($i = 0; $i < $count; $i ++){
			$files[$i] = [];
			foreach($keys as $key){
				if(isset($file[$key][$i])){
					$files[$i][$key] = $file[$key][$i];
				}
			}
		}
		$uploaded_files = [];
		foreach($files as $index => $file){
			$uploaded_files[$index] = ldc_handle_upload($file, $dir, $mimes);
		}
		return $uploaded_files;
	}
}

if(!function_exists('ldc_handle_files')){
	/**
	 * This function works only if the files were uploaded via HTTP POST.
	 *
	 * @return array|WP_Error
	 */
	function ldc_handle_files($files = [], $dir = '', $mimes = null){
		if(empty($files)){
			if(empty($_FILES)){
				$error_msg = translate('No data supplied.');
				return ldc_error($error_msg);
			}
			$files = $_FILES;
		}
		$uploaded_files = [];
		foreach($files as $key => $file){
			$uploaded_files[$key] = ldc_handle_file($file, $dir, $mimes);
		}
		return $uploaded_files;
	}
}

if(!function_exists('ldc_handle_upload')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_handle_upload($file = [], $dir = '', $mimes = null){
	    $dir = ldc_check_dir($dir);
	    if(is_wp_error($dir)){
	        return $dir;
	    }
		if(empty($file)){
			$error_msg = translate('No data supplied.');
			return ldc_error($error_msg);
		}
		$file = shortcode_atts([
			'error' => 0,
			'name' => '',
			'size' => 0,
			'tmp_name' => '',
			'type' => '',
		], $file);
		$uploaded_file = ldc_test_uploaded_file($file['tmp_name']);
		if(is_wp_error($uploaded_file)){
			return $uploaded_file;
		}
		$error = ldc_test_error($file['error']);
		if(is_wp_error($error)){
			return $error;
		}
		$size = ldc_test_size($file['size']);
		if(is_wp_error($size)){
			return $size;
		}
		$filename = ldc_test_type($file['tmp_name'], $file['name'], $mimes);
		if(is_wp_error($filename)){
			return $filename;
		}
		$size_check = ldc_check_upload_size($file['size']);
		if(is_wp_error($size_check)){
			return $size_check;
		}
		if($dir){
			$upload_dir = ldc_check_upload_dir($dir);
			if(is_wp_error($upload_dir)){
				return $upload_dir;
			}
		} else {
			$upload_dir = wp_upload_dir();
			if($upload_dir['error']){
				return ldc_error($upload_dir['error']);
			}
			$dir = $upload_dir['path'];
		}
		$filename = wp_unique_filename($dir, $filename);
		$new_file = path_join($dir, $filename);
		$move_new_file = @move_uploaded_file($file['tmp_name'], $new_file);
		if(false === $move_new_file){
			$error_path = str_replace(ABSPATH, '', $dir);
			$error_msg = sprintf(translate('The uploaded file could not be moved to %s.'), $error_path);
			return ldc_error($error_msg);
		}
		$stat = stat(dirname($new_file));
		$perms = $stat['mode'] & 0000666;
		chmod($new_file, $perms); // Set correct file permissions.
		if(is_multisite()){
			clean_dirsize_cache($new_file);
		}
		return $new_file;
	}
}

if(!function_exists('ldc_is_extension_allowed')){
	/**
	 * @return bool
	 */
	function ldc_is_extension_allowed($extension = ''){
		foreach(wp_get_mime_types() as $exts => $mime){
			if(preg_match('!^(' . $exts . ')$!i', $extension)){
				return true;
			}
		}
		return false;
	}
}

if(!function_exists('ldc_mkdir')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_mkdir($subdir = ''){
		$upload_dir = wp_get_upload_dir();
		if($upload_dir['error']){
			return ldc_error($upload_dir['error']);
		}
		$path = $upload_dir['basedir'];
	    $subdir = ltrim($subdir, '/');
	    $subdir = untrailingslashit($subdir);
	    if($subdir){
	        $path .= '/' . $subdir;
	    }
		return ldc_mkdir_p($path);
	}
}

if(!function_exists('ldc_mkdir_p')){
	/**
	 * Alias for wp_mkdir_p.
	 *
	 * Differs from wp_mkdir_p in that it will return an error if path wasn't created.
	 *
	 * @return string|WP_Error
	 */
	function ldc_mkdir_p($target = ''){
		$key = md5($target);
		if(ldc_isset_array_cache('mkdir_p', $key)){
			return (string) ldc_get_array_cache('mkdir_p', $key, '');
		}
		if(!wp_mkdir_p($target)){
			return ldc_error(translate('Could not create directory.'));
		}
		if(!wp_is_writable($target)){
			return ldc_error(translate('Destination directory for file streaming does not exist or is not writable.'));
		}
		ldc_set_array_cache('mkdir_p', $key, $target);
		return $target;
	}
}

if(!function_exists('ldc_read_file_chunk')){
	/**
	 * @return string
	 */
	function ldc_read_file_chunk($handle = null, $chunk_size = 0, $chunk_lenght = 0){
		$giant_chunk = '';
		if(is_resource($handle) and $chunk_size){
			$byte_count = 0;
			if(!$chunk_lenght){
				$chunk_lenght = 8 * KB_IN_BYTES;
			}
			while(!feof($handle)){
				$chunk = fread($handle, $chunk_lenght);
				$byte_count += strlen($chunk);
				$giant_chunk .= $chunk;
				if($byte_count >= $chunk_size){
					return $giant_chunk;
				}
			}
		}
		return $giant_chunk;
	}
}

if(!function_exists('ldc_remove_query')){
    /**
     * @return string
     */
    function ldc_remove_query($path = ''){
        return preg_replace('/\?.*/', '', $path); // Fix file filename for query strings.
    }
}

if(!function_exists('ldc_sideload')){
	/**
	 * @return int|WP_Error
	 */
	function ldc_sideload($file = '', $post_id = 0, $generate_attachment_metadata = true){
		if(!@is_file($file)){
			$error_msg =  translate('File doesn&#8217;t exist?');
			return ldc_error($error_msg, $file);
		}
	    $filename = wp_basename($file);
	    $filename = ldc_test_type($file, $filename);
		if(is_wp_error($filename)){
			return $filename;
		}
	    $filetype_and_ext = wp_check_filetype($filename);
	    $attachment_id = wp_insert_attachment([
	        'guid' => ldc_dir_to_url($file),
	        'post_mime_type' => $filetype_and_ext['type'],
	        'post_status' => 'inherit',
	        'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
	    ], $file, $post_id, true);
	    if($generate_attachment_metadata){
	        ldc_maybe_generate_attachment_metadata($attachment_id);
	    }
	    return $attachment_id;
	}
}

if(!function_exists('ldc_test_error')){
	/**
	 * @return bool|WP_Error
	 */
	function ldc_test_error($error = 0){ // A successful upload will pass this test.
		$upload_error_strings = [
			false,
			sprintf(translate('The uploaded file exceeds the %1$s directive in %2$s.'), 'upload_max_filesize', 'php.ini'),
			sprintf(translate('The uploaded file exceeds the %s directive that was specified in the HTML form.'), 'MAX_FILE_SIZE'),
			translate('The uploaded file was only partially uploaded.'),
			translate('No file was uploaded.'),
			'',
			translate('Missing a temporary folder.'),
			translate('Failed to write file to disk.'),
			translate('File upload stopped by extension.'),
		]; // Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
		if($error > 0){
			if(empty($upload_error_strings[$error])){
				$error_msg = translate('Something went wrong.');
			} else {
				$error_msg = $upload_error_strings[$error];
			}
			return ldc_error($error_msg);
		}
		return true;
	}
}

if(!function_exists('ldc_test_size')){
	/**
	 * @return bool|WP_Error
	 */
	function ldc_test_size($file_size = 0){ // A non-empty file will pass this test.
		if(0 === $file_size){
			if(is_multisite()){
				$error_msg = translate('File is empty. Please upload something more substantial.');
			} else {
				$error_msg = sprintf(translate('File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your %1$s file or by %2$s being defined as smaller than %3$s in %1$s.'), 'php.ini', 'post_max_size', 'upload_max_filesize');
			}
			return ldc_error($error_msg);
		}
		return true;
	}
}

if(!function_exists('ldc_test_type')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_test_type($tmp_name = '', $name = '', $mimes = null){ // A correct MIME type will pass this test.
		$wp_filetype = wp_check_filetype_and_ext($tmp_name, $name, $mimes);
		$ext = empty($wp_filetype['ext']) ? '' : $wp_filetype['ext'];
		$type = empty($wp_filetype['type']) ? '' : $wp_filetype['type'];
		$proper_filename = empty($wp_filetype['proper_filename']) ? '' : $wp_filetype['proper_filename']; // Check to see if wp_check_filetype_and_ext() determined the filename was incorrect.
		if($proper_filename){
			$name = $proper_filename;
		}
		if((!$type or !$ext) and !current_user_can('unfiltered_upload')){
			$error_msg = translate('Sorry, you are not allowed to upload this file type.');
			return ldc_error($error_msg);
		}
		return $name;
	}
}

if(!function_exists('ldc_test_uploaded_file')){
	/**
	 * @return bool|WP_Error
	 */
	function ldc_test_uploaded_file($tmp_name = ''){ // A properly uploaded file will pass this test.
		if(!is_uploaded_file($tmp_name)){
			$error_msg = translate('Specified file failed upload test.');
			return ldc_error($error_msg);
		}
		return true;
	}
}

if(!function_exists('ldc_upload_dir')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_upload_dir($subdir = ''){
        $dir = 'uploads';
        $subdir = ltrim($subdir, '/');
	    $subdir = untrailingslashit($subdir);
	    if($subdir){
	        $dir .= '/' . $subdir;
	    }
		return ldc_dir($dir);
	}
}

if(!function_exists('ldc_url_to_dir')){
	/**
	 * @return string
	 */
	function ldc_url_to_dir($url = ''){
	    $site_url = site_url('/');
	    if(!ldc_str_starts_with($url, $site_url)){
	        return '';
	    }
		return str_replace($site_url, wp_normalize_path(ABSPATH), $url);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Google
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_hide_recaptcha_badge')){
	/**
	 * @return void
	 */
	function ldc_hide_recaptcha_badge(){
		if(doing_action('wp_head')){ // Just in time.
	        ldc_echo_hide_recaptcha_badge();
			return;
	    }
		if(did_action('wp_head')){ // Too late.
			return;
		}
		ldc_add_action_once('wp_head', 'ldc_maybe_hide_recaptcha_badge');
	}
}

if(!function_exists('ldc_is_google_workspace')){
	/**
	 * @return bool|string
	 */
	function ldc_is_google_workspace($email = ''){
		if(!is_email($email)){
			return false;
		}
		list($local, $domain) = explode('@', $email, 2);
		if('gmail.com' === strtolower($domain)){
			return 'gmail.com';
		}
		if(!getmxrr($domain, $mxhosts)){
			return false;
		}
		if(!in_array('aspmx.l.google.com', $mxhosts)){
			return false;
		}
		return $domain;
	}
}

if(!function_exists('ldc_recaptcha_branding')){
	/**
	 * @return string
	 */
	function ldc_recaptcha_branding(){
		return 'This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank">Terms of Service</a> apply.';
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_echo_hide_recaptcha_badge')){
	/**
	 * This function MUST be called inside the 'wp_head' action hook.
	 *
	 * @return void
	 */
	function ldc_echo_hide_recaptcha_badge(){
		if(!doing_action('wp_head')){
	        return;
	    }
        $html = '<style id="' . ldc_str_slug('hide-recaptcha-badge') . '">.grecaptcha-badge { visibility: hidden !important; }</style>';
        echo $html . "\n";
	}
}

if(!function_exists('ldc_maybe_hide_recaptcha_badge')){
	/**
	 * This function MUST be called inside the 'wp_head' action hook.
	 *
	 * @return void
	 */
	function ldc_maybe_hide_recaptcha_badge(){
		if(!doing_action('wp_head')){ // Too early or too late.
	        return;
	    }
		ldc_echo_hide_recaptcha_badge();
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Hide
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_add_hiding_rule')){
    /**
     * @return void
     */
    function ldc_add_hiding_rule($args = []){
        if(is_multisite()){
    		return; // The rewrite rules are not for WordPress MU networks.
    	}
    	$pairs = [
            'capability' => '',
            'exclude_other_media' => [],
            'exclude_public_media' => false,
            'file' => '',
    		'subdir' => '',
    	];
        $args = shortcode_atts($pairs, $args);
    	$md5 = ldc_md5($args);
        if(ldc_isset_array_cache('hide_uploads_subdir', $md5)){
            return; // Prevent adding rule when already added.
        }
        ldc_set_array_cache('hide_uploads_subdir', $md5, $args);
    	$uploads_use_yearmonth_folders = false;
    	$subdir = ltrim(untrailingslashit($args['subdir']), '/');
    	if($subdir){
    		$subdir = '/(' . $subdir . ')';
    	} else {
    		if(get_option('uploads_use_yearmonth_folders')){
    			$subdir = '/(\d{4})/(\d{2})';
    			$uploads_use_yearmonth_folders = true;
    		} else {
    			$subdir = '';
    		}
    	}
    	$upload_dir = wp_get_upload_dir();
    	if($upload_dir['error']){
    		return;
    	}
        $atts = [];
        $path = ldc_shortinit_dir() . '/files.php';
    	$tmp = str_replace(wp_normalize_path(ABSPATH), '', wp_normalize_path($path));
    	$parts = explode('/', $tmp);
    	$levels = count($parts);
    	$query = ldc_dir_to_url($path);
    	$regex = $upload_dir['baseurl'] . $subdir. '/(.+)';
    	if($uploads_use_yearmonth_folders){
    		$atts['yyyy'] = '$1';
    		$atts['mm'] = '$2';
    		$atts['file'] = '$3';
    	} else {
    		$atts['subdir'] = '$1';
    		$atts['file'] = '$2';
    	}
    	$atts['levels'] = $levels;
        $atts['md5'] = $md5;
        $value = [
            'capability' => $args['capability'],
            'exclude_other_media' => $args['exclude_other_media'],
            'exclude_public_media' => $args['exclude_public_media'],
        ];
        //$option = ldc_str_prefix('hide_uploads_subdir_exclude_' . $md5);
        $option = ldc_str_prefix('hide_uploads_subdir_' . $md5);
        //update_option($option, (array) $args['exclude'], 'no');
        update_option($option, $value, 'no');
    	$query = add_query_arg($atts, $query);
    	ldc_add_external_rule($regex, $query, $args['file']);
    }
}

if(!function_exists('ldc_hide_others_media')){
    /**
     * @return void
     */
    function ldc_hide_others_media($capability = 'edit_others_posts'){
        ldc_set_cache('hide_others_media', [
            'capability' => $capability,
        ]);
        ldc_add_filter_once('ajax_query_attachments_args', 'ldc_maybe_hide_others_media');
    }
}

if(!function_exists('ldc_hide_others_posts')){
    /**
     * @return void
     */
    function ldc_hide_others_posts($capability = 'edit_others_posts'){
        ldc_set_cache('hide_others_posts', [
            'capability' => $capability,
        ]);
        ldc_add_action_once('current_screen', 'ldc_maybe_hide_others_posts_count');
        ldc_add_action_once('pre_get_posts', 'ldc_maybe_hide_others_posts_query_args');
    }
}

if(!function_exists('ldc_hide_the_dashboard')){
    /**
     * @return void
     */
    function ldc_hide_the_dashboard($capability = 'edit_posts', $location = ''){
        ldc_set_cache('hide_the_dashboard', [
            'capability' => $capability,
    		'location' => $location,
        ]);
        ldc_add_action_once('admin_init', 'ldc_maybe_hide_the_dashboard');
    }
}

if(!function_exists('ldc_hide_the_frontend')){
    /**
     * @return void
     */
    function ldc_hide_the_frontend($capability = 'read', $exclude_special_pages = [], $exclude_other_pages = []){
        ldc_set_cache('hide_the_frontend', [
            'capability' => $capability,
            'exclude_other_pages' => $exclude_other_pages,
    		'exclude_special_pages' => $exclude_special_pages,
        ]);
        ldc_add_action_once('template_redirect', 'ldc_maybe_hide_the_frontend');
    }
}

if(!function_exists('ldc_hide_the_rest_api')){
    /**
     * @return void
     */
    function ldc_hide_the_rest_api($capability = 'read'){
        ldc_set_cache('hide_the_rest_api', [
            'capability' => $capability,
        ]);
        ldc_add_filter_once('rest_authentication_errors', 'ldc_maybe_hide_the_rest_api');
    }
}

if(!function_exists('ldc_hide_the_toolbar')){
    /**
     * @return void
     */
    function ldc_hide_the_toolbar($capability = 'edit_posts'){
        ldc_set_cache('hide_the_toolbar', [
            'capability' => $capability,
        ]);
        ldc_add_filter_once('show_admin_bar', 'ldc_maybe_hide_the_toolbar');
    }
}

if(!function_exists('ldc_hide_uploads_subdir')){
    /**
     * @return void
     */
    function ldc_hide_uploads_subdir($subdir = '', $capability = 'edit_others_posts', $exclude_public_media = false, $exclude_other_media = [], $file = ''){
        $args = [
            'capability' => $capability,
            'exclude_other_media' => $exclude_other_media,
            'exclude_public_media' => $exclude_public_media,
            'subdir' => $subdir,
        ];
        if(!$file){
            $file = ldc_caller_file(1); // One level above.
    	}
        $args['file'] = $file;
        return ldc_add_hiding_rule($args);
    }
}

if(!function_exists('ldc_hide_wp')){
    /**
     * @return void
     */
    function ldc_hide_wp(){
        ldc_local_login_header();
        ldc_set_cache('hide_wp', true);
    	ldc_add_action_once('admin_init', 'ldc_maybe_hide_wp_from_admin');
        ldc_add_action_once('wp_before_admin_bar_render', 'ldc_maybe_hide_wp_from_admin_bar');
    }
}

if(!function_exists('ldc_local_login_header')){
    /**
     * @return void
     */
    function ldc_local_login_header(){
        ldc_set_cache('local_login_header', true);
        ldc_add_filter_once('login_headertext', 'ldc_maybe_local_login_headertext');
        ldc_add_filter_once('login_headerurl', 'ldc_maybe_local_login_headerurl');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_maybe_hide_others_media')){
    /**
	 * This function MUST be called inside the 'ajax_query_attachments_args' filter hook.
	 *
     * @return array
     */
    function ldc_maybe_hide_others_media($query){
        if(!doing_filter('ajax_query_attachments_args')){ // Too early or too late.
	        return;
	    }
        $hide_others_media = (array) ldc_get_cache('hide_others_media', []);
    	if(!$hide_others_media){
    		return;
    	}
        if(current_user_can($hide_others_media['capability'])){
            return $query;
        }
        $query['author'] = get_current_user_id();
        return $query;
    }
}

if(!function_exists('ldc_maybe_hide_others_posts_count')){
    /**
	 * This function MUST be called inside the 'current_screen' action hook.
	 *
     * @return void
     */
    function ldc_maybe_hide_others_posts_count(){
        global $current_screen, $pagenow;
        if(!doing_action('current_screen')){ // Too early or too late.
	        return;
	    }
        $hide_others_posts = (array) ldc_get_cache('hide_others_posts', []);
    	if(!$hide_others_posts){
    		return;
    	}
        if('edit.php' !== $pagenow){
            return;
        }
        if(current_user_can($hide_others_posts['capability'])){
    		return;
    	}
        ldc_add_filter_once('views_' . $current_screen->id, 'ldc_maybe_hide_others_posts_count_from_views');
    }
}

if(!function_exists('ldc_maybe_hide_others_posts_count_from_views')){
    /**
	 * This function MUST be called inside the 'views_SCREEN_ID' filter hook.
	 *
     * @return array
     */
    function ldc_maybe_hide_others_posts_count_from_views($views){
        $current_filter = current_filter();
        if(!ldc_str_starts_with($current_filter, 'views_')){ // Too early or too late.
            return;
        }
        foreach($views as $index => $view){
            $views[$index] = preg_replace('/ <span class="count">\([0-9]+\)<\/span>/', '', $view);
        }
    	return $views;
    }
}

if(!function_exists('ldc_maybe_hide_others_posts_query_args')){
    /**
	 * This function MUST be called inside the 'pre_get_posts' action hook.
	 *
     * @return array
     */
    function ldc_maybe_hide_others_posts_query_args($query){
        global $pagenow;
        if(!doing_action('pre_get_posts')){ // Too early or too late.
	        return;
	    }
        $hide_others_posts = (array) ldc_get_cache('hide_others_posts', []);
    	if(!$hide_others_posts){
    		return;
    	}
        if('edit.php' !== $pagenow){
            return;
        }
        if(current_user_can($hide_others_posts['capability'])){
    		return;
    	}
        $query->set('author', get_current_user_id());
        return $query;
    }
}

if(!function_exists('ldc_maybe_hide_the_dashboard')){
    /**
	 * This function MUST be called inside the 'admin_init' action hook.
	 *
     * @return void
     */
    function ldc_maybe_hide_the_dashboard(){
        if(!doing_action('admin_init')){ // Too early or too late.
	        return;
	    }
        $hide_the_dashboard = (array) ldc_get_cache('hide_the_dashboard', []);
    	if(!$hide_the_dashboard){
    		return;
    	}
        if(wp_doing_ajax()){
            return;
        }
        if(current_user_can($hide_the_dashboard['capability'])){
            return;
        }
        $location = wp_validate_redirect($hide_the_dashboard['location'], home_url());
    	wp_safe_redirect($location);
    	exit;
    }
}

if(!function_exists('ldc_maybe_hide_the_frontend')){
    /**
	 * This function MUST be called inside the 'template_redirect' action hook.
	 *
     * @return void
     */
    function ldc_maybe_hide_the_frontend(){
        if(!doing_action('template_redirect')){ // Too early or too late.
	        return;
	    }
        $hide_the_frontend = (array) ldc_get_cache('hide_the_frontend', []);
    	if(!$hide_the_frontend){
    		return;
    	}
        $exclude_other_pages = in_array(get_the_ID(), (array) $hide_the_frontend['exclude_other_pages']);
    	$exclude_special_pages = ((is_front_page() and in_array('front_page', (array) $hide_the_frontend['exclude_special_pages'])) or (is_home() and in_array('home', (array) $hide_the_frontend['exclude_special_pages'])));
        if($exclude_other_pages or $exclude_special_pages){
            return;
        }
        if(!is_user_logged_in()){
            auth_redirect();
        }
        if(current_user_can($hide_the_frontend['capability'])){
            return;
        }
        ldc_exit_with_error(translate('Sorry, you are not allowed to access this page.'), translate('You need a higher level of permission.'), 403);
    }
}

if(!function_exists('ldc_maybe_hide_the_rest_api')){
    /**
	 * This function MUST be called inside the 'rest_authentication_errors' filter hook.
	 *
     * @return null|WP_Error
     */
    function ldc_maybe_hide_the_rest_api($errors = null){
        if(!doing_filter('rest_authentication_errors')){ // Too early or too late.
	        return $errors;
	    }
        if(!is_null($errors)){
    		return $errors; // WP_Error if authentication error or true if authentication succeeded.
    	}
        $hide_the_rest_api = (array) ldc_get_cache('hide_the_rest_api', []);
    	if(!$hide_the_rest_api){
    		return $errors;
    	}
        if(current_user_can($hide_the_rest_api['capability'])){
            return $errors;
        }
        return ldc_error(translate('You need a higher level of permission.'), [
    		'status' => 401,
    	]);
    }
}

if(!function_exists('ldc_maybe_hide_the_toolbar')){
    /**
	 * This function MUST be called inside the 'show_admin_bar' filter hook.
	 *
     * @return bool
     */
    function ldc_maybe_hide_the_toolbar($show = false){
        if(!doing_filter('show_admin_bar')){ // Too early or too late.
	        return $show;
	    }
        $hide_the_toolbar = (array) ldc_get_cache('hide_the_toolbar', []);
    	if(!$hide_the_toolbar){
    		return $show;
    	}
        if(current_user_can($hide_the_toolbar['capability'])){
            return $show;
        }
        $show = false;
        return $show;
    }
}

if(!function_exists('ldc_maybe_hide_wp_from_admin')){
    /**
	 * This function MUST be called inside the 'admin_init' action hook.
	 *
     * @return void
     */
    function ldc_maybe_hide_wp_from_admin(){
        if(!doing_action('admin_init')){ // Too early or too late.
	        return;
	    }
        $hide_wp = (bool) ldc_get_cache('hide_wp', false);
    	if(!$hide_wp){
    		return;
    	}
        remove_action('welcome_panel', 'wp_welcome_panel');
    }
}

if(!function_exists('ldc_maybe_hide_wp_from_admin_bar')){
    /**
	 * This function MUST be called inside the 'wp_before_admin_bar_render' action hook.
	 *
     * @return void
     */
    function ldc_maybe_hide_wp_from_admin_bar(){
        global $wp_admin_bar;
        if(!doing_action('wp_before_admin_bar_render')){ // Too early or too late.
	        return;
	    }
        $hide_wp = (bool) ldc_get_cache('hide_wp', false);
    	if(!$hide_wp){
    		return;
    	}
    	$wp_admin_bar->remove_node('wp-logo');
    }
}

if(!function_exists('ldc_maybe_local_login_headertext')){
    /**
	 * This function MUST be called inside the 'login_headertext' filter hook.
	 *
     * @return string
     */
    function ldc_maybe_local_login_headertext($login_header_text = ''){
        if(!doing_filter('login_headertext')){ // Too early or too late.
	        return $login_header_text;
	    }
    	$local_login_header = (bool) ldc_get_cache('local_login_header', false);
    	if(!$local_login_header){
            return $login_header_text;
    	}
    	return get_option('blogname');
    }
}

if(!function_exists('ldc_maybe_local_login_headerurl')){
    /**
	 * This function MUST be called inside the 'login_headerurl' filter hook.
	 *
     * @return string
     */
    function ldc_maybe_local_login_headerurl($login_header_url = ''){
        if(!doing_filter('login_headerurl')){ // Too early or too late.
	        return $login_header_url;
	    }
        $local_login_header = (bool) ldc_get_cache('local_login_header', false);
    	if(!$local_login_header){
            return $login_header_url;
    	}
        return home_url();
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Hooks
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_add_action')){
    /**
     * Alias for add_action.
     *
     * Differs from add_action in that it will always return a string.
     *
     * @return string
     */
    function ldc_add_action($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
        return ldc_on($hook_name, $callback, $priority, $accepted_args);
    }
}

if(!function_exists('ldc_add_action_once')){
    /**
     * @return string
     */
    function ldc_add_action_once($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
        return ldc_one($hook_name, $callback, $priority, $accepted_args);
    }
}

if(!function_exists('ldc_add_filter')){
    /**
     * Alias for add_filter.
     *
     * Differs from add_filter in that it will always return a string.
     *
     * @return string
     */
    function ldc_add_filter($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
        return ldc_on($hook_name, $callback, $priority, $accepted_args);
    }
}

if(!function_exists('ldc_add_filter_once')){
    /**
     * @return string
     */
    function ldc_add_filter_once($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
        return ldc_one($hook_name, $callback, $priority, $accepted_args);
    }
}

if(!function_exists('ldc_callback_idx')){
    /**
     * @return string
     */
    function ldc_callback_idx($callback = null){
        return _wp_filter_build_unique_id('', $callback, 0);
    }
}

if(!function_exists('ldc_callback_md5')){
    /**
     * @return string
     */
    function ldc_callback_md5($callback = null){
        $idx = ldc_callback_idx($callback);
        $md5 = md5($idx);
        if(!$callback instanceof \Closure){
            return $md5;
        }
        $md5_closure = ldc_md5_closure($callback);
        if(is_wp_error($md5_closure)){
            return $md5;
        }
        return $md5_closure;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_on')){
    /**
     * @return string
     */
    function ldc_on($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
        add_filter($hook_name, $callback, $priority, $accepted_args);
        return ldc_callback_idx($callback);
    }
}

if(!function_exists('ldc_one')){
    /**
     * @return string
     */
    function ldc_one($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
        $md5 = ldc_callback_md5($callback);
        $key = $hook_name . '_' . $md5;
        if(ldc_isset_array_cache('hooks', $key)){
            return ldc_get_array_cache('hooks', $key);
        }
        $idx = ldc_on($hook_name, $callback, $priority, $accepted_args);
        ldc_set_array_cache('hooks', $key, $idx);
        return $idx;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Login forms
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_custom_interim_login_page')){
    /**
     * @return void
     */
    function ldc_custom_interim_login_page($post_id = 0){
        $post = get_post($post_id);
        if(is_null($post) or 'page' !== $post->post_type or 'publish' !== $post->post_status){
            return;
        }
        ldc_set_array_cache('login_forms', 'login', $post->ID);
    }
}

if(!function_exists('ldc_custom_login_page')){
    /**
     * @return void
     */
    function ldc_custom_login_page($post_id = 0, $interim_login = 0){
        $post = get_post($post_id);
        if(is_null($post) or 'page' !== $post->post_type or 'publish' !== $post->post_status){
            return;
        }
        ldc_set_array_cache('login_forms', 'login', $post->ID);
        ldc_add_action_once('login_form_login', 'ldc_maybe_redirect_login_form_login');
        if(!$interim_login){
            return;
        }
        ldc_custom_interim_login_page($interim_login);
    }
}

if(!function_exists('ldc_custom_lostpassword_page')){
    /**
     * @return void
     */
    function ldc_custom_lostpassword_page($post_id = 0){
        $post = get_post($post_id);
        if(is_null($post) or 'page' !== $post->post_type or 'publish' !== $post->post_status){
            return;
        }
        ldc_set_array_cache('login_forms', 'lostpassword', $post->ID);
        ldc_add_action_once('login_form_lostpassword', 'ldc_maybe_redirect_login_form_lostpassword');
        ldc_add_action_once('login_form_retrievepassword', 'ldc_maybe_redirect_login_form_lostpassword');
    }
}

if(!function_exists('ldc_custom_retrievepassword_page')){
    /**
	 * Alias for __custom_lostpassword_page.
	 *
     * @return void
     */
    function ldc_custom_retrievepassword_page($post_id = 0){
        ldc_custom_lostpassword_page($post_id);
    }
}

if(!function_exists('ldc_custom_register_page')){
    /**
     * @return void
     */
    function ldc_custom_register_page($post_id = 0){
        $post = get_post($post_id);
        if(is_null($post) or 'page' !== $post->post_type or 'publish' !== $post->post_status){
            return;
        }
        ldc_set_array_cache('login_forms', 'register', $post->ID);
        ldc_add_action_once('login_form_register', 'ldc_maybe_redirect_login_form_register');
    }
}

if(!function_exists('ldc_custom_resetpass_page')){
    /**
     * @return void
     */
    function ldc_custom_resetpass_page($post_id = 0){
        $post = get_post($post_id);
        if(is_null($post) or 'page' !== $post->post_type or 'publish' !== $post->post_status){
            return;
        }
        ldc_set_array_cache('login_forms', 'resetpass', $post->ID);
        ldc_add_action_once('login_form_resetpass', 'ldc_maybe_redirect_login_form_resetpass');
        ldc_add_action_once('login_form_rp', 'ldc_maybe_redirect_login_form_resetpass');
    }
}

if(!function_exists('ldc_custom_rp_page')){
    /**
	 * Alias for __custom_resetpass_page.
	 *
     * @return void
     */
    function ldc_custom_rp_page($post_id = 0){
        ldc_custom_resetpass_page($post_id);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_maybe_redirect_login_form_login')){
    /**
	 * This function MUST be called inside the 'login_form_login' action hook.
	 *
     * @return void
     */
    function ldc_maybe_redirect_login_form_login(){
        if(!doing_action('login_form_login')){ // Too early or too late.
	        return;
	    }
        $action = (isset($_REQUEST['interim-login']) ? 'interim_login' : 'login');
        if(!ldc_isset_array_cache('login_forms', $action)){
            return;
        }
        $post_id = (int) ldc_get_array_cache('login_forms', $action, 0);
        $url = get_permalink($post_id);
        if($_GET){
            $_GET = urlencode_deep($_GET); // This re-URL-encodes things that were already in the query string.
            $url = add_query_arg($_GET, $url);
        }
        wp_safe_redirect($url);
        exit;
    }
}

if(!function_exists('ldc_maybe_redirect_login_form_lostpassword')){
    /**
	 * This function MUST be called inside the 'login_form_lostpassword' or 'login_form_retrievepassword' action hooks.
	 *
     * @return void
     */
    function ldc_maybe_redirect_login_form_lostpassword(){
        if(!doing_action('login_form_lostpassword') and !doing_action('login_form_retrievepassword')){ // Too early or too late.
	        return;
	    }
        if(!ldc_isset_array_cache('login_forms', 'lostpassword')){
            return;
        }
        $post_id = (int) ldc_get_array_cache('login_forms', 'lostpassword', 0);
        $url = get_permalink($post_id);
        if($_GET){
            $_GET = urlencode_deep($_GET); // This re-URL-encodes things that were already in the query string.
            $url = add_query_arg($_GET, $url);
        }
        wp_safe_redirect($url);
        exit;
    }
}

if(!function_exists('ldc_maybe_redirect_login_form_register')){
    /**
	 * This function MUST be called inside the 'login_form_register' action hook.
	 *
     * @return void
     */
    function ldc_maybe_redirect_login_form_register(){
        if(!doing_action('login_form_register')){ // Too early or too late.
	        return;
	    }
        if(!ldc_isset_array_cache('login_forms', 'register')){
            return;
        }
        $post_id = (int) ldc_get_array_cache('login_forms', 'register', 0);
        $url = get_permalink($post_id);
        if($_GET){
            $_GET = urlencode_deep($_GET); // This re-URL-encodes things that were already in the query string.
            $url = add_query_arg($_GET, $url);
        }
        wp_safe_redirect($url);
        exit;
    }
}

if(!function_exists('ldc_maybe_redirect_login_form_resetpass')){
    /**
	 * This function MUST be called inside the 'login_form_resetpass' or 'login_form_rp' action hooks.
	 *
     * @return void
     */
    function ldc_maybe_redirect_login_form_resetpass(){
        if(!doing_action('login_form_resetpass') and !doing_action('login_form_rp')){ // Too early or too late.
	        return;
	    }
        if(!ldc_isset_array_cache('login_forms', 'resetpass')){
            return;
        }
        $post_id = (int) ldc_get_array_cache('login_forms', 'resetpass', 0);
        $url = get_permalink($post_id);
        if($_GET){
            $_GET = urlencode_deep($_GET); // This re-URL-encodes things that were already in the query string.
            $url = add_query_arg($_GET, $url);
        }
        wp_safe_redirect($url);
        exit;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// MD5
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_md5')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_md5($data = ''){
		if(is_object($data)){
			if($data instanceof \Closure){
				$md5_closure = ldc_md5_closure($data); // String or WP_Error.
				return $md5_closure;
			}
			$data = ldc_object_to_array($data); // Array or WP_Error.
			if(is_wp_error($data)){
				return $data;
			}
		}
		if(is_array($data)){
			$data = ldc_ksort_deep($data);
			$data = serialize($data);
		}
		return md5($data);
	}
}

if(!function_exists('ldc_md5_closure')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_md5_closure($data = null, $spl_object_hash = false){
		if(!$data instanceof \Closure){
			return ldc_error(translate('Invalid object type.'));
		}
		$wrapper = ldc_serializable_closure($data);
		if(is_wp_error($wrapper)){
			return $wrapper;
		}
		$serialized = serialize($wrapper);
		if(!$spl_object_hash){
			$spl_object_hash = spl_object_hash($data);
			$serialized = str_replace($spl_object_hash, 'spl_object_hash', $serialized);
		}
		return md5($serialized);
	}
}

if(!function_exists('ldc_md5_to_uuid4')){
	/**
	 * @return string
	 */
	function ldc_md5_to_uuid4($md5 = ''){
		if(32 !== strlen($md5)){
			return '';
		}
		return substr($md5, 0, 8) . '-' . substr($md5, 8, 4) . '-' . substr($md5, 12, 4) . '-' . substr($md5, 16, 4) . '-' . substr($md5, 20, 12);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Media
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_add_4k_image_size')){
	/**
	 * @return void
	 */
	function ldc_add_4k_image_size(){
		ldc_add_image_size('4K', 3840, 3840);
	}
}

if(!function_exists('ldc_add_full_hd_image_size')){
	/**
	 * @return void
	 */
	function ldc_add_full_hd_image_size(){
		ldc_add_image_size('Full HD', 1920, 1920);
	}
}

if(!function_exists('ldc_add_hd_image_size')){
	/**
	 * @return void
	 */
	function ldc_add_hd_image_size(){
		ldc_add_image_size('HD', 1280, 1280);
	}
}

if(!function_exists('ldc_add_image_size')){
	/**
	 * @return void
	 */
	function ldc_add_image_size($name = '', $width = 0, $height = 0, $crop = false){
		$image_sizes = get_intermediate_image_sizes();
		$size = sanitize_title($name);
		if(in_array($size, $image_sizes)){
			return; // Does NOT overwrite.
		}
		add_image_size($size, $width, $height, $crop);
		ldc_set_array_cache('image_sizes', $size, $name);
		ldc_add_filter_once('image_size_names_choose', 'ldc_maybe_add_image_size_names');
	}
}

if(!function_exists('ldc_add_larger_image_sizes')){
	/**
	 * @return void
	 */
	function ldc_add_larger_image_sizes(){
		ldc_add_hd_image_size();
		ldc_add_full_hd_image_size();
		ldc_add_4k_image_size();
	}
}

if(!function_exists('ldc_attachment_url_to_postid')){
	/**
	 * @return int
	 */
	function ldc_attachment_url_to_postid($url = ''){
		$post_id = ldc_guid_to_postid($url);
		if($post_id){
			return $post_id;
		}
		preg_match('/^(.+)(\-\d+x\d+)(\.' . substr($url, strrpos($url, '.') + 1) . ')?$/', $url, $matches); // Resized.
		if($matches){
			$url = $matches[1];
			if(isset($matches[3])){
				$url .= $matches[3];
			}
			$post_id = ldc_guid_to_postid($url);
			if($post_id){
				return $post_id;
			}
		}
		preg_match('/^(.+)(\-scaled)(\.' . substr($url, strrpos($url, '.') + 1) . ')?$/', $url, $matches); // Scaled.
		if($matches){
			$url = $matches[1];
			if(isset($matches[3])){
				$url .= $matches[3];
			}
			$post_id = ldc_guid_to_postid($url);
			if($post_id){
				return $post_id;
			}
		}
		preg_match('/^(.+)(\-e\d+)(\.' . substr($url, strrpos($url, '.') + 1) . ')?$/', $url, $matches); // Edited.
		if($matches){
			$url = $matches[1];
			if(isset($matches[3])){
				$url .= $matches[3];
			}
			$post_id = ldc_guid_to_postid($url);
			if($post_id){
				return $post_id;
			}
		}
		return 0;
	}
}

if(!function_exists('ldc_convert_exts_to_mimes')){
	/**
	 * @return array
	 */
	function ldc_convert_exts_to_mimes($exts = []){
	    if(empty($exts)){
	        $exts = array_merge(wp_get_audio_extensions(), wp_get_video_extensions(), ldc_image_extensions());
	    }
	    $mimes = wp_get_mime_types();
	    $ext_mimes = [];
	    foreach($exts as $ext){
	        foreach($mimes as $ext_preg => $mime_match){
	            if(preg_match('#' . $ext . '#i', $ext_preg)){
	                $ext_mimes[$ext] = $mime_match;
	                break;
	            }
	        }
	    }
	    return $ext_mimes;
	}
}

if(!function_exists('ldc_fa_file_type')){
	/**
	 * @return string
	 */
	function ldc_fa_file_type($post = null){
		if('attachment' !== get_post_type($post)){
			return '';
		}
		if(wp_attachment_is('audio', $post)){
			return 'audio';
		}
		if(wp_attachment_is('image', $post)){
			return 'image';
		}
		if(wp_attachment_is('video', $post)){
			return 'video';
		}
		$type = get_post_mime_type($post);
		switch($type){
			case 'application/zip':
			case 'application/x-rar-compressed':
			case 'application/x-7z-compressed':
			case 'application/x-tar':
				return 'archive';
				break;
			case 'application/vnd.ms-excel':
			case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
				return 'excel';
				break;
			case 'application/pdf':
				return 'pdf';
				break;
			case 'application/vnd.ms-powerpoint':
			case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
				return 'powerpoint';
				break;
			case 'application/msword':
			case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
				return 'word';
				break;
			default:
				return 'file';
		}
	}
}

if(!function_exists('ldc_guid_to_postid')){
	/**
	 * @return int
	 */
	function ldc_guid_to_postid($guid = '', $check_rewrite_rules = false){
		global $wpdb;
		$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid = %s", $guid);
		$post_id = $wpdb->get_var($query);
		if(!is_null($post_id)){
			return intval($post_id);
		}
		if($check_rewrite_rules){
			return url_to_postid($guid);
		}
		return 0;
	}
}

if(!function_exists('ldc_image_extensions')){
	/**
	 * @return array
	 */
	function ldc_image_extensions(){
		$image_extensions = ['jpg', 'jpeg', 'jpe', 'png', 'gif', 'bmp', 'tiff', 'tif', 'webp', 'ico', 'heic'];
	    return $image_extensions;
	}
}

if(!function_exists('ldc_maybe_generate_attachment_metadata')){
	/**
	 * @return void
	 */
	function ldc_maybe_generate_attachment_metadata($attachment_id = 0){
		$attachment = get_post($attachment_id);
		if(is_null($attachment)){
			return;
		}
		if('attachment' !== $attachment->post_type){
			return;
		}
		wp_raise_memory_limit('image');
		if(!function_exists('wp_generate_attachment_metadata')){
			require_once(ABSPATH . 'wp-admin/includes/image.php');
		}
		wp_maybe_generate_attachment_metadata($attachment);
	}
}

if(!function_exists('ldc_mime_content_type')){
	/**
	 * @return string
	 */
	function ldc_mime_content_type($filename = '', $mimes = null){
        $mime = wp_check_filetype($filename, $mimes);
        if(false === $mime['type'] and function_exists('mime_content_type')){
            $mime['type'] = mime_content_type($filename);
        }
        if(false === $mime['type']){
            return '';
        }
        return $mime['type'];
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_maybe_add_image_size_names')){
	/**
	 * This function MUST be called inside the 'image_size_names_choose' filter hook.
	 *
	 * @return array
	 */
	function ldc_maybe_add_image_size_names($sizes){
		if(!doing_filter('image_size_names_choose')){
	        return $sizes;
	    }
		$image_sizes = (array) ldc_get_cache('image_sizes', []);
		foreach($image_sizes as $size => $name){
			$sizes[$size] = $name;
		}
		return $sizes;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Miscellaneous
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_absint')){
	/**
	 * @return int
	 */
	function ldc_absint($maybeint = 0){
		if(!is_numeric($maybeint)){
			return 0; // Make sure the value is numeric to avoid casting objects, for example, to int 1.
		}
		return absint($maybeint);
	}
}

if(!function_exists('ldc_breadcrumbs')){
	/**
	 * @return string
	 */
	function ldc_breadcrumbs($breadcrumbs = [], $separator = '>'){
	    $elements = [];
	    foreach($breadcrumbs as $breadcrumb){
	        if(!isset($breadcrumb['text'])){
	            continue;
	        }
	        $text = $breadcrumb['text'];
	        if(isset($breadcrumb['link'])){
	            $href = $breadcrumb['link'];
	            $target = isset($breadcrumb['target']) ? $breadcrumb['target'] : '_self';
	            $element = sprintf('<a href="%1$s" target="%2$s">%3$s</a>', esc_url($href), esc_attr($target), esc_html($text));
	        } else {
	            $element = sprintf('<span>%1$s</span>', esc_html($text));
	        }
	        $elements[] = $element;
	    }
	    $separator = ' ' . trim($separator) . ' ';
		return implode($separator, $elements);
	}
}

if(!function_exists('ldc_clone_role')){
	/**
	 * @return void|WP_Role
	 */
	function ldc_clone_role($source = '', $destination = '', $display_name = ''){
		$role = get_role($source);
		if(is_null($role)){
			return;
		}
		$destination = ldc_canonicalize($destination);
		return add_role($destination, $display_name, $role->capabilities);
	}
}

if(!function_exists('ldc_current_screen_in')){
	/**
	 * @return bool
	 */
	function ldc_current_screen_in($ids = []){
		global $current_screen;
		if(!is_array($ids)){
			return false;
		}
		if(!isset($current_screen)){
			return false;
		}
		return in_array($current_screen->id, $ids);
	}
}

if(!function_exists('ldc_current_screen_is')){
	/**
	 * @return bool
	 */
	function ldc_current_screen_is($id = ''){
		global $current_screen;
		if(!is_string($id)){
			return false;
		}
		if(!isset($current_screen)){
			return false;
		}
		return ($current_screen->id === $id);
	}
}

if(!function_exists('ldc_custom_login_logo')){
    /**
     * @return bool|WP_Error
     */
    function ldc_custom_login_logo($attachment_id = 0, $half = true){
        if(!wp_attachment_is_image($attachment_id)){
            return ldc_error(translate('File is not an image.'));
        }
        $custom_logo = wp_get_attachment_image_src($attachment_id, 'medium');
        $height = $custom_logo[2];
        $width = $custom_logo[1];
        if($width > 300){ // Fix for SVG.
            $r = 300 / $width;
            $width = 300;
            $height *= $r;
        }
        if($half){
            $height = $height / 2;
            $width = $width / 2;
        }
        $custom_login_logo = [$custom_logo[0], $width, $height];
        ldc_set_cache('custom_login_logo', $custom_login_logo);
        ldc_add_action_once('login_enqueue_scripts', 'ldc_maybe_replace_login_logo');
        return true;
    }
}

if(!function_exists('ldc_format_function')){
	/**
	 * @return string
	 */
	function ldc_format_function($function_name = '', $args = []){
		$str = '<span style="color: #24831d; font-family: monospace; font-weight: 400;">' . $function_name . '(';
		$function_args = [];
		foreach($args as $arg){
			$arg = shortcode_atts([
				'default' => 'null',
				'name' => '',
				'type' => '',
			], $arg);
			if($arg['default'] and $arg['name'] and $arg['type']){
				$function_args[] = '<span style="color: #cd2f23; font-family: monospace; font-style: italic; font-weight: 400;">' . $arg['type'] . '</span> <span style="color: #0f55c8; font-family: monospace; font-weight: 400;">$' . $arg['name'] . '</span> = <span style="color: #000; font-family: monospace; font-weight: 400;">' . $arg['default'] . '</span>';
			}
		}
		if($function_args){
			$str .= ' ' . implode(', ', $function_args) . ' ';
		}
		$str .= ')</span>';
		return $str;
	}
}

if(!function_exists('ldc_go_to')){
	/**
	 * @return bool
	 */
	function ldc_go_to($str = ''){
		return trim(str_replace('&larr;', '', sprintf(translate_with_gettext_context('&larr; Go to %s', 'site'), $str)));
	}
}

if(!function_exists('ldc_has_btn_class')){
	/**
	 * @return bool
	 */
	function ldc_has_btn_class($class = ''){
	    $class = ldc_remove_whitespaces($class);
	    preg_match_all('/btn-[A-Za-z][-A-Za-z0-9_:.]*/', $class, $matches);
		$matches = array_filter($matches[0], function($match){
			return !in_array($match, ['btn-block', 'btn-lg', 'btn-sm']);
		});
		return (bool) $matches;
	}
}

if(!function_exists('ldc_has_shortcode')){
	/**
	 * @return array
	 */
	function ldc_has_shortcode($content = '', $tag = ''){
	    if(false === strpos($content, '[')){
	        return [];
	    }
	    if(!shortcode_exists($tag)){
	        return [];
	    }
	    preg_match_all('/' . get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER);
	    if(!$matches){
	        return [];
	    }
	    foreach($matches as $shortcode){
	        if($tag === $shortcode[2]){
	            return shortcode_parse_atts($shortcode[3]);
	        }
	        if(!$shortcode[5]){
	            continue;
	        }
	        $attr = ldc_has_shortcode($shortcode[5], $tag);
	        if(!$attr){
	            continue;
	        }
	        return $attr;
	    }
	    return [];
	}
}

if(!function_exists('ldc_host_url')){
	/**
     * @return string
     */
    function ldc_host_url($url = ''){
        $host = wp_parse_url(sanitize_url($url), PHP_URL_HOST);
        if(is_null($host)){
            return '';
        }
        return substr($url, 0, (strpos($url, $host) + strlen($host)));
    }
}

if(!function_exists('ldc_is_doing_heartbeat')){
	/**
	 * @return bool
	 */
	function ldc_is_doing_heartbeat(){
		return (wp_doing_ajax() and isset($_POST['action']) and 'heartbeat' === $_POST['action']);
	}
}

if(!function_exists('ldc_is_false')){
	/**
	 * @return bool
	 */
	function ldc_is_false($data = ''){
		return in_array((string) $data, ['0', 'false', 'off'], true);
	}
}

if(!function_exists('ldc_is_name')){
	/**
     * A valid name starts with a letter or underscore, followed by any number of letters, numbers, or underscores.
     *
     * @link https://www.php.net/manual/en/functions.user-defined.php
     *
	 * @return bool
	 */
	function ldc_is_name($name = ''){
		return preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $name);
	}
}

if(!function_exists('ldc_is_post_edit')){
    /**
     * @return string
     */
    function ldc_is_post_edit($post_type = ''){
        global $hook_suffix;
        if(!is_admin()){
            return '';
        }
        if('post.php' !== $hook_suffix){
            return '';
        }
        if(!isset($_GET['action'], $_GET['post'])){
			return '';
		}
        if('edit' !== $_GET['action']){
			return;
		}
        if(!$post_type){
            return get_post_type($_GET['post']);
        }
        if($post_type !== get_post_type($_GET['post'])){
			return '';
		}
        return $post_type;
    }
}

if(!function_exists('ldc_is_post_list')){
    /**
     * @return string
     */
    function ldc_is_post_list($post_type = ''){
        global $hook_suffix;
        if(!is_admin()){
            return '';
        }
        if('edit.php' !== $hook_suffix){
            return '';
        }
        if(!isset($_GET['post_type'])){
			return '';
		}
        if(!$post_type){
            return $_GET['post_type'];
        }
        if($post_type !== $_GET['post_type']){
			return '';
		}
        return $post_type;
    }
}

if(!function_exists('ldc_is_post_new')){
    /**
     * @return string
     */
    function ldc_is_post_new($post_type = ''){
        global $hook_suffix;
        if(!is_admin()){
            return '';
        }
        if('post-new.php' !== $hook_suffix){
            return '';
        }
        if(!isset($_GET['post_type'])){
			return '';
		}
        if(!$post_type){
            return $_GET['post_type'];
        }
        if($post_type !== $_GET['post_type']){
			return '';
		}
        return $post_type;
    }
}

if(!function_exists('ldc_is_revision_or_auto_draft')){
	/**
	 * @return bool
	 */
	function ldc_is_revision_or_auto_draft($post = null){
		return (wp_is_post_revision($post) or 'auto-draft' === get_post_status($post));
	}
}

if(!function_exists('ldc_is_true')){
	/**
	 * @return bool
	 */
	function ldc_is_true($data = ''){
		return in_array((string) $data, ['1', 'on', 'true'], true);
	}
}

if(!function_exists('ldc_load_admin_textdomain')){
	/**
	 * @return bool
	 */
	function ldc_load_admin_textdomain($domain = 'default'){
        if(is_admin()){
            return false;
        }
        $locale = determine_locale();
        $mofile = WP_LANG_DIR . '/admin-' . $locale . '.mo';
        if(!file_exists($mofile)){
            return false;
        }
        return load_textdomain($domain, $mofile, $locale);
	}
}

if(!function_exists('ldc_object_to_array')){
	/**
	 * @return array|WP_Error
	 */
	function ldc_object_to_array($data = null){
		if(!is_object($data)){
            $error_msg = translate('Invalid data provided.');
			return ldc_error($error_msg, $data);
		}
		$data = wp_json_encode($data);
		return ldc_json_decode($data, true);
	}
}

if(!function_exists('ldc_post_type_labels')){
	/**
	 * @return array
	 */
	function ldc_post_type_labels($singular = '', $plural = '', $all = true){
		if(empty($singular)){
			return [];
		}
		if(empty($plural)){
			$plural = $singular;
		}
		return [
			'name' => $plural,
			'singular_name' => $singular,
			'add_new' => 'Add New',
			'add_new_item' => 'Add New ' . $singular,
			'edit_item' => 'Edit ' . $singular,
			'new_item' => 'New ' . $singular,
			'view_item' => 'View ' . $singular,
			'view_items' => 'View ' . $plural,
			'search_items' => 'Search ' . $plural,
			'not_found' => 'No ' . strtolower($plural) . ' found.',
			'not_found_in_trash' => 'No ' . strtolower($plural) . ' found in Trash.',
			'parent_item_colon' => 'Parent ' . $singular . ':',
			'all_items' => ($all ? 'All ' : '') . $plural,
			'archives' => $singular . ' Archives',
			'attributes' => $singular . ' Attributes',
			'insert_into_item' => 'Insert into ' . strtolower($singular),
			'uploaded_to_this_item' => 'Uploaded to this ' . strtolower($singular),
			'featured_image' => 'Featured image',
			'set_featured_image' => 'Set featured image',
			'remove_featured_image' => 'Remove featured image',
			'use_featured_image' => 'Use as featured image',
			'filter_items_list' => 'Filter ' . strtolower($plural) . ' list',
			'items_list_navigation' => $plural . ' list navigation',
			'items_list' => $plural . ' list',
			'item_published' => $singular . ' published.',
			'item_published_privately' => $singular . ' published privately.',
			'item_reverted_to_draft' => $singular . ' reverted to draft.',
			'item_scheduled' => $singular . ' scheduled.',
			'item_updated' => $singular . ' updated.',
		];
	}
}

if(!function_exists('ldc_properties_exists')){
	/**
	 * @return bool
	 */
	function ldc_properties_exists($properties = [], $object = []){
		if(!is_array($properties) or !is_object($object)){
			return false;
		}
		foreach($properties as $property){
			if(!property_exists($object, $property)){
				return false;
			}
		}
		return true;
	}
}

if(!function_exists('ldc_test')){
	/**
	 * @return void
	 */
	function ldc_test(){
        $message = sprintf(translate('%1$s is proudly powered by %2$s'), get_bloginfo('name'), '<a href="https://wordpress.org/">WordPress</a>');
        $title = translate('Hello world!');
        ldc_exit_with_error($message, $title, 200);
	}
}

if(!function_exists('ldc_validate_redirect_to')){
	/**
	 * @return string
	 */
	function ldc_validate_redirect_to($url = ''){
		$redirect_to = isset($_REQUEST['redirect_to']) ? wp_http_validate_url($_REQUEST['redirect_to']) : false;
		if(!$redirect_to and !empty($url)){
			$redirect_to = wp_http_validate_url($url);
		}
		return (string) $redirect_to;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_maybe_replace_login_logo')){
    /**
	 * This function MUST be called inside the 'login_enqueue_scripts' action hook.
	 *
     * @return string
     */
    function ldc_maybe_replace_login_logo(){
        if(!doing_action('login_enqueue_scripts')){ // Too early or too late.
	        return false;
	    }
        $custom_login_logo = (array) ldc_get_cache('custom_login_logo', []);
        if(!$custom_login_logo){
            return;
        } ?>
        <style type="text/css">
            #login h1 a,
            .login h1 a {
                background-image: url(<?php echo $custom_login_logo[0]; ?>);
                background-size: <?php echo $custom_login_logo[1]; ?>px <?php echo $custom_login_logo[2]; ?>px;
                height: <?php echo $custom_login_logo[2]; ?>px;
                width: <?php echo $custom_login_logo[1]; ?>px;
            }
        </style><?php
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Opis Closure
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_serializable_closure')){
	/**
	 * @return Opis\Closure\SerializableClosure|WP_Error
	 */
	function ldc_serializable_closure($closure = null){
		$lib = ldc_use_serializable_closure();
		if(is_wp_error($lib)){
			return $lib;
		}
		return new \Opis\Closure\SerializableClosure($closure);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_serializable_closure')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_use_serializable_closure($ver = '3.6.3'){
		$key = 'serializable-closure-' . $ver;
		if(ldc_isset_cache($key)){
			return (string) ldc_get_cache($key, '');
		}
		$class = 'Opis\Closure\SerializableClosure';
		if(class_exists($class)){
			return ''; // Already handled outside of this function.
		}
		$dir = ldc_remote_lib('https://github.com/opis/closure/archive/refs/tags/' . $ver . '.zip', 'closure-' . $ver);
		if(is_wp_error($dir)){
			return $dir;
		}
		$file = $dir . '/autoload.php';
		if(!file_exists($file)){
			return ldc_error(translate('File doesn&#8217;t exist?'), $file);
		}
		require_once($file);
		if(!class_exists($class)){
			return ldc_error(sprintf(translate('Missing parameter(s): %s'), $class) . '.');
		}
		ldc_set_cache($key, $dir);
		return $dir;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// PHP Simple HTML DOM Parser
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_file_get_html')){
	/**
	 * @return simple_html_dom|WP_Error
	 */
	function ldc_file_get_html(...$args){
		$remote_lib = ldc_use_simple_html_dom();
		if(is_wp_error($remote_lib)){
			return $remote_lib;
		}
		return file_get_html(...$args);
	}
}

if(!function_exists('ldc_str_get_html')){
	/**
	 * @return simple_html_dom|WP_Error
	 */
	function ldc_str_get_html(...$args){
		$remote_lib = ldc_use_simple_html_dom();
		if(is_wp_error($remote_lib)){
			return $remote_lib;
		}
		return str_get_html(...$args);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_simple_html_dom')){
	/**
	 * @return bool|WP_Error
	 */
	function ldc_use_simple_html_dom($ver = '1.9.1'){
		$key = 'simplehtmldom-' . $ver;
		if(ldc_isset_cache($key)){
			return (string) ldc_get_cache($key, '');
		}
		$class = 'simple_html_dom';
		if(class_exists($class)){
			return ''; // Already handled outside of this function.
		}
		$dir = ldc_remote_lib('https://github.com/simplehtmldom/simplehtmldom/archive/refs/tags/' . $ver . '.zip', 'simplehtmldom-' . $ver);
		if(is_wp_error($dir)){
			return $dir;
		}
		$file = $dir . '/simple_html_dom.php';
		if(!file_exists($file)){
			return ldc_error(translate('File doesn&#8217;t exist?'), $file);
		}
		require_once($file);
		if(!class_exists($class)){
			return ldc_error(sprintf(translate('Missing parameter(s): %s'), $class) . '.');
		}
		ldc_set_cache($key, $dir);
		return $dir;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// PHP_XLSXWriter
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_xlsx_writer')){
	/**
	 * @return XLSXWriter|WP_Error
	 */
	function ldc_xlsx_writer(){
		$lib = ldc_use_xlsxwriter();
		if(is_wp_error($lib)){
			return $lib;
		}
		return new \XLSXWriter;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_xlsxwriter')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_use_xlsxwriter($ver = '0.39'){
		$key = 'xlsxwriter-' . $ver;
	    if(ldc_isset_cache($key)){
	        return (string) ldc_get_cache($key, '');
	    }
		$class = 'XLSXWriter';
		if(class_exists($class)){
	        return ''; // Already handled outside of this function.
	    }
		$dir = ldc_remote_lib('https://github.com/mk-j/PHP_XLSXWriter/archive/refs/tags/' . $ver . '.zip', 'PHP_XLSXWriter-' . $ver);
		if(is_wp_error($dir)){
			return $dir;
		}
		$file = $dir . '/xlsxwriter.class.php';
		if(!file_exists($file)){
			return ldc_error(translate('File doesn&#8217;t exist?'), $file);
		}
		require_once($file);
		if(!class_exists($class)){
			return ldc_error(sprintf(translate('Missing parameter(s): %s'), $class) . '.');
		}
		ldc_set_cache($key, $dir);
		return $dir;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Plugin cache
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_get_plugin_array_cache')){
	/**
	 * @return mixed
	 */
	function ldc_get_plugin_array_cache($array_key = '', $key = '', $default = null){
		$array = (array) ldc_get_cache($array_key, []);
		return isset($array[$key]) ? $array[$key] : $default;
	}
}

if(!function_exists('ldc_get_plugin_cache')){
	/**
	 * @return mixed
	 */
	function ldc_get_plugin_cache($key = '', $default = null){
		$group = ldc_plugin_group();
		$value = wp_cache_get($key, $group, false, $found);
		if($found){
			return $value;
		}
	    return $default;
	}
}

if(!function_exists('ldc_isset_plugin_array_cache')){
	/**
	 * @return bool
	 */
	function ldc_isset_plugin_array_cache($array_key = '', $key = ''){
		$array = (array) ldc_get_cache($array_key, []);
		return isset($array[$key]);
	}
}

if(!function_exists('ldc_isset_plugin_cache')){
	/**
	 * @return bool
	 */
	function ldc_isset_plugin_cache($key = ''){
		$group = ldc_plugin_group();
		$value = wp_cache_get($key, $group, false, $found);
	    return $found;
	}
}

if(!function_exists('ldc_set_plugin_array_cache')){
	/**
	 * @return bool
	 */
	function ldc_set_plugin_array_cache($array_key = '', $key = '', $data = null){
		$array = (array) ldc_get_cache($array_key, []);
		$array[$key] = $data;
		return ldc_set_cache($array_key, $array);
	}
}

if(!function_exists('ldc_set_plugin_cache')){
	/**
	 * @return bool
	 */
	function ldc_set_plugin_cache($key = '', $data = null){
		$group = ldc_plugin_group();
		return wp_cache_set($key, $data, $group);
	}
}

if(!function_exists('ldc_unset_plugin_array_cache')){
	/**
	 * @return bool
	 */
	function ldc_unset_plugin_array_cache($array_key = '', $key = ''){
		$array = (array) ldc_get_cache($array_key, []);
		if(isset($array[$key])){
			unset($array[$key]);
		}
		return ldc_set_cache($array_key, $array);
	}
}

if(!function_exists('ldc_unset_plugin_cache')){
	/**
	 * @return bool
	 */
	function ldc_unset_plugin_cache($key = ''){
		$group = ldc_plugin_group();
		return wp_cache_delete($key, $group);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_plugin_group(){
    $file = ldc_caller_file(2); // Two levels above.
    $group = ldc_plugin_prefix('', $file);
    return $group;
}


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Plugin hooks
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_add_plugin_action($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
    $hook_name = ldc_plugin_hook_name($hook_name);
    return ldc_on($hook_name, $callback, $priority, $accepted_args);
}

/**
 * @return string
 */
function ldc_add_plugin_action_once($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
    $hook_name = ldc_plugin_hook_name($hook_name);
    return ldc_one($hook_name, $callback, $priority, $accepted_args);
}

/**
 * @return string
 */
function ldc_add_plugin_filter($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
    $hook_name = ldc_plugin_hook_name($hook_name);
    return ldc_on($hook_name, $callback, $priority, $accepted_args);
}

/**
 * @return string
 */
function ldc_add_plugin_filter_once($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
    $hook_name = ldc_plugin_hook_name($hook_name);
    return ldc_one($hook_name, $callback, $priority, $accepted_args);
}

/**
 * @return mixed
 */
function ldc_apply_plugin_filters($hook_name = '', $value = null, ...$arg){
    $hook_name = ldc_plugin_hook_name($hook_name);
    return apply_filters($hook_name, $value, ...$arg);
}

/**
 * @return bool
 */
function ldc_did_plugin_action($hook_name = ''){
	$hook_name = ldc_plugin_hook_name($hook_name);
	return did_action($hook_name);
}

/**
 * @return bool
 */
function ldc_did_plugin_filter($hook_name = ''){
	$hook_name = ldc_plugin_hook_name($hook_name);
	return did_filter($hook_name);
}

/**
 * @return void
 */
function ldc_do_plugin_action($hook_name = '', ...$arg){
	$hook_name = ldc_plugin_hook_name($hook_name);
	do_action($hook_name, ...$arg);
}

/**
 * @return void
 */
function ldc_do_plugin_action_ref_array($hook_name = '', $args = []){
    $hook_name = ldc_plugin_hook_name($hook_name);
	do_action_ref_array($hook_name, $args);
}

/**
 * @return bool
 */
function ldc_doing_plugin_action($hook_name = ''){
    $hook_name = ldc_plugin_hook_name($hook_name);
    return doing_filter($hook_name);
}

/**
 * @return bool
 */
function ldc_doing_plugin_filter($hook_name = ''){
	$hook_name = ldc_plugin_hook_name($hook_name);
    return doing_filter($hook_name);
}

/**
 * @return bool
 */
function ldc_has_plugin_action($hook_name = '', $callback = false){
	$hook_name = ldc_plugin_hook_name($hook_name);
    return has_filter($hook_name, $callback);
}

/**
 * @return bool
 */
function ldc_has_plugin_filter($hook_name = '', $callback = false){
	$hook_name = ldc_plugin_hook_name($hook_name);
    return has_filter($hook_name, $callback);
}

/**
 * @return bool
 */
function ldc_remove_plugin_action($hook_name = '', $callback = null, $priority = 10){
    $hook_name = ldc_plugin_hook_name($hook_name);
    return remove_filter($hook_name, $callback, $priority);
}

/**
 * @return bool
 */
function ldc_remove_plugin_filter($hook_name = '', $callback = null, $priority = 10){
    $hook_name = ldc_plugin_hook_name($hook_name);
    return remove_filter($hook_name, $callback, $priority);
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_plugin_hook_name($hook_name = ''){
    $file = ldc_caller_file(2); // Two levels above.
    $hook_name = ldc_plugin_prefix($hook_name, $file);
    return $hook_name;
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Plugin Update Checker
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_build_update_checker')){
	/**
	 * @return YahnisElsts\PluginUpdateChecker\v5p4\Vcs\BaseChecker|WP_Error
	 */
	function ldc_build_update_checker(...$args){
		$md5 = ldc_md5($args);
		if(ldc_isset_array_cache('update_checkers', $md5)){
			return ldc_get_array_cache('update_checkers', $md5);
		}
		$remote_lib = ldc_use_plugin_update_checker();
		if(is_wp_error($remote_lib)){
			return $remote_lib;
		}
		$update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(...$args);
		ldc_set_array_cache('update_checkers', $md5, $update_checker);
		return $update_checker;
	}
}

if(!function_exists('ldc_set_update_license')){
	/**
	 * @return void
	 */
	function ldc_set_update_license($slug = '', $license = ''){
		if(empty($slug) or empty($license)){
			return;
		}
		ldc_set_array_cache('update_licenses', $slug, $license);
		ldc_add_filter_once('puc_request_info_query_args-' . $slug, 'ldc_maybe_set_update_license');
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_maybe_set_update_license')){
	/**
	 * This function MUST be called inside the 'puc_request_info_query_args-SLUG' filter hook.
	 *
	 * @return array
	 */
	function ldc_maybe_set_update_license($queryArgs){
		$current_filter = current_filter();
		if(!ldc_str_starts_with($current_filter, 'puc_request_info_query_args-')){ // Too early or too late.
	        return;
	    }
		$slug = str_replace('puc_request_info_query_args-', '', $current_filter);
		if(!ldc_isset_array_cache('update_licenses', $slug)){
			return $queryArgs;
		}
		$queryArgs['license'] = (string) ldc_get_array_cache('update_licenses', $slug, '');
		return $queryArgs;
	}
}

if(!function_exists('ldc_use_plugin_update_checker')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_use_plugin_update_checker($ver = '5.5'){
		$key = 'plugin-update-checker-' . $ver;
	    if(ldc_isset_cache($key)){
	        return (string) ldc_get_cache($key, '');
	    }
		$class = 'YahnisElsts\PluginUpdateChecker\v5\PucFactory';
		if(class_exists($class)){
			return ''; // Already handled outside of this function.
		}
		$dir = ldc_remote_lib('https://github.com/YahnisElsts/plugin-update-checker/archive/refs/tags/v' . $ver . '.zip', 'plugin-update-checker-' . $ver);
		if(is_wp_error($dir)){
			return $dir;
		}
		$file = $dir . '/plugin-update-checker.php';
		if(!file_exists($file)){
			return ldc_error(translate('File doesn&#8217;t exist?'), $file);
		}
		require_once($file);
		if(!class_exists($class)){
			return ldc_error(sprintf(translate('Missing parameter(s): %s'), $class) . '.');
		}
		ldc_set_cache($key, $dir);
		return $dir;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Plugins
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_are_plugins_active')){
    /**
     * @return bool
     */
    function ldc_are_plugins_active($plugins = []){
        if(!is_array($plugins)){
            return false;
        }
        foreach($plugins as $plugin){
            if(!ldc_is_plugin_active($plugin)){
                return false;
            }
        }
        return true;
    }
}

if(!function_exists('ldc_is_plugin_active')){
    /**
     * @return bool
     */
    function ldc_is_plugin_active($plugin = ''){
        if(ldc_isset_array_cache('active_plugins', $plugin)){
            return (bool) ldc_get_array_cache('active_plugins', $plugin, false);
        }
        if(!function_exists('is_plugin_active')){
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $status = is_plugin_active($plugin);
        ldc_set_array_cache('active_plugins', $plugin, $status);
        return $status;
    }
}

if(!function_exists('ldc_is_plugin_deactivating')){
    /**
     * @return bool
     */
    function ldc_is_plugin_deactivating($file = ''){
        global $pagenow;
        if(!$file){
            return false;
        }
        $plugin_file = ldc_plugin_file($file);
        if(is_wp_error($plugin_file)){
            return false; // File is not a plugin.
        }
        return (is_admin() and 'plugins.php' === $pagenow and isset($_GET['action'], $_GET['plugin']) and 'deactivate' === $_GET['action'] and plugin_basename($file) === $_GET['plugin']);
    }
}

if(!function_exists('ldc_mu_plugins')){
    /**
     * @return array
     */
    function ldc_mu_plugins(){
        if(ldc_isset_cache('mu_plugins')){
            return (array) ldc_get_cache('mu_plugins', []);
        }
        $mu_plugins = wp_get_mu_plugins();
        ldc_set_cache('mu_plugins', $mu_plugins);
        return $mu_plugins;
    }
}

if(!function_exists('ldc_plugin_basename')){
    /**
     * @return string|WP_Error
     */
    function ldc_plugin_basename($file = ''){
        if(!$file){
            $file = ldc_caller_file(1); // One level above.
            if(is_wp_error($file)){
                return $file;
            }
        }
        $plugin_file = ldc_plugin_file($file);
        if(is_wp_error($plugin_file)){
            return $plugin_file;
        }
        return plugin_basename($plugin_file);
    }
}

if(!function_exists('ldc_plugin_data')){
    /**
     * @return array|WP_Error
     */
    function ldc_plugin_data($file = '', $markup = true, $translate = true){
        if(!$file){
            $file = ldc_caller_file(1); // One level above.
            if(is_wp_error($file)){
                return $file;
            }
        }
        $plugin_file = ldc_plugin_file($file);
        if(is_wp_error($plugin_file)){
            return $plugin_file;
        }
        $data = [
            'markup' => $markup,
            'plugin_file' => $plugin_file,
            'translate' => $translate,
        ];
        $md5 = ldc_md5($data);
        if(ldc_isset_array_cache('plugin_data', $md5)){
            return (array) ldc_get_array_cache('plugin_data', $md5, []);
        }
        if(!function_exists('get_plugin_data')){
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        $data = get_plugin_data($plugin_file, $markup, $translate);
        ldc_set_array_cache('plugin_data', $md5, $data);
        return $data;
    }
}

if(!function_exists('ldc_plugin_file')){
    /**
     * @return string|WP_Error
     */
    function ldc_plugin_file($file = ''){
        global $wp_plugin_paths;
        if(!$file){
            $file = ldc_caller_file(1); // One level above.
            if(is_wp_error($file)){
                return $file;
            }
        } else {
            if(!file_exists($file)){
                $error_msg = translate('File does not exist! Please double check the name and try again.');
                return ldc_error($error_msg);
            }
        }
        $md5 = md5($file);
        if(ldc_isset_array_cache('plugin_files', $md5)){
            return (string) ldc_get_array_cache('plugin_files', $md5, '');
        }
        $file = wp_normalize_path($file); // $wp_plugin_paths contains normalized paths.
        arsort($wp_plugin_paths);
        foreach($wp_plugin_paths as $dir => $realdir){
            if(!ldc_str_starts_with($file, $realdir)){
                continue;
            }
            $file = $dir . substr($file, strlen($realdir));
            break;
        }
        $mu_plugin_dir = wp_normalize_path(WPMU_PLUGIN_DIR);
        $plugin_dir = wp_normalize_path(WP_PLUGIN_DIR);
        if(preg_match('#^' . preg_quote($plugin_dir, '#') . '/#', $file)){ // File is a plugin.
            $dir = $plugin_dir;
            $file = preg_replace('#^' . preg_quote($plugin_dir, '#') . '/#', '', $file); // Get relative path from plugins directory.
            $mu_plugin = false;
        } elseif(preg_match('#^' . preg_quote($mu_plugin_dir, '#') . '/#', $file)){ // File is a must-use plugin.
            $dir = $mu_plugin_dir;
            $file = preg_replace('#^' . preg_quote($mu_plugin_dir, '#') . '/#', '', $file); // Get relative path from must-use plugins directory.
            $mu_plugin = true;
        } else { // File is not a plugin.
            $error_msg = translate('Plugin not found.');
            return ldc_error($error_msg);
        }
        $file = trim($file, '/'); // Note: This may not actually be necessary.
        $parts = explode('/', $file);
        if(count($parts) < 2){ // The entire plugin consists of just a single PHP file, like Hello Dolly.
            if($mu_plugin or ldc_is_plugin_active($file)){ // Plugin is a must-use plugin or plugin is active.
                $file = $dir . '/' . $file;
                ldc_set_array_cache('plugin_files', $md5, $file);
                return $file;
            }
            $error_msg = translate('Plugin not found.');
            return ldc_error($error_msg); // Plugin is inactive.
        }
        $dir_path = trailingslashit($parts[0]);
        if($mu_plugin){ // Rarely needed.
            $mu_plugins = ldc_mu_plugins();
            foreach($mu_plugins as $mu_plugin){
                if(!ldc_str_starts_with($mu_plugin, $dir_path)){
                    continue;
                }
                $file = $dir . '/' . $mu_plugin;
                ldc_set_array_cache('plugin_files', $md5, $file);
                return $file; // Plugin is a must-use plugin.
            }
            $error_msg = translate('An unexpected error occurred. Something may be wrong with WordPress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.');
            $error_msg = ldc_first_p($error_msg);
            return ldc_error($error_msg); // An unexpected error occurred.
        }
        $active_plugins = (array) get_option('active_plugins', []);
        foreach($active_plugins as $active_plugin){
            if(!ldc_str_starts_with($active_plugin, $dir_path)){
                continue;
            }
            $file = $dir . '/' . $active_plugin;
            ldc_set_array_cache('plugin_files', $md5, $file);
            return $file; // Plugin is active.
        }
        $active_sitewide_plugins = (array) get_site_option('active_sitewide_plugins', []);
        $active_sitewide_plugins = array_keys($active_sitewide_plugins);
        foreach($active_sitewide_plugins as $active_sitewide_plugin){
            if(!ldc_str_starts_with($active_sitewide_plugin, $dir_path)){
                continue;
            }
            $file = $dir . '/' . $active_sitewide_plugin;
            ldc_set_cache($key, $file);
            return $file; // Plugin is active for the entire network.
        }
        $error_msg = translate('Plugin not found.');
        return ldc_error($error_msg); // Plugin is inactive.
    }
}

if(!function_exists('ldc_plugin_folder')){
    /**
     * @return string|WP_Error
     */
    function ldc_plugin_folder($file = ''){
        if(!$file){
            $file = ldc_caller_file(1); // One level above.
            if(is_wp_error($file)){
                return $file;
            }
        }
        $basename = ldc_plugin_basename($file);
        if(is_wp_error($basename)){
            return $basename;
        }
        $parts = explode('/', $basename);
        if(count($parts) < 2){ // The entire plugin consists of just a single PHP file, like Hello Dolly.
            $error_msg = 'The entire plugin consists of just a single PHP file.';
            return ldc_error($error_msg); // Ignore.
        }
        return $parts[0];
    }
}

if(!function_exists('ldc_plugin_load')){
    /**
     * @return __Singleton|WP_Error
     */
    function ldc_plugin_load($file = ''){
        if(!$file){
            $file = ldc_caller_file(1); // One level above.
            if(is_wp_error($file)){
                return $file;
            }
        }
        $plugin_file = ldc_plugin_file($file);
        if(is_wp_error($plugin_file)){
            return $plugin_file;
        }
        $plugin_slug = ldc_plugin_slug('', $plugin_file);
        $class_file = plugin_dir_path($plugin_file) . 'class-' . $plugin_slug . '.php';
        if(!file_exists($class_file)){
            $error_msg = translate('File does not exist! Please double check the name and try again.');
            return ldc_error($error_msg);
        }
        require_once($class_file);
        $class_name = ldc_plugin_prefix('', $plugin_file);
        $plugin = ldc_get_instance($class_name);
        return $plugin;
    }
}

if(!function_exists('ldc_plugin_meta')){
    /**
     * @return string|WP_Error
     */
    function ldc_plugin_meta($key = '', $file = '', $markup = false, $translate = false){
        if(!$file){
            $file = ldc_caller_file(1); // One level above.
            if(is_wp_error($file)){
                return $file;
            }
        }
        $data = ldc_plugin_data($file, $markup, $translate);
        if(is_wp_error($data)){
            return $data;
        }
        if(isset($data[$key])){
            $arr = $data;
        } elseif(isset($data['sections'], $data['sections'][$key])){
            $arr = $data['sections'];
        } else {
            $error_msg = '"' . $key . '" ' . translate('(not found)');
            return ldc_error($error_msg);
        }
        return $arr[$key];
    }
}

if(!function_exists('ldc_plugin_prefix')){
    /**
     * @return string
     */
    function ldc_plugin_prefix($str = '', $file = ''){
        if(!$file){
            $file = ldc_caller_file(1); // One level above.
            if(is_wp_error($file)){
                return ''; // Silence is golden.
            }
        }
        $plugin_folder = ldc_plugin_folder($file);
        if(is_wp_error($plugin_folder)){
            return ''; // Silence is golden.
        }
        return ldc_str_prefix($str, $plugin_folder);
    }
}

if(!function_exists('ldc_plugin_slug')){
    /**
     * @return string
     */
    function ldc_plugin_slug($str = '', $file = ''){
        if(!$file){
            $file = ldc_caller_file(1); // One level above.
            if(is_wp_error($file)){
                return ''; // Silence is golden.
            }
        }
        $plugin_folder = ldc_plugin_folder($file);
        if(is_wp_error($plugin_folder)){
            return ''; // Silence is golden.
        }
        return ldc_str_slug($str, $plugin_folder);
    }
}

if(!function_exists('ldc_plugin_update_checker')){
    /**
     * @return YahnisElsts\PluginUpdateChecker\v5p4\Vcs\BaseChecker|WP_Error
     */
    function ldc_plugin_update_checker($file = ''){
        if(!$file){
            $file = ldc_caller_file(1); // One level above.
            if(is_wp_error($file)){
                return $file;
            }
        }
        $plugin_file = ldc_plugin_file($file);
        if(is_wp_error($plugin_file)){
            return $plugin_file;
        }
        $plugin_slug = ldc_plugin_slug('', $plugin_file);
        $plugin_uri = ldc_plugin_meta('PluginURI', $plugin_file);
        $update_uri = ldc_plugin_meta('UpdateURI', $plugin_file);
        if(is_wp_error($update_uri)){
            if(is_wp_error($plugin_uri)){
                return ldc_error(translate('A valid URL was not provided.'));
            }
            $host_url = ldc_host_url($update_uri);
            if(!$host_url){
                return ldc_error(translate('Invalid URL Provided.'));
            }
            $update_uri = add_query_arg([
                'action' => 'get_metadata',
                'slug' => $plugin_slug,
            ], path_join($host_url, 'wp-update-server'));
        }
        if(!wp_http_validate_url($update_uri)){
            return ldc_error(translate('Invalid URL.'));
        }
        $update_checker = ldc_build_update_checker($update_uri, $plugin_file, $plugin_slug);
        if(is_wp_error($update_checker)){
            return $update_checker;
        }
        $constant_name = strtoupper(ldc_plugin_prefix('license', $plugin_file));
        if(!defined($constant_name)){
            return $update_checker;
        }
        $constant_value = constant($constant_name);
        ldc_set_update_license($plugin_slug, $constant_value);
        return $update_checker;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Queries
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_get_posts_query')){
	/**
	 * @return WP_Query
	 */
	function ldc_get_posts_query($args = []){
		$defaults = [
			'category' => 0,
			'exclude' => [],
			'include' => [],
			'meta_key' => '',
			'meta_value' => '',
			'numberposts' => 5,
			'order' => 'DESC',
			'orderby' => 'date',
			'post_type' => 'post',
			'suppress_filters' => true,
		];
		$parsed_args = wp_parse_args($args, $defaults);
		if(empty($parsed_args['post_status'])){
			$parsed_args['post_status'] = ('attachment' === $parsed_args['post_type']) ? 'inherit' : 'publish';
		}
		if(!empty($parsed_args['numberposts']) and empty($parsed_args['posts_per_page'])){
			$parsed_args['posts_per_page'] = $parsed_args['numberposts'];
		}
		if(!empty($parsed_args['category'])){
			$parsed_args['cat'] = $parsed_args['category'];
		}
		if(!empty($parsed_args['include'])){
			$incposts = wp_parse_id_list($parsed_args['include']);
			$parsed_args['posts_per_page'] = count($incposts);  // Only the number of posts included.
			$parsed_args['post__in'] = $incposts;
		} elseif(!empty($parsed_args['exclude'])){
			$parsed_args['post__not_in'] = wp_parse_id_list($parsed_args['exclude']);
		}
		$parsed_args['ignore_sticky_posts'] = true;
		$parsed_args['no_found_rows'] = true;
		$query = new \WP_Query;
		$query->query($parsed_args);
		return $query;
	}
}

if(!function_exists('ldc_get_user')){
	/**
	 * Alias for wp_get_current_user, get_user_by, get_userdata.
	 *
	 * @return bool|WP_User
	 */
	function ldc_get_user($user = null){
	    if(is_null($user)){
	        return (is_user_logged_in() ? wp_get_current_user() : false);
		}
	    if($user instanceof \WP_User){
	        return $user->exists() ? $user : false;
	    }
	    if(is_numeric($user)){
	        return get_userdata($user);
	    }
	    if(!is_string($user)){
	        return false;
	    }
	    if(username_exists($user)){
	        return get_user_by('login', $user);
	    }
	    if(!is_email($user)){
	        return false;
	    }
	    return get_user_by('email', $email);
	}
}

if(!function_exists('ldc_get_users_query')){
	/**
	 * @return WP_User_Query
	 */
	function ldc_get_users_query($args = []){
	    $defaults = [
	        'count_total' => false,
	    ];
	    $parsed_args = wp_parse_args($args, $defaults);
	    $query = new \WP_User_Query($parsed_args);
	    return $query;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Remote
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_download_url')){
	/**
	 * Alias for download_url.
	 *
	 * @return string|WP_Error
	 */
	function ldc_download_url($url = '', $args = []){
		if(!$url){
            $error_msg = translate('Invalid URL Provided.');
			return ldc_error($error_msg);
		}
        $args = wp_parse_args($args, [
            'filename' => '',
            'timeout' => 300,
        ]);
        $args = ldc_sanitize_remote_args($args, $url);
        $filename = $args['filename'];
        if($filename){
            $filename = ldc_check_upload_dir($filename);
            if(is_wp_error($filename)){
                return $filename;
            }
        } else {
            $download_dir = ldc_download_dir();
            if(is_wp_error($download_dir)){
                return $download_dir;
            }
            $url_filename = ldc_basename($url);
            $unique_filename = wp_unique_filename($download_dir, $url_filename);
            $filename = path_join($download_dir, $unique_filename);
            $args['filename'] = $filename;
        }
		$args['stream'] = true;
		$response = wp_safe_remote_get($url, $args);
		if(is_wp_error($response)){
			unlink($filename);
			return $response;
		}
		$code = wp_remote_retrieve_response_code($response);
		if(!ldc_is_success($code)){
			$body = ldc_get_file_sample($filename);
			$message = ldc_get_response_message($response);
			$data = [
				'body' => $body,
				'code' => $code,
			];
			unlink($filename);
			return ldc_error($message, $data);
		}
		return $filename;
	}
}

if(!function_exists('ldc_get_content_type')){
	/**
	 * Alias for WP_REST_Request::get_content_type.
	 *
	 * Retrieves the Content-Type of the request or response.
	 *
	 * @return array
	 */
	function ldc_get_content_type($r = []){
		$content_type = (array) wp_remote_retrieve_header($r, 'Content-Type');
		if(!$content_type){
			return $content_type;
		}
		$value = $content_type[0];
		$parameters = '';
		if(strpos($value, ';')){
			list($value, $parameters) = explode(';', $value, 2);
		}
		$value = strtolower($value);
		if(!str_contains($value, '/')){
			return [];
		}
		list($type, $subtype) = explode('/', $value, 2); // Parse type and subtype out.
		$data = compact('value', 'type', 'subtype', 'parameters');
		$data = array_map('trim', $data);
		return $data;
	}
}

if(!function_exists('ldc_get_file_sample')){
	/**
	 * @return string
	 */
	function ldc_get_file_sample($tmpfname = ''){
		if(!is_file($tmpfname)){
			return '';
		}
		$tmpf = fopen($tmpfname, 'rb'); // Retrieve a sample of the response body for debugging purposes.
		if(!$tmpf){
			return '';
		}
		$response_size = apply_filters('download_url_error_max_body_size', KB_IN_BYTES); // Filters the maximum error response body size. Default 1 KB.
		$sample = fread($tmpf, $response_size);
		fclose($tmpf);
		return $sample;
	}
}

if(!function_exists('ldc_get_response_message')){
	/**
	 * @return string
	 */
	function ldc_get_response_message($response = []){
		$message = wp_remote_retrieve_response_message($response);
        $message = trim($message);
		if($message){
			return $message;
		}
		$message = ldc_get_status_message($response);
		return $message;
	}
}

if(!function_exists('ldc_get_status_message')){
	/**
	 * @return string
	 */
	function ldc_get_status_message($response = []){
		$code = wp_remote_retrieve_response_code($response);
		$message = get_status_header_desc($code);
        $message = trim($message);
		if($message){
			return $message;
		}
        if(ldc_is_success($code)){
            $message = 'Success';
        } else {
            $message = 'Error';
        }
		return $message;
	}
}

if(!function_exists('ldc_is_cloudflare')){
	/**
	 * @return bool
	 */
	function ldc_is_cloudflare(){
		return isset($_SERVER['CF-ray']); // TODO: Check for Cloudflare Enterprise.
	}
}

if(!function_exists('ldc_is_content_type')){
	/**
	 * @return bool
	 */
	function ldc_is_content_type($content_type = []){
		if(!ldc_array_keys_exists(['parameters', 'subtype', 'type', 'value'], $content_type)){
			return false;
		}
		$count = count($content_type);
		if(4 !== $count){
			return false;
		}
	    return true;
	}
}

if(!function_exists('ldc_is_json_content_type')){
	/**
	 * Checks if the request or response has specified a JSON Content-Type.
	 *
	 * @return bool
	 */
	function ldc_is_json_content_type($content_type = []){
		if(!$content_type){
			return wp_is_json_request(); // Checks whether current request is a JSON request, or is expecting a JSON response.
		}
		if(!ldc_is_content_type($content_type)){
			$content_type = ldc_get_content_type($content_type);
			if(!$content_type){
				return false;
			}
		}
		return wp_is_json_media_type($content_type['value']);
	}
}

if(!function_exists('ldc_is_success')){
	/**
     * Alias for is_success.
	 *
	 * @return bool
	 */
	function ldc_is_success($sc = 0){
        $sc = ldc_absint($sc);
		return ($sc >= 200 && $sc < 300);
	}
}

if(!function_exists('ldc_is_wp_http_request')){
	/**
	 * @return bool
	 */
	function ldc_is_wp_http_request($args = []){
		if(!is_array($args)){
			return false;
		}
		if(!$args){
			return true;
		}
		$wp_http_request_args = ['body', 'blocking', 'compress', 'cookies', 'decompress', 'filename', 'headers', 'httpversion', 'limit_response_size', 'method', 'redirection', 'reject_unsafe_urls', 'sslcertificates', 'sslverify', 'stream', 'timeout', 'user-agent'];
		$wp_http_request = true;
		foreach(array_keys($args) as $arg){
			if(!in_array($arg, $wp_http_request_args)){
				$wp_http_request = false;
				break;
			}
		}
		if(!isset($args['method'])){
			return $wp_http_request;
		}
		if(!in_array($args['method'], ['DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT', 'TRACE'])){
			return false;
		}
		return true;
	}
}

if(!function_exists('ldc_is_wp_http_requests_response')){
	/**
	 * @return bool
	 */
	function ldc_is_wp_http_requests_response($response = []){
		if(!ldc_array_keys_exists(['body', 'cookies', 'filename', 'headers', 'http_response', 'response'], $response)){
			return false;
		}
		if(!$response['http_response'] instanceof \WP_HTTP_Requests_Response){
			return false;
		}
	    return true;
	}
}

if(!function_exists('ldc_json_decode')){
	/**
	 * Alias for json_decode.
	 *
	 * Differs from json_decode in that it will return a WP_Error on failure.
	 *
	 * Retrieves the parameters from a JSON-formatted body.
	 *
	 * @return array|stdClass|WP_Error
	 */
	function ldc_json_decode($json = '', $associative = null, $depth = 512, $flags = 0){
		$json = trim($json);
		if($associative or ($flags & JSON_OBJECT_AS_ARRAY)){
			$empty = [];
		} else {
			$empty = new \stdClass;
		}
		if(empty($json)){
			return $empty;
		}
		$params = json_decode($json, $associative, $depth, $flags); // Parses the JSON parameters.
		if(is_null($params) and JSON_ERROR_NONE !== json_last_error()){ // Check for a parsing error.
			$error_data = [
				'json_error_code' => json_last_error(),
				'json_error_message' => json_last_error_msg(),
				'status' => \WP_Http::BAD_REQUEST,
			];
            $error_msg = translate('Invalid JSON body passed.');
			return ldc_error($error_msg, $error_data);
		}
		return $params;
	}
}

if(!function_exists('ldc_parse_response')){
	/**
	 * @return array|string|WP_Error
	 */
	function ldc_parse_response($response = []){
		if(is_wp_error($response)){
			return $response;
		}
		if(!ldc_is_wp_http_requests_response($response)){
            $error_msg = translate('Invalid data provided.');
	 		return ldc_error($error_msg, $response);
	 	}
        $r = new \stdClass;
        $r->body = trim(wp_remote_retrieve_body($response));
        $r->code = absint(wp_remote_retrieve_response_code($response));
        $r->cookies = wp_remote_retrieve_cookies($response);
        $r->headers = wp_remote_retrieve_headers($response);
        $r->is_json = ldc_is_json_content_type($response);
        $r->is_success = ldc_is_success($r->code);
        $r->json_params = [];
        $r->message = ldc_get_response_message($response);
        $r->raw_response = $response;
        $r->status = ldc_get_status_message($response);
        $r->wp_error = new \WP_Error;
        if($r->is_json){
            $json_params = ldc_json_decode($r->body, true);
            if($r->is_success){
                if(is_wp_error($json_params)){
                    $r->is_success = false;
                    $r->wp_error->merge_from($json_params);
                } else {
                    $r->json_params = $json_params;
                }
            } else {
                $maybe_error = ldc_seems_error($json_params);
                if(is_wp_error($maybe_error)){
                    $r->wp_error->merge_from($maybe_error);
                } else {
                    $error = ldc_error($r->message, $r->raw_response);
                    $r->wp_error->merge_from($error);
                }
            }
        } else {
            if(!$r->is_success){
                $error = ldc_error($r->message, $r->raw_response);
                $r->wp_error->merge_from($error);
            }
        }
		return $r;
	}
}

if(!function_exists('ldc_remote_country')){
	/**
	 * @return string
	 */
	function ldc_remote_country(){
		switch(true){
			case !empty($_SERVER['HTTP_CF_IPCOUNTRY']):
				$country = $_SERVER['HTTP_CF_IPCOUNTRY']; // Cloudflare.
				break;
			case is_callable(['wfUtils', 'IP2Country']):
				$country = \wfUtils::IP2Country(ldc_remote_ip()); // Wordfence.
				break;
			default:
				$country = '';
		}
		return strtoupper($country); // ISO 3166-1 alpha-2.
	}
}

if(!function_exists('ldc_remote_delete')){
	/**
	 * @return array|string|WP_Error
	 */
	function ldc_remote_delete($url = '', $args = []){
		return ldc_remote_request('DELETE', $url, $args);
	}
}

if(!function_exists('ldc_remote_get')){
	/**
	 * @return array|string|WP_Error
	 */
	function ldc_remote_get($url = '', $args = []){
		return ldc_remote_request('GET', $url, $args);
	}
}

if(!function_exists('ldc_remote_head')){
	/**
	 * @return array|string|WP_Error
	 */
	function ldc_remote_head($url = '', $args = []){
		return ldc_remote_request('HEAD', $url, $args);
	}
}

if(!function_exists('ldc_remote_ip')){
	/**
	 * @return string
	 */
	function ldc_remote_ip($default = ''){
		switch(true){
			case !empty($_SERVER['HTTP_CF_CONNECTING_IP']):
				$ip = $_SERVER['HTTP_CF_CONNECTING_IP']; // Cloudflare.
				break;
			case (ldc_is_plugin_active('wordfence/wordfence.php') and is_callable(['wfUtils', 'getIP'])):
				$ip = \wfUtils::getIP(); // Wordfence.
				break;
			case !empty($_SERVER['HTTP_X_FORWARDED_FOR']):
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				break;
			case !empty($_SERVER['HTTP_X_REAL_IP']):
				$ip = $_SERVER['HTTP_X_REAL_IP'];
				break;
			case !empty($_SERVER['REMOTE_ADDR']):
				$ip = $_SERVER['REMOTE_ADDR'];
				break;
			default:
				return $default;
		}
		if(false === strpos($ip, ',')){
			$ip = trim($ip);
		} else {
			$ip = explode(',', $ip);
			$ip = array_map('trim', $ip);
			$ip = array_filter($ip);
			if(empty($ip)){
				return $default;
			}
			$ip = $ip[0];
		}
		if(!\WP_Http::is_ip_address($ip)){
			return $default;
		}
		return $ip;
	}
}

if(!function_exists('ldc_remote_lib')){
	/**
	 * @return string|WP_Error
	 */
	function ldc_remote_lib($url = '', $expected_dir = ''){
	    $key = md5($url);
	    if(ldc_isset_cache($key)){
	        return (string) ldc_get_cache($key, '');
	    }
		$download_dir = ldc_download_dir();
		if(is_wp_error($download_dir)){
			return $download_dir;
		}
		$fs = ldc_fs_direct();
		if(is_wp_error($fs)){
			return $fs;
		}
		$name = 'remote-lib-' . $key;
		$to = $download_dir . '/' . $name;
		if(empty($expected_dir)){
			$expected_dir = $to;
		} else {
			$expected_dir = ltrim($expected_dir, '/');
			$expected_dir = untrailingslashit($expected_dir);
			$expected_dir = $to . '/' . $expected_dir;
		}
		$dirlist = $fs->dirlist($expected_dir, false);
		if(!empty($dirlist)){
	        ldc_set_cache($key, $expected_dir);
			return $expected_dir; // Already exists.
		}
		//$file = ldc_download_url($url, $download_dir);
        $file = ldc_download_url($url); // TODO: send filename.
		if(is_wp_error($file)){
			return $file;
		}
		$result = unzip_file($file, $to);
		@unlink($file);
		if(is_wp_error($result)){
			$fs->rmdir($to, true);
			return $result;
		}
		if(!$fs->dirlist($expected_dir, false)){
			$fs->rmdir($to, true);
			return ldc_error(translate('Destination directory for file streaming does not exist or is not writable.'));
		}
	    ldc_set_cache($key, $expected_dir);
		return $expected_dir;
	}
}

if(!function_exists('ldc_remote_options')){
	/**
	 * @return array|string|WP_Error
	 */
	function ldc_remote_options($url = '', $args = []){
		return ldc_remote_request('OPTIONS', $url, $args);
	}
}

if(!function_exists('ldc_remote_patch')){
	/**
	 * @return array|string|WP_Error
	 */
	function ldc_remote_patch($url = '', $args = []){
		return ldc_remote_request('PATCH', $url, $args);
	}
}

if(!function_exists('ldc_remote_post')){
	/**
	 * @return array|string|WP_Error
	 */
	function ldc_remote_post($url = '', $args = []){
		return ldc_remote_request('POST', $url, $args);
	}
}

if(!function_exists('ldc_remote_put')){
	/**
	 * @return array|string|WP_Error
	 */
	function ldc_remote_put($url = '', $args = []){
		return ldc_remote_request('PUT', $url, $args);
	}
}

if(!function_exists('ldc_remote_request')){
	/**
	 * @return array|WP_Error
	 */
	function ldc_remote_request($method = '', $url = '', $args = []){
        $args = wp_parse_args($args);
		$args['method'] = $method;
		$args = ldc_sanitize_remote_args($args, $url);
		$response = wp_remote_request($url, $args);
		return ldc_parse_response($response);
	}
}

if(!function_exists('ldc_remote_trace')){
	/**
	 * @return array|string|WP_Error
	 */
	function ldc_remote_trace($url = '', $args = []){
		return ldc_remote_request('TRACE', $url, $args);
	}
}

if(!function_exists('ldc_sanitize_remote_args')){
	/**
	 * @return array
	 */
	function ldc_sanitize_remote_args($args = [], $url = ''){
		$args = wp_parse_args($args);
        if(isset($args['method'])){
            $args['method'] = strtoupper($args['method']);
        }
		if(!ldc_is_wp_http_request($args)){
			return [
				'body' => $args,
			];
		}
		if(isset($args['timeout'])){
			$args['timeout'] = ldc_sanitize_timeout($args['timeout']);
		}
		if(empty($args['cookies'])){
			if(!empty($url)){
				$location = wp_sanitize_redirect($url);
				if(wp_validate_redirect($location)){
					$args['cookies'] = $_COOKIE;
				}
			}
		}
		if(empty($args['user-agent'])){
			if(empty($_SERVER['HTTP_USER_AGENT'])){
				$args['user-agent'] = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36'; // Example Chrome UA string: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent#chrome_ua_string
			} else {
				$args['user-agent'] = $_SERVER['HTTP_USER_AGENT'];
			}
		}
		if(isset($args['body']) and is_array($args['body']) and ldc_is_json_content_type($args)){
			$args['body'] = wp_json_encode($args['body']);
		}
		return $args;
	}
}

if(!function_exists('ldc_sanitize_timeout')){
	/**
	 * @return int
	 */
	function ldc_sanitize_timeout($timeout = 0){
		$timeout = (int) $timeout;
		if($timeout < 0){
			$timeout = 0;
		}
		$max_execution_time = (int) ini_get('max_execution_time');
		if(0 !== $max_execution_time){
			if(0 === $timeout or $timeout > $max_execution_time){
				$timeout = $max_execution_time - 1;
			}
		}
		if(ldc_is_cloudflare()){
			if(0 === $timeout or $timeout > 98){
				$timeout = 98; // If the max_execution_time is set to greater than 98 seconds, reduce it a bit to prevent edge-case timeouts that may happen before the page is fully loaded. TODO: Check for Cloudflare Enterprise. See: https://developers.cloudflare.com/support/troubleshooting/cloudflare-errors/troubleshooting-cloudflare-5xx-errors/#error-524-a-timeout-occurred.
			}
		}
		return $timeout;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Rewrite
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_add_external_rule')){
	/**
	 * @return void
	 */
	function ldc_add_external_rule($regex = '', $query = '', $plugin_file = ''){
		$rule = [
			'plugin_file' => $plugin_file,
			'query' => str_replace(site_url('/'), '', $query),
			'regex' => str_replace(site_url('/'), '', $regex),
		];
		$md5 = ldc_md5($rule);
		if(doing_action('generate_rewrite_rules')){ // Just in time.
			if(ldc_isset_array_cache('external_rules', $md5)){
				return; // Already exists.
			}
			ldc_maybe_add_external_rule($rule);
			ldc_add_action_once('admin_notices', 'ldc_maybe_add_external_rules_notice');
			return;
		}
		if(did_action('generate_rewrite_rules')){ // Too late.
			return;
		}
		ldc_set_array_cache('external_rules', $md5, $rule);
	    ldc_add_action_once('generate_rewrite_rules', 'ldc_maybe_add_external_rules');
		ldc_add_action_once('admin_notices', 'ldc_maybe_add_external_rules_notice');
	}
}

if(!function_exists('ldc_external_rule_exists')){
	/**
	 * @return bool
	 */
	function ldc_external_rule_exists($regex = '', $query = ''){
		$regex = str_replace('.+?', '.+', $regex); // Apache 1.3 does not support the reluctant (non-greedy) modifier.
		$rewrite_rules = ldc_get_rewrite_rules();
		$rule = 'RewriteRule ^' . $regex . ' ' . ldc_home_root() . $query . ' [QSA,L]';
		return in_array($rule, $rewrite_rules);
	}
}

if(!function_exists('ldc_get_rewrite_rules')){
	/**
	 * @return array
	 */
	function ldc_get_rewrite_rules(){
		if(ldc_isset_cache('rewrite_rules')){
			$rewrite_rules = (array) ldc_get_cache('rewrite_rules', []);
			return $rewrite_rules;
		}
		$rewrite_rules = array_filter(extract_from_markers(get_home_path() . '.htaccess', 'WordPress'));
		ldc_set_cache('rewrite_rules', $rewrite_rules);
		return $rewrite_rules;
	}
}

if(!function_exists('ldc_home_root')){
	/**
	 * @return string
	 */
	function ldc_home_root(){
		$home_root = parse_url(home_url());
		if(isset($home_root['path'])){
			$home_root = trailingslashit($home_root['path']);
		} else {
			$home_root = '/';
		}
		return $home_root;
	}
}

if(!function_exists('ldc_is_external_rule')){
	/**
	 * @return bool
	 */
	function ldc_is_external_rule($rule = []){
		if(!ldc_array_keys_exists(['plugin_file', 'query', 'regex'], $rule)){
			return false;
		}
		$count = count($rule);
		if(3 !== $count){
			return false;
		}
	    return true;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_maybe_add_external_rule')){
	/**
	 * This function MUST be called inside the 'generate_rewrite_rules' action hook.
	 *
	 * @return void
	 */
	function ldc_maybe_add_external_rule($rule = []){
		global $wp_rewrite;
		if(!doing_action('generate_rewrite_rules')){ // Too early or too late.
	        return;
	    }
		if(!ldc_is_external_rule($rule)){
			return;
		}
		if(ldc_is_plugin_deactivating($rule['plugin_file'])){
			return;
		}
		$wp_rewrite->add_external_rule($rule['regex'], $rule['query']);
	}
}

if(!function_exists('ldc_maybe_add_external_rules')){
	/**
	 * This function MUST be called inside the 'generate_rewrite_rules' action hook.
	 *
	 * @return void
	 */
	function ldc_maybe_add_external_rules($wp_rewrite){
		if(!doing_action('generate_rewrite_rules')){ // Too early or too late.
	        return;
	    }
		$external_rules = (array) ldc_get_cache('external_rules', []);
	    if(!$external_rules){
	        return;
	    }
	    foreach($external_rules as $rule){
			ldc_maybe_add_external_rule($rule);
	    }
	}
}

if(!function_exists('ldc_maybe_add_external_rules_notice')){
	/**
	 * This function MUST be called inside the 'admin_notices' action hook.
	 *
	 * @return void
	 */
	function ldc_maybe_add_external_rules_notice(){
		if(!doing_action('admin_notices')){ // Too early or too late.
	        return;
	    }
		if(!current_user_can('manage_options')){
			return;
		}
		$external_rules = (array) ldc_get_cache('external_rules', []);
	    if(!$external_rules){
	        return;
	    }
	    $add_admin_notice = false;
		foreach($external_rules as $rule){
			if(!ldc_external_rule_exists($rule['regex'], $rule['query'])){
				$add_admin_notice = true;
				break;
			}
		}
		if(!$add_admin_notice){
	        return;
		}
	    $message = sprintf(translate('You should update your %s file now.'), '<code>.htaccess</code>');
	    $message .= ' ';
	    $message .= sprintf('<a href="%s">%s</a>', esc_url(admin_url('options-permalink.php')), translate('Flush permalinks')) . '.';
	    ldc_add_admin_notice($message);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Strings
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_base64_urldecode')){
	/**
	 * @return string
	 */
	function ldc_base64_urldecode($data = '', $strict = false){
		return base64_decode(strtr($data, '-_', '+/'), $strict);
	}
}

if(!function_exists('ldc_base64_urlencode')){
	/**
	 * @return string
	 */
	function ldc_base64_urlencode($data = ''){
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
}

if(!function_exists('ldc_canonicalize')){
	/**
	 * @return string
	 */
	function ldc_canonicalize($key = ''){
		$key = sanitize_title($key);
		$key = str_replace('-', '_', $key);
		return $key;
	}
}

if(!function_exists('ldc_first_p')){
	/**
	 * @return string
	 */
	function ldc_first_p($text = '', $dot = true){
		return ldc_one_p($text, $dot, 'first');
	}
}

if(!function_exists('ldc_implode_and')){
	/**
	 * @return string
	 */
	function ldc_implode_and($array = [], $separator = ''){
        if(!$separator){
            $separator = trim(sprintf(translate('%1$s and %2$s'), '', ''));
        }
		return ldc_implode_comma($array, $separator);
	}
}

if(!function_exists('ldc_implode_comma')){
	/**
	 * @return string
	 */
	function ldc_implode_comma($array = [], $separator = ''){
		if(!is_array($array)){
			return '';
		}
		if(empty($array)){
			return '';
		}
        if(1 === count($array)){
			return $array[0];
		}
        $separator = trim($separator);
        if(!$separator){
            return implode(', ', $array);
        }
		$last = array_pop($array);
		return implode(', ', $array) . ' ' . $separator . ' ' . $last;
	}
}

if(!function_exists('ldc_implode_or')){
	/**
	 * @return string
	 */
	function ldc_implode_or($array = [], $separator = ''){
        if(!$separator){
            $separator = trim(sprintf(translate('%1$s or %2$s'), '', ''));
        }
		return ldc_implode_comma($array, $separator);
	}
}

if(!function_exists('ldc_last_p')){
	/**
	 * @return string
	 */
	function ldc_last_p($text = '', $dot = true){
		return ldc_one_p($text, $dot, 'last');
	}
}

if(!function_exists('ldc_one_p')){
	/**
	 * @return string
	 */
	function ldc_one_p($text = '', $dot = true, $p = 'first'){
        $text = sanitize_text_field($text);
        $matches = preg_split('/[\.\?!]+/', $text, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_OFFSET_CAPTURE);
        switch($p){
            case 'first':
                $match = array_shift($matches);
                break;
            case 'last':
                $match = array_pop($matches);
                break;
            default:
                $p = absint($p);
                if(count($matches) >= $p){
                    $p --;
                    $match = $matches[$p];
                } else {
                    $error_msg = translate('Error');
                    if($dot){
                        $error_msg .= '.';
                    }
                    return $error_msg;
                }
        }
        $one = trim($match[0]);
        if(!$dot){
            return $one;
        }
        $dot_chr = substr($text, strlen($match[0]) + $match[1], 1);
        if(empty($dot_chr)){
            $dot_chr = '.';
        }
        $one .= $dot_chr;
        return $one;
	}
}

if(!function_exists('ldc_prepare')){
	/**
	 * @return string
	 */
	function ldc_prepare($str = '', ...$args){
		global $wpdb;
		if(!$args){
			return $str;
		}
		if(false === strpos($str, '%')){
			return $str;
		}
		$subject = $wpdb->prepare($str, ...$args);
		$subject = $wpdb->remove_placeholder_escape($subject);
		return str_replace("'", '', $subject);
	}
}

if(!function_exists('ldc_remove_whitespaces')){
	/**
	 * @return string
	 */
	function ldc_remove_whitespaces($str = ''){
		return trim(preg_replace('/[\r\n\t ]+/', ' ', $str));
	}
}

if(!function_exists('ldc_str_ends_with')){
	/**
     * Polyfill for `str_ends_with()` function added in PHP 8.0 and WordPress 5.9.
     *
	 * @return string
	 */
	function ldc_str_ends_with($haystack = '', $needle = ''){
        if(function_exists('str_ends_with')){
            return str_ends_with($haystack, $needle);
        }
		if('' === $haystack){
			return '' === $needle;
		}
        $len = strlen($needle);
        return substr($haystack, -$len, $len) === $needle;
	}
}

if(!function_exists('ldc_str_prefix')){
	/**
	 * @return string
	 */
	function ldc_str_prefix($str = '', $prefix = ''){
		$prefix = str_replace('\\', '_', $prefix); // Fix namespaces.
		$prefix = ldc_canonicalize($prefix);
		$prefix = rtrim($prefix, '_');
		if(!$prefix){
			$prefix = ldc_prefix();
		}
		$str = ldc_remove_whitespaces($str);
		if(!$str){
			return $prefix;
		}
		if(0 === strpos($str, $prefix)){
			return $str; // Text is already prefixed.
		}
		return $prefix . '_' . $str;
	}
}

if(!function_exists('ldc_str_slug')){
	/**
	 * @return string
	 */
	function ldc_str_slug($str = '', $slug = ''){
		$slug = str_replace('_', '-', $slug); // Fix canonicalized.
		$slug = str_replace('\\', '-', $slug); // Fix namespaces.
		$slug = sanitize_title($slug);
		$slug = rtrim($slug, '-');
		if(!$slug){
			$slug = ldc_slug();
		}
		$str = ldc_remove_whitespaces($str);
		if(!$str){
			return $slug;
		}
		if(0 === strpos($str, $slug)){
			return $str; // Text is already slugged.
		}
		return $slug . '-' . $str;
	}
}

if(!function_exists('ldc_str_split')){
	/**
	 * @return string
	 */
	function ldc_str_split($str = '', $line_length = 55){
		$str = sanitize_text_field($str);
		$lines = ceil(strlen($str) / $line_length);
		$words = explode(' ', $str);
		if(count($words) <= $lines){
			return $words;
		}
		$length = 0;
		$index = 0;
		$oputput = [];
		foreach($words as $word){
			$word_length = strlen($word);
			if((($length + $word_length) <= $line_length) or empty($oputput[$index])){
				$oputput[$index][] = $word;
				$length += ($word_length + 1);
			} else {
				if($index < ($lines - 1)){
					$index ++;
				}
				$length = $word_length;
				$oputput[$index][] = $word;
			}
		}
		foreach($oputput as $index => $words){
			$oputput[$index] = implode(' ', $words);
		}
		return $oputput;
	}
}

if(!function_exists('ldc_str_split_lines')){
	/**
	 * @return string
	 */
	function ldc_str_split_lines($str = '', $lines = 2){
		$str = sanitize_text_field($str);
		$words = explode(' ', $str);
		if(count($words) <= $lines){
			return $words;
		}
		$line_length = ceil(strlen($str) / $lines);
		$length = 0;
		$index = 0;
		$oputput = [];
		foreach($words as $word){
			$word_length = strlen($word);
			if((($length + $word_length) <= $line_length) or empty($oputput[$index])){
				$oputput[$index][] = $word;
				$length += ($word_length + 1);
			} else {
				if($index < ($lines - 1)){
					$index ++;
				}
				$length = $word_length;
				$oputput[$index][] = $word;
			}
		}
		foreach($oputput as $index => $words){
			$oputput[$index] = implode(' ', $words);
		}
		return $oputput;
	}
}

if(!function_exists('ldc_str_starts_with')){
	/**
     * Polyfill for `str_starts_with()` function added in PHP 8.0 and WordPress 5.9.
     *
	 * @return string
	 */
	function ldc_str_starts_with($haystack = '', $needle = ''){
        if(function_exists('str_starts_with')){
            return str_starts_with($haystack, $needle);
        }
		if('' === $needle){
			return true;
		}
		return 0 === strpos($haystack, $needle);
	}
}

if(!function_exists('ldc_trailingdotit')){
	/**
	 * @return string
	 */
	function ldc_trailingdotit($text = ''){
        $text = sanitize_textarea_field($text);
		if(ldc_str_ends_with($text, '.') or ldc_str_ends_with($text, '?') or ldc_str_ends_with($text, '!')){
			return $text;
		}
		return $text . '.';
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// TGM Plugin Activation
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_tgmpa_register')){
	/**
	 * @return void
	 */
	function ldc_tgmpa_register($plugins = [], $config = []){
        $lib = ldc_use_tgm_plugin_activation();
		if(is_wp_error($lib)){
			return; // Silence is golden.
		}
		if(doing_action('tgmpa_register')){ // Just in time.
			ldc_tgmpa($plugins, $config);
			return;
		}
		if(did_action('tgmpa_register')){ // Too late.
			return;
		}
		$tgmpa = [
			'config' => $config,
			'plugins' => $plugins,
		];
		$md5 = ldc_md5($tgmpa);
		ldc_set_array_cache('tgmpa', $md5, $tgmpa);
		ldc_add_action_once('tgmpa_register', 'ldc_maybe_tgmpa_register');
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_maybe_tgmpa_register')){
	/**
	 * @return void
	 */
	function ldc_maybe_tgmpa_register(){
		$tgmpa = (array) ldc_get_cache('tgmpa', []);
		if(empty($tgmpa)){
			return;
		}
		foreach($tgmpa as $args){
			ldc_tgmpa($args['plugins'], $args['config']);
		}
	}
}

if(!function_exists('ldc_tgmpa')){
	/**
	 * This function MUST be called inside the 'tgmpa_register' action hook.
	 *
	 * @return void
	 */
	function ldc_tgmpa($plugins = [], $config = []){
		if(!doing_action('tgmpa_register')){
			return; // Too early or too late.
		}
		tgmpa($plugins, $config);
	}
}

if(!function_exists('ldc_use_tgm_plugin_activation')){
	/**
	 * @return bool|WP_Error
	 */
	function ldc_use_tgm_plugin_activation($ver = '2.6.1'){
		$key = 'tgm-plugin-activation-' . $ver;
		if(ldc_isset_cache($key)){
			return (string) ldc_get_cache($key, '');
		}
		$class = 'TGM_Plugin_Activation';
		if(class_exists($class)){
			return ''; // Already handled outside of this function.
		}
		$dir = ldc_remote_lib('https://github.com/TGMPA/TGM-Plugin-Activation/archive/refs/tags/' . $ver . '.zip', 'TGM-Plugin-Activation-' . $ver);
		if(is_wp_error($dir)){
			return $dir;
		}
		$file = $dir . '/class-tgm-plugin-activation.php';
		if(!file_exists($file)){
			return ldc_error(translate('File doesn&#8217;t exist?'), $file);
		}
		require_once($file);
		if(!class_exists($class)){
			return ldc_error(sprintf(translate('Missing parameter(s): %s'), $class) . '.');
		}
		ldc_set_cache($key, $dir);
		return $dir;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Urchin Tracking Module
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_current_utm_param')){
    /**
     * @return string
     */
    function ldc_current_utm_param($name = ''){
        $utm_params = ldc_current_utm_params();
        if(!isset($utm_params[$name])){
            return '';
        }
        return $utm_params[$name];
    }
}

if(!function_exists('ldc_current_utm_params')){
    /**
     * @return array
     */
    function ldc_current_utm_params(){
        if(ldc_at_least_one_utm_get_param()){
            return ldc_utm_params_from_get(); // 1. GET
        }
        return ldc_utm_params_from_cookie(); // 2. COOKIE
    }
}

if(!function_exists('ldc_track_utm_parameters')){
    /**
     * @return void
     */
    function ldc_track_utm_parameters(){
        ldc_set_cache('track_utm_parameters', true);
		if(doing_action('after_setup_theme')){ // Just in time.
			ldc_maybe_set_utm_cookie();
			return;
		}
		if(did_action('after_setup_theme')){ // Too late.
			return;
		}
		ldc_add_action_once('after_setup_theme', 'ldc_maybe_set_utm_cookie');
    }
}

if(!function_exists('ldc_utm_param_name')){
    /**
     * @return string
     */
    function ldc_utm_param_name($name = ''){
        $pairs = ldc_utm_pairs();
        if(!array_key_exists($name, $pairs)){
            return '';
        }
        return $pairs[$name];
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_at_least_one_utm_get_param')){
    /**
     * @return void
     */
    function ldc_at_least_one_utm_get_param(){
        $at_least_one = false;
        $utm_params = ldc_utm_params_from_get();
        foreach($utm_params as $key => $value){
            if(!$value){
                continue;
            }
            $at_least_one = true;
            break;
        }
        return $at_least_one;
    }
}

if(!function_exists('ldc_maybe_set_utm_cookie')){
    /**
     * This function MUST be called inside the 'after_setup_theme' action hook.
	 *
	 * @return void
     */
    function ldc_maybe_set_utm_cookie(){
		if(!doing_action('after_setup_theme')){ // Too early or too late.
	        return;
	    }
        $track_utm_parameters = (bool) ldc_get_cache('track_utm_parameters', false);
        if(!$track_utm_parameters){
            return;
        }
        if(!ldc_at_least_one_utm_get_param()){
            return;
        }
        ldc_maybe_unset_utm_cookie();
        $cookie_lifetime = time() + (2 * MONTH_IN_SECONDS); // https://support.google.com/analytics/answer/7667196?hl=en_US&utm_id=ad
        $utm_params = ldc_utm_params_from_get();
        $value = build_query($utm_params);
        $name = ldc_utm_cookie_name();
        ldc_set_cookie($name, $value, $cookie_lifetime);
    }
}

if(!function_exists('ldc_maybe_unset_utm_cookie')){
    /**
     * @return void
     */
    function ldc_maybe_unset_utm_cookie(){
        $name = ldc_utm_cookie_name();
        ldc_unset_cookie($name);
    }
}

if(!function_exists('ldc_utm_cookie_name')){
    /**
     * @return string
     */
    function ldc_utm_cookie_name(){
        return ldc_str_prefix('utm_parameters');
    }
}

if(!function_exists('ldc_utm_keys')){
    /**
     * @return array
     */
    function ldc_utm_keys(){
        return array_keys(ldc_utm_pairs());
    }
}

if(!function_exists('ldc_utm_pairs')){
    /**
     * @return array
     */
    function ldc_utm_pairs(){
        return [
            'utm_campaign' => 'Name',
            'utm_content' => 'Content',
            'utm_id' => 'ID',
            'utm_medium' => 'Medium',
            'utm_source' => 'Source',
            'utm_term' => 'Term',
        ];
    }
}

if(!function_exists('ldc_utm_params_from_cookie')){
    /**
     * @return array
     */
    function ldc_utm_params_from_cookie(){
        $name = ldc_utm_cookie_name();
        $value = '';
        if(isset($_COOKIE[$name])){
            $value = $_COOKIE[$name];
        }
        wp_parse_str($value, $values);
        $utm_params = [];
        foreach(ldc_utm_keys() as $key){
            if(isset($values[$key])){
                $utm_params[$key] = $values[$key];
            } else {
                $utm_params[$key] = '';
            }
        }
        return $utm_params;
    }
}

if(!function_exists('ldc_utm_params_from_get')){
    /**
     * @return array
     */
    function ldc_utm_params_from_get(){
        $utm_params = [];
        foreach(ldc_utm_keys() as $key){
            if(isset($_GET[$key])){
                $utm_params[$key] = $_GET[$key];
            } else {
                $utm_params[$key] = '';
            }
        }
        return $utm_params;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Wordfence
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_wf_bulk_countries')){
    /**
     * @return array
     */
    function ldc_wf_bulk_countries(){
        if(ldc_isset_cache('wf_bulk_countries')){
            return (array) ldc_get_cache('wf_bulk_countries', []);
        }
        if(!ldc_is_plugin_active('wordfence/wordfence.php')){
            ldc_set_cache('wf_bulk_countries', []);
            return [];
        }
        require(WORDFENCE_PATH . 'lib/wfBulkCountries.php'); /** @var array $wfBulkCountries */
        asort($wfBulkCountries);
        ldc_set_cache('wf_bulk_countries', $wfBulkCountries);
        return $wfBulkCountries;
    }
}

if(!function_exists('ldc_wf_countries')){
    /**
     * @return array
     */
    function ldc_wf_countries($preferred_countries = []){
        $wf_countries = ldc_wf_bulk_countries();
        if(!$preferred_countries){
            return $wf_countries;
        }
        $countries = [];
        foreach($preferred_countries as $iso2){
            if(!isset($wf_countries[$iso2])){
                continue;
            }
            $countries[$iso2] = $wf_countries[$iso2];
            unset($wf_countries[$iso2]);
        }
        return array_merge($countries, $wf_countries);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Zoom
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_zoom_access_token')){
    /**
     * @return string|WP_Error
     */
    function ldc_zoom_access_token(){
        if(ldc_isset_cache('zoom_access_token')){
            return (string) ldc_get_cache('zoom_access_token', '');
        }
        $app_credentials = ldc_zoom_app_credentials();
        if(is_wp_error($app_credentials)){
            return $app_credentials;
        }
        $authorization = base64_encode($app_credentials['client_id'] . ':' . $app_credentials['client_secret']);
        $url = 'https://zoom.us/oauth/token';
        $args = [
            'body' => [
                'account_id' => $app_credentials['account_id'],
                'grant_type' => 'account_credentials',
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . $authorization,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'timeout' => 10,
        ];
        $response = ldc_remote_post($url, $args);
        if(is_wp_error($response)){
            return $response;
        }
        if(!$response->is_success()){
            return ldc_error($response->message(), $response->raw_response());
        }
        $access_token = $response->json_param('access_token');
        ldc_set_cache('zoom_access_token', $access_token);
        return $access_token;
    }
}

if(!function_exists('ldc_zoom_api_url')){
    /**
     * @return string
     */
    function ldc_zoom_api_url($endpoint = ''){
        $base = 'https://api.zoom.us/v2';
        if(ldc_str_starts_with($endpoint, $base)){
            $endpoint = str_replace($base, '', $endpoint);
        }
        $endpoint = ltrim($endpoint, '/');
        $endpoint = untrailingslashit($endpoint);
        $endpoint = trailingslashit($base) . $endpoint;
        return $endpoint;
    }
}

if(!function_exists('ldc_zoom_app_credentials')){
    /**
     * @return array|WP_Error
     */
    function ldc_zoom_app_credentials($app_credentials = []){
        if(ldc_isset_cache('zoom_app_credentials')){
            return (array) ldc_get_cache('zoom_app_credentials', []);
        }
        $app_credentials = shortcode_atts([
            'account_id' => '',
            'client_id' => '',
            'client_secret' => '',
        ], $app_credentials);
        $missing = [];
        if(!$app_credentials['account_id']){
            $missing[] = 'Account ID';
        }
        if(!$app_credentials['client_id']){
            $missing[] = 'Client ID';
        }
        if(!$app_credentials['client_secret']){
            $missing[] = 'Client Secret';
        }
        if($missing){
            $message = sprintf(translate('Missing parameter(s): %s'), ldc_implode_and($missing)) . '.';
            return ldc_error($message);
        }
        ldc_set_cache('zoom_app_credentials', $app_credentials);
        return $app_credentials;
    }
}

if(!function_exists('ldc_zoom_oauth_token')){
    /**
     * @return string|WP_Error
     */
    function ldc_zoom_oauth_token(){
        $transient = ldc_str_prefix('zoom_oauth_token');
        $oauth_token = get_transient($transient);
        if($oauth_token){
            return $oauth_token;
        }
        $oauth_token = ldc_zoom_access_token();
        if(is_wp_error($oauth_token)){
            return $oauth_token;
        }
        $expiration = 59 * MINUTE_IN_SECONDS; // The token’s time to live is 1 hour. https://developers.zoom.us/docs/internal-apps/s2s-oauth/
        set_transient($transient, $oauth_token, $expiration);
        return $oauth_token;
    }
}

if(!function_exists('ldc_zoom_delete')){
    /**
     * @return stdClass|WP_Error
     */
    function ldc_zoom_delete($endpoint = '', $args = [], $timeout = 10){
        return ldc_zoom_request('DELETE', $endpoint, $args, $timeout);
    }
}

if(!function_exists('ldc_zoom_get')){
    /**
     * @return stdClass|WP_Error
     */
    function ldc_zoom_get($endpoint = '', $args = [], $timeout = 10){
        return ldc_zoom_request('GET', $endpoint, $args, $timeout);
    }
}

if(!function_exists('ldc_zoom_patch')){
    /**
     * @return stdClass|WP_Error
     */
    function ldc_zoom_patch($endpoint = '', $args = [], $timeout = 10){
        return ldc_zoom_request('PATCH', $endpoint, $args, $timeout);
    }
}

if(!function_exists('ldc_zoom_post')){
    /**
     * @return stdClass|WP_Error
     */
    function ldc_zoom_post($endpoint = '', $args = [], $timeout = 10){
        return ldc_zoom_request('POST', $endpoint, $args, $timeout);
    }
}

if(!function_exists('ldc_zoom_put')){
    /**
     * @return stdClass|WP_Error
     */
    function ldc_zoom_put($endpoint = '', $args = [], $timeout = 10){
        return ldc_zoom_request('PUT', $endpoint, $args, $timeout);
    }
}

if(!function_exists('ldc_zoom_request')){
    /**
     * @return stdClass|WP_Error
     */
    function ldc_zoom_request($method = '', $endpoint = '', $args = [], $timeout = 10){
        $oauth_token = ldc_zoom_oauth_token();
        if(is_wp_error($oauth_token)){
            return $oauth_token;
        }
        $url = ldc_zoom_api_url($endpoint);
        if(!is_array($args)){
            $args = wp_parse_args($args);
        }
        $args = [
            'body' => $args,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $oauth_token,
                'Content-Type' => 'application/json',
            ],
            'timeout' => $timeout,
        ];
        return ldc_remote_request($method, $url, $args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// GitHub
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_github_api_token')){
    /**
     * @return void
     */
    function ldc_github_api_token($token = ''){
        if(!$token){
            return;
        }
		ldc_set_cache('github_api_token', $token);
		ldc_add_filter_once('http_request_args', 'ldc_maybe_add_github_api_token', 10, 2);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_maybe_add_github_api_token')){
	/**
	 * This function MUST be called inside the 'http_request_args' filter hook.
	 *
	 * @return array
	 */
	function ldc_maybe_add_github_api_token($parsed_args, $url){
		if(!doing_filter('http_request_args')){
	        return $parsed_args;
	    }
		$token = (string) ldc_get_cache('github_api_token', '');
		if(!$token){
            return $parsed_args;
        }
        if(0 !== strpos($url, 'https://api.github.com/')){
            return $parsed_args;
        }
        if(!isset($parsed_args['headers'])){
            $parsed_args['headers'] = [];
        }
        $parsed_args['headers']['Authorization'] = 'token ' . $token;
		return $parsed_args;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Meta Box
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_image_switch')){
	/**
	 * @return array|WP_Error
	 */
	function ldc_mb_image_switch($id = '', $args = []){
        if(!$id){
            $error_msg = translate('The "%s" argument must be a non-empty string.');
            $error_msg = sprintf($error_msg, 'id');
            return ldc_error($error_msg);
        }
        $defaults = [
            'columns' => 4,
            'icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj4KICA8cGF0aCBkPSJNNTEyLDMwMS4xOGgtNjUuNjZjLTUuMTIsMjEuMDgtMTMuMjUsNDAuNjYtMjQuNCw1OC4xM2w0Ni4zOCw0Ni4zOC02My4yNSw2My4yNS00Ni4zOC00Ni4zOGMtMTcuNDcsMTAuODQtMzcuMDQsMTguOTctNTcuNTIsMjMuNzl2NjUuNjZoLTkwLjM1di02NS42NmMtMjAuNDgtNC44Mi00MC4wNi0xMi45NS01Ny41Mi0yMy43OWwtNDYuMzgsNDYuMzgtNjMuODUtNjMuODUsNDYuMzgtNDYuMzhjLTEwLjg0LTE3LjQ3LTE4Ljk3LTM3LjA0LTIzLjc5LTU3LjUySDB2LTg5LjQ1aDY1LjM2YzQuODItMjEuMDgsMTMuMjUtNDAuNjYsMjQuMDktNTguNDNsLTQ2LjM4LTQ2LjM4LDYzLjI1LTYzLjI1LDQ2LjM4LDQ2LjM4YzE3LjQ3LTExLjE0LDM3LjM1LTE5LjI4LDU4LjEzLTI0LjRWMGg5MC4zNXY2NS42NmMyMC40OCw0LjgyLDQwLjA2LDEyLjk1LDU3LjUyLDIzLjc5bDQ2LjM4LTQ2LjM4LDYzLjg1LDYzLjg1LTQ2LjM4LDQ2LjM4YzEwLjg0LDE3Ljc3LDE5LjI4LDM3LjM1LDI0LjA5LDU4LjQzaDY1LjM2djg5LjQ1Wk0yNTYsMzQ2LjM1YzUwLDAsOTAuMzUtNDAuMzYsOTAuMzUtOTAuMzVzLTQwLjM2LTkwLjM1LTkwLjM1LTkwLjM1LTkwLjM1LDQwLjM2LTkwLjM1LDkwLjM1LDQwLjM2LDkwLjM1LDkwLjM1LDkwLjM1WiIvPgo8L3N2Zz4=',
            'name' => '',
        ];
        $args = wp_parse_args($args, $defaults);
        if(!$args['name']){
            $args['name'] = $id;
        }
        $field = [
            'after' => '</div></div></div>',
            'before' => '<div style="padding: 6px; margin: 6px 0 0;"><div style="background-color: #f8f9fa; border: 1px solid rgba(0, 0, 0, 0.125); border-radius: 0.25rem; padding: 20px 20px 8px;"><div style="text-align: center;"><img src="' . $args['icon'] . '" style="max-height: 40px;"></div><div style="text-align: center;"><p>' . $args['name'] . '</p></div><div style="text-align: center;">',
            'columns' => $args['columns'],
            'id' => $id,
            'on_label' => translate('Yes'),
            'off_label' => translate('No'),
            'style' => 'square',
            'type' => 'switch',
        ];
        return $field;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Other
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_is_front')){
	/**
	 * @return bool
	 */
    function ldc_is_front(){
        global $wp_query;
        if(is_admin()){
            return false; // The current request is for an administrative interface page.
        }
        if(wp_doing_ajax()){
            return false; // The current request is a WordPress Ajax request.
        }
        if(wp_is_serving_rest_request()){
            return false; // WordPress is currently serving a REST API request.
        }
        if(wp_is_json_request()){
            return false; // The current request is a JSON request, or is expecting a JSON response.
        }
        if(wp_is_jsonp_request()){
            return false; // The current request is a JSONP request, or is expecting a JSONP response.
        }
        if(defined('XMLRPC_REQUEST') and XMLRPC_REQUEST){
            return false; // The current request is a WordPress XML-RPC request.
        }
        if(wp_is_xml_request() or isset($wp_query) and (function_exists('is_feed') and is_feed() or function_exists('is_comment_feed') and is_comment_feed() or function_exists('is_trackback') and is_trackback())){
            return false; // The current request is an XML request, or is expecting an XML response.
        }
        return true;
    }
}

if(!function_exists('ldc_get_the_id')){
	/**
	 * @return int
	 */
    function ldc_get_the_id(){
        if(in_the_loop()){
            return get_the_ID();
        }
        return ldc_get_the_id_early();
    }
}

if(!function_exists('ldc_get_the_id_early')){
	/**
	 * @return int
	 */
    function ldc_get_the_id_early(){
        if(!ldc_is_front()){
            return 0;
        }
        if(!isset($_SERVER['HTTP_HOST'])){
            return 0;
        }
        // Build the URL in the address bar.
        $requested_url = (is_ssl() ? 'https://' : 'http://');
        $requested_url .= $_SERVER['HTTP_HOST'];
        if(isset($_SERVER['REQUEST_URI'])){
            $requested_url .= $_SERVER['REQUEST_URI'];
        }
        return url_to_postid($requested_url);
    }
}

if(!function_exists('ldc_bb_avoid_full_size_images')){
	/**
	 * @return void
	 */
    function ldc_bb_avoid_full_size_images(){
        ldc_add_filter_once('fl_builder_photo_sizes_select', 'ldc_maybe_bb_avoid_full_size_images', 11);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_maybe_bb_avoid_full_size_images')){
	/**
	 * @return array
	 */
	function ldc_maybe_bb_avoid_full_size_images($sizes){
        if(!isset($sizes['full'])){
            return $sizes;
        }
        if(1 === count($sizes)){
            return $sizes;
        }
        unset($sizes['full']);
        return $sizes;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Nonces
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_create_nonce_guest')){
	/**
	 * @return string
	 */
	function ldc_create_nonce_guest($action = -1){
        $i = wp_nonce_tick($action);
        $token = ldc_get_session_token_guest();
        $uid = 0;
        return substr(wp_hash($i . '|' . $action . '|' . $uid . '|' . $token, 'nonce'), -12, 10);
	}
}

if(!function_exists('ldc_get_session_token_guest')){
	/**
	 * @return string
	 */
	function ldc_get_session_token_guest($action = -1){
        return '';
	}
}

if(!function_exists('ldc_nonce_url_guest')){
	/**
	 * @return string
	 */
	function ldc_nonce_url_guest($actionurl = '', $action = -1, $name = '_wpnonce'){
        $actionurl = str_replace('&amp;', '&', $actionurl);
        return esc_html(add_query_arg($name, ldc_create_nonce_guest($action), $actionurl));
	}
}

if(!function_exists('ldc_verify_nonce_guest')){
	/**
	 * @return bool
	 */
	function ldc_verify_nonce_guest($nonce = '', $action = -1){
        $nonce = (string) $nonce;
    	if(empty($nonce)){
    		return false;
    	}
        $i = wp_nonce_tick($action);
        $token = ldc_get_session_token_guest();
    	$uid = 0;
    	$expected = substr(wp_hash($i . '|' . $action . '|' . $uid . '|' . $token, 'nonce'), -12, 10);
    	if(hash_equals($expected, $nonce)){
    		return 1; // Nonce generated 0-12 hours ago
    	}
    	$expected = substr(wp_hash(($i - 1) . '|' . $action . '|' . $uid . '|' . $token, 'nonce'), -12, 10);
    	if(hash_equals($expected, $nonce)){
    		return 2; // Nonce generated 12-24 hours ago
    	}
    	return false; // Invalid nonce
	}
}
