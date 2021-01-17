<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_add_admin_notice')){
	function ldc_add_admin_notice($admin_notice = '', $class = 'error', $is_dismissible = false){
        if(!isset($GLOBALS['ldc_admin_notices'])){
            $GLOBALS['ldc_admin_notices'] = [];
        }
        if($admin_notice){
            if(!in_array($class, ['error', 'warning', 'success', 'info'])){
    			$class = 'warning';
    		}
    		if($is_dismissible){
    			$class .= ' is-dismissible';
    		}
    		$GLOBALS['ldc_admin_notices'][] = '<div class="notice notice-' . $class . '"><p>' . $admin_notice . '</p></div>';
        }
		ldc_one('admin_notices', function(){
			if($GLOBALS['ldc_admin_notices']){
				foreach($GLOBALS['ldc_admin_notices'] as $admin_notice){
					echo $admin_notice;
				}
			}
		});
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_are_plugins_active')){
	function ldc_are_plugins_active($plugins = []){
		$r = false;
		if($plugins){
			$r = true;
			foreach($plugins as $plugin){
				if(!ldc_is_plugin_active($plugin)){
					$r = false;
					break;
				}
			}
		}
		return $r;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_array_keys_exist')){
	function ldc_array_keys_exist($keys = [], $array = []){
		if(!$keys or !$array or !is_array($keys) or !is_array($array)){
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

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_base64_urldecode')){
	function ldc_base64_urldecode($data = '', $strict = false){
		return base64_decode(strtr($data, '-_', '+/'), $strict);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_base64_urlencode')){
	function ldc_base64_urlencode($data = ''){
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_clone_role')){
	function ldc_clone_role($source = '', $destination = '', $display_name = ''){
        if($source and $destination and $display_name){
            $role = get_role($source);
            $capabilities = $role->capabilities;
            add_role($destination, $display_name, $capabilities);
        }
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_current_screen_is')){
	function ldc_current_screen_is($id = ''){
		if(is_admin()){
			if(function_exists('get_current_screen')){
				$current_screen = get_current_screen();
	            if($current_screen){
					if($current_screen->id == $id){
						return true;
					}
	            }
			}
        }
        return false;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_destroy_other_sessions')){
    function ldc_destroy_other_sessions(){
        ldc_one('init', function(){
			wp_destroy_other_sessions();
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_format_function')){
	function ldc_format_function($function_name = '', $args = []){
		$str = '';
		if($function_name){
			$str .= '<div style="color: #24831d; font-family: monospace; font-weight: 400;">' . $function_name . '(';
			$function_args = [];
			if($args){
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
			}
			if($function_args){
				$str .= ' ' . implode(', ', $function_args) . ' ';
			}
			$str .= ')</div>';
		}
		return $str;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_get_memory_size')){
    function ldc_get_memory_size(){
        if(!function_exists('exec')){
	        return 0;
	    }
	    exec('free -b', $output);
	    $output = $output[1];
	    $output = preg_replace('/\s+/', ' ', $output);
	    $output = trim($output);
	    $output = explode(' ', $output);
	    $output = $output[1];
	    return absint($output);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_is_array_assoc')){
    function ldc_is_array_assoc($array = []){
        if(is_array($array)){
            return (array_keys($array) !== range(0, count($array) - 1));
        }
		return false;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_is_doing_heartbeat')){
    function ldc_is_doing_heartbeat(){
        return (defined('DOING_AJAX') and DOING_AJAX and isset($_POST['action']) and $_POST['action'] == 'heartbeat');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_is_plugin_active')){
    function ldc_is_plugin_active($plugin = ''){
		if(!function_exists('is_plugin_active')){
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}
		return is_plugin_active($plugin);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_is_plugin_deactivating')){
    function ldc_is_plugin_deactivating($file = ''){
        global $pagenow;
        if(!is_file($file)){
            $file = __FILE__;
        }
        return (is_admin() and $pagenow == 'plugins.php' and isset($_GET['action'], $_GET['plugin']) and $_GET['action'] == 'deactivate' and $_GET['plugin'] == plugin_basename($file));
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_is_post_revision_or_auto_draft')){
    function ldc_is_post_revision_or_auto_draft($post = null){
        return (wp_is_post_revision($post) or get_post_status($post) == 'auto-draft');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_ksort_deep')){
    function ldc_ksort_deep($data = []){
        if(ldc_is_array_assoc($data)){
            ksort($data);
            foreach($data as $index => $item){
                $data[$index] = ldc_ksort_deep($item);
            }
        }
        return $data;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_md5')){
    function ldc_md5($data = ''){
        if(is_object($data)){
			if($data instanceof \Closure){
				return ldc_md5_closure($data);
			} else {
				$data = json_decode(wp_json_encode($data), true);
			}
        }
        if(is_array($data)){
            $data = ldc_ksort_deep($data);
            $data = maybe_serialize($data);
        }
		return md5($data);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_md5_to_uuid4')){
    function ldc_md5_to_uuid4($md5 = ''){
    	if(strlen($md5) == 32){
    		return substr($md5, 0, 8) . '-' . substr($md5, 8, 4) . '-' . substr($md5, 12, 4) . '-' . substr($md5, 16, 4) . '-' . substr($md5, 20, 12);
    	}
        return '';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_new')){
    function ldc_new(...$args){
        if(!$args){
            return null;
        }
        $class_name = array_shift($args);
        if(!class_exists($class_name)){
            return null;
        }
        if($args){
            return new $class_name(...$args);
        } else {
            return new $class_name;
        }
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
		if($function_to_add instanceof \Closure){
			$md5 = ldc_md5_closure($function_to_add);
		} else {
			$md5 = md5($idx);
		}
		if(!in_array($md5, $hooks)){
			$hooks[] = $md5;
			return ldc_on($tag, $function_to_add, $priority, $accepted_args);
		} else {
			return $idx;
		}
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_post_type_labels')){
    function ldc_post_type_labels($singular = '', $plural = '', $all = true){
    	if($singular and $plural){
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
    	return [];
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_signon_without_password')){
    function ldc_signon_without_password($username_or_email = '', $remember = false){
        if(is_user_logged_in()){
            return wp_get_current_user();
        } else {
            $hook = ldc_on('authenticate', function($user = null, $username_or_email = ''){
                if(is_null($user)){
                    $user = get_user_by('login', $username_or_email);
                    if(!$user){
						$user = get_user_by('email', $username_or_email);
	                    if(!$user){
	                        return new WP_Error('does_not_exist', __('The requested user does not exist.'));
	                    }
                    }
                }
                return $user;
            }, 10, 2);
            $user = wp_signon([
                'user_login' => $username_or_email,
                'user_password' => '',
                'remember' => $remember,
            ]);
            ldc_off('authenticate', $hook);
            return $user;
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_upload_basedir')){
    function ldc_upload_basedir(){
		$wp_upload_dir = wp_upload_dir();
        return $wp_upload_dir['basedir'] . '/ldc-functions';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_upload_baseurl')){
    function ldc_upload_baseurl(){
		$wp_upload_dir = wp_upload_dir();
        return $wp_upload_dir['baseurl'] . '/ldc-functions';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
