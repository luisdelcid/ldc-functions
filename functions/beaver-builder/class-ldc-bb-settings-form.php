<?php

if(!class_exists('LDC_BB_Settings_Form')){
    class LDC_BB_Settings_Form {

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

        public function __construct($id = '', $title = ''){
    		$this->id = str_replace('-', '_', sanitize_title($id));
    		$this->title = $title;
    		$this->prefix = $this->id . '_';
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function parse(){
    		if($this->tabs){
    			foreach($this->tabs as $id => $tab){
    				$this->tabs[$id] = $tab->parse();
    			}
    		}
    		return [
    			'title' => $this->title,
    			'tabs' => $this->tabs,
    		];
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	public function prefix(){
    		return $this->prefix;
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	public function register(){
    		FLBuilder::register_settings_form($this->id, $this->parse());
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	public function tab($id = '', $title = ''){
    		if($title){
    			$this->tabs[$id] = ldc_new('LDC_BB_Module_Tab', $id, $title, $this->prefix);
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
