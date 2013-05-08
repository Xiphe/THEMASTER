THE MASTER CHANGELOG
====================

### 3.3.*
+   see [readme.md](https://github.com/Xiphe/THEMASTER/blob/master/readme.md)

### 3.2.7
* update spin.js
* add jquery.mousewheel.min.js
* add jquery.outer_html.min.js

### 3.2.6
* add validateCountFor, removeEmptyFrom, get_countErrorMessageFor & getValidCountStr methods to FileSelect.
* bugfix


### 3.2.5
* minor bugfixes

### 3.2.4
* *.inc.less files will be checked against updated files imported by the .inc.less file and re-parsed correctly
* minor bugfixes

### 3.2.3
* refined the js-var printing process.
* minor bugfixes.

### 3.2.2
* introducing xiphe_responsiveimages_availablehosts filter
* fileselect.js bugfixes

### 3.2.1
* new scrollend.js
* minified resizeend.js
* fixed bug where responsive images were not placed after generation.
* minor bugfixes

### 3.2
* exclude THEDEBUG, THETOOLS and THEWPTOOLS
* switch to composer libraries
* Autoloading of THEMASTER handled through composer
* Autoloading of other plugin and theme classes handled by THEAUTOLOADER
* include coffeescript parser

### 3.1.6
+ minor bugfixes
+ THEWPMASTER will register hooks now BEFORE less files are registered. To ensure error messages will be printed correctly
+ Shortend the merge of js globals

### 3.1.5
+ sanitizeValidation for fileSelect
+ bugfixes and restructuring of ResponsiveImages.

### 3.1.4
+ some File Select Bugs fixed
+ even better injection of less globals
+ URLNormalizer class for usage in THETOOLS::normalizeUrl() added 

### 3.1.3
+ Better injection of less variables
+ added THEWPMASTER::wrapJsVar()

### 3.1.2
+ updated jQuery UI themes
+ updated combobox.js
+ bugfixes for models and language in THEWPMASTER
+ added @masterRes and @baseUrl as less variables to every less file.

### 3.1.1
+ enhanced the content filter for ResponsiveImages.

### 3.1.0
+ new namespace autoloading
+ lots of small adjustments
+ update from 3.0.* needs many adjustments to depending themes.

### 3.0.12
+ Added option to disable CSSFix for a .less file. Just add "// NOFIX //" in the 5th or 6th line of your less file.
+ Internal enhancement of the THETOOLS::is_browser() method.
+ Added THETOOLS::get\_browser(), THETOOLS::get\_browserVersion() and THETOOLS::get\_layoutEngine()
+ Enhanced the slash methods in THETOOLS. They all now accept a boolean as second argument. When true the slashes in the passed string will be unified.
+ Deprecated THETOOLS::get\_cleanedPath() in favor of THETOOLS::unify\_slashes(). Same logic -> more semantic name.
+ Nicer ID for registered sources in THEWPMASTER::twpm_enqueue()
+ Added a > img { border: 0; } to base.less
+ Added and enhanced the gradient-mixins in elements.less

### 3.0.11
+ multiple methods can be bind to wordpress hook by using an array as value in the actions_ class variable.
+ Added .clear, hr.sep and hr.clear.sep as Buttons to TinyMCE

### 3.0.10
+ Introducing TM\WP() Method as shorthand for class_exists('\WP')
+ First standalone Bugfixes

### 3.0.9
+ Updated the PHPQuery helper.
+ Added settings_saved callback to THEWPSETTINGS.
+ bugfixes

### 3.0.8
+ FileSelect now tries to bind newly uploaded files to the parent post.
+ You can now change title and description of attachment files via the fileselect dialog.
+ Added THEWPTOOLS::get\_nav\_menu\_parent()
+ Bugfixes

### 3.0.7
+ FileSelect Class supports height and width for dimension validation.
+ Fixed bug with registered source destinations.
+ Added .nowrap class to base.less and tm-admin.less

### 3.0.6
+ Fixed some errors when project file is not found by THEWPBUILDER.
+ Added FileSelect::get_sizeStr() for easy comparing sizes of multiple files.
+ Fixed errors with deleted attachments in FileSelect.
+ Added ResponsiveImages::get_imagefile() for getting the full image path.
+ Added Percent values as possible width for ResponsiveImages
+ Added THEWPTOOLS::relUrl() for removing the wp-baseUrl from given url.
+ Fixed Bug in tm-fileselect.js where the selection of one field affected other siblings.

### 3.0.5
+ Added THETOOLS::shorten() for cutting a text after x letters 
+ Added THEWPTOOLS::get\_nav\_menu\_id()
+ Added THEWPTOOLS::get\_the\_date()
+ Bugfixes

### 3.0.4
+ Added DS, preDS, unDS and unPreDS to THETOOLS
+ Reactivated check\_folder\_structure_ in THEWPBUILDER used for gaining desired chmod on folders
  after project update.

### 3.0.3
+ Added FileSelect Class for manipulating the wordpress media upload dialog.
+ Added "attachment" as key for Setting type.
+ Added ResponsiveImage Class for preloading mini images and then get the right image files
  for the current screen-size.
+ Lots of bugfixes

### 3.0.2
+ Added THEWPTOOLS::posted_on() from TwentyEleven
+ LessPHP to 0.3.8 and fixed import of elements.less
+ fixed import of textdomain
+ Added edit\_theme\_options as capability for Theme Settings
+ Added 'textarea' and 'tiny_mce' as keys for Setting type
+ Improved tm-admin.js setting-save-logic to enable tinyMCE
+ Improved def Theme template.

### 3.0.1
+ Added THETOOLS::filter\_urlQuery() and THETOOLS::create\_sprite()
+ Fixed Names of Masterclass in THEBASE::extr() and THEBASE::get_view() methods.
+ Bigfixes.

### 3.0.0
+	Complete refactoring of the initiation process
+	Updates via own server now throu [WP Project Update API](https://github.com/Xiphe/WP-Project-Update-API)
+	added shorthand-options
+	merged !themasteroptions
+ deleted all deprecated methods.
+ THEDEBUG is now standalone.
+ Added THETOOLS for non-relating methods.
+ Added THEWPBUILDER as a skeleton builder for wp-plugins and wp-themes

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