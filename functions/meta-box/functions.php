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
    		$submit_classes = [];
    		$meta_boxes = [];
    		$meta_box_ids = array_filter(explode(',', $meta_box_id . ','));
    		foreach($meta_box_ids as $meta_box_id){
    			$meta_boxes[] = rwmb_get_registry('meta_box')->get($meta_box_id);
    		}
    		$meta_boxes = array_filter($meta_boxes);
    		if($meta_boxes){
    			foreach($meta_boxes as $meta_box){
    				if(isset($meta_box->meta_box['ldc_submit_class']) and $meta_box->meta_box['ldc_submit_class']){
    					$classes = trim(ldc_remove_whitespaces($meta_box->meta_box['ldc_submit_class']));
    					$classes = explode(' ', $classes);
    					$classes = array_map('sanitize_html_class', $classes);
    					foreach($classes as $class){
    						$submit_classes[] = $class;
    					}
    				}
    			}
    		}
    		if($submit_classes){
    			return implode(' ', $submit_classes);
    		}
    	}
    	return '';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_support_bootstrap')){
    function ldc_mb_support_bootstrap($bootstrap = 0){
        ldc_one('rwmb_enqueue_scripts', function() use($bootstrap){
            if(!$bootstrap){
                $bootstrap = ldc_bb_get_bootstrap();
            }
            switch($bootstrap){
                case 4:
                    wp_register_style('select2-bootstrap', plugin_dir_url(__FILE__) . 'select2-bootstrap.min.css', ['rwmb-select2'], '1.0.0');
                    wp_enqueue_style('select2-bootstrap');
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_support_floating_labels')){
    function ldc_mb_support_floating_labels(){
        ldc_enqueue_functions();
        ldc_support_floating_labels();
        ldc_support_simple_html_dom();
        ldc_one('rwmb_outer_html', function($outer_html, $field, $meta){
        	if(isset($field['type']) and in_array($field['type'], ['text', 'email', 'url', 'tel'])){
        		if(isset($field['ldc_floating_labels']) and $field['ldc_floating_labels']){
        			if(isset($field['placeholder']) and $field['placeholder']){
        				$placeholder = $field['placeholder'];
        				$outer_html = str_get_html($outer_html);
        				$div = $outer_html->find('.rwmb-input', 0);
        				$div->addClass('ldc-floating-labels');
        				$input = $div->find('input', 0);
        				$input->addClass('form-control mw-100');
        				$input->outertext = $input->outertext . '<label>' . $placeholder . '</label>';
        			}
        		}
        	}
        	return $outer_html;
        }, 10, 3);
        ldc_one('rwmb_select_outer_html', function($outer_html, $field, $meta){
        	if(isset($field['ldc_floating_labels']) and $field['ldc_floating_labels']){
        		if(isset($field['placeholder']) and $field['placeholder']){
        			$placeholder = $field['placeholder'];
        			$outer_html = str_get_html($outer_html);
        			$div = $outer_html->find('.rwmb-input', 0);
        			$div->addClass('ldc-floating-labels');
        			$select = $div->find('select', 0);
        			$select->addClass('custom-select mw-100');
        			$option = $select->find('option', 0);
        			$option->innertext = '';
        			$select->outertext = $select->outertext . '<label>' . $placeholder . '</label>';
        		}
        	}
        	return $outer_html;
        }, 10, 3);
        ldc_one('rwmb_textarea_outer_html', function($outer_html, $field, $meta){
        	if(isset($field['ldc_floating_labels']) and $field['ldc_floating_labels']){
        		if(isset($field['placeholder']) and $field['placeholder']){
        			$placeholder = $field['placeholder'];
        			$outer_html = str_get_html($outer_html);
        			$div = $outer_html->find('.rwmb-input', 0);
        			$div->addClass('ldc-floating-labels');
        			$textarea = $div->find('textarea', 0);
        			$textarea->addClass('form-control mw-100');
        			$textarea->cols = null;
        			$textarea->rows = null;
        			$textarea->outertext = $textarea->outertext . '<label>' . $placeholder . '</label>';
        		}
        	}
        	return $outer_html;
        }, 10, 3);
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
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
