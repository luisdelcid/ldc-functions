<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_checklist_table')){
    function ldc_checklist_table($data = [], $auto = true){
        $html = '';
        if($data){
            $html .= '<table class="wp-list-table widefat fixed striped">';
        	$html .= '<thead>';
        	$html .= '<tr>';
        	$html .= '<th scope="col">Item</th>';
        	$html .= '<th scope="col">' . ($auto ? 'Status' : 'Recommended value') . '</th>';
        	$html .= '</tr>';
        	$html .= '</thead>';
        	$html .= '<tbody>';
        	foreach($data as $key => $value){
        		$html .= '<tr>';
        		$html .= '<td>' . $key . '</td>';
        		$html .= '<td>' . $value . '</td>';
        		$html .= '</tr>';
        	}
        	$html .= '</tbody>';
        	$html .= '</table>';
        }
        return $html;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_checklist_error')){
    function ldc_checklist_error(){
        return '<i class="dashicons dashicons-no" style="color: #dc3232;"></i>';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_checklist_info')){
    function ldc_checklist_info(){
        return '<i class="dashicons dashicons-info" style="color: #00a0d2;"></i>';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_checklist_success')){
    function ldc_checklist_success(){
        return '<i class="dashicons dashicons-yes" style="color: #46b450;"></i>';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_checklist_warning')){
    function ldc_checklist_warning(){
        return '<i class="dashicons dashicons-warning" style="color: #ffb900;"></i>';
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
