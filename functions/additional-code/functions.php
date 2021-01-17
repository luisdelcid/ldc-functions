<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_support_additional_code')){
    function ldc_support_additional_code(){
        if(ldc_is_plugin_active('meta-box/meta-box.php')){
            ldc_one('admin_enqueue_scripts', function(){
                if(ldc_current_screen_is('edit-ldc_css')){
                    wp_enqueue_script('ace', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.min.js', [], '1.4.12', true);
                }
            });
            ldc_one('admin_print_footer_scripts', function(){
                if(ldc_current_screen_is('edit-ldc_css')){ ?>
        			<script>
        				jQuery(function($){
        					if(typeof ace != 'undefined'){
                                ace.config.set('basePath', 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12');<?php
        						foreach(['css', 'javascript'] as $mode){ ?>
        							if($('#ldc_<?php echo $mode; ?>_editor').length){
        								var ldc_<?php echo $mode; ?>_editor = ace.edit('ldc_<?php echo $mode; ?>_editor');
        								ldc_<?php echo $mode; ?>_editor.$blockScrolling = Infinity;
        								ldc_<?php echo $mode; ?>_editor.setOptions({
        									maxLines: 25,
        									minLines: 5,
        								});
        								ldc_<?php echo $mode; ?>_editor.getSession().on('change', function(){
        									$('#ldc_additional_<?php echo $mode; ?>').val(ldc_<?php echo $mode; ?>_editor.getSession().getValue()).trigger('change');
        								});
        								ldc_<?php echo $mode; ?>_editor.getSession().setMode('ace/mode/<?php echo $mode; ?>');
        								ldc_<?php echo $mode; ?>_editor.getSession().setValue($('#ldc_additional_<?php echo $mode; ?>').val());
        							}<?php
        						} ?>
        					}
        				});
        			</script><?php
                }
            });
            ldc_one('init', function(){
                foreach([
    				'css' => 'CSS',
    				'javascript' => 'JavaScript',
    			] as $mode => $label){
    				register_post_type('ldc_' . $mode, [
    					'capability_type' => 'page',
    					'labels' => ldc_post_type_labels('Additional ' . $label, 'Additional ' . $label, false),
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
    				$meta_boxes[] = [
    					'fields' => [
    						[
    							'id' => 'ldc_additional_' . $mode,
    							'sanitize_callback' => 'none',
    							'type' => 'hidden',
    						],
    						[
    							'id' => 'custom_html',
    							'std' => '<div id="ldc_' . $mode . '_editor" style="border: 1px solid #e5e5e5; height: 0; margin-top: 6px; width: 100%;"></div>',
    							'type' => 'custom_html',
    						],
    					],
    					'post_types' => 'ldc_' . $mode,
    					'title' => 'Additional ' . $label,
    				];
    			}
    			return $meta_boxes;
            });
            // 999 should be enough for themes
            ldc_one('wp_head', function(){
                $posts = get_posts([
    				'post_type' => 'ldc_css',
    				'posts_per_page' => -1,
    			]);
    			if($posts){
    				echo '<style id="ldc-additional-css">';
    				foreach($posts as $post){
    					if(apply_filters('ldc_additional_css', true, $post)){
                            echo '/* ' . $post->post_title . " */\n";
    						echo trim(get_post_meta($post->ID, 'ldc_additional_css', true)) . "\n";
    					}
    				}
    				echo '</style>';
    			}
            }, 1000);
            ldc_one('wp_print_footer_scripts', function(){
                $posts = get_posts([
    				'post_type' => 'ldc_javascript',
    				'posts_per_page' => -1,
    			]);
    			if($posts){
    				echo '<script id="ldc-additional-javascript">';
    				foreach($posts as $post){
    					if(apply_filters('ldc_additional_javascript', true, $post)){
    						echo '/* ' . $post->post_title . " */\n";
    						echo trim(get_post_meta($post->ID, 'ldc_additional_javascript', true)) . "\n";
    					}
    				}
    				echo '</script>';
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
