
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(typeof ldc_floating_labels_select !== 'function'){
    function ldc_floating_labels_select(select){
        if(jQuery(select).val() == ''){
            jQuery(select).removeClass('placeholder-hidden');
        } else {
            jQuery(select).addClass('placeholder-hidden');
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(typeof ldc_floating_labels_textarea !== 'function'){
    function ldc_floating_labels_textarea(textarea){
        jQuery(textarea).height(parseInt(jQuery(textarea).data('element'))).height(textarea.scrollHeight - parseInt(jQuery(textarea).data('padding')));
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(typeof ldc_floating_labels !== 'function'){
    function ldc_floating_labels(){
        if(jQuery('.ldc-floating-labels > textarea').length){
            jQuery('.ldc-floating-labels > textarea').each(function(){
                ldc_floating_labels_textarea(this);
            });
        }
        if(jQuery('.ldc-floating-labels > select').length){
            jQuery('.ldc-floating-labels > select').each(function(){
                ldc_floating_labels_select(this);
            });
        }
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

jQuery(function($){
    if($('.ldc-floating-labels > textarea').length){
        $('.ldc-floating-labels > textarea').each(function(){
            $(this).data({
                'border': $(this).outerHeight() - $(this).innerHeight(),
                'element': $(this).height(),
                'padding': $(this).innerHeight() - $(this).height(),
            });
        });
    }
    ldc_floating_labels();
    if($('.ldc-floating-labels > textarea').length){
        $('.ldc-floating-labels > textarea').on('input propertychange', function(){
            ldc_floating_labels_textarea(this);
        });
    }
    if($('.ldc-floating-labels > select').length){
        $('.ldc-floating-labels > select').on('change', function(){
            ldc_floating_labels_select(this);
        });
    }
    $(document).on(ldc_page_visibility_event(), ldc_floating_labels);
});

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
