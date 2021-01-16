<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_hide_others_media')){
    function ldc_hide_others_media($capability = 'edit_others_posts'){
        ldc_one('ajax_query_attachments_args', function($query) use($capability){
            if(!current_user_can($capability)){
                $query['author'] = get_current_user_id();
            }
            return $query;
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_hide_others_posts')){
    function ldc_hide_others_posts($capability = 'edit_others_posts'){
        ldc_one('current_screen', function($current_screen) use($capability){
            global $pagenow;
            if($pagenow == 'edit.php' and !current_user_can($capability)){
                ldc_on('views_' . $current_screen->id, function($views){
                    foreach($views as $index => $view){
                        $views[$index] = preg_replace('/ <span class="count">\([0-9]+\)<\/span>/', '', $view);
                    }
                    return $views;
                });
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_hide_site')){
    function ldc_hide_site($capability = 'read', $exclude = []){
        ldc_one('template_redirect', function() use($capability, $exclude){
            if(is_front_page() and in_array('front_page', $exclude)){
                return;
            }
            if(is_home() and in_array('home', $exclude)){
                return;
            }
            if(in_array(get_the_ID(), $exclude)){
                return;
            }
            if(!is_user_logged_in()){
                auth_redirect();
            } else {
                if(!current_user_can($capability)){
                    wp_die('<h1>' . __('You need a higher level of permission.') . '</h1>' . '<p>' . __('Sorry, you are not allowed to access this page.') . '</p>', 403);
                }
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_hide_the_dashboard')){
    function ldc_hide_the_dashboard($capability = 'edit_posts'){
        ldc_one('admin_init', function() use($capability){
            if(!wp_doing_ajax() and !current_user_can($capability)){
                wp_safe_redirect(home_url());
                exit;
            }
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_hide_the_rest_api')){
    function ldc_hide_the_rest_api($capability = 'read'){
        ldc_one('rest_authentication_errors', function($error) use($capability){
            if($error){
                return $error;
            }
            if(!current_user_can($capability)){
                return new WP_Error('rest_user_cannot_view', __('You need a higher level of permission.'), [
                    'status' => 401,
                ]);
            }
            return null;
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_hide_the_toolbar')){
    function ldc_hide_the_toolbar($capability = 'edit_posts'){
        ldc_one('show_admin_bar', function($show) use($capability){
            if(!current_user_can($capability)){
                return false;
            }
            return $show;
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
