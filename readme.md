THE MASTER
==========

A base for plugins and themes

This plugin allocates THEWPMASTER Class which brings handy functionalety and presets
for plugin and theme development in wordpress.

See [Plugin Page](https://github.com/Xiphe/-THE-MASTER) for details


Installation
------------

1. Download the [latest version](https://github.com/Xiphe/THEMASTER/archive/master.zip).
2. Extract the archive and upload plugin to the `/wp-content/plugins/` directory of your Wordpress installation.
3. Activate the plugin through the 'Plugins' menu in WordPress
4. You can now use Plugins and Themes that require THEMASTER.


Support
-------

I've written this project for my own needs so i am not willing to give
full support. Anyway, i am very interested in any bugs, hints, requests
or whatever. Please use the [github issue system](https://github.com/Xiphe/-THE-MASTER/issues)
and i will try to answer.


Special Thanks
--------------

The update mechanics are inspired* by:
**[automatic-theme-plugin-update](https://github.com/jeremyclark13/automatic-theme-plugin-update)**  
  by [Kaspars Dambis](kaspars@konstruktors.com)  
  Modified by [Jeremy Clark](http://clark-technet.com)  
  [Donate](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=73N8G8UPGDG2Q)

The FileSelect module is inspired* by:
**[SLT File Select](http://sltaylor.co.uk/wordpress/plugins/slt-file-select/)**  
  by [Steve Taylor](http://sltaylor.co.uk)  
  released under GNU GENERAL PUBLIC LICENSE Version 2  


*Inspired means that i have seen this **brilliant project**, started to use them
and than started to write my own version with the intention to just have the
functionality i really use.  
I got the idea of how things are working by analyzing and using the code of
it and i can't say where i used snippets from this project but they've
influenced my code strongly.


3rd Party
---------

This plugin uses and includes some scripts from other people.
Here is a list:

### Composer
coffeescript/coffeescript, css-crush/css-crush, duvanmonsa/php-query, 
firephp/firephp-core leafo/lessphp, ikimea/browser and some of my own packages.

### PHP
* Diverse Snippets from all around the World - added the author whenever possible.
* **MIMEType-Array from [MIME Types Class](http://www.phpclasses.org/phpmimetypeclass)**  

###JS
* **[animaterotatescale](https://github.com/zachstronaut/jquery-animate-css-rotate-scale/blob/master/jquery-animate-css-rotate-scale.js)**  
  Devloped by zachstronaut

* **[coloumnmanager](http://p.sohei.org/jquery-plugins/columnmanager/)**  

* **combobox**  
  From jQueryUI-Examples - modifyed by me.

* **[colorbox](http://www.jacklmoore.com/colorbox)**  
  (c) 2011 Jack Moore - jacklmoore.com  
  License: http://www.opensource.org/licenses/mit-license.php

* **[jquery.cookie](https://github.com/carhartl/jquery-cookie)**  
  Copyright 2013 Klaus Hartl  
  [MIT License](https://github.com/carhartl/jquery-cookie/blob/master/MIT-LICENSE.txt)

* **[jquery-json](code.google.com/p/jquery-json)**  
  by Brantley Harris  
  [MIT License](http://opensource.org/licenses/mit-license.php)

* **[jQuery Mouse Wheel Plugin](https://github.com/brandonaaron/jquery-mousewheel)**  
  by Brandon Aaron  
  [MIT License](https://github.com/brandonaaron/jquery-mousewheel/blob/master/LICENSE.txt)

* **jQuery Outer HTML**
  by [Ca-Phun Ung](http://www.yelotofu.com/2008/08/jquery-outerhtml/)

* **[jquery-timing](https://github.com/creativecouple/jquery-timing)**  
  by [CreativeCouple](http://www.creativecouple.de/) - Peter Liske
  [MIT License](https://github.com/creativecouple/jquery-timing/blob/master/README.md)

* **[modernizr](www.modernizr.com)**  
  License: http://modernizr.com/license/

* **notextselect**  
  By [SLaks](http://stackoverflow.com/users/34397/slaks)

* **[-prefix-free](http://leaverou.github.com/prefixfree/)**  
  License: [MIT License](http://www.opensource.org/licenses/mit-license.php)
  Developed by [Lea Verou](http://lea.verou.me/)

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

### pre 3.2
+ See changelog.md



Todo
----

+ Cleanup the js lib. (Bower?)
+ Convert ResponsiveImages and FileSelect into WP-Plugins.
+	build skeleton builder for standalones
+ Better documentation
+ Spell-check O_°

License
-------

Copyright (C) 2013 Hannes Diercks

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.