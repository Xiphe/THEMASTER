<?php 
namespace Xiphe\THEMASTER;

/**
 * THEWPTOOLS is a collection of standalone methods related to Wordpress used by !THE MASTER.
 *
 * @copyright Copyright (c) 2012, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.0.1
 * @link      https://github.com/Xiphe/-THE-MASTER/
 * @package   !THE MASTER
 */
class THEWPTOOLS {

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