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

###PHP
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

* **MIMEType-Array from [MIME Types Class](http://www.phpclasses.org/phpmimetypeclass)**  
  Used for suffix to mimetype.  
  License: GNU GPL.  
  File: http://www.gnu.org/copyleft/gpl.html  
  Developed by Robert Widdick

* **[modernizr-server](https://github.com/jamesgpearce/modernizr-server)**  
  Author: [James Pearce](http://tripleodeon.com/)
  License: [The MIT License](https://github.com/jamesgpearce/modernizr-server/blob/master/LICENSE)  

* **[phpQuery](http://code.google.com/p/phpquery/)**  
  Not used by THE MASTER but some shorthands are served.  
  License: MIT License  
  File: http://www.opensource.org/licenses/mit-license.php  
  Developed by Tobiasz Cudnik <tobiasz.cudnik/gmail.com>

* **[SLT File Select](http://sltaylor.co.uk/wordpress/plugins/slt-file-select/)**
  I implemented the idears of Steve Taylors File Select Plugin as Class FileSelect for usage in other Plugins
  and as a part of THEWPSETTINGS. I modified his code to make it fit into my workflow.
  Donate Link: http://www.babyloniantimes.co.uk/index.php?page=donate

* **[URLNormalizer](https://github.com/glenscott/url-normalizer)**
  Author: [Glen Scott](http://www.glenscott.co.uk/)
  License: (c) Glen Scott - Usage allowed here: http://www.glenscott.co.uk/blog/2011/01/09/normalize-urls-with-php/#comment-98235


###JS
* **[animaterotatescale](https://github.com/zachstronaut/jquery-animate-css-rotate-scale/blob/master/jquery-animate-css-rotate-scale.js)**  
  Devloped by zachstronaut

* **[coloumnmanager](http://p.sohei.org/jquery-plugins/columnmanager/)**  

* **combobox**  
  From jQueryUI-Examples - modifyed by Hannes Diercks.

* **[colorbox](http://www.jacklmoore.com/colorbox)**  
  (c) 2011 Jack Moore - jacklmoore.com  
  License: http://www.opensource.org/licenses/mit-license.php

* **[jquery.cookie](https://github.com/carhartl/jquery-cookie)**  
  Copyright 2013 Klaus Hartl  
  [MIT License](https://github.com/carhartl/jquery-cookie/blob/master/MIT-LICENSE.txt)

* **[jquery-json](code.google.com/p/jquery-json)**  
  by Brantley Harris  
  [MIT License](http://opensource.org/licenses/mit-license.php)

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

### pre 3.1
+ See changelog.md


Known Bugs
----------

+   None ;)


Todo
----

+ composer compatibility
+ ResponsiveImages, FileSelect, THETOOLS, THEWPTOOLS and THEDEBUG should be
  WP-Plugins by itself and/or composer packages.
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