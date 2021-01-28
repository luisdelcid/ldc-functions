<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_add_bootstrap_to_checkbox')){
    function ldc_mb_add_bootstrap_to_checkbox($html = '', $field = []){
    	ldc_support_simple_html_dom();
    	$html = str_get_html($html);
    	$div = $html->find('.rwmb-input', 0);
    	$div->addClass('custom-control custom-checkbox');
    	$label = $div->find('label', 0);
    	$label->addClass('custom-control-label');
    	$input = $label->find('input', 0);
    	$input->addClass('custom-control-input');
    	$label->for = $input->id;
    	$label->innertext = $label->plaintext;
    	$label->outertext = $input . $label->outertext;
    	return $html;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_add_bootstrap_to_checkbox_list')){
    function ldc_mb_add_bootstrap_to_checkbox_list($html = '', $field = []){
    	ldc_support_simple_html_dom();
    	$html = str_get_html($html);
    	$button = $html->find('button', 0);
    	if($button){
    		$button->addClass('btn btn-outline-secondary btn-sm');
    	}
    	$ul = $html->find('.rwmb-input-list', 0);
    	$ul->addClass('pl-0');
    	foreach($ul->find('li') as $li){
    		$li->addClass('custom-control custom-checkbox');
    		if(isset($field['inline']) and $field['inline']){
    			$li->addClass('custom-control-inline');
    		}
    		$label = $li->find('label', 0);
    		$label->addClass('custom-control-label');
    		$input = $label->find('input', 0);
    		$input->addClass('custom-control-input');
    		$id = $field['id'] . '_' . sanitize_title($input->value);
    		$input->id = $id;
    		$label->for = $id;
    		$label->innertext = $label->plaintext;
    		$label->outertext = $input . $label->outertext;
    	}
    	$description = $html->find('.description', 0);
    	if($description){
    		$description->addClass('form-text text-muted small mb-0');
    	}
    	return $html;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_add_bootstrap_to_radio')){
    function ldc_mb_add_bootstrap_to_radio($html = '', $field = []){
    	ldc_support_simple_html_dom();
    	$html = str_get_html($html);
    	$ul = $html->find('.rwmb-input-list', 0);
    	$ul->addClass('pl-0');
    	foreach($ul->find('li') as $li){
    		$li->addClass('custom-control custom-radio');
    		if(isset($field['inline']) and $field['inline']){
    			$li->addClass('custom-control-inline');
    		}
    		$label = $li->find('label', 0);
    		$label->addClass('custom-control-label');
    		$input = $label->find('input', 0);
    		$input->addClass('custom-control-input');
    		$id = $field['id'] . '_' . sanitize_title($input->value);
    		$input->id = $id;
    		$label->for = $id;
    		$label->innertext = $label->plaintext;
    		$label->outertext = $input . $label->outertext;
    	}
    	$description = $html->find('.description', 0);
    	if($description){
    		$description->addClass('form-text text-muted small mb-0');
    	}
    	return $html;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_mb_add_bootstrap_to_select')){
    function ldc_mb_add_bootstrap_to_select($html = '', $field = []){
    	ldc_support_simple_html_dom();
        $html = str_get_html($html);
        $div = $html->find('.rwmb-input', 0);
        $select = $div->find('select', 0);
        $select->addClass('custom-select mw-100');
        $class = '';
        if(isset($field['ldc_bootstrap_class']) and $field['ldc_bootstrap_class']){
            $class = ldc_sanitize_html_class($field['ldc_bootstrap_class']);
        }
        if($class){
            $select->addClass($class);
        }
        $description = $div->find('.description', 0);
        if($description){
            $description->addClass('form-text text-muted small mb-0');
        }
        return $html;
    }
}

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
		ldc_enqueue_bs_custom_file_input();
		ldc_enqueue_functions();
        ldc_support_simple_html_dom();
        ldc_one('rwmb_outer_html', function($outer_html, $field, $meta){
        	if(isset($field['type']) and in_array($field['type'], ['text', 'email', 'url', 'tel'])){
        		if(isset($field['ldc_bootstrap']) and $field['ldc_bootstrap']){
        			$outer_html = str_get_html($outer_html);
					$div = $outer_html->find('.rwmb-input', 0);
					$input = $div->find('input', 0);
					$input->addClass('form-control mw-100');
					$class = '';
					if(isset($field['ldc_bootstrap_class']) and $field['ldc_bootstrap_class']){
						$class = ldc_sanitize_html_class($field['ldc_bootstrap_class']);
					}
					if($class){
						$input->addClass($class);
					}
					$description = $div->find('.description', 0);
					if($description){
						$description->addClass('form-text text-muted small mb-0');
					}
        		}
        	}
            if(isset($field['type']) and in_array($field['type'], ['post'])){
				if(isset($field['ldc_bootstrap']) and $field['ldc_bootstrap']){
					if(isset($field['field_type']) and $field['field_type'] == 'checkbox_list'){
						$outer_html = ldc_mb_add_bootstrap_to_checkbox_list($outer_html, $field);
					}
					if(isset($field['field_type']) and $field['field_type'] == 'radio'){
						$outer_html = ldc_mb_add_bootstrap_to_radio($outer_html, $field);
					}
                    if(isset($field['field_type']) and $field['field_type'] == 'select'){
						$outer_html = ldc_mb_add_bootstrap_to_select($outer_html, $field);
					}
				}
			}
        	return $outer_html;
        }, 10, 3);
		ldc_one('rwmb_select_outer_html', function($outer_html, $field, $meta){
        	if(isset($field['ldc_bootstrap']) and $field['ldc_bootstrap']){
				$outer_html = str_get_html($outer_html);
				$div = $outer_html->find('.rwmb-input', 0);
				$select = $div->find('select', 0);
				$select->addClass('custom-select mw-100');
				$class = '';
				if(isset($field['ldc_bootstrap_class']) and $field['ldc_bootstrap_class']){
					$class = ldc_sanitize_html_class($field['ldc_bootstrap_class']);
				}
				if($class){
					$select->addClass($class);
				}
				$description = $div->find('.description', 0);
				if($description){
					$description->addClass('form-text text-muted small mb-0');
				}
        	}
        	return $outer_html;
        }, 10, 3);
		ldc_one('rwmb_textarea_outer_html', function($outer_html, $field, $meta){
        	if(isset($field['ldc_bootstrap']) and $field['ldc_bootstrap']){
				$outer_html = str_get_html($outer_html);
				$div = $outer_html->find('.rwmb-input', 0);
				$textarea = $div->find('textarea', 0);
				$textarea->addClass('form-control mw-100');
				$class = '';
				if(isset($field['ldc_bootstrap_class']) and $field['ldc_bootstrap_class']){
					$class = ldc_sanitize_html_class($field['ldc_bootstrap_class']);
				}
				if($class){
					$textarea->addClass($class);
				}
				$description = $div->find('.description', 0);
				if($description){
					$description->addClass('form-text text-muted small mb-0');
				}
        	}
        	return $outer_html;
        }, 10, 3);
		ldc_one('rwmb_file_outer_html', function($outer_html, $field, $meta){
			if(isset($field['ldc_bootstrap']) and $field['ldc_bootstrap']){
				$outer_html = str_get_html($outer_html);
				$div = $outer_html->find('.rwmb-file-new', 0);
				$div->addClass('custom-file');
				$file = $div->find('input', 0);
				$file->addClass('custom-file-input');
				$file->outertext = $file->outertext . '<label class="custom-file-label" for="' . $field['id'] . '" data-browse="' . __('Select') . '">' . __('Select Files') . '</label>';
				$description = $div->find('.description', 0);
				if($description){
					$description->addClass('form-text text-muted small mb-0');
				}
			}
        	return $outer_html;
        }, 10, 3);
		ldc_one('rwmb_checkbox_list_outer_html', function($outer_html, $field, $meta){
			if(isset($field['ldc_bootstrap']) and $field['ldc_bootstrap']){
				$outer_html = ldc_mb_add_bootstrap_to_checkbox_list($outer_html, $field);
			}
        	return $outer_html;
        }, 10, 3);
		ldc_one('rwmb_checkbox_outer_html', function($outer_html, $field, $meta){
			if(isset($field['ldc_bootstrap']) and $field['ldc_bootstrap']){
				$outer_html = ldc_mb_add_bootstrap_to_checkbox($outer_html, $field);
			}
        	return $outer_html;
        }, 10, 3);
		ldc_one('rwmb_radio_outer_html', function($outer_html, $field, $meta){
			if(isset($field['ldc_bootstrap']) and $field['ldc_bootstrap']){
				$outer_html = ldc_mb_add_bootstrap_to_radio($outer_html, $field);
			}
        	return $outer_html;
        }, 10, 3);
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
						$description = $div->find('.description', 0);
						if($description){
							$description->addClass('form-text text-muted small mb-0');
						}
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
					$description = $div->find('.description', 0);
					if($description){
						$description->addClass('form-text text-muted small mb-0');
					}
        		}
        	}
        	return $outer_html;
        }, 10, 3);
		ldc_one('rwmb_normalize_field', function($field){
        	if((isset($field['ldc_bootstrap']) and $field['ldc_bootstrap']) or (isset($field['ldc_floating_labels']) and $field['ldc_floating_labels'])){
				if(isset($field['clone']) and $field['clone']){
					$field['clone'] = false;
				}
				if(isset($field['max_file_uploads'])){
					$field['max_file_uploads'] = 1;
				}
			}
			return $field;
        });
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
					$description = $div->find('.description', 0);
					if($description){
						$description->addClass('form-text text-muted small mb-0');
					}
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
