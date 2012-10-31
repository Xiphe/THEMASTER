<?php 
namespace Xiphe;

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

	public static function relUrl($url) {
		$rurl = parse_url(get_bloginfo('url'));
		if (isset($rurl['path'])) {
			unset($rurl['path']);
		}
		$rurl = THETOOLS::unparse_url($url);
		return str_replace($rurl, '', $url);
	}

	public static function get_nav_menu_parent($menu, $postID = null)
	{
		global $post, $wpdb;
		$menu = self::get_nav_menu_id($menu);

		if (empty($postID)) {
			if (is_page() && isset($post)) {
				$postID = $post->ID;
			} else {
				return false;
			}
		}
		$qry = $wpdb->prepare(
			"SELECT meta.meta_value as parent

			 FROM $wpdb->postmeta as meta
			 INNER JOIN $wpdb->posts as posts
			 	ON  posts.post_type   = 'nav_menu_item'
			 	AND posts.post_status = 'publish'
			 INNER JOIN $wpdb->term_relationships as termrel
			 	ON  termrel.object_id        = posts.ID
			 	AND termrel.term_taxonomy_id = %d
			 INNER JOIN $wpdb->postmeta as meta2
			 	ON  meta.post_id     = meta2.post_id
			 	AND meta2.meta_key   = '_menu_item_object_id'
			 	AND meta2.meta_value = %s

			 WHERE meta.post_id  = posts.ID
			 AND   meta.meta_key = '_menu_item_menu_item_parent'
			",
			$menu,
			$postID
		);
		$r = $wpdb->get_results($qry);
		if (empty($r)) {
			return false;
		}
		if ($r[0]->parent == 0) {
			return 0;
		} else {
			$qry = $wpdb->prepare(
				"SELECT meta.meta_value  as ID,
					    meta2.meta_value as type

				 FROM $wpdb->postmeta as meta
				 INNER JOIN $wpdb->postmeta as meta2
				 	ON  meta.post_id     = meta2.post_id
				 	AND meta2.meta_key   = '_menu_item_object'

				 WHERE meta.post_id  = %d
				 AND   meta.meta_key = '_menu_item_object_id'
				",
				$r[0]->parent
			);
			$r = $wpdb->get_results($qry);
			if (empty($r)) {
				return false;
			} else {
				return $r[0];
			}

		}
	}

	public static function get_nav_menu_id($menu)
	{
		if (!is_numeric($menu)) {
			$locations = get_nav_menu_locations();
			if (isset($locations[$menu])) {
				$menu = $locations[$menu];
			} else {
				global $wpdb;
				$menu = $wpdb->get_results($wpdb->prepare(
					"SELECT term_id
					FROM $wpdb->terms
					WHERE slug = %s",
					$menu
				));
				if (count($menu) == 1) {
					$menu = intval($menu[0]->term_id);
				} else {
					return false;
				}
			}
		}
		return $menu;
	}

	public static function get_the_date($post_id, $format = null) {
		global $post;
		$save_post = $post;
		$post = get_post($post_id);
		$date = get_the_date($format);
		$post = $save_post;
		return $date;
	}

	public static function create_noprivnonce($action, $id)
	{
		$i = wp_nonce_tick();
		return substr(wp_hash($i . $action . $id, 'nonce'), -12, 10);
	}

	public static function verify_noprivnonce($nonce, $action, $id)
	{
		$i = wp_nonce_tick();
		if ( substr(wp_hash($i . $action . $id, 'nonce'), -12, 10) == $nonce )
			return 1;
		if ( substr(wp_hash(($i - 1) . $action . $id, 'nonce'), -12, 10) == $nonce )
			return 2;
		return false;
	}

	/**
	 * The "Posted by Derp in FooBar" post-meta.
	 * Taken from TwentyEleven Wordpress Theme.
	 * 
	 * @return string
	 */
	public static function posted_on()
	{
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