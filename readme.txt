=== LDC Functions ===
Contributors: luisdelcid
Donate link: https://luisdelcid.com
Tags: ldc, functions
Tested up to: 6.7.1
Requires at least: 5.6
Requires PHP: 5.6
Stable tag: 0.1.14
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A personal collection of useful functions for WordPress plugins and themes.

== Description ==

A personal collection of useful functions for WordPress plugins and themes.

= Functions for plugins =

Add the following code to the main plugin file:

`add_action('plugins_loaded', function(){`
`    if(did_action('ldc_functions')){`
`        'Add your custom code here...';`
`    }`
`});`

= Functions for themes =

Create a new file named `ldc-functions.php` and add your custom code there or add the following code to the functions.php file:

`if(did_action('ldc_functions')){`
`    'Add your custom code here...';`
`}`

Note that `after_ldc_functions` is the **first action hook available to themes**, instead of `after_setup_theme`.

== Changelog ==

To see what’s changed, visit the [GitHub repository &#187;](https://github.com/luisdelcid/ldc-functions)
