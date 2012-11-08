<?php
/**
 * Header file for __projectName__
 *
 * @category: Wordpress Theme
 * @package: __namespace__
 * @author: __author__
 * @license: __license__
 * @link: __projectURI__
 */

namespace __namespace__;

use Xiphe as X;

if (!isset($Master)) {
	extract(extr());
}

$HTML->HTML5(array('html' => X\THEWPTOOLS::get_language_attributes()))
	->title(X\THEWPTOOLS::get_title())
	->description(get_bloginfo('description', 'display'))
	->viewport();

wp_head();
$HTML->end('head')
	->s_body(implode(' ', get_body_class()))
	->s_div('#page|.hfeed');

