<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_cf7_disable_autop')){
    function ldc_cf7_disable_autop(){
        ldc_one('wpcf7_autop_or_not', '__return_false');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_cf7_use_bootstrap_styles')){
    function ldc_cf7_use_bootstrap_styles($ver = 4){
        ldc_one('wp_enqueue_scripts', function() use($ver){
            switch($ver){
                case 4:
                    $file = plugin_dir_path(__FILE__) . 'ldc-cf7-b4-styles.css';
                    $ver = filemtime($file);
                    wp_enqueue_style('ldc-cf7-b4-styles', plugin_dir_url(__FILE__) . 'ldc-cf7-b4-styles.css', [], $ver);
                    break;
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_cf7_use_floating_labels')){
    function ldc_cf7_use_floating_labels(){
        ldc_enqueue_functions();
        ldc_use_floating_labels();
        ldc_use_simple_html_dom();
		ldc_one('wp_print_footer_scripts', function(){ ?>
            <script id="ldc-cf7-use-floating-labels">
                document.addEventListener('wpcf7mailsent', function(event){
                    setTimeout(function(){
                        ldc_floating_labels();
                    }, 500);
                }, false);
            </script><?php
        });
        ldc_off('wpcf7_init', 'wpcf7_add_form_tag_text', 10, 0);
        ldc_one('wpcf7_init', function(){
        	wpcf7_add_form_tag(['text', 'text*', 'email', 'email*', 'url', 'url*', 'tel', 'tel*'], function($tag){
        		$html = wpcf7_text_form_tag_handler($tag);
        		$ldc = $tag->get_option('ldc');
        		if($ldc){
        			if(in_array('floating_labels', $ldc)){
        				if($tag->has_option('placeholder') or $tag->has_option('watermark')){
        					$values = $tag->values;
        					if($values){
        						$placeholder = reset($values);
        						$html = str_get_html($html);
        						$span = $html->find('.wpcf7-form-control-wrap', 0);
								$span->addClass('ldc-floating-labels');
        						$input = $span->find('input', 0);
        						$input->addClass('form-control');
        						$input->outertext = $input->outertext . '<label>' . $placeholder . '</label>';
        					}
        				}
        			}
        		}
        		return $html;
        	}, [
        		'name-attr' => true,
        	]);
        }, 10, 0);
        ldc_off('wpcf7_init', 'wpcf7_add_form_tag_textarea', 10, 0);
        ldc_one('wpcf7_init', function(){
        	wpcf7_add_form_tag(['textarea', 'textarea*'], function($tag){
        		$html = wpcf7_textarea_form_tag_handler($tag);
        		$ldc = $tag->get_option('ldc');
        		if($ldc){
        			if(in_array('floating_labels', $ldc)){
        				if($tag->has_option('placeholder') or $tag->has_option('watermark')){
        					$values = $tag->values;
        					if($values){
        						$placeholder = reset($values);
        						$html = str_get_html($html);
        						$span = $html->find('.wpcf7-form-control-wrap', 0);
								$span->addClass('ldc-floating-labels');
        						$textarea = $span->find('textarea', 0);
        						$textarea->addClass('form-control');
        						$textarea->cols = null;
        						$textarea->rows = null;
        						$textarea->outertext = $textarea->outertext . '<label>' . $placeholder . '</label>';
        					}
        				}
        			}
        		}
        		return $html;
        	}, [
        		'name-attr' => true,
        	]);
        }, 10, 0);
        ldc_off('wpcf7_init', 'wpcf7_add_form_tag_select', 10, 0);
        ldc_one('wpcf7_init', function(){
        	wpcf7_add_form_tag(['select', 'select*'], function($tag){
        		$html = wpcf7_select_form_tag_handler($tag);
        		$ldc = $tag->get_option('ldc');
        		if($ldc){
        			if(in_array('floating_labels', $ldc)){
        				if($tag->has_option('first_as_label')){
        					$values = $tag->values;
        					if($values){
        						$placeholder = $values[0];
        						$html = str_get_html($html);
        						$span = $html->find('.wpcf7-form-control-wrap', 0);
								$span->addClass('ldc-floating-labels');
        						$select = $span->find('select', 0);
        						$select->addClass('custom-select');
        						$option = $select->find('option', 0);
        						$option->innertext = '';
        						$select->outertext = $select->outertext . '<label>' . $placeholder . '</label>';
        					}
        				}
        			}
        		}
        		return $html;
        	}, [
        		'name-attr' => true,
        	]);
        }, 10, 0);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~