<?php
/**
 * Main template file for __projectName__
 *
 * @category: Wordpress Theme
 * @package: __namespace__
 * @author: __author__
 * @license: __license__
 * @link: __projectURI__
 */

namespace __namespace__;

if (!isset($Master)) {
	extract(extr());
}


// **EXTENDED** //
get_header();

$HTML->s_div('#primary')
	->s_div('#content|role=main');

	if (have_posts()) {
		while (have_posts()) {
			the_post();
			get_template_part('content', get_post_format());
		}
	} else {
		get_template_part('noresults');
	}
$HTML->end('#primary');

get_sidebar();
get_footer();
// **EXTENDED_END** //