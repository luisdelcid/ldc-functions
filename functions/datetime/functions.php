<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_current_time')){
    function ldc_current_time($type = 'U', $offset_or_tz = ''){ // If $offset_or_tz is an empty string, the output is adjusted with the GMT offset in the WordPress option.
        if('timestamp' === $type){
            $type = 'U';
        }
        if('mysql' === $type){
            $type = 'Y-m-d H:i:s';
        }
        $timezone = $offset_or_tz ? ldc_timezone($offset_or_tz) : wp_timezone();
        $datetime = new DateTime('now', $timezone);
        return $datetime->format($type);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_date_convert')){
    function ldc_date_convert($string = '', $fromtz = '', $totz = '', $format = 'Y-m-d H:i:s'){
        $datetime = date_create($string, ldc_timezone($fromtz));
        if($datetime === false){
            return gmdate($format, 0);
        }
        return $datetime->setTimezone(ldc_timezone($totz))->format($format);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_offset_or_tz')){
    function ldc_offset_or_tz($offset_or_tz = ''){ // Default GMT offset or timezone string. Must be either a valid offset (-12 to 14) or a valid timezone string.
        if(is_numeric($offset_or_tz)){
            return [
                'gmt_offset' => $offset_or_tz,
                'timezone_string' => '',
            ];
        } else {
            if(preg_match('/^UTC[+-]/', $offset_or_tz)){ // Map UTC+- timezones to gmt_offsets and set timezone_string to empty.
                return [
                    'gmt_offset' => intval(preg_replace('/UTC\+?/', '', $offset_or_tz)),
                    'timezone_string' => '',
                ];
            } else {
                if(in_array($offset_or_tz, timezone_identifiers_list())){
                    return [
                        'gmt_offset' => 0,
                        'timezone_string' => $offset_or_tz,
                    ];
                } else {
                    return [
                        'gmt_offset' => 0,
                        'timezone_string' => 'UTC',
                    ];
                }
            }
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_seems_mysql_date')){
	function ldc_seems_mysql_date($pattern = ''){
        return preg_match('/^\d{4}-\d{2}-\d{2}\s{1}\d{2}:\d{2}:\d{2}$/', $pattern);
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_timezone')){
    function ldc_timezone($offset_or_tz = ''){
        return new DateTimeZone(ldc_timezone_string($offset_or_tz));
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ldc_timezone_string')){
    function ldc_timezone_string($offset_or_tz = ''){
        $offset_or_tz = ldc_offset_or_tz($offset_or_tz);
        $timezone_string = $offset_or_tz['timezone_string'];
        if($timezone_string){
            return $timezone_string;
        }
        $offset = floatval($offset_or_tz['gmt_offset']);
        $hours = intval($offset);
        $minutes = ($offset - $hours);
        $sign = ($offset < 0) ? '-' : '+';
        $abs_hour = abs($hours);
        $abs_mins = abs($minutes * 60);
        $tz_offset = sprintf('%s%02d:%02d', $sign, $abs_hour, $abs_mins);
        return $tz_offset;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
