<?php

if(!class_exists('LDC_BB_Module_Section')){
    class LDC_BB_Module_Section {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // dynamic protected
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	protected $fields = [], $id = '', $prefix = '', $title = '';

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

    	public function field($id = '', $field = []){
    		if($field){
    			$this->fields[$id] = new LDC_BB_Module_Field($id, $field, $this->prefix);
    			return $this->fields[$id];
    		} else {
    			if(isset($this->fields[$id])){
    				return $this->fields[$id];
    			} else {
    				wp_die('Field "' . esc_html($id) . '" does not exist.');
    			}
    		}
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	public function parse(){
    		if($this->fields){
    			foreach($this->fields as $id => $field){
    				$this->fields[$id] = $field->parse();
    			}
    		}
    		return [
    			'title' => $this->title,
    			'fields' => $this->fields,
    		];
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    	public function prefix(){
    		return $this->prefix;
    	}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}
