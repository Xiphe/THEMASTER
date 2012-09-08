<?php 
namespace Xiphe\THEMASTER;

/**
 * THEWPTOOLS is a collection of standalone methods related to Wordpress used by !THE MASTER.
 *
 * @copyright Copyright (c) 2012, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.0.2
 * @link      https://github.com/Xiphe/-THE-MASTER/
 * @package   !THE MASTER
 */
class THEWPTOOLS {

	/**
	 * The "Posted by Derp in FooBar" post-meta.
	 * Taken from TwentyEleven Wordpress Theme.
	 * 
	 * @return string
	 */
	public static function posted_on() {
        return sprintf(__('<span class="sep">Posted on </span><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a><span class="by-author"> <span class="sep"> by </span> <span class="author vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'themaster'),
            esc_url(get_permalink()),
            esc_attr(get_the_time()),
            esc_attr(get_the_date('c')),
            esc_html(get_the_date()),
            esc_url(get_author_posts_url(get_the_author_meta('ID'))),
            esc_attr(sprintf(__('View all posts by %s', 'themaster'), get_the_author())),
            get_the_author()
        );
	}

	/**
	 * A Standard title for Wordpress Pages.
	 * Taken from TwentyEleven Wordpress Theme.
	 * 
	 * @access public
	 * @return string the title.
	 */
	public static function get_title()
	{
		global $page, $paged;

		$r = wp_title('|', false, 'right');

		// Add the blog name.
		$r .= get_bloginfo('name');

		// Add the blog description for the home/front page.
		$site_description = get_bloginfo('description', 'display');
		if ($site_description && (is_home() || is_front_page())) {
			$r .= " | $site_description";
		}

		// Add a page number if necessary:
		if ($paged >= 2 || $page >= 2) {
			$r .= ' | ' . sprintf(__('Page %s', 'themaster'), max($paged, $page));
		}
		return $r;
	}

	/**
	 * Wrapper for language_attributes() to return its content instead of echoing it.
	 *
	 * @access public
	 * @return string  the attributes string ready to be used in !HTML Class.
	 */
	public static function get_language_attributes()
	{
		ob_start();
		language_attributes();
		$r = ob_get_clean();
		$r = str_replace('"', '', $r);
		$r = str_replace(' ', '|', $r);
		
		return $r;
	}
}