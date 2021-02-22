<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_cl_add_image_size')){
	function ldc_cl_add_image_size($name = '', $options = []){
        if(!isset($GLOBALS['ldc_cl_image_sizes'])){
            $GLOBALS['ldc_cl_image_sizes'] = [];
        }
        $size = sanitize_title($name);
        if(!array_key_exists($size, $GLOBALS['ldc_cl_image_sizes'])){
            $GLOBALS['ldc_cl_image_sizes'][$size] = [
                'name' => $name,
                'options' => $options,
                'options_md5' => ldc_md5($options),
            ];
        }
        ldc_one('fl_builder_photo_sizes_select', function($sizes){
            if(isset($sizes['full'])){
    			$id = ldc_attachment_url_to_postid($sizes['full']['url']);
    			if($id){
                    foreach($GLOBALS['ldc_cl_image_sizes'] as $size => $args){
                        $image = get_post_meta($id, '_ldc_cl_' . $args['options_md5'], true);
                        if($image and !isset($sizes[$size])){
                             $url = (isset($image['secure_url']) ? $image['secure_url'] : (isset($image['url']) ? $image['url'] : ''));
                             $width = (isset($image['width']) ? $image['width'] : 0);
                             $height = (isset($image['height']) ? $image['height'] : 0);
                             $sizes[$size] = [
                                'filename' => $image['public_id'],
                                'height' => $height,
                                'url' => $url,
                                'width' => $width,
                             ];
                        }
        			}
    			}
    		}
    		return $sizes;
        });
        ldc_one('image_downsize', function($out, $id, $size){
            if(wp_attachment_is_image($id) and is_string($size) and isset($GLOBALS['ldc_cl_image_sizes'][$size])){
                $image_size = $GLOBALS['ldc_cl_image_sizes'][$size];
                $image = ldc_cl_upload($id, $image_size['options']);
                if(!is_wp_error($image)){
                    $url = (isset($image['secure_url']) ? $image['secure_url'] : (isset($image['url']) ? $image['url'] : ''));
                    $width = (isset($image['width']) ? $image['width'] : 0);
                    $height = (isset($image['height']) ? $image['height'] : 0);
                    if($url and $width and $height){
                        return [$url, $width, $height, true];
                    }
                }
            }
    		return $out;
        }, 10, 3);
        ldc_one('image_size_names_choose', function($sizes){
            foreach($GLOBALS['ldc_cl_image_sizes'] as $size => $args){
                if(!isset($sizes[$size])){
                    $sizes[$size] = $args['name'];
                }
			}
            return $sizes;
        });
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_cl_config')){
    function ldc_cl_config($config = []){
		return ldc_use_cloudinary_php($config);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_cl_upload')){
    function ldc_cl_upload($attachment_id = 0, $options = []){
        if(!wp_attachment_is_image($attachment_id)){
            return ldc_error('invalid_image', __('File is not an image.'));
        }
        $use = ldc_use_cloudinary_php();
        if(is_wp_error($use)){
            return $use;
        }
        $md5 = ldc_md5($options);
        $image = get_post_meta($attachment_id, '_ldc_cl_' . $md5, true);
        if(!$image){
            $image = Cloudinary\Uploader::upload(get_attached_file($attachment_id), $options);
            if($image instanceof Cloudinary\Error){
                return ldc_error('cloudinary_error', $image->getMessage());
            }
            update_post_meta($attachment_id, '_ldc_cl_' . $md5, $image);
        }
        return $image;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_cloudinary_php')){
    function ldc_use_cloudinary_php($config = []){
        if(class_exists('Cloudinary')){
            return true;
        }
        $dir = ldc_upload_basedir() . '/github/cloudinary/cloudinary_php/1.20.0';
        $expected = $dir . '/cloudinary_php-1.20.0';
        if(is_dir($expected)){
            require_once($expected . '/autoload.php');
			if($config){
				Cloudinary::config($config);
			} else {
				if(defined('LDC_CL_API_KEY') and defined('LDC_CL_API_SECRET') and defined('LDC_CL_CLOUD_NAME')){
					Cloudinary::config([
						'api_key' => LDC_CL_API_KEY,
						'api_secret' => LDC_CL_API_SECRET,
						'cloud_name' => LDC_CL_CLOUD_NAME,
					]);
				}
			}
            return true;
        }
        $url = 'https://github.com/cloudinary/cloudinary_php/archive/1.20.0.zip';
        $result = ldc_download_and_unzip($dir, $url);
        if(is_wp_error($result)){
            return $result;
        }
        require_once($expected . '/autoload.php');
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
