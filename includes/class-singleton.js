var ldc_singleton = class LDC_Singleton {

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    static #instances = [];
	static #is_internal_constructing = false;

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	/**
	 * @return this
	 */
	static get_instance(){
        if(_.isUndefined(ldc_singleton.#instances[this.name])){ // Hardcoded.
            ldc_singleton.#is_internal_constructing = true; // Hardcoded.
		    ldc_singleton.#instances[this.name] = new this; // Hardcoded.
        }
		return ldc_singleton.#instances[this.name]; // Hardcoded.
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	/**
	 * @return void
	 */
	constructor(){
        this.constructor_name = this.constructor.name;
        if(ldc_singleton.#is_internal_constructing){ // Hardcoded.
            if(_.isFunction(this.loader)){
                this.loader();
            }
		} else {
			throw new TypeError('This class is not constructable.');
        }
        ldc_singleton.#is_internal_constructing = false; // Hardcoded.
	}

	/**
	 * @return mixed
	 */
	l10n(key = ''){
        var object_name = this.prefix('l10n');
        return ldc_object_property(key, object_name);
    }

	/**
	 * @return string
	 */
	get_name(){
		return this.constructor_name;
	}

	/**
	 * @return string
	 */
	prefix(str = ''){
		var name = this.get_name();
        return ldc_str_prefix(str, name);
	}

	/**
	 * @return string
	 */
	slug(str = ''){
		var name = this.get_name();
        return ldc_str_slug(str, name);
	}

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    //
    // Hooks
    //
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    /**
     * @return void
     */
    add_action(hook_name = '', callback = null, priority = 10){
        hook_name = this.prefix(hook_name);
        wp.hooks.addAction(hook_name, ldc_namespace(), callback, priority);
    }

    /**
     * @return void
     */
    add_filter(hook_name = '', callback = null, priority = 10){
        hook_name = this.prefix(hook_name);
        wp.hooks.addFilter(hook_name, ldc_namespace(), callback, priority);
    }

    /**
     * @return mixed
     */
    apply_filters(hook_name = '', value = null, ...arg){
        hook_name = this.prefix(hook_name);
        return wp.hooks.applyFilters(hook_name, value, ...arg);
    }

    /**
     * @return int|void
     */
    did_action(hook_name = ''){
        hook_name = this.prefix(hook_name);
        return wp.hooks.didAction(hook_name);
    }

    /**
     * @return int|void
     */
    did_filter(hook_name = ''){
        hook_name = this.prefix(hook_name);
        return wp.hooks.didFilter(hook_name);
    }

    /**
     * @return void
     */
    do_action(hook_name = '', ...arg){
        hook_name = this.prefix(hook_name);
        wp.hooks.doAction(hook_name, ...arg);
    }

    /**
     * @return void
     */
    doing_action(hook_name = ''){
        hook_name = this.prefix(hook_name);
        wp.hooks.doingAction(hook_name);
    }

    /**
     * @return void
     */
    doing_filter(hook_name = ''){
        hook_name = this.prefix(hook_name);
        wp.hooks.doingFilter(hook_name);
    }

    /**
     * @return bool
     */
    has_action(hook_name = ''){
        hook_name = this.prefix(hook_name);
        return wp.hooks.hasAction(hook_name, ldc_namespace());
    }

    /**
     * @return bool
     */
    has_filter(hook_name = ''){
        hook_name = this.prefix(hook_name);
        return wp.hooks.hasFilter(hook_name, ldc_namespace());
    }

    /**
     * @return int|void
     */
    remove_action(hook_name = ''){
        hook_name = this.prefix(hook_name);
        return wp.hooks.removeAction(hook_name, ldc_namespace());
    }

    /**
     * @return int|void
     */
    remove_filter(hook_name = ''){
        hook_name = this.prefix(hook_name);
        return wp.hooks.removeFilter(hook_name, ldc_namespace());
    }

    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    //
    // REST API
    //
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	/**
	 * @return string
	 */
	rest_namespace(version = 1){
        var namespace = '',
            slug = '';
        version = ldc_absint(version);
        if(version < 1){
            version = 1;
        }
        slug = this.slug();
        namespace = slug + '/v' + version;
        return namespace;
	}

	/**
	 * @return string
	 */
	rest_route(route = ''){
        var search = '',
            slug = '';
        route = ldc_sanitize_title(route);
        if(!route){
            return '';
        }
        slug = this.slug();
        search = slug + '-'; // With trailing dash.
        if(route.startsWith(search)){
            route = route.replace(search, '');
        }
        return route;
	}

	/**
	 * @return string
	 */
	rest_url(route = '', version = 1){
        route = this.rest_route(route);
        if(!route){
            return '';
        }
        return (wpApiSettings.root + this.rest_namespace(version) + '/' + route);
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

}
