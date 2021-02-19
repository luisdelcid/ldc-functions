<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_custom_login_logo')){
    function ldc_custom_login_logo($attachment_id = 0){
        if(wp_attachment_is_image($attachment_id)){
            ldc_one('login_enqueue_scripts', function() use($attachment_id){
                $custom_logo = wp_get_attachment_image_src($attachment_id, 'medium'); ?>
                <style type="text/css">
                    #login h1 a,
                    .login h1 a {
                        background-image: url(<?php echo $custom_logo[0]; ?>);
                        background-size: <?php echo $custom_logo[1] / 2; ?>px <?php echo $custom_logo[2] / 2; ?>px;
                        height: <?php echo $custom_logo[2] / 2; ?>px;
                        width: <?php echo $custom_logo[1] / 2; ?>px;
                    }
                </style><?php
    		});
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_local_login_header')){
    function ldc_local_login_header(){
		ldc_one('login_headertext', function($login_headertext){
			return get_option('blogname');
		});
		ldc_one('login_headerurl', function($login_headerurl){
			return home_url();
		});
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
