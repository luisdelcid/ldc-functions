<?php

if(!class_exists('LDC_MB_B4')){
    class LDC_MB_B4 {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // protected
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected static function checkbox($html = '', $field = []){
            $html = str_get_html($html);
            $wrapper = $html->find('.rwmb-input', 0);
            $wrapper->addClass('custom-control custom-checkbox');
            $label = $wrapper->find('label', 0);
            $label->addClass('custom-control-label');
            $input = $label->find('input', 0);
            $input->addClass('custom-control-input');
            $label->for = $input->id;
            $label->innertext = $label->plaintext;
            $label->outertext = $input . $label->outertext;
        	return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected static function file($html = '', $field = []){
            $html = str_get_html($html);
            $wrapper = $html->find('.rwmb-file-new', 0);
            $wrapper->addClass('custom-file');
            $input = $wrapper->find('input', 0);
            $input->addClass('custom-file-input');
            if(isset($field['ldc_b4_browse']) and $field['ldc_b4_browse']){
                $browse = $field['ldc_b4_browse'];
            } else {
                $browse = __('Select');
            }
            if(isset($field['ldc_b4_label']) and $field['ldc_b4_label']){
                $label = $field['ldc_b4_label'];
            } else {
                $label = __('Select Files');
            }
            $input->outertext = $input->outertext . '<label class="custom-file-label" for="' . $input->id . '" data-browse="' . $browse . '">' . $label . '</label>';
            $html = self::maybe_parse_description($html);
        	return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected static function list($type = '', $html = '', $field = []){
            $html = str_get_html($html);
            if($type == 'checkbox'){
                $button = $html->find('button', 0);
            	if($button){
            		$button->addClass('btn btn-outline-secondary btn-sm');
            	}
            }
            $ul = $html->find('.rwmb-input-list', 0);
            $ul->addClass('pl-0');
            foreach($ul->find('li') as $li){
                $li->addClass('custom-control custom-' . $type);
                if(isset($field['inline']) and $field['inline']){
                    $li->addClass('custom-control-inline');
                }
                $label = $li->find('label', 0);
                $label->addClass('custom-control-label');
                $input = $label->find('input', 0);
                $input->addClass('custom-control-input');
                $input->id = $field['id'] . '_' . sanitize_title($input->value);
                $label->for = $input->id;
                $label->innertext = $label->plaintext;
                $label->outertext = $input . $label->outertext;
            }
            $html = self::maybe_parse_description($html);
        	return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected static function maybe_parse($html = '', $field = []){
            if(isset($field['type'])){
                switch($field['type']){
                    case 'checkbox':
                        $html = self::checkbox($html, $field);
                        break;
                    case 'checkbox_list':
                        $html = self::list('checkbox', $html, $field);
                        break;
                    case 'post':
                    case 'taxonomy':
                    case 'taxonomy_advanced':
                    case 'user':
                        if(isset($field['field_type'])){
                            switch($field['field_type']){
                                case 'checkbox':
                                case 'radio':
                                    $html = self::list($field['field_type'], $html, $field);
                                    break;
                                case 'select':
                                    $html = self::select($html, $field);
                                    break;
                            }
                        }
                        break;
                    case 'radio':
                        $html = self::list('radio', $html, $field);
                        break;
                    case 'select':
                        $html = self::select($html, $field);
                        break;
                    case 'text':
                    case 'email':
                    case 'number':
                    case 'password':
                    case 'url':
                        $html = self::text($html, $field);
                        break;
                    case 'textarea':
                        $html = self::textarea($html, $field);
                        break;
                }
            }
        	return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected static function maybe_parse_description($html = ''){
            $description = $html->find('.description', 0);
            if($description){
                $description->addClass('small form-text text-muted mb-0');
            }
        	return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected static function select($html = '', $field = []){
            $html = str_get_html($html);
            $wrapper = $html->find('.rwmb-input', 0);
            $select = $wrapper->find('select', 0);
            $select->addClass('custom-select mw-100');
            if(isset($field['ldc_b4_lg']) and $field['ldc_b4_lg']){
                $select->addClass('custom-select-lg');
            }
            if(isset($field['ldc_b4_sm']) and $field['ldc_b4_sm']){
                $select->addClass('custom-select-sm');
            }
            $html = self::maybe_parse_description($html);
        	return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected static function text($html = '', $field = []){
            $html = str_get_html($html);
            $wrapper = $html->find('.rwmb-input', 0);
            $input = $wrapper->find('input', 0);
            $input->addClass('form-control mw-100');
            if(isset($field['ldc_b4_lg']) and $field['ldc_b4_lg']){
                $input->addClass('form-control-lg');
            }
            if(isset($field['ldc_b4_sm']) and $field['ldc_b4_sm']){
                $input->addClass('form-control-sm');
            }
            $html = self::maybe_parse_description($html);
        	return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected static function textarea($html = '', $field = []){
            $html = str_get_html($html);
            $wrapper = $html->find('.rwmb-input', 0);
            $textarea = $wrapper->find('textarea', 0);
            $textarea->addClass('form-control mw-100');
            if(isset($field['ldc_b4_lg']) and $field['ldc_b4_lg']){
                $input->addClass('form-control-lg');
            }
            if(isset($field['ldc_b4_sm']) and $field['ldc_b4_sm']){
                $input->addClass('form-control-sm');
            }
            $html = self::maybe_parse_description($html);
        	return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        public static function outer_html($html = '', $field = []){
            if(!is_admin()){
                if(isset($field['id'], $field['ldc_b4']) and $field['id'] and $field['ldc_b4'] and !isset($field['ldc_b4_fl'])){
                    $html = self::maybe_parse($html, $field);
                }
            }
        	return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}
