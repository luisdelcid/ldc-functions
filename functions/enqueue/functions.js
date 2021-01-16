
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(typeof ldc_add_query_arg !== 'function'){
    function ldc_add_query_arg(key, value, url){
        'use strict';
        var a = document.createElement('a'),
            href = '';
        if(url === ''){
            a.href = jQuery(location).attr('href');
        } else {
            a.href = url;
        }
        if(a.protocol){
            href += a.protocol + '//';
        }
        if(a.hostname){
            href += a.hostname;
        }
        if(a.port){
            href += ':' + a.port;
        }
        if(a.pathname){
            if(a.pathname[0] !== '/'){
                href += '/';
            }
            href += a.pathname;
        }
        if(a.search){
            var search = [],
                search_object = ldc_parse_str(a.search);
            jQuery.each(search_object, function(k, v){
                if(k != key){
                    search.push(k + '=' + v);
                }
            });
            if(search.length > 0){
                href += '?' + search.join('&') + '&';
            } else {
                href += '?';
            }
        } else {
            href += '?';
        }
        href += key + '=' + value;
        if(a.hash){
            href += a.hash;
        }
        return href;
    };
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(typeof ldc_add_query_args !== 'function'){
    function ldc_add_query_args(args, url){
        'use strict';
        var a = document.createElement('a'),
            href = '';
        if(url === ''){
            a.href = jQuery(location).attr('href');
        } else {
            a.href = url;
        }
        if(a.protocol){
            href += a.protocol + '//';
        }
        if(a.hostname){
            href += a.hostname;
        }
        if(a.port){
            href += ':' + a.port;
        }
        if(a.pathname){
            if(a.pathname[0] !== '/'){
                href += '/';
            }
            href += a.pathname;
        }
        if(a.search){
            var search = [],
                search_object = ldc_parse_str(a.search);
            jQuery.each(search_object, function(k, v){
                if(!(k in args)){
                    search.push(k + '=' + v);
                }
            });
            if(search.length > 0){
                href += '?' + search.join('&') + '&';
            } else {
                href += '?';
            }
        } else {
            href += '?';
        }
        jQuery.each(args, function(k, v){
            href += k + '=' + v;
        });
        if(a.hash){
            href += a.hash;
        }
        return href;
    };
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(typeof ldc_page_visibility_event !== 'function'){
    function ldc_page_visibility_event(){
        'use strict';
        var visibilityChange = '';
        if(typeof document.hidden !== 'undefined'){ // Opera 12.10 and Firefox 18 and later support
            visibilityChange = 'visibilitychange';
        } else if(typeof document.webkitHidden !== 'undefined'){
            visibilityChange = 'webkitvisibilitychange';
        } else if(typeof document.msHidden !== 'undefined'){
            visibilityChange = 'msvisibilitychange';
        } else if(typeof document.mozHidden !== 'undefined'){ // Deprecated
            visibilityChange = 'mozvisibilitychange';
        }
        return visibilityChange;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(typeof ldc_page_visibility_state !== 'function'){
    function ldc_page_visibility_state(){
        'use strict';
        var hidden = '';
        if(typeof document.hidden !== 'undefined'){ // Opera 12.10 and Firefox 18 and later support
            hidden = 'hidden';
        } else if(typeof document.webkitHidden !== 'undefined'){
            hidden = 'webkitHidden';
        } else if(typeof document.msHidden !== 'undefined'){
            hidden = 'msHidden';
        } else if(typeof document.mozHidden !== 'undefined'){ // Deprecated
            hidden = 'mozHidden';
        }
        return document[hidden];
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(typeof ldc_parse_str !== 'function'){
    function ldc_parse_str(str){
        'use strict';
        var i = 0,
            search_object = {},
            search_array = str.replace('?', '').split('&');
        for(i = 0; i < search_array.length; i ++){
            search_object[search_array[i].split('=')[0]] = search_array[i].split('=')[1];
        }
        return search_object;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(typeof ldc_parse_url !== 'function'){
    function ldc_parse_url(url, component){
        'use strict';
        var a = document.createElement('a'),
            components = {},
            keys = ['protocol', 'hostname', 'port', 'pathname', 'search', 'hash'];
        if(url === ''){
            a.href = jQuery(location).attr('href');
        } else {
            a.href = url;
        }
        if(typeof component === 'undefined' || component === ''){
            jQuery.map(keys, function(c){
                components[c] = a[c];
            });
            return components;
        } else if(jQuery.inArray(component, keys) !== -1){
            return a[component];
        } else {
            return '';
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(typeof ldc_rem_to_px !== 'function'){
    function ldc_rem_to_px(count){
        'use strict';
        var unit = jQuery('html').css('font-size');
    	if(typeof count !== 'undefined' && count > 0){
    		return (parseInt(unit) * count);
    	} else {
    		return parseInt(unit);
    	}
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
