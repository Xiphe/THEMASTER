THE MASTER - A base for plugins and themes
==========================================

Author: Hannes Diercks  
URL: http://www.red-thorn.de  
Plugin Info: http://plugins.red-thorn.de/libary/!themaster/  
Version: 2.1.0  
Date: 2012-01-30 13:35:00  
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

### 2.1.0
+	relocated base class files from /classes to /core
+	added init_THEWPMASTER() function wich comes with an try - catch phrase
+	changed suffix from readme from .txt to .md

### 2.0.6
+	added THEWPMASTER::_masterUpdate() and PLUGIN::update() based on THEVERSION_PLUGINNAME or PLUGIN::$theversion
+	renamed old activation hooks from PLUGIN::install() to PLUGIN::activate()
+	added THEWPMASTER::$_folderStructure and PLUGIN::$folderStructure to create the dirs on update and check dir permissions
+	added THEMODEL::debug()
+	fixed bug that hides the output (bool) false and (null) null general and (int) 0 in firePHP
+	added functionality to enable static calls of THEDEBUG::debug(), rebug() & diebug();

### 2.0.5
+	fixed THEBASE::echo_sources()
+	added THEWPMASTER::force_echo_sources()
+	added res/js/animaterotatescale.js by [zachstronaut](http://www.zachstronaut.com/posts/2009/08/07/jquery-animate-css-rotate-scale.html)
*	removed base.less.css from index

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
+   Intern versions *no details*




Upgrade Notice
--------------

### 2.0.4
+	init on github





Known Bugs
----------

+   None ;)




Todo
----

+	enable deep subfolders for classes, models & views
+	first look for global includable classes, models & views in /!themaster/...
+	Add Admin Option Page generator (2.0.1)