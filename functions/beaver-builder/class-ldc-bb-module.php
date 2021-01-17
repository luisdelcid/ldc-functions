<?php

if(!class_exists('LDC_BB_Module')){
    class LDC_BB_Module {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // dynamic protected
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected $id = '', $prefix = '', $tabs = [];

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // dynamic public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function __construct($class_name = ''){
    		if(class_exists($class_name)){
    			$this->id = str_replace('-', '_', sanitize_title($class_name));
    			$this->prefix = $this->id . '_';
    		} else {
    			wp_die('Class "' . esc_html($id) . '" does not exist.');
    		}
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function parse(){
    		if($this->tabs){
    			foreach($this->tabs as $id => $tab){
    				$this->tabs[$id] = $tab->parse();
    			}
    		}
    		return $this->tabs;
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	public function prefix(){
    		return $this->prefix;
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	public function register(){
    		FLBuilder::register_module($this->id, $this->parse());
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	public function tab($id = '', $title = ''){
    		if($title){
    			$this->tabs[$id] = new LDC_BB_Module_Tab($id, $title, $this->prefix);
    			return $this->tabs[$id];
    		} else {
    			if(isset($this->tabs[$id])){
    				return $this->tabs[$id];
    			} else {
    				wp_die('Tab "' . esc_html($id) . '" does not exist.');
    			}
    		}
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}
