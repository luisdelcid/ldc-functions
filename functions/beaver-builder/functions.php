<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_bb_expand_templates')){
    function ldc_bb_expand_templates(){
        ldc_one('walker_nav_menu_start_el', function($item_output, $item, $depth, $args){
            if($item->object == 'fl-builder-template'){
                $item_output = $args->before;
                $item_output .= do_shortcode('[fl_builder_insert_layout id="' . $item->object_id . '"]');
                $item_output .= $args->after;
            }
            return $item_output;
        }, 10, 4);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_bb_disable_column_resizing')){
    function ldc_bb_disable_column_resizing(){
        ldc_one('wp_head', function(){
            if(array_key_exists('fl_builder', $_GET)){ ?>
                <style type="text/css">
                    .fl-block-col-resize {
                        display: none;
                    }
                </style><?php
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_bb_disable_inline_editing')){
    function ldc_bb_disable_inline_editing(){
        ldc_one('fl_inline_editing_enabled', '__return_false');
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_bb_disable_row_resizing')){
    function ldc_bb_disable_row_resizing(){
        ldc_one('fl_row_resize_settings', function($settings){
            $settings['userCanResizeRows'] = false;
            return $settings;
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_bb_fix_in_the_loop')){
    function ldc_bb_fix_in_the_loop(){
        ldc_one('fl_theme_builder_before_render_content', function(){
            global $wp_query;
            if(!$wp_query->in_the_loop){
                $wp_query->in_the_loop = true;
                ldc_one('fl_theme_builder_after_render_content', function(){
                    global $wp_query;
                    $wp_query->in_the_loop = false;
                });
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_bb_get_setting')){
    function ldc_bb_get_setting($key = '', $default = ''){
        if(get_template() == 'bb-theme'){
            return FLTheme::get_setting($key, $default);
        }
        return $default;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_bb_module')){
    function ldc_bb_module($class_name = ''){
        if(!class_exists('\LDC_BB_Module')){
			require_once(plugin_dir_path(__FILE__) . 'class-ldc-bb-module.php');
		}
        if(!class_exists('\LDC_BB_Module_Tab')){
			require_once(plugin_dir_path(__FILE__) . 'class-ldc-bb-module-tab.php');
		}
        if(!class_exists('\LDC_BB_Module_Section')){
			require_once(plugin_dir_path(__FILE__) . 'class-ldc-bb-module-section.php');
		}
        if(!class_exists('\LDC_BB_Module_Field')){
			require_once(plugin_dir_path(__FILE__) . 'class-ldc-bb-module-field.php');
		}
        return new LDC_BB_Module($class_name);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_bb_reboot_default_styles')){
    function ldc_bb_reboot_default_styles($hard = false){
        if(get_template() == 'bb-theme'){
            $mods = get_theme_mods();
            $custom_css_post_id = 0;
            if(isset($mods['custom_css_post_id'])){
                $custom_css_post_id = $mods['custom_css_post_id'];
                unset($mods['custom_css_post_id']);
            }
            if($hard or ldc_md5($mods) == '1098625cbdfed9801fa6cea80b522d2a'){
                if($custom_css_post_id){
                    $mods['custom_css_post_id'] = $custom_css_post_id;
                }
                $mods['fl-scroll-to-top'] = 'enable';
                $mods['fl-framework'] = 'bootstrap-4';
                $mods['fl-awesome'] = 'fa5';
                $mods['fl-body-bg-color'] = '#ffffff';
                $mods['fl-accent'] = '#007bff';
                $mods['fl-accent-hover'] = '#0056b3';
                $mods['fl-heading-text-color'] = '#343a40';
                $mods['fl-heading-font-family'] = 'Open Sans';
                $mods['fl-h1-font-size'] = 40;
                $mods['fl-h1-font-size_medium'] = 33;
                $mods['fl-h1-font-size_mobile'] = 28;
                $mods['fl-h1-line-height'] = 1.2;
                $mods['fl-h1-line-height_medium'] = 1.2;
                $mods['fl-h1-line-height_mobile'] = 1.2;
                $mods['fl-h2-font-size'] = 32;
                $mods['fl-h2-font-size_medium'] = 28;
                $mods['fl-h2-font-size_mobile'] = 24;
                $mods['fl-h2-line-height'] = 1.2;
                $mods['fl-h2-line-height_medium'] = 1.2;
                $mods['fl-h2-line-height_mobile'] = 1.2;
                $mods['fl-h3-font-size'] = 28;
                $mods['fl-h3-font-size_medium'] = 25;
                $mods['fl-h3-font-size_mobile'] = 22;
                $mods['fl-h3-line-height'] = 1.2;
                $mods['fl-h3-line-height_medium'] = 1.2;
                $mods['fl-h3-line-height_mobile'] = 1.2;
                $mods['fl-h4-font-size'] = 24;
                $mods['fl-h4-font-size_medium'] = 22;
                $mods['fl-h4-font-size_mobile'] = 20;
                $mods['fl-h4-line-height'] = 1.2;
                $mods['fl-h4-line-height_medium'] = 1.2;
                $mods['fl-h4-line-height_mobile'] = 1.2;
                $mods['fl-h5-font-size'] = 20;
                $mods['fl-h5-font-size_medium'] = 19;
                $mods['fl-h5-font-size_mobile'] = 16;
                $mods['fl-h5-line-height'] = 1.2;
                $mods['fl-h5-line-height_medium'] = 1.2;
                $mods['fl-h5-line-height_mobile'] = 1.2;
                $mods['fl-h6-font-size'] = 16;
                $mods['fl-h6-font-size_medium'] = 16;
                $mods['fl-h6-font-size_mobile'] = 16;
                $mods['fl-h6-line-height'] = 1.2;
                $mods['fl-h6-line-height_medium'] = 1.2;
                $mods['fl-h6-line-height_mobile'] = 1.2;
                $mods['fl-body-text-color'] = '#6c757d';
                $mods['fl-body-font-family'] = 'Open Sans';
                $mods['fl-body-font-size'] = 16;
                $mods['fl-body-font-size_medium'] = 16;
                $mods['fl-body-font-size_mobile'] = 16;
                $mods['fl-body-line-height'] = 1.5;
                $mods['fl-body-line-height_medium'] = 1.5;
                $mods['fl-body-line-height_mobile'] = 1.5;
                $mods['fl-header-layout'] = 'none';
                $mods['fl-fixed-header'] = 'hidden';
                $mods['fl-footer-widgets-display'] = 'disabled';
                $mods['fl-footer-layout'] = 'none';
                update_option('theme_mods_' . get_option('stylesheet'), $mods);
                return true;
            }
        }
        return false;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_bb_remove_default_styles')){
    function ldc_bb_remove_default_styles(){
        ldc_one('fl_theme_compile_less_paths', function($paths){
            foreach($paths as $index => $path){
                if($path == FL_THEME_DIR . '/less/theme-1.7.8.less'){
                    $paths[$index] = plugin_dir_path(__FILE__) . 'theme-1.7.8.less';
                }
            }
            return $paths;
        });
        if(get_template() == 'bb-theme'){
            ldc_one('wp_enqueue_scripts', function(){
                $ver = filemtime(plugin_dir_path(__FILE__) . 'remove.css');
                wp_enqueue_style('ldc-bb-remove', plugin_dir_url(__FILE__) . 'remove.css', [], $ver);
            });
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_bb_remove_presets')){
    function ldc_bb_remove_presets(){
        ldc_one('customize_register', function($wp_customize){
            $wp_customize->remove_section('fl-presets');
        }, 20);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_bb_settings_form')){
    function ldc_bb_settings_form($id = '', $title = ''){
        if(!class_exists('\LDC_BB_Settings_Form')){
			require_once(plugin_dir_path(__FILE__) . 'class-ldc-bb-settings-form.php');
		}
        return new LDC_BB_Settings_Form($id, $title);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_bb_use_bootstrap_colors')){
    function ldc_bb_use_bootstrap_colors(){
        ldc_one('customize_controls_print_footer_scripts', function(){ ?>
            <script>
                var bootstrap_colors = [
                    '#007bff', // primary
                    '#6c757d', // secondary
                    '#28a745', // success
                    '#17a2b8', // info
                    '#ffc107', // warning
                    '#dc3545', // danger
                    '#f8f9fa', // light
                    '#343a40', // dark
                ];
                jQuery(function($){
                    $('.wp-picker-container').iris({
                        mode: 'hsl',
                        controls: {
                            horiz: 'h', // square horizontal displays hue
                            vert: 's', // square vertical displays saturdation
                            strip: 'l' // slider displays lightness
                        },
                        palettes: bootstrap_colors,
                    });
                });
            </script><?php
        });
        ldc_one('fl_builder_color_presets', function($colors){
            $bootstrap_colors = [
                '007bff', // primary
                '6c757d', // secondary
                '28a745', // success
                '17a2b8', // info
                'ffc107', // warning
                'dc3545', // danger
                'f8f9fa', // light
                '343a40', // dark
            ];
            return array_values(array_unique(array_merge($colors, $bootstrap_colors)));
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
