THE MASTER - A base for plugins and themes
==========================================

This plugin allocates THEWPMASTER Class which brings handy functionalety and presets
for plugin and theme development in wordpress.

See [Plugin Page](https://github.com/Xiphe/-THE-MASTER) for details


Installation
------------

1. Upload the plugin directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
4. Does nothing now
3. See [Plugin Page](https://github.com/Xiphe/-THE-MASTER) for usage details


Support
-------

I've written this project for my own needs so i am not willing to give
full support. Anyway, i am very interested in any bugs, hints, requests
or whatever. Please use the [github issue system](https://github.com/Xiphe/-THE-MASTER/issues)
and i will try to answer.


Probs
-----

The update mechanics are inspired by:
**[automatic-theme-plugin-update](https://github.com/jeremyclark13/automatic-theme-plugin-update)**  
  by Kaspars Dambis (kaspars@konstruktors.com)  
  Modified by [Jeremy Clark](http://clark-technet.com)  
  [Donate](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=73N8G8UPGDG2Q)

Inspired means that i have seen this **brilliant project**, started to use it
and than started to write my own version with the intention to just have the
functionality i really use.  
I got the idea of how things are working by analyzing and using the code of
it and i can't say where i used snippets from this project but they've
influenced my code strongly.


3rd Party
---------

This plugin uses and includes some scipts from other people.
Here is a list:

###PHP
* **[FirePHP](http://www.firephp.org/)**  
  Used for debuging into Firebug console.  
  License: New BSD License  
  File: /_themaster/classes/FirePHPCore/LICENSE  
  Copyright (c) 2006-2009, Christoph Dorn  
  All rights reserved.
* **[lessPHP](http://leafo.net/lessphp/)**  
  Used for server-side compiling of .less files.  
  License: MIT LICENSE  
  File: /_themaster/classes/lessPHP/LICENSE  
  Copyright (c) 2010 Leaf Corcoran, http://leafo.net/lessphp
* **[Browser.php](http://chrisschuld.com/projects/browser-php-detecting-a-users-browser-from-php/)**  
  Used for Browser-detection.  
  License: GNU General Public License V2  
  File: http://www.gnu.org/copyleft/gpl.html  
  Copyright (C) 2008-2010 Chris Schuld  (chris@chrisschuld.com)
* **[CSSfix](http://www.phpclasses.org/css-fix)**  
  Used to apply vendor prefixes to specified CSS rules while parsing less to css.  
  License: BSD  
  File: http://opensource.org/licenses/bsd-license.html  
  Developed by Arturs Sosins aka ar2rsawseen, http://webcodingeasy.com
* **[phpQuery](http://code.google.com/p/phpquery/)**  
  Not used by THE MASTER but some shorthands are served.  
  License: MIT License  
  File: http://www.opensource.org/licenses/mit-license.php  
  Developed by Tobiasz Cudnik <tobiasz.cudnik/gmail.com>
* **MIMEType-Array from [MIME Types Class](http://www.phpclasses.org/phpmimetypeclass)**  
  Used for suffix to mimetype.  
  License: GNU GPL.  
  File: http://www.gnu.org/copyleft/gpl.html  
  Developed by Robert Widdick

###JS
* **[animaterotatescale](https://github.com/zachstronaut/jquery-animate-css-rotate-scale/blob/master/jquery-animate-css-rotate-scale.js)**  
  Devloped by zachstronaut
* **[coloumnmanager](http://p.sohei.org/jquery-plugins/columnmanager/)**  
* **combobox**  
  From jQueryUI-Examples - modifyed by Hannes Diercks.
* **[colorbox](http://www.jacklmoore.com/colorbox)**  
  (c) 2011 Jack Moore - jacklmoore.com  
  License: http://www.opensource.org/licenses/mit-license.php
* **jsonencode**  
  Sorry, can't find the author :(
* **[modernizr](www.modernizr.com)**  
  License: http://modernizr.com/license/
* **notextselect**  
  By [SLaks](http://stackoverflow.com/users/34397/slaks)
* **resizeend**  
  By [zetce21](http://forum.jquery.com/user/zetce21)
* **[spin](http://fgnass.github.com/spin.js#v1.2.5)**  
* **[sprintf](http://www.diveintojavascript.com/projects/javascript-sprintf)**
  Copyright (c) Alexandru Marasteanu <alexaholic [at) gmail (dot] com>  
  All rights reserved.)  
* **[Tablesorter](http://tablesorter.com/docs/)**  
  License: http://opensource.org/licenses/mit-license.php
  Developed by [Christian Bach](https://twitter.com/lovepeacenukes)


Changelog
---------

### 3.0.0
+	Complete refactoring of the initiation process
+	Updates via own server now throu [WP Project Update API](https://github.com/Xiphe/WP-Project-Update-API)
+	added shorthand-options
+	merged !themasteroptions
+	minor bugfixes

### 2.1.1
+	added THEWPMASTER::twpm_wp_login() and his friends to enable &forceRederect=(true||'hash') in wordpress login url
+	added phpQuery Libary 
	suport functions: THEMASTER::initPhpQuery(), THEMASTER::pq('<html...>') & THEMASTER::get_pqHtml()
+	added 'ipa' (iPad) as option for THEMASTER::is_browser()
+	added THESETTINGS::_set_setting($key, $value) for internal emergency setting changes
+	added auto-import of elements.less to each .less file
+	added modernizr.js from http://www.modernizr.com
+	changed call of reg_less('base') to WPMASTER::_masterInit() and called only in non-admin pages
+	changed THEWPMASTER::table from instance variable to static
+	added functionality to give THEBASE::reg_model( 'name', array( 'foo' => 'bar' ) ); static Model Variables

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

### 3.0.0
+	incobytibility with old red thorn update api
### 2.0.4
+	init on github


Known Bugs
----------

+   None ;)


Todo
----

+	build skeleton builder for plugins/themes/standalones
+	enable deep subfolders for classes, models & views
+	search for global includable classes, models & views in /!themaster/... at first
+	Add phpThumb or similar image resize functionality. see http://phpthumb.sourceforge.net/demo/docs/phpthumb.readme.txt (High-Security mode)