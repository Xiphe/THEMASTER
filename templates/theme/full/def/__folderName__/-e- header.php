<?php
namespace __namespace__;

use Xiphe\THEMASTER as TM;

if(!isset($Master)) extract(extr());
$HTML->HTML5(array('html' => TM\THEWPTOOLS::get_language_attributes()))
	->title(TM\THEWPTOOLS::get_title())
	->description(get_bloginfo('description', 'display'))
	->viewport();

wp_head();
$HTML->end('head')
	->s_body(implode(' ', get_body_class()))
	->s_div('#page|.hfeed');

?>