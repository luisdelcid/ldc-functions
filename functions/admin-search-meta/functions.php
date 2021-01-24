<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_support_admin_search_meta')){
    function ldc_support_admin_search_meta(){
        ldc_support_admin_search_post_meta();
        ldc_support_admin_search_user_meta();
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_support_admin_search_post_meta')){
    function ldc_support_admin_search_post_meta(){
        ldc_one('posts_groupby', function($groupby){
            global $pagenow, $wpdb;
            if(is_admin() and $pagenow === 'edit.php' and is_search()){
                $g = $wpdb->posts . '.ID';
                if(!$groupby){
                    $groupby = $g;
                } else {
                    $groupby = trim($groupby) . ', ' . $g;
                }
            }
            return $groupby;
        });
        ldc_one('posts_join', function($join){
            global $pagenow, $wpdb;
            if(is_admin() and $pagenow === 'edit.php' and is_search()){
                $j = 'LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id';
                if(!$join){
                    $join = $j;
                } else {
                    $join = trim($join) . ' ' . $j;
                }
            }
            return $join;
        });
        ldc_one('posts_where', function($where){
            global $pagenow, $wpdb;
            if(is_admin() and $pagenow === 'edit.php' and is_search()){
                $s = get_query_var('s');
                $s = $wpdb->esc_like($s);
                $s = '%' . $s . '%';
                $str = '(' . $wpdb->posts . '.post_title LIKE %s)';
                $sql = $wpdb->prepare($str, $s);
                $search = $sql;
                $str = '(' . $wpdb->postmeta . '.meta_value LIKE %s)';
                $sql = $wpdb->prepare($str, $s);
                $replace = $search . ' OR ' . $sql;
                $where = str_replace($search, $replace, $where);
            }
            return $where;
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_support_admin_search_user_meta')){
    function ldc_support_admin_search_user_meta(){
        ldc_one('users_pre_query', function($results, $user_query){
            global $pagenow, $wpdb;
            if(is_admin() and $pagenow === 'users.php' and $user_query->get('search') and is_null($user_query->results)){
                $j = 'LEFT JOIN ' . $wpdb->usermeta . ' ON ' . $wpdb->users . '.ID = ' . $wpdb->usermeta . '.user_id';
                $user_query->query_from .= ' ' . $j;
                $s = $user_query->get('search');
                $s = str_replace('*', '%', $s);
                $str = 'user_login LIKE %s';
                $sql = $wpdb->prepare($str, $s);
                $search = $sql;
                $str = 'meta_value LIKE %s';
                $sql = $wpdb->prepare($str, $s);
                $replace = $search . ' OR ' . $sql;
                $user_query->query_where = str_replace($search, $replace, $user_query->query_where);
                $user_query->query_where .= ' GROUP BY ID';
            }
            return $results;
        }, 10, 2);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
