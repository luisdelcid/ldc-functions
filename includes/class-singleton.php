<?php

if(!class_exists('LDC_Singleton')){
    class LDC_Singleton {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static private $instances = [];

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        /**
         * @return self
         */
        static public function get_instance(){
            $class_name = get_called_class();
            $md5 = md5($class_name);
            if(isset(self::$instances[$md5])){
                return self::$instances[$md5];
            }
            self::$instances[$md5] = new $class_name;
            return self::$instances[$md5];
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        /**
         * @return void
         */
        protected function __construct(){
            if(is_callable([$this, 'loader'])){
                call_user_func([$this, 'loader']);
            }
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        /**
         * @return string
         */
        public function get_name(){
            return get_called_class();
        }

        /**
         * @return string
         */
        public function prefix($str = ''){
            $name = $this->get_name();
            return ldc_str_prefix($str, $name);
        }

        /**
         * @return string
         */
        public function slug($str = ''){
            $name = $this->get_name();
            return ldc_str_slug($str, $name);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // Hooks
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        /**
         * @return string
         */
        public function add_action($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
            $hook_name = $this->prefix($hook_name);
            return ldc_on($hook_name, $callback, $priority, $accepted_args);
        }

        /**
         * @return string
         */
        public function add_action_once($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
            $hook_name = $this->prefix($hook_name);
            return ldc_one($hook_name, $callback, $priority, $accepted_args);
        }

        /**
         * @return string
         */
        public function add_filter($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
            $hook_name = $this->prefix($hook_name);
            return ldc_on($hook_name, $callback, $priority, $accepted_args);
        }

        /**
         * @return string
         */
        public function add_filter_once($hook_name = '', $callback = null, $priority = 10, $accepted_args = 1){
            $hook_name = $this->prefix($hook_name);
            return ldc_one($hook_name, $callback, $priority, $accepted_args);
        }

        /**
         * @return mixed
         */
        public function apply_filters($hook_name = '', $value = null, ...$arg){
            $hook_name = $this->prefix($hook_name);
            return apply_filters($hook_name, $value, ...$arg);
        }

        /**
         * @return bool
         */
        public function did_action($hook_name = ''){
            $hook_name = $this->prefix($hook_name);
            return did_action($hook_name);
        }

        /**
         * @return bool
         */
        public function did_filter($hook_name = ''){
            $hook_name = $this->prefix($hook_name);
            return did_filter($hook_name);
        }

        /**
         * @return void
         */
        public function do_action($hook_name = '', ...$arg){
            $hook_name = $this->prefix($hook_name);
            do_action($hook_name, ...$arg);
        }

        /**
         * @return void
         */
        public function do_action_ref_array($hook_name = '', $args = []){
            $hook_name = $this->prefix($hook_name);
            do_action_ref_array($hook_name, $args);
        }

        /**
         * @return bool
         */
        public function doing_action($hook_name = ''){
            $hook_name = $this->prefix($hook_name);
            return doing_filter($hook_name);
        }

        /**
         * @return bool
         */
        public function doing_filter($hook_name = ''){
            $hook_name = $this->prefix($hook_name);
            return doing_filter($hook_name);
        }

        /**
         * @return bool
         */
        public function has_action($hook_name = '', $callback = false){
            $hook_name = $this->prefix($hook_name);
            return has_filter($hook_name, $callback);
        }

        /**
         * @return bool
         */
        public function has_filter($hook_name = '', $callback = false){
            $hook_name = $this->prefix($hook_name);
            return has_filter($hook_name, $callback);
        }

        /**
         * @return bool
         */
        public function remove_action($hook_name = '', $callback = null, $priority = 10){
            $hook_name = $this->prefix($hook_name);
            return remove_filter($hook_name, $callback, $priority);
        }

        /**
         * @return bool
         */
        public function remove_filter($hook_name = '', $callback = null, $priority = 10){
            $hook_name = $this->prefix($hook_name);
            return remove_filter($hook_name, $callback, $priority);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // REST API
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        /**
         * @return bool
         */
        public function register_rest_route($route = '', $args = [], $override = false){
            $route = $this->rest_route($route);
            if(!$route){
                return false;
            }
            return register_rest_route($this->rest_namespace(), $route, $args, $override);
        }

        /**
         * @return string
         */
        public function rest_namespace($version = 0){
            $version = ldc_absint($version);
            if($version < 1){
                $version = 1;
            }
            $slug = $this->slug();
            $namespace = $slug . '/v' . $version;
            return $namespace;
        }

        /**
         * @return string
         */
        public function rest_route($route = ''){
            $route = sanitize_title($route);
            if(!$route){
                return '';
            }
            $slug = $this->slug();
            $search = $slug . '-'; // With trailing dash.
            if(ldc_str_starts_with($route, $search)){
                $route = str_replace($search, '', $route);
            }
            return $route;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // Cache
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        /**
         * @return mixed
         */
        public function get_array_cache($array_key = '', $key = '', $default = null){
            $array = (array) $this->get_cache($array_key, []);
            return isset($array[$key]) ? $array[$key] : $default;
        }

        /**
         * @return mixed
         */
        public function get_cache($key = '', $default = null){
            $group = $this->prefix();
            $value = wp_cache_get($key, $group, false, $found);
            if($found){
                return $value;
            }
            return $default;
        }

        /**
         * @return bool
         */
        public function isset_array_cache($array_key = '', $key = ''){
            $array = (array) $this->get_cache($array_key, []);
            return isset($array[$key]);
        }

        /**
         * @return bool
         */
        public function isset_cache($key = ''){
            $group = $this->prefix();
            $value = wp_cache_get($key, $group, false, $found);
            return $found;
        }

        /**
         * @return bool
         */
        public function set_array_cache($array_key = '', $key = '', $data = null){
            $array = (array) $this->get_cache($array_key, []);
            $array[$key] = $data;
            return $this->set_cache($array_key, $array);
        }

        /**
         * @return bool
         */
        public function set_cache($key = '', $data = null){
            $group = $this->prefix();
            return wp_cache_set($key, $data, $group);
        }

        /**
         * @return bool
         */
        public function unset_array_cache($array_key = '', $key = ''){
            $array = (array) $this->get_cache($array_key, []);
            if(isset($array[$key])){
                unset($array[$key]);
            }
            return $this->set_cache($array_key, $array);
        }

        /**
         * @return bool
         */
        public function unset_cache($key = ''){
            $group = $this->prefix();
            return wp_cache_delete($key, $group);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}
