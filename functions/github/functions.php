<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_github_has_assets')){
    function ldc_github_has_assets($release = []){
        return !empty($release['assets']);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_github_release')){
    function ldc_github_release($owner = '', $repo = '', $version = 'latest'){
        if($owner and $repo and $version){
            if($version == 'latest_with_assets'){
                $releases = ldc_github_releases($owner, $repo, true);
                if($releases){
                    return $releases[0];
                }
            } else {
                $url = 'https://api.github.com/repos/' . $owner . '/' . $repo . '/releases/' . $version;
                $response = ldc_request($url)->get();
                if($response->success){
                    return $response->data;
                }
            }
        }
        return [];
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_github_releases')){
    function ldc_github_releases($owner = '', $repo = '', $with_assets = false){
        if($owner and $repo){
            $url = 'https://api.github.com/repos/' . $owner . '/' . $repo . '/releases';
            $response = ldc_request($url)->get();
            if($response->success){
                $releases = $response->data;
                if($with_assets){
                    $releases = array_values(array_filter($releases, 'ldc_github_has_assets'));
                }
                return $releases;
            }
        }
        return [];
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
