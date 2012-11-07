<?php
/**
 * Footer file for __projectName__
 *
 * @category: Wordpress Theme
 * @package: __namespace__
 * @author: __author__
 * @link: __themeuri__
 */

namespace __namespace__;

if (!isset($Master)) {
	extract(extr());
}

$HTML->end('#page');
wp_footer();
echo rtrim($HTML->r_end('all'));