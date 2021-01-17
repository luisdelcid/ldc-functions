<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Add the ${your_site_url}/ldc-google-oauth redirect URI to the ones authorized for the OAuth client.
// To update the authorized redirect URIs, visit: https://console.developers.google.com/apis/credentials/oauthclient/${your_client_id}?project=${your_project_number}
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_google_oauth_url')){
    function ldc_google_oauth_url($client = null, $redirect_to = ''){
        if(!$redirect_to and isset($_GET['redirect_to'])){
            $redirect_to = $_GET['redirect_to'];
        }
        $redirect_to = wp_http_validate_url($redirect_to);
        if($client instanceof \Google_Client){
            $client->addScope('email');
            $client->addScope('profile');
            $client->setRedirectUri(site_url('ldc-google-oauth'));
            if($redirect_to){
                $client->setState(urlencode($redirect_to));
            }
            return $client->createAuthUrl();
        } else {
            if(!$redirect_to){
                $redirect_to = '';
            }
            return wp_login_url($redirect_to);
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_hide_recaptcha_badge')){
    function ldc_hide_recaptcha_badge(){
        ldc_one('init', function(){
            add_shortcode('ldc_hide_recaptcha_badge', function($atts = [], $content = ''){
                return '<span class="ldc-hide-recaptcha-badge">This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy" target="_blank">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank">Terms of Service</a> apply.</span>';
            });
        });
        ldc_one('wp_head', function(){ ?>
            <style type="text/css">
                .grecaptcha-badge {
                    visibility: hidden !important;
                }
            </style><?php
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_google_api')){
    function ldc_use_google_api($ver = '2.8.2'){
        if(class_exists('\Google_Client')){
            return true;
        }
        $url = '';
        switch($ver){
            case '2.8.2':
                $basename = 'google-api-2.8.2-PHP';
                $php_versions = ['7.4', '7.0', '5.6', '5.4'];
                foreach($php_versions as $php_version){
                    if(is_php_version_compatible($php_version)){
                        $basename .= $php_version;
                        $url = 'https://github.com/googleapis/google-api-php-client/releases/download/v2.8.2/google-api-php-client-v2.8.2-PHP' . $php_version . '.zip';
                        break;
                    }
                }
                break;
        }
        if(!$url){
            return false;
        }
        $dir = ldc_upload_basedir() . '/' . $basename;
        $result = ldc_download_and_unzip($dir, $url);
        if(!$result){
            return false;
        }
        require_once($dir . '/vendor/autoload.php');
        return true;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_use_google_oauth')){
    function ldc_use_google_oauth($client = null, $args = []){
        ldc_one('template_redirect', function() use($client, $args){
            global $wp;
            if($wp->request != 'ldc-google-oauth'){
                return;
            }
            $args = shortcode_atts([
                'home_url' => apply_filters('ldc_google_oauth_home_url', home_url()),
                'remember_credentials' => apply_filters('ldc_google_oauth_remember_credentials', false),
                'users_can_register' => apply_filters('ldc_google_oauth_users_can_register', get_option('users_can_register')),
            ], $args, 'ldc_google_oauth_args');
            $url = $args['home_url'];
            if($client instanceof \Google_Client){
                $referer = wp_get_raw_referer();
                if($referer){
                    if(wp_parse_url($referer, PHP_URL_HOST) == 'accounts.google.com'){
                        if(!is_user_logged_in()){
                            if(isset($_GET['code'])){
                                $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
                                if(array_key_exists('error', $token)){
                                    $error = new WP_Error($token['error'],$token['error_description']);
                                    wp_die($error);
                                }
                                $client->setAccessToken($token);
                                $oauth = new \Google_Service_Oauth2($client);
                                $userinfo = $oauth->userinfo->get();
                                $state = isset($_GET['state']) ? wp_http_validate_url(urldecode($_GET['state'])) : false;
                                if($state){
                                    $url = $state;
                                }
                                do_action('ldc_pre_google_oauth', $userinfo);
                                $email = $userinfo->email;
                                if(email_exists($email)){
                                    $user = get_user_by('email', $email);
                                    ldc_signon_without_password($user->user_login, $args['remember_credentials']);
                                    do_action('ldc_google_oauth_login', $user->ID, $userinfo);
                                } else {
                                    if($args['users_can_register']){
                                        $username = $email;
                                        $password = wp_generate_password();
                                        $user_id = wp_create_user($username, $password, $email);
                                        if(is_wp_error($user_id)){
                                            wp_die($user_id);
                                        } else {
                                            update_user_meta($user_id, 'first_name', $userinfo->givenName);
                                            update_user_meta($user_id, 'last_name', $userinfo->familyName);
                                            do_action('ldc_google_oauth_signup', $user_id, $userinfo);
                                            $credentials = [
                                                'user_login' => $email,
                                                'user_password' => $password,
                                                'remember' => $args['remember_credentials'],
                                            ];
                                            wp_signon($credentials);
                                            do_action('ldc_google_oauth_login', $user_id, $userinfo);
                                        }
                                    } else {
                                        $error = new WP_Error('invalid_username', __('Unknown username. Check again or try your email address.'));
                                        wp_die($error);
                                    }
                                }
                            } else {
                                $url = ldc_google_oauth_url($client);
                            }
                        }
                    }
                }
            }
            wp_safe_redirect($url);
            exit;
        });
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
