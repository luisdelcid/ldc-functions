<?php

if(!class_exists('LDC_MB_B4_FL')){
    class LDC_MB_B4_FL {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // protected
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected static function maybe_parse($html = '', $field = []){
            if(isset($field['type'])){
                switch($field['type']){
                    case 'post':
                    case 'taxonomy':
                    case 'taxonomy_advanced':
                    case 'user':
                        if(isset($field['field_type'])){
                            switch($field['field_type']){
                                case 'select':
                                    $html = self::select($html, $field);
                                    break;
                            }
                        }
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
            $wrapper->addClass('ldc-floating-labels');
            $select = $wrapper->find('select', 0);
            $select->addClass('custom-select mw-100');
            $option = $select->find('option', 0);
            $option->innertext = '';
            $select->outertext = $select->outertext . '<label>' . $field['placeholder'] . '</label>';
            $html = self::maybe_parse_description($html);
        	return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected static function text($html = '', $field = []){
            $html = str_get_html($html);
            $wrapper = $html->find('.rwmb-input', 0);
            $wrapper->addClass('ldc-floating-labels');
            $input = $wrapper->find('input', 0);
            $input->addClass('form-control mw-100');
            $input->outertext = $input->outertext . '<label>' . $field['placeholder'] . '</label>';
            $html = self::maybe_parse_description($html);
        	return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        protected static function textarea($html = '', $field = []){
            $html = str_get_html($html);
            $wrapper = $html->find('.rwmb-input', 0);
            $wrapper->addClass('ldc-floating-labels');
            $textarea = $wrapper->find('textarea', 0);
            $textarea->addClass('form-control mw-100');
            $textarea->cols = null;
            $textarea->rows = null;
            $textarea->outertext = $textarea->outertext . '<label>' . $field['placeholder'] . '</label>';
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
                if(isset($field['id'], $field['ldc_b4_fl'], $field['placeholder']) and $field['id'] and $field['ldc_b4_fl'] and $field['placeholder']){
                    $html = self::maybe_parse($html, $field);
                }
            }
        	return $html;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}
