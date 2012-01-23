THE MASTER - A base for plugins and themes
==========================================

Author: Hannes Diercks  
URL: http://www.red-thorn.de  
Plugin Info: http://plugins.red-thorn.de/libary/!themaster/  
Version: 2.0.6  
Date: 2012-01-23 13:11:00  
Requires: 3.0  
Tested: 3.3.1  




Description
-----------

This plugin allocates THEWPMASTER Class which brings handy functionalety and presets
for plugin and theme development in wordpress.

See [Plugin Page](http://plugins.red-thorn.de/libary/!themaster) for details




Installation
------------

1. Upload the plugin directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
4. Does nothing now
3. See [Plugin Page](http://plugins.red-thorn.de/libary/!themaster) for usage details




Changelog
---------

### 2.0.6
+	added themasterInstall()
+	added THEWPMASTER::_masterInstall() that generates the directory tree in the plugin folder

### 2.0.5
+	fixed THEBASE::echo_sources()
+	added THEWPMASTER::force_echo_sources()
+	added res/js/animaterotatescale.js by [zachstronaut](http://www.zachstronaut.com/posts/2009/08/07/jquery-animate-css-rotate-scale.html)
*	remove base.less.css from index

### 2.0.3
+	added toAlpha(), toAlphaNumeric(), toSlugForm() methods to THEMASTER
+	added recursive_get() to THEMASTER
+	changed $WPMaster->currentUser to Static & fixed bug in get_user($key)
+	added global source functionality
+	fixed required init args functionality
+	Added is_browser() to THEMASTER

### 2.0.2
+	added THEWPMASTER::add_contentTag($tag, $callback)
+	added get_HTML for fallback on global HTML class or error
+	admin_notices() changed to pure static function.
+	added reg_model() for including model classes
+	fixed THEDEBUG - arrays can now be debugged ;)
+	added reg_js($filename, $vars), reg_css($filename, $vars) & echo_sources() to THEMASTER
+	echo_sources in wp_master replaced, sources will be added via wp_enqueue_script/style

### 2.0.1
+   cleaned data structure and funcional update proccess

### 2.0
+   **First public version**

### pre 2.0
+   Intern version *no details*




Upgrade Notice
--------------

### 2.0.4
+	init on github





Known Bugs
----------

+   None ;)




Todo
----

### 2.0.1
+	Add Admin Option Page generator