<?php

if(!class_exists('LDC_Request')){
    class LDC_Request {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // protected
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected $args = [], $url = '';

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected function request($method = '', $body = []){
            $this->args['method'] = $method;
            if($body){
                if(!empty($this->args['body'])){
                    $this->args['body'] = wp_parse_args($body, $this->args['body']);
                } else {
                    $this->args['body'] = $body;
                }
            }
            $response = wp_remote_request($this->url, $this->args);
            return ldc_response($response);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function __construct($url = '', $args = []){
            $this->args = $args;
            $this->url = $url;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function delete($body = []){
            return $this->request('DELETE', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function download($parent = 0){
            $this->args = wp_parse_args($this->args, [
                'filename' => '',
                'timeout' => MINUTE_IN_SECONDS,
            ]);
            $wp_upload_dir = wp_get_upload_dir();
            if($this->args['filename']){
                $filename = basename($this->args['filename']);
                if(strpos($this->args['filename'], $wp_upload_dir['basedir']) !== 0){
                    return ldc_error('http_request_failed', 'Destination directory for file streaming is not valid.');
                }
            } else {
                $filename = preg_replace('/\?.*/', '', basename($this->url));
            }
            $filetype_and_ext = wp_check_filetype($filename);
            $type = $filetype_and_ext['type'];
            if(!$type){
                return ldc_error('http_request_failed', 'Filename is not valid.');
            }
            if(!$this->args['filename']){
                $filename = wp_unique_filename($wp_upload_dir['path'], $filename);
                $this->args['filename'] = $wp_upload_dir['path'] . '/' . $filename;
            }
            $this->args['stream'] = true;
            $this->args['timeout'] = ldc_sanitize_timeout($this->args['timeout']);
            $response = $this->get();
            if(!$response->success){
                @unlink($this->args['filename']);
                return $response->to_wp_error();
            }
            $filetype_and_ext = wp_check_filetype_and_ext($this->args['filename'], $filename);
            $type = $filetype_and_ext['type'];
            if(!$type){
                @unlink($this->args['filename']);
                return ldc_error('http_request_failed', 'Filetype is not valid.');
            }
            $post_id = wp_insert_attachment([
                'guid' => str_replace($wp_upload_dir['basedir'], $wp_upload_dir['baseurl'], $this->args['filename']),
                'post_mime_type' => $type,
                'post_status' => 'inherit',
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            ], $this->args['filename'], $parent, true);
            if(is_wp_error($post_id)){
                @unlink($this->args['filename']);
                return $post_id;
            }
            return $post_id;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function get($body = []){
            return $this->request('GET', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function head($body = []){
            return $this->request('HEAD', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function options($body = []){
            return $this->request('OPTIONS', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function patch($body = []){
            return $this->request('PATCH', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function post($body = []){
            return $this->request('POST', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function put($body = []){
            return $this->request('PUT', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function trace($body = []){
            return $this->request('TRACE', $body);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}
