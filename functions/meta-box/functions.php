<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_emulate_bootstrap')){
    function ldc_mb_emulate_bootstrap($bootstrap = 0){
        ldc_one('wp_enqueue_scripts', function() use($bootstrap){
            if(!$bootstrap){
                $bootstrap = ldc_bb_get_bootstrap();
            }
            switch($bootstrap){
                case 4:
                    $ver = filemtime(plugin_dir_path(__FILE__) . 'bootstrap-4.css');
                    wp_enqueue_style('ldc-mb-bootstrap-4', plugin_dir_url(__FILE__) . 'bootstrap-4.css', [], $ver);
                    break;
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_enqueue_jquery_validation_messages_es')){
    function ldc_mb_enqueue_jquery_validation_messages_es(){
        ldc_one('rwmb_enqueue_scripts', function(){
            wp_enqueue_script('jquery-validation-messages-es', 'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/localization/messages_es.min.js', ['rwmb-validation'], '1.19.3', true);
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_enqueue_select2_full')){
    function ldc_mb_enqueue_select2_full(){
        ldc_one('rwmb_enqueue_scripts', function(){
            if(wp_script_is('rwmb-select2', 'enqueued')){
				wp_dequeue_script('rwmb-select2');
			}
			if(wp_script_is('rwmb-select2', 'registered')){
				wp_deregister_script('rwmb-select2');
			}
			wp_register_script('rwmb-select2', plugin_dir_url(__FILE__) . 'select2.full.min.js', ['jquery'], '4.0.10', true);
			wp_enqueue_script('rwmb-select2');
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_get_submit_class')){
    function ldc_mb_get_submit_class($meta_box_id = ''){
        if($meta_box_id){
    		$classes = [];
    		$meta_boxes = [];
    		$meta_box_ids = array_filter(explode(',', $meta_box_id . ','));
    		foreach($meta_box_ids as $meta_box_id){
    			$meta_boxes[] = rwmb_get_registry('meta_box')->get($meta_box_id);
    		}
    		$meta_boxes = array_filter($meta_boxes);
    		if($meta_boxes){
    			foreach($meta_boxes as $meta_box){
    				if(isset($meta_box->meta_box['ldc_submit_class']) and $meta_box->meta_box['ldc_submit_class']){
						$class = ldc_sanitize_html_class($meta_box->meta_box['ldc_submit_class']);
						if($class){
							$classes[] = $class;
						}
    				}
    			}
    		}
    		if($classes){
				$classes = array_unique($classes);
    			return implode(' ', $classes);
    		}
    	}
    	return '';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_sanitize_html_class')){
    function ldc_sanitize_html_class($class = '', $fallback = ''){
    	if(is_array($class)){
    		$class = implode(' ', $class);
    	}
    	if(is_string($class)){
    		$class = trim(ldc_remove_whitespaces($class));
    		$class = explode(' ', $class);
    		$class = array_map('sanitize_html_class', $class);
    		$class = array_unique($class);
    		$class = implode(' ', $class);
    		return $class;
    	}
    	return $fallback;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_support_bootstrap')){
    function ldc_mb_support_bootstrap($bootstrap = 0){
        if(!$bootstrap){
            $bootstrap = ldc_bb_get_bootstrap();
        }
        switch($bootstrap){
            case 4:
                ldc_enqueue_bs_custom_file_input();
                ldc_support_simple_html_dom();
                ldc_one('rwmb_normalize_field', function($field){
                	if(isset($field['ldc_b4_fl']) and $field['ldc_b4_fl']){
        				if(isset($field['clone']) and $field['clone']){
        					$field['clone'] = false;
        				}
        			}
        			return $field;
                });
                ldc_one('rwmb_enqueue_scripts', function(){
                    wp_register_style('select2-bootstrap', plugin_dir_url(__FILE__) . 'select2-bootstrap.min.css', ['rwmb-select2'], '1.0.0');
                    wp_enqueue_style('select2-bootstrap');
                });
                if(!class_exists('LDC_MB_B4')){
                    require_once(plugin_dir_path(__FILE__) . 'class-ldc-mb-b4.php');
                }
                ldc_one('rwmb_outer_html', ['LDC_MB_B4', 'outer_html'], 10, 2);
                break;
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_support_floating_labels')){
    function ldc_mb_support_floating_labels(){
        ldc_enqueue_functions();
        ldc_support_floating_labels();
        ldc_support_simple_html_dom();
        ldc_one('rwmb_frontend_before_submit_button', function($config){
        	ob_start();
        });
        ldc_one('rwmb_frontend_after_submit_button', function($config){
        	$html = ob_get_clean();
        	$class = '';
        	if(isset($config['id']) and $config['id']){
        		$class = ldc_mb_get_submit_class($config['id']);
        	}
        	if($class){
        		$html = str_get_html($html);
        		$button = $html->find('.rwmb-button', 0);
        		$button->addClass($class);
        	}
        	echo $html;
        });
        ldc_one('rwmb_normalize_field', function($field){
        	if(isset($field['ldc_b4']) and $field['ldc_b4']){
				if(isset($field['clone']) and $field['clone']){
					$field['clone'] = false;
				}
				if(isset($field['max_file_uploads']) and $field['max_file_uploads'] != 1){
					$field['max_file_uploads'] = 1;
				}
			}
			return $field;
        });
        if(!class_exists('LDC_MB_B4_FL')){
            require_once(plugin_dir_path(__FILE__) . 'class-ldc-mb-b4-fl.php');
        }
        ldc_one('rwmb_outer_html', ['LDC_MB_B4_FL', 'outer_html'], 10, 2);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
