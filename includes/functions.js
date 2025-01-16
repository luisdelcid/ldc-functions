// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Hardcoded
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_document_has_visibility_event(){
    var event_name = ldc_document_visibility_change_event(),
        events = jQuery._data(document, 'events'),
        has_visibility_event = false;
    if('undefined' !== typeof(events[event_name])){
        jQuery.each(events[event_name], function(index, value){
            if('ldc_do_visibilitychange_action' !== value.handler.name){ // Hardcoded.
                return true;
            }
            has_visibility_event = true;
            return false;
        });
    }
    return has_visibility_event;
}

/**
 * @return LDC_Singleton|null // Hardcoded.
 */
function ldc_get_instance(class_name = ''){
    if(!class_name){
        return null;
    }
    if(!_.isFunction(window[class_name])){
        return null;
    }
    if(!ldc_is_subclass_of(window[class_name], 'ldc_singleton')){ // Hardcoded.
        return null;
    }
    return window[class_name].get_instance();
}

/**
 * @return string
 */
function ldc_namespace(){
    return 'luisdelcid/ldc-functions/ldc'; // The unique namespace identifying the callback in the form `vendor/plugin/function`. Hardcoded.
}

/**
 * @return string
 */
function ldc_prefix(){
    return 'ldc_functions'; // Hardcoded.
}

/**
 * @return string
 */
function ldc_slug(){
    return 'ldc-functions'; // Hardcoded.
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Debug
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_caller_url(index = 0){
    var debug_backtrace = null;
    index = ldc_absint(index) + 1;
    debug_backtrace = ldc_debug_backtrace(index);
    if(_.isNull(debug_backtrace)){
        return '';
    }
    if(_.isUndefined(debug_backtrace.fileName)){
        return '';
    }
    return debug_backtrace.fileName;
}

/**
 * @return object
 */
function ldc_debug_backtrace(index = 0){
    var backtrace = [],
        error = null,
        fake_function = null,
        limit = 0;
    try {
        fake_function();
    } catch(e){
        error = e;
    }
    backtrace = ldc_debug_context(error);
    if(_.isEmpty(backtrace)){
        return null;
    }
    index = ldc_absint(index) + 1;
    limit = index + 1;
    if(limit > backtrace.length){
        return null;
    }
    return backtrace[index];
}

/**
 * @return array
 */
function ldc_debug_context(error = null){
    var backtrace = [];
    if(!_.isError(error)){
        return backtrace;
    }
    jQuery.each(ErrorStackParser.parse(error), function(index, value){
        var stackframe = {
            args: [],
            columnNumber: 0,
            fileName: '',
            functionName: '',
            isEval: false,
            isNative: false,
            lineNumber: 0,
            source: '',
        };
        jQuery.each(stackframe, function(key, property){
            if(!_.isUndefined(value[key])){
                stackframe[key] = value[key];
            }
        });
        backtrace.push(stackframe);
    });
    return backtrace;
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Hooks
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return void
 */
function ldc_add_action(hook_name = '', callback = null, priority = 10){
    wp.hooks.addAction(hook_name, ldc_namespace(), callback, priority);
}

/**
 * @return void
 */
function ldc_add_filter(hook_name = '', callback = null, priority = 10){
    wp.hooks.addFilter(hook_name, ldc_namespace(), callback, priority);
}

/**
 * @return mixed
 */
function ldc_apply_filters(hook_name = '', value = null, ...arg){
    return wp.hooks.applyFilters(hook_name, value, ...arg);
}

/**
 * @return void
 */
function ldc_current_action(){
    wp.hooks.currentAction();
}

/**
 * @return void
 */
function ldc_current_filter(){
    wp.hooks.currentFilter();
}

/**
 * @return int|void
 */
function ldc_did_action(hook_name = ''){
    return wp.hooks.didAction(hook_name);
}

/**
 * @return int|void
 */
function ldc_did_filter(hook_name = ''){
    return wp.hooks.didFilter(hook_name);
}

/**
 * @return void
 */
function ldc_do_action(hook_name = '', ...arg){
    wp.hooks.doAction(hook_name, ...arg);
}

/**
 * @return void
 */
function ldc_doing_action(hook_name = ''){
    wp.hooks.doingAction(hook_name);
}

/**
 * @return void
 */
function ldc_doing_filter(hook_name = ''){
    wp.hooks.doingFilter(hook_name);
}

/**
 * @return bool
 */
function ldc_has_action(hook_name = ''){
    return wp.hooks.hasAction(hook_name, ldc_namespace());
}

/**
 * @return bool
 */
function ldc_has_filter(hook_name = ''){
    return wp.hooks.hasFilter(hook_name, ldc_namespace());
}

/**
 * @return int|void
 */
function ldc_remove_action(hook_name = ''){
    return wp.hooks.removeAction(hook_name, ldc_namespace());
}

/**
 * @return int|void
 */
function ldc_remove_all_actions(hook_name = ''){
    return wp.hooks.removeAllActions(hook_name, ldc_namespace());
}

/**
 * @return int|void
 */
function ldc_remove_all_filters(hook_name = ''){
    return wp.hooks.removeAllFilters(hook_name, ldc_namespace());
}

/**
 * @return int|void
 */
function ldc_remove_filter(hook_name = ''){
    return wp.hooks.removeFilter(hook_name, ldc_namespace());
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Magic methods
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @see JavaScript Magic Methods
 * @link https://gist.github.com/loilo/4d385d64e2b8552dcc12a0f5126b6df8
 *
 * @return object
 */
function ldc_add_magic_methods(clazz){
    /**
     * A toggle switch for the __isset method
     * Needed to control "prop in instance" inside of getters
     */
    let issetEnabled = true;
    const classHandler = Object.create(null);
    /**
     * Trap for class instantiation
     */
    classHandler.construct = (target, args, receiver) => {
        /**
         * Wrapped class instance
         */
        const instance = Reflect.construct(target, args, receiver);
        /**
         * Instance traps
         */
        const instanceHandler = Object.create(null);
        /**
         * __get()
         * Catches "instance.property"
         */
        const get = Object.getOwnPropertyDescriptor(clazz.prototype, '__get');
        if(get){
            instanceHandler.get = (target, name, receiver) => {
                // We need to turn off the __isset() trap for the moment to establish compatibility with PHP behaviour
                // PHP's __get() method doesn't care about its own __isset() method, so neither should we
                issetEnabled = false;
                const exists = Reflect.has(target, name);
                issetEnabled = true;
                if(exists){
                    return Reflect.get(target, name, receiver);
                } else {
                    return get.value.call(target, name);
                }
            }
        }
        /**
         * __set()
         * Catches "instance.property = ..."
         */
        const set = Object.getOwnPropertyDescriptor(clazz.prototype, '__set');
        if(set){
            instanceHandler.set = (target, name, value, receiver) => {
                if(name in target){
                    Reflect.set(target, name, value, receiver);
                } else {
                    return target.__set.call(target, name, value);
                }
            }
        }
        /**
         * __isset()
         * Catches "'property' in instance"
         */
        const isset = Object.getOwnPropertyDescriptor(clazz.prototype, '__isset');
        if(isset){
            instanceHandler.has = (target, name) => {
                if(!issetEnabled){
                    return Reflect.has(target, name);
                }
                return isset.value.call(target, name);
            }
        }
        /**
         * __unset()
         * Catches "delete instance.property"
         */
        const unset = Object.getOwnPropertyDescriptor(clazz.prototype, '__unset');
        if(unset){
            instanceHandler.deleteProperty = (target, name) => {
                return unset.value.call(target, name);
            }
        }
        return new Proxy(instance, instanceHandler);
    }
    /**
     * __getStatic()
     * Catches "class.property"
     */
    if(Object.getOwnPropertyDescriptor(clazz, '__getStatic')){
        classHandler.get = (target, name, receiver) => {
            if(name in target){
                return target[name];
            } else {
                return target.__getStatic.call(receiver, name);
            }
        }
    }
    /**
     * __setStatic()
     * Catches "class.property = ..."
     */
    if(Object.getOwnPropertyDescriptor(clazz, '__setStatic')){
        classHandler.set = (target, name, value, receiver) => {
            if(name in target){
                return target[name];
            } else {
                return target.__setStatic.call(receiver, name, value);
            }
        }
    }
    return new Proxy(clazz, classHandler);
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Miscellaneous
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return int
 */
function ldc_absint(maybeint = 0){
    if(!_.isNumber(maybeint)){
        return 0; // Make sure the value is numeric to avoid casting objects, for example, to int 1.
    }
    return Math.abs(parseInt(maybeint));
}

/**
 * @return bool
 */
function ldc_is_false(data){
    return (-1 < jQuery.inArray(String(data), ['0', 'false', 'off']));
}

/**
 * @return bool
 */
function ldc_is_subclass_of(func = null, class_name = ''){
    if(!_.isFunction(func)){
        return false;
    }
    if(!class_name){
        return false;
    }
    while(func && func !== Function.prototype){
        if(func === window[class_name]){
            return true;
        }
        func = Object.getPrototypeOf(func);
    }
    return false;
}

/**
 * @return bool
 */
function ldc_is_true(data){
    return (-1 < jQuery.inArray(String(data), ['1', 'on', 'true']));
}

/**
 * @return mixed
 */
function ldc_object_property(key = '', object_name = ''){
    if(!key){
        return null;
    }
    if(!object_name){
        object_name = ldc_str_prefix('l10n');
    }
    if(_.isUndefined(window[object_name])){
        return null;
    }
    if(_.isUndefined(window[object_name][key])){
        return null;
    }
    return window[object_name][key];
}

/**
 * @return int
 */
function ldc_rem_to_px(count){
    var unit = parseInt(jQuery('html').css('font-size'));
    if(!unit){
        unit = 16;
    }
    if(!_.isNumber(count)){
        return unit;
    }
    if(count > 0){
        return (count * unit);
    }
    return unit;
}

/**
 * @return void
 */
function ldc_test(){
    console.log('Hello, World!');
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Page Visibility API
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_document_visibility_change_event(){
    var visibilityChange = '';
    if('undefined' !== typeof(document.hidden)){ // Opera 12.10 and Firefox 18 and later support
        visibilityChange = 'visibilitychange';
    } else if('undefined' !== typeof(document.webkitHidden)){
        visibilityChange = 'webkitvisibilitychange';
    } else if('undefined' !== typeof(document.msHidden)){
        visibilityChange = 'msvisibilitychange';
    } else if('undefined' !== typeof(document.mozHidden)){ // Deprecated
        visibilityChange = 'mozvisibilitychange';
    }
    return visibilityChange;
}

/**
 * @return bool
 */
function ldc_is_document_hidden(){
    var hidden = false;
    if('undefined' !== typeof(document.hidden)){ // Opera 12.10 and Firefox 18 and later support
        hidden = document.hidden;
    } else if('undefined' !== typeof(document.webkitHidden)){
        hidden = document.webkitHidden;
    } else if('undefined' !== typeof(document.msHidden)){
        hidden = document.msHidden;
    } else if('undefined' !== typeof(document.mozHidden)){ // Deprecated
        hidden = document.mozHidden;
    }
    return hidden;
}

/**
 * @return void
 */
function ldc_track_document_visibility(){
    var $this = this;
    jQuery(function($){
        var event_name = ldc_document_visibility_change_event();
        if(ldc_document_has_visibility_event()){
            return;
        }
        jQuery(document).on(event_name, $this, ldc_do_visibilitychange_action);
    });
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_do_visibilitychange_action(event){
    var $this = event.data;
    ldc_do_action('visibilitychange', ldc_is_document_hidden()); // Hidden.
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Plugin hooks
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return void
 */
function ldc_add_plugin_action(hook_name = '', callback = null, priority = 10){
    hook_name = ldc_plugin_hook_name(hook_name);
    wp.hooks.addAction(hook_name, ldc_namespace(), callback, priority);
}

/**
 * @return void
 */
function ldc_add_plugin_filter(hook_name = '', callback = null, priority = 10){
    hook_name = ldc_plugin_hook_name(hook_name);
    wp.hooks.addFilter(hook_name, ldc_namespace(), callback, priority);
}

/**
 * @return mixed
 */
function ldc_apply_plugin_filters(hook_name = '', value = null, ...arg){
    hook_name = ldc_plugin_hook_name(hook_name);
    return wp.hooks.applyFilters(hook_name, value, ...arg);
}

/**
 * @return void
 */
function ldc_current_plugin_action(){
    wp.hooks.currentAction();
}

/**
 * @return void
 */
function ldc_current_plugin_filter(){
    wp.hooks.currentFilter();
}

/**
 * @return int|void
 */
function ldc_did_plugin_action(hook_name = ''){
    hook_name = ldc_plugin_hook_name(hook_name);
    return wp.hooks.didAction(hook_name);
}

/**
 * @return int|void
 */
function ldc_did_plugin_filter(hook_name = ''){
    hook_name = ldc_plugin_hook_name(hook_name);
    return wp.hooks.didFilter(hook_name);
}

/**
 * @return void
 */
function ldc_do_plugin_action(hook_name = '', ...arg){
    hook_name = ldc_plugin_hook_name(hook_name);
    wp.hooks.doAction(hook_name, ...arg);
}

/**
 * @return void
 */
function ldc_doing_plugin_action(hook_name = ''){
    hook_name = ldc_plugin_hook_name(hook_name);
    wp.hooks.doingAction(hook_name);
}

/**
 * @return void
 */
function ldc_doing_plugin_filter(hook_name = ''){
    hook_name = ldc_plugin_hook_name(hook_name);
    wp.hooks.doingFilter(hook_name);
}

/**
 * @return bool
 */
function ldc_has_plugin_action(hook_name = ''){
    hook_name = ldc_plugin_hook_name(hook_name);
    return wp.hooks.hasAction(hook_name, ldc_namespace());
}

/**
 * @return bool
 */
function ldc_has_plugin_filter(hook_name = ''){
    hook_name = ldc_plugin_hook_name(hook_name);
    return wp.hooks.hasFilter(hook_name, ldc_namespace());
}

/**
 * @return int|void
 */
function ldc_remove_plugin_action(hook_name = ''){
    hook_name = ldc_plugin_hook_name(hook_name);
    return wp.hooks.removeAction(hook_name, ldc_namespace());
}

/**
 * @return int|void
 */
function ldc_remove_all_plugin_actions(hook_name = ''){
    hook_name = ldc_plugin_hook_name(hook_name);
    return wp.hooks.removeAllActions(hook_name, ldc_namespace());
}

/**
 * @return int|void
 */
function ldc_remove_all_plugin_filters(hook_name = ''){
    hook_name = ldc_plugin_hook_name(hook_name);
    return wp.hooks.removeAllFilters(hook_name, ldc_namespace());
}

/**
 * @return int|void
 */
function ldc_remove_plugin_filter(hook_name = ''){
    hook_name = ldc_plugin_hook_name(hook_name);
    return wp.hooks.removeFilter(hook_name, ldc_namespace());
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_plugin_hook_name(hook_name = ''){
    var url = ldc_caller_url(2); // Two levels above.
    hook_name = ldc_plugin_prefix(hook_name, url);
    return hook_name;
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Plugins
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_plugin_folder(url = ''){
    var folder = '',
        mu_plugins_url = ldc_mu_plugins_url(),
        path = '',
        plugins_url = ldc_plugins_url();
    if(!url){
        url = ldc_caller_url(1); // One level above.
    }
    if(!url){
        return '';
    }
    if(0 === url.indexOf(mu_plugins_url)){
         path = url.substr(mu_plugins_url.length, url.length - 1); // File is a must-use plugin.
     } else if(0 === url.indexOf(plugins_url)){
         path = url.substr(plugins_url.length, url.length - 1); // File is a plugin.
     } else {
        return ''; // File is not a plugin.
    }
    folder = path.split('/', 3);
    if(folder.length < 3){
        return ''; // The entire plugin consists of just a single PHP file, like Hello Dolly or file is the plugin's main file.
    }
    return folder[1];
}

/**
 * @return string
 */
function ldc_plugin_prefix(str = '', url = ''){
    var plugin_folder = '';
    if(!url){
        url = ldc_caller_url(1); // One level above.
    }
    plugin_folder = ldc_plugin_folder(url);
    if(!plugin_folder){
        return '';
    }
    return ldc_str_prefix(str, plugin_folder);
}

/**
 * @return string
 */
function ldc_plugin_slug(str = '', url = ''){
    var plugin_folder = '';
    if(!url){
        url = ldc_caller_url(1); // One level above.
    }
    plugin_folder = ldc_plugin_folder(url);
    if(!plugin_folder){
        return '';
    }
    return ldc_str_slug(str, plugin_folder);
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Strings
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_canonicalize(str = ''){
    str = ldc_sanitize_title(str);
    str = str.replaceAll('-', '_');
    return str;
}

/**
 * TODO: Improve.
 *
 * @return string
 */
function ldc_remove_accents(str = ''){
    str = str.replace(new RegExp('[àáâãäå]', 'g'), 'a');
    str = str.replace(new RegExp('[èéêë]', 'g'), 'e');
    str = str.replace(new RegExp('[ìíîï]', 'g'), 'i');
    str = str.replace(new RegExp('[òóôõö]', 'g'), 'o');
    str = str.replace(new RegExp('[ùúûü]', 'g'), 'u');
    str = str.replace(new RegExp('[ñ]', 'g'), 'n');
    return str;
}

/**
 * @return string
 */
function ldc_remove_whitespaces(str = ''){
    str = str.replace(/[\r\n\t ]+/g, ' ').trim();
    return str;
}

/**
 * @return string
 */
function ldc_sanitize_title(str = ''){
    str = ldc_remove_accents(str);
    str = ldc_sanitize_title_with_dashes(str);
    return str;
}

/**
 * TODO: Improve.
 *
 * @return string
 */
function ldc_sanitize_title_with_dashes(str = ''){
    str = str.toLowerCase();
    str = str.replace(/\s+/g, ' ');
    str = str.trim();
    str = str.replaceAll(' ', '-');
    str = str.replace(/[^a-z0-9-_]/g, '');
    return str;
}

/**
 * @return string
 */
function ldc_str_prefix(str = '', prefix = ''){
    prefix = prefix.replaceAll('\\', '_'); // Fix namespaces.
    prefix = ldc_canonicalize(prefix);
    prefix = ldc_rtrim(prefix, '_');
    if(!prefix){
        prefix = ldc_prefix();
    }
    str = ldc_remove_whitespaces(str);
    if(!str){
        return prefix;
    }
    if(0 === str.indexOf(prefix)){
        return str; // Text is already prefixed.
    }
    return prefix + '_' + str;
}

/**
 * @return string
 */
function ldc_str_slug(str = '', slug = ''){
    slug = slug.replaceAll('_', '-'); // Fix canonicalized.
    slug = slug.replaceAll('\\', '-'); // Fix namespaces.
    slug = ldc_sanitize_title(slug);
    slug = ldc_rtrim(slug, '-');
    if(!slug){
        slug = ldc_slug();
    }
    str = ldc_remove_whitespaces(str);
    if(!str){
        return slug;
    }
    if(0 === str.indexOf(slug)){
        return str; // Text is already slugged.
    }
    return slug + '-' + str;
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// Urchin Tracking Module
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_current_utm_param(name = ''){
    var utm_params = ldc_current_utm_params();
    if(_.isUndefined(utm_params[name])){
        return '';
    }
    return utm_params[name];
}

/**
 * @return object
 */
function ldc_current_utm_params(){
    if(ldc_at_least_one_utm_get_param()){
        return ldc_utm_params_from_get(); // 1. GET
    }
    return ldc_utm_params_from_cookie(); // 2. COOKIE
}

/**
 * @return string
 */
function ldc_utm_param_name(name = ''){
    var utm_pairs = ldc_utm_pairs();
    if(_.isUndefined(utm_pairs[name])){
        return '';
    }
    return utm_pairs[name];
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// These functions’ access is marked private. This means they are not intended for use by plugin or theme developers, only in other core functions.
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_at_least_one_utm_get_param(){
    var at_least_one = false,
        utm_params = ldc_utm_params_from_get();
    jQuery.each(utm_params, function(key, value){
        if(!value){
            return true; // Continue.
        }
        at_least_one = true;
        return false; // Break.
    });
    return utm_params;
}

/**
 * @return string
 */
function ldc_utm_cookie_name(){
    return ldc_str_prefix('utm_parameters');
}

/**
 * @return array
 */
function ldc_utm_keys(){
    return _.keys(ldc_utm_pairs());
}

/**
 * @return object
 */
function ldc_utm_pairs(){
    var utm_pairs = {
        utm_campaign: 'Name',
        utm_content: 'Content',
        utm_id: 'ID',
        utm_medium: 'Medium',
        utm_source: 'Source',
        utm_term: 'Term',
    };
    return utm_pairs;
}

/**
 * @return object
 */
function ldc_utm_params_from_cookie(){
    var utm_params = {},
        value = wpCookies.get(ldc_utm_cookie_name()),
        values = {};
    if(_.isUndefined(value) || _.isNull(value)){
        value = '';
    }
    values = ldc_parse_str(value);
    jQuery.each(ldc_utm_keys(), function(index, key){
        if(_.isUndefined(values[key])){
            utm_params[key] = '';
        } else {
            utm_params[key] = values[key];
        }
    });
    return utm_params;
}

/**
 * @return object
 */
function ldc_utm_params_from_get(){
    var query_args = ldc_get_query_args(),
        utm_params = {};
    jQuery.each(ldc_utm_keys(), function(index, key){
        if(_.isUndefined(query_args[key])){
            utm_params[key] = '';
        } else {
            utm_params[key] = query_args[key];
        }
    });
    return utm_params;
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// URLs
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_add_query_arg(key, value, url){
    var a = {},
        href = '';
    a = ldc_get_a(url);
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
}

/**
 * @return string
 */
function ldc_add_query_args(args, url){
    var a = {},
        href = '';
    a = ldc_get_a(url);
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
        href += k + '=' + v + '&';
    });
    href = href.slice(0, -1);
    if(a.hash){
        href += a.hash;
    }
    return href;
}

/**
 * @return object
 */
function ldc_get_a(url){
    var a = document.createElement('a');
    if('undefined' !== typeof(url) && '' !== url){
        a.href = url;
    } else {
        a.href = jQuery(location).attr('href');
    }
    return a;
}

/**
 * @return string
 */
function ldc_get_query_arg(key, url){
    var search_object = {};
    search_object = ldc_get_query_args(url);
    if('undefined' !== typeof(search_object[key])){
        return search_object[key];
    }
    return '';
}

/**
 * @return object
 */
function ldc_get_query_args(url){
    var a = {};
    a = ldc_get_a(url);
    if(a.search){
        return ldc_parse_str(a.search);
    }
    return {};
}

/**
 * @return string
 */
function ldc_mu_plugins_url(){
    var mu_plugins_url = ldc_object_property('mu_plugins_url');
    return (_.isNull(mu_plugins_url) ? '' : mu_plugins_url);
}

/**
 * @return object
 */
function ldc_parse_str(str){
    var i = 0, search_object = {},
        search_array = str.replace('?', '').split('&');
    for(i = 0; i < search_array.length; i ++){
        search_object[search_array[i].split('=')[0]] = search_array[i].split('=')[1];
    }
    return search_object;
}

/**
 * @return object|string
 */
function ldc_parse_url(url, component){
    var a = {},
        components = {},
        keys = ['protocol', 'hostname', 'port', 'pathname', 'search', 'hash'];
    a = ldc_get_a(url);
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

/**
 * @return string
 */
function ldc_plugins_url(){
    var plugins_url = ldc_object_property('plugins_url');
    return (_.isNull(plugins_url) ? '' : plugins_url);
}

/**
 * @return string
 */
function ldc_site_url(){
    var site_url = ldc_object_property('site_url');
    return (_.isNull(site_url) ? '' : site_url);
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// PHP
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/**
 * @return string
 */
function ldc_md5(str){
  //  discuss at: http://phpjs.org/functions/md5/
  // original by: Webtoolkit.info (http://www.webtoolkit.info/)
  // improved by: Michael White (http://getsprink.com)
  // improved by: Jack
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //    input by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //  depends on: utf8_encode
  //   example 1: md5('Kevin van Zonneveld');
  //   returns 1: '6e658d4bfcb59cc13f96c14450ac40b9'

  var xl;

  var rotateLeft = function(lValue, iShiftBits) {
    return (lValue << iShiftBits) | (lValue >>> (32 - iShiftBits));
  };

  var addUnsigned = function(lX, lY) {
    var lX4, lY4, lX8, lY8, lResult;
    lX8 = (lX & 0x80000000);
    lY8 = (lY & 0x80000000);
    lX4 = (lX & 0x40000000);
    lY4 = (lY & 0x40000000);
    lResult = (lX & 0x3FFFFFFF) + (lY & 0x3FFFFFFF);
    if (lX4 & lY4) {
      return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
    }
    if (lX4 | lY4) {
      if (lResult & 0x40000000) {
        return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
      } else {
        return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
      }
    } else {
      return (lResult ^ lX8 ^ lY8);
    }
  };

  var _F = function(x, y, z) {
    return (x & y) | ((~x) & z);
  };
  var _G = function(x, y, z) {
    return (x & z) | (y & (~z));
  };
  var _H = function(x, y, z) {
    return (x ^ y ^ z);
  };
  var _I = function(x, y, z) {
    return (y ^ (x | (~z)));
  };

  var _FF = function(a, b, c, d, x, s, ac) {
    a = addUnsigned(a, addUnsigned(addUnsigned(_F(b, c, d), x), ac));
    return addUnsigned(rotateLeft(a, s), b);
  };

  var _GG = function(a, b, c, d, x, s, ac) {
    a = addUnsigned(a, addUnsigned(addUnsigned(_G(b, c, d), x), ac));
    return addUnsigned(rotateLeft(a, s), b);
  };

  var _HH = function(a, b, c, d, x, s, ac) {
    a = addUnsigned(a, addUnsigned(addUnsigned(_H(b, c, d), x), ac));
    return addUnsigned(rotateLeft(a, s), b);
  };

  var _II = function(a, b, c, d, x, s, ac) {
    a = addUnsigned(a, addUnsigned(addUnsigned(_I(b, c, d), x), ac));
    return addUnsigned(rotateLeft(a, s), b);
  };

  var convertToWordArray = function(str) {
    var lWordCount;
    var lMessageLength = str.length;
    var lNumberOfWords_temp1 = lMessageLength + 8;
    var lNumberOfWords_temp2 = (lNumberOfWords_temp1 - (lNumberOfWords_temp1 % 64)) / 64;
    var lNumberOfWords = (lNumberOfWords_temp2 + 1) * 16;
    var lWordArray = new Array(lNumberOfWords - 1);
    var lBytePosition = 0;
    var lByteCount = 0;
    while (lByteCount < lMessageLength) {
      lWordCount = (lByteCount - (lByteCount % 4)) / 4;
      lBytePosition = (lByteCount % 4) * 8;
      lWordArray[lWordCount] = (lWordArray[lWordCount] | (str.charCodeAt(lByteCount) << lBytePosition));
      lByteCount++;
    }
    lWordCount = (lByteCount - (lByteCount % 4)) / 4;
    lBytePosition = (lByteCount % 4) * 8;
    lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80 << lBytePosition);
    lWordArray[lNumberOfWords - 2] = lMessageLength << 3;
    lWordArray[lNumberOfWords - 1] = lMessageLength >>> 29;
    return lWordArray;
  };

  var wordToHex = function(lValue) {
    var wordToHexValue = '',
      wordToHexValue_temp = '',
      lByte, lCount;
    for (lCount = 0; lCount <= 3; lCount++) {
      lByte = (lValue >>> (lCount * 8)) & 255;
      wordToHexValue_temp = '0' + lByte.toString(16);
      wordToHexValue = wordToHexValue + wordToHexValue_temp.substr(wordToHexValue_temp.length - 2, 2);
    }
    return wordToHexValue;
  };

  var x = [],
    k, AA, BB, CC, DD, a, b, c, d, S11 = 7,
    S12 = 12,
    S13 = 17,
    S14 = 22,
    S21 = 5,
    S22 = 9,
    S23 = 14,
    S24 = 20,
    S31 = 4,
    S32 = 11,
    S33 = 16,
    S34 = 23,
    S41 = 6,
    S42 = 10,
    S43 = 15,
    S44 = 21;

  //str = this.utf8_encode(str);
    str = ldc_utf8_encode(str);
  x = convertToWordArray(str);
  a = 0x67452301;
  b = 0xEFCDAB89;
  c = 0x98BADCFE;
  d = 0x10325476;

  xl = x.length;
  for (k = 0; k < xl; k += 16) {
    AA = a;
    BB = b;
    CC = c;
    DD = d;
    a = _FF(a, b, c, d, x[k + 0], S11, 0xD76AA478);
    d = _FF(d, a, b, c, x[k + 1], S12, 0xE8C7B756);
    c = _FF(c, d, a, b, x[k + 2], S13, 0x242070DB);
    b = _FF(b, c, d, a, x[k + 3], S14, 0xC1BDCEEE);
    a = _FF(a, b, c, d, x[k + 4], S11, 0xF57C0FAF);
    d = _FF(d, a, b, c, x[k + 5], S12, 0x4787C62A);
    c = _FF(c, d, a, b, x[k + 6], S13, 0xA8304613);
    b = _FF(b, c, d, a, x[k + 7], S14, 0xFD469501);
    a = _FF(a, b, c, d, x[k + 8], S11, 0x698098D8);
    d = _FF(d, a, b, c, x[k + 9], S12, 0x8B44F7AF);
    c = _FF(c, d, a, b, x[k + 10], S13, 0xFFFF5BB1);
    b = _FF(b, c, d, a, x[k + 11], S14, 0x895CD7BE);
    a = _FF(a, b, c, d, x[k + 12], S11, 0x6B901122);
    d = _FF(d, a, b, c, x[k + 13], S12, 0xFD987193);
    c = _FF(c, d, a, b, x[k + 14], S13, 0xA679438E);
    b = _FF(b, c, d, a, x[k + 15], S14, 0x49B40821);
    a = _GG(a, b, c, d, x[k + 1], S21, 0xF61E2562);
    d = _GG(d, a, b, c, x[k + 6], S22, 0xC040B340);
    c = _GG(c, d, a, b, x[k + 11], S23, 0x265E5A51);
    b = _GG(b, c, d, a, x[k + 0], S24, 0xE9B6C7AA);
    a = _GG(a, b, c, d, x[k + 5], S21, 0xD62F105D);
    d = _GG(d, a, b, c, x[k + 10], S22, 0x2441453);
    c = _GG(c, d, a, b, x[k + 15], S23, 0xD8A1E681);
    b = _GG(b, c, d, a, x[k + 4], S24, 0xE7D3FBC8);
    a = _GG(a, b, c, d, x[k + 9], S21, 0x21E1CDE6);
    d = _GG(d, a, b, c, x[k + 14], S22, 0xC33707D6);
    c = _GG(c, d, a, b, x[k + 3], S23, 0xF4D50D87);
    b = _GG(b, c, d, a, x[k + 8], S24, 0x455A14ED);
    a = _GG(a, b, c, d, x[k + 13], S21, 0xA9E3E905);
    d = _GG(d, a, b, c, x[k + 2], S22, 0xFCEFA3F8);
    c = _GG(c, d, a, b, x[k + 7], S23, 0x676F02D9);
    b = _GG(b, c, d, a, x[k + 12], S24, 0x8D2A4C8A);
    a = _HH(a, b, c, d, x[k + 5], S31, 0xFFFA3942);
    d = _HH(d, a, b, c, x[k + 8], S32, 0x8771F681);
    c = _HH(c, d, a, b, x[k + 11], S33, 0x6D9D6122);
    b = _HH(b, c, d, a, x[k + 14], S34, 0xFDE5380C);
    a = _HH(a, b, c, d, x[k + 1], S31, 0xA4BEEA44);
    d = _HH(d, a, b, c, x[k + 4], S32, 0x4BDECFA9);
    c = _HH(c, d, a, b, x[k + 7], S33, 0xF6BB4B60);
    b = _HH(b, c, d, a, x[k + 10], S34, 0xBEBFBC70);
    a = _HH(a, b, c, d, x[k + 13], S31, 0x289B7EC6);
    d = _HH(d, a, b, c, x[k + 0], S32, 0xEAA127FA);
    c = _HH(c, d, a, b, x[k + 3], S33, 0xD4EF3085);
    b = _HH(b, c, d, a, x[k + 6], S34, 0x4881D05);
    a = _HH(a, b, c, d, x[k + 9], S31, 0xD9D4D039);
    d = _HH(d, a, b, c, x[k + 12], S32, 0xE6DB99E5);
    c = _HH(c, d, a, b, x[k + 15], S33, 0x1FA27CF8);
    b = _HH(b, c, d, a, x[k + 2], S34, 0xC4AC5665);
    a = _II(a, b, c, d, x[k + 0], S41, 0xF4292244);
    d = _II(d, a, b, c, x[k + 7], S42, 0x432AFF97);
    c = _II(c, d, a, b, x[k + 14], S43, 0xAB9423A7);
    b = _II(b, c, d, a, x[k + 5], S44, 0xFC93A039);
    a = _II(a, b, c, d, x[k + 12], S41, 0x655B59C3);
    d = _II(d, a, b, c, x[k + 3], S42, 0x8F0CCC92);
    c = _II(c, d, a, b, x[k + 10], S43, 0xFFEFF47D);
    b = _II(b, c, d, a, x[k + 1], S44, 0x85845DD1);
    a = _II(a, b, c, d, x[k + 8], S41, 0x6FA87E4F);
    d = _II(d, a, b, c, x[k + 15], S42, 0xFE2CE6E0);
    c = _II(c, d, a, b, x[k + 6], S43, 0xA3014314);
    b = _II(b, c, d, a, x[k + 13], S44, 0x4E0811A1);
    a = _II(a, b, c, d, x[k + 4], S41, 0xF7537E82);
    d = _II(d, a, b, c, x[k + 11], S42, 0xBD3AF235);
    c = _II(c, d, a, b, x[k + 2], S43, 0x2AD7D2BB);
    b = _II(b, c, d, a, x[k + 9], S44, 0xEB86D391);
    a = addUnsigned(a, AA);
    b = addUnsigned(b, BB);
    c = addUnsigned(c, CC);
    d = addUnsigned(d, DD);
  }

  var temp = wordToHex(a) + wordToHex(b) + wordToHex(c) + wordToHex(d);

  return temp.toLowerCase();
}

/**
 * @return string
 */
function ldc_ltrim(str, charlist){
  //  discuss at: http://phpjs.org/functions/ltrim/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //    input by: Erkekjetter
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Onno Marsman
  //   example 1: ltrim('    Kevin van Zonneveld    ');
  //   returns 1: 'Kevin van Zonneveld    '

  charlist = !charlist ? ' \\s\u00A0' : (charlist + '')
    .replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
  var re = new RegExp('^[' + charlist + ']+', 'g');
  return (str + '')
    .replace(re, '');
}

/**
 * @return string
 */
function ldc_rtrim(str, charlist){
  //  discuss at: http://phpjs.org/functions/rtrim/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //    input by: Erkekjetter
  //    input by: rem
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Onno Marsman
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //   example 1: rtrim('    Kevin van Zonneveld    ');
  //   returns 1: '    Kevin van Zonneveld'

  charlist = !charlist ? ' \\s\u00A0' : (charlist + '')
    .replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\\$1');
  var re = new RegExp('[' + charlist + ']+$', 'g');
  return (str + '')
    .replace(re, '');
}

/**
 * @return string
 */
function ldc_utf8_decode(str_data){
  //  discuss at: http://phpjs.org/functions/utf8_decode/
  // original by: Webtoolkit.info (http://www.webtoolkit.info/)
  //    input by: Aman Gupta
  //    input by: Brett Zamir (http://brett-zamir.me)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Norman "zEh" Fuchs
  // bugfixed by: hitwork
  // bugfixed by: Onno Marsman
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: kirilloid
  // bugfixed by: w35l3y (http://www.wesley.eti.br)
  //   example 1: utf8_decode('Kevin van Zonneveld');
  //   returns 1: 'Kevin van Zonneveld'

  var tmp_arr = [],
    i = 0,
    c1 = 0,
    seqlen = 0;

  str_data += '';

  while (i < str_data.length) {
    c1 = str_data.charCodeAt(i) & 0xFF;
    seqlen = 0;

    // http://en.wikipedia.org/wiki/UTF-8#Codepage_layout
    if (c1 <= 0xBF) {
      c1 = (c1 & 0x7F);
      seqlen = 1;
    } else if (c1 <= 0xDF) {
      c1 = (c1 & 0x1F);
      seqlen = 2;
    } else if (c1 <= 0xEF) {
      c1 = (c1 & 0x0F);
      seqlen = 3;
    } else {
      c1 = (c1 & 0x07);
      seqlen = 4;
    }

    for (var ai = 1; ai < seqlen; ++ai) {
      c1 = ((c1 << 0x06) | (str_data.charCodeAt(ai + i) & 0x3F));
    }

    if (seqlen == 4) {
      c1 -= 0x10000;
      tmp_arr.push(String.fromCharCode(0xD800 | ((c1 >> 10) & 0x3FF)), String.fromCharCode(0xDC00 | (c1 & 0x3FF)));
    } else {
      tmp_arr.push(String.fromCharCode(c1));
    }

    i += seqlen;
  }

  return tmp_arr.join("");
}

/**
 * @return string
 */
function ldc_utf8_encode(argString){
  //  discuss at: http://phpjs.org/functions/utf8_encode/
  // original by: Webtoolkit.info (http://www.webtoolkit.info/)
  // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: sowberry
  // improved by: Jack
  // improved by: Yves Sucaet
  // improved by: kirilloid
  // bugfixed by: Onno Marsman
  // bugfixed by: Onno Marsman
  // bugfixed by: Ulrich
  // bugfixed by: Rafal Kukawski
  // bugfixed by: kirilloid
  //   example 1: utf8_encode('Kevin van Zonneveld');
  //   returns 1: 'Kevin van Zonneveld'

  if (argString === null || typeof argString === 'undefined') {
    return '';
  }

  // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
  var string = (argString + '');
  var utftext = '',
    start, end, stringl = 0;

  start = end = 0;
  stringl = string.length;
  for (var n = 0; n < stringl; n++) {
    var c1 = string.charCodeAt(n);
    var enc = null;

    if (c1 < 128) {
      end++;
    } else if (c1 > 127 && c1 < 2048) {
      enc = String.fromCharCode(
        (c1 >> 6) | 192, (c1 & 63) | 128
      );
    } else if ((c1 & 0xF800) != 0xD800) {
      enc = String.fromCharCode(
        (c1 >> 12) | 224, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
      );
    } else {
      // surrogate pairs
      if ((c1 & 0xFC00) != 0xD800) {
        throw new RangeError('Unmatched trail surrogate at ' + n);
      }
      var c2 = string.charCodeAt(++n);
      if ((c2 & 0xFC00) != 0xDC00) {
        throw new RangeError('Unmatched lead surrogate at ' + (n - 1));
      }
      c1 = ((c1 & 0x3FF) << 10) + (c2 & 0x3FF) + 0x10000;
      enc = String.fromCharCode(
        (c1 >> 18) | 240, ((c1 >> 12) & 63) | 128, ((c1 >> 6) & 63) | 128, (c1 & 63) | 128
      );
    }
    if (enc !== null) {
      if (end > start) {
        utftext += string.slice(start, end);
      }
      utftext += enc;
      start = end = n + 1;
    }
  }

  if (end > start) {
    utftext += string.slice(start, stringl);
  }

  return utftext;
}
