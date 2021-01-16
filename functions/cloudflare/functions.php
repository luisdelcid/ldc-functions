<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_cloudflare_checklist')){
    function ldc_cloudflare_checklist(){
        $items = [];
        if(ldc_seems_cloudflare()){
            $domain = wp_parse_url(site_url(), PHP_URL_HOST);
            $items['SSL/TLS > Encryption Mode'] = 'Full';
            $items['SSL/TLS > Edge Certificates <code>*.' . $domain . ', ' . $domain . '</code>'] = 'Active';
            $items['SSL/TLS > Always Use HTTPS'] = 'On';
            $items['SSL/TLS > Automatic HTTPS Rewrites'] = 'On';
            $items['Speed > Optimization > Auto Minify'] = 'JavaScript, CSS, HTML';
            $items['Speed > Optimization > Rocket Loader™'] = 'On';
            $items['Caching > Configuration > Caching Level'] = 'Standard';
            $items['Caching > Configuration > Browser Cache TTL'] = '>= 8 days';
            $items['Page Rules > <code>*' . $domain . '/*wp-login.php*</code>'] = 'Security Level: High';
            $items['Page Rules > <code>*' . $domain . '/*wp-admin/*</code>'] = 'Security Level: High, Cache Level: Bypass, Disable Apps, Disable Performance';
            $items['Page Rules > <code>*' . $domain . '/*fl_builder</code>'] = 'Rocket Loader: Off';
        }
        if($items){
            return ldc_checklist_table($items, false);
        }
        return '';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_cloudflare_checklist_auto')){
    function ldc_cloudflare_checklist_auto(){
        $items = [];
        if(ldc_seems_cloudflare()){
            $items['Cloudflare'] = ldc_checklist_success();
            $items['Network > IP Geolocation'] = isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? ldc_checklist_success() : ldc_checklist_error();
            $post_max_size = wp_convert_hr_to_bytes(ini_get('post_max_size'));
            $items['PHP > post_max_size <= Network > Maximum Upload Size (100 MB)'] = ($post_max_size <= (100 * MB_IN_BYTES) ? ldc_checklist_success() : ldc_checklist_error()) . ' (' . size_format($post_max_size) . ')';
        } else {
            $items['Cloudflare'] = ldc_checklist_error();
        }
        if($items){
            return ldc_checklist_table($items);
        }
        return '';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_seems_cloudflare')){
    function ldc_seems_cloudflare(){
        return isset($_SERVER['HTTP_CF_RAY']);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
