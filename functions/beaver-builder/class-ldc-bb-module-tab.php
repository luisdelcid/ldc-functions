<?php

if(!class_exists('LDC_BB_Module_Tab')){
    class LDC_BB_Module_Tab {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // dynamic protected
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	protected $id = '', $prefix = '', $sections = [], $title = '';

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // dynamic public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function __construct($id = '', $title = '', $prefix = ''){
    		$this->id = str_replace('-', '_', sanitize_title($id));
    		$this->title = $title;
    		$this->prefix = $prefix . $this->id . '_';
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public function parse(){
    		if($this->sections){
    			foreach($this->sections as $id => $section){
    				$this->sections[$id] = $section->parse();
    			}
    		}
    		return [
    			'title' => $this->title,
    			'sections' => $this->sections,
    		];
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	public function prefix(){
    		return $this->prefix;
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	public function section($id = '', $title = null){
    		if(is_null($title)){
    			if(isset($this->sections[$id])){
    				return $this->sections[$id];
    			} else {
    				wp_die('Section "' . esc_html($id) . '" does not exist.');
    			}
    		} else {
    			$this->sections[$id] = new \LDC_BB_Module_Section($id, $title, $this->prefix);
    			return $this->sections[$id];
    		}
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}
