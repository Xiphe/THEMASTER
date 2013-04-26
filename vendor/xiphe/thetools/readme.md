THETOOLS
========

A little collection of standalone functions and libs for everyday usage.


Installation
------------

Use [composer](http://getcomposer.org/) and require `"xiphe/thetools": "~1.0"`

The `Xiphe\THETOOLS::is_browser('ff');` methods require [Browser](https://github.com/Ikimea/Browser) from Ikimea  
The `Xiphe\THETOOLS::pq('<div />');` methods require [phpQuery](https://github.com/duvanmonsa/phpQuery)


Support
-------

I've written this project for my own needs so i am not willing to give
full support. Anyway, i am very interested in any bugs, hints, requests
or whatever. Please use the [github issue system](https://github.com/Xiphe/THETOOLS/issues)
and i will try to answer.


3rd Party
---------

This plugin uses and includes some scripts from other people.
Here is a list:

###PHP
* **[Browser.php](https://github.com/Ikimea/Browser)**  
  Used for Browser-detection.  
  License: GNU General Public License V2  
  File: http://www.gnu.org/copyleft/gpl.html  
  Copyright (C) 2008-2010 Chris Schuld (chris@chrisschuld.com)

* **[phpQuery](https://github.com/duvanmonsa/phpQuery)**  
  License: MIT License  
  Homepage: http://code.google.com/p/phpquery/  
  File: http://www.opensource.org/licenses/mit-license.php  
  Developed by Tobiasz Cudnik <tobiasz.cudnik/gmail.com>

* **[URLNormalizer](https://github.com/glenscott/url-normalizer)**
  Author: [Glen Scott](http://www.glenscott.co.uk/)
  License: (c) Glen Scott - Usage allowed here: http://www.glenscott.co.uk/blog/2011/01/09/normalize-urls-with-php/#comment-98235


Changelog
---------

### 1.0.9
+ compareNumbers added.

### 1.0.8
+ bugfix for get_currentUrl filters

### 1.0.2
+ move class file into src

### 1.0
+ initiation as standalone package
+ added to composer

### pre 1.0
+ This once was a part of [THEMASTER](https://github.com/Xiphe/THEMASTER/).


Todo
----

+ Revisit the ar and sc methods. Maybe they should be deprecated in favor of typecasting.
+ More tests
+ Better documentation
+ Spell-check


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