<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_get_scripts')){
    function ldc_get_scripts($context = 'front-end', $in_footer = false){
		return get_posts([
			'meta_query' => [
				[
					'key' => 'ldc_context',
					'value' => $context,
				],
				[
					'key' => 'ldc_in_footer',
					'value' => (int) $in_footer,
				],
			],
			'post_type' => 'ldc_javascript',
			'posts_per_page' => -1,
		]);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_get_styles')){
    function ldc_get_styles($context = 'front-end'){
		return get_posts([
			'meta_query' => [
				[
					'key' => 'ldc_context',
					'value' => $context,
				],
			],
			'post_type' => 'ldc_css',
			'posts_per_page' => -1,
		]);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_support_additional_code')){
    function ldc_support_additional_code(){
        if(ldc_is_plugin_active('meta-box/meta-box.php')){
            ldc_one('admin_enqueue_scripts', function(){
                if(ldc_current_screen_in(['ldc_css', 'ldc_javascript'])){
                    wp_enqueue_script('ace', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.min.js', [], '1.4.12', true);
                }
            });
			ldc_one('admin_print_footer_scripts', function(){
                switch(true){
                    case ldc_current_screen_is('ldc_css'): ?>
            			<script id="ldc=additional-css">
            				jQuery(function($){
            					if(typeof ace != 'undefined'){
                                    ace.config.set('basePath', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12');
                                    if($('#ldc_css_editor').length){
                                        var ldc_css_editor = ace.edit('ldc_css_editor');
                                        ldc_css_editor.$blockScrolling = Infinity;
                                        ldc_css_editor.setOptions({
                                            maxLines: 25,
                                            minLines: 5,
                                        });
                                        ldc_css_editor.getSession().on('change', function(){
                                            $('#ldc_additional_css').val(ldc_css_editor.getSession().getValue()).trigger('change');
                                        });
                                        ldc_css_editor.getSession().setMode('ace/mode/css');
                                        ldc_css_editor.getSession().setValue($('#ldc_additional_css').val());
                                    }
            					}
            				});
            			</script><?php
                        break;
                    case ldc_current_screen_is('ldc_javascript'): ?>
                        <script id="ldc=additional-javascript">
                            jQuery(function($){
                                if(typeof ace != 'undefined'){
                                    ace.config.set('basePath', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12');
                                    if($('#ldc_javascript_editor').length){
                                        var ldc_javascript_editor = ace.edit('ldc_javascript_editor');
                                        ldc_javascript_editor.$blockScrolling = Infinity;
                                        ldc_javascript_editor.setOptions({
                                            maxLines: 25,
                                            minLines: 5,
                                        });
                                        ldc_javascript_editor.getSession().on('change', function(){
                                            $('#ldc_additional_javascript').val(ldc_javascript_editor.getSession().getValue()).trigger('change');
                                        });
                                        ldc_javascript_editor.getSession().setMode('ace/mode/javascript');
                                        ldc_javascript_editor.getSession().setValue($('#ldc_additional_javascript').val());
                                    }
                                }
                            });
                        </script><?php
                        break;
                }
				$posts = ldc_get_scripts('admin', true);
    			if($posts){
    				echo '<script id="ldc-admin-footer-scripts">';
    				foreach($posts as $post){
    					if(apply_filters('ldc_additional_javascript', true, $post)){
    						echo '/* ' . $post->post_title . " */\n";
    						echo trim(get_post_meta($post->ID, 'ldc_additional_javascript', true)) . "\n";
    					}
    				}
    				echo '</script>';
    			}
            });
			ldc_one('admin_print_scripts', function(){
				$posts = ldc_get_scripts('admin');
    			if($posts){
    				echo '<script id="ldc-admin-scripts">';
    				foreach($posts as $post){
    					if(apply_filters('ldc_additional_javascript', true, $post)){
    						echo '/* ' . $post->post_title . " */\n";
    						echo trim(get_post_meta($post->ID, 'ldc_additional_javascript', true)) . "\n";
    					}
    				}
    				echo '</script>';
    			}
            });
			ldc_one('admin_print_styles', function(){
				$posts = ldc_get_styles('admin');
    			if($posts){
    				echo '<style id="ldc-admin-styles">';
    				foreach($posts as $post){
    					if(apply_filters('ldc_additional_css', true, $post)){
                            echo '/* ' . $post->post_title . " */\n";
    						echo trim(get_post_meta($post->ID, 'ldc_additional_css', true)) . "\n";
    					}
    				}
    				echo '</style>';
    			}
            });
            ldc_one('init', function(){
                foreach([
    				'css' => 'CSS',
    				'javascript' => 'JavaScript',
    			] as $mode => $label){
					$singular = str_replace('CSS', $label, __('Additional CSS'));
					$plural = $singular;
    				register_post_type('ldc_' . $mode, [
    					'capability_type' => 'page',
    					'labels' => ldc_post_type_labels($singular, $plural, false),
    					'show_in_admin_bar' => false,
    					'show_in_menu' => 'themes.php',
    					'show_ui' => true,
    					'supports' => ['title'],
    				]);
    			}
            });
            ldc_one('rwmb_meta_boxes', function($meta_boxes){
                foreach([
    				'css' => 'CSS',
    				'javascript' => 'JavaScript',
    			] as $mode => $label){
					$fields = [
						[
							'id' => 'ldc_additional_' . $mode,
							'sanitize_callback' => 'none',
							'type' => 'hidden',
						],
						[
							'std' => '<div id="ldc_' . $mode . '_editor" style="border: 1px solid #e5e5e5; height: 0; margin-top: 6px; width: 100%;"></div>',
							'type' => 'custom_html',
						],
					];
					if($mode == 'javascript'){
						$fields[] = [
							'desc' => esc_html('Enqueue before </body> instead of in the <head>.'),
							'id' => 'ldc_in_footer',
							'std'  => 1,
							'type' => 'checkbox',
						];
					}
    				$meta_boxes[] = [
    					'fields' => $fields,
    					'post_types' => 'ldc_' . $mode,
    					'title' => str_replace('CSS', $label, __('Additional CSS')),
    				];
					$meta_boxes[] = [
						'context' => 'side',
    					'fields' => [
							[
								'id' => 'ldc_context',
								'inline' => false,
								'options' => [
									'admin' => 'Admin',
									'front-end' => 'Front-end',
								],
								'std'  => 'front-end',
								'type' => 'radio',
							],
						],
    					'post_types' => 'ldc_' . $mode,
    					'title' => 'Context',
    				];
    			}
    			return $meta_boxes;
            });
			// 999 should be enough for themes
            ldc_one('wp_print_footer_scripts', function(){
				$posts = ldc_get_scripts('front-end', true);
    			if($posts){
    				echo '<script id="ldc-footer-scripts">';
    				foreach($posts as $post){
    					if(apply_filters('ldc_additional_javascript', true, $post)){
    						echo '/* ' . $post->post_title . " */\n";
    						echo trim(get_post_meta($post->ID, 'ldc_additional_javascript', true)) . "\n";
    					}
    				}
    				echo '</script>';
    			}
            }, 1000);
			ldc_one('wp_print_scripts', function(){
				$posts = ldc_get_scripts();
    			if($posts){
    				echo '<script id="ldc-scripts">';
    				foreach($posts as $post){
    					if(apply_filters('ldc_additional_javascript', true, $post)){
    						echo '/* ' . $post->post_title . " */\n";
    						echo trim(get_post_meta($post->ID, 'ldc_additional_javascript', true)) . "\n";
    					}
    				}
    				echo '</script>';
    			}
            }, 1000);
			ldc_one('wp_print_styles', function(){
				$posts = ldc_get_styles();
    			if($posts){
    				echo '<style id="ldc-styles">';
    				foreach($posts as $post){
    					if(apply_filters('ldc_additional_css', true, $post)){
                            echo '/* ' . $post->post_title . " */\n";
    						echo trim(get_post_meta($post->ID, 'ldc_additional_css', true)) . "\n";
    					}
    				}
    				echo '</style>';
    			}
            }, 1000);
        } else {
            ldc_tgmpa_register([
                'name' => 'Meta Box',
                'required' => true,
                'slug' => 'meta-box',
            ]);
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
