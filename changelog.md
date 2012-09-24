THE MASTER CHANGELOG
====================


### 2.1.1
+	added THEWPMASTER::twpm_wp_login() and his friends to enable &forceRederect=(true||'hash') in wordpress login url
+	added phpQuery Library 
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
+   cleaned data structure and functional update process

### 2.0
+   **First public version**

### pre 2.0
+   Intern versions *no details*