<?php
/**
 * Content file for __projectName__
 *
 * @category: Wordpress Theme
 * @package: __namespace__
 * @author: __author__
 * @license: __license__
 * @link: __projectURI__
 */

namespace __namespace__;

use Xiphe\THEMASTER as TM;
use Xiphe as X;

if (!isset($Master)) {
    extract(extr());
}

$HTML->s_article('#post-'.get_the_ID().'|class='.implode(' ', get_post_class()))
    ->s_header('.entry-header')
        ->s_h1('.entry-title')
            ->a(get_the_title(), array(
                'href' => get_permalink(),
                'title' => sprintf(
                    __('Permalink to %s', '__textdomain__'),
                    the_title_attribute('echo=0')
                ),
                'rel' => 'bookmark'
            ))
    ->end('.entry-header');

    if ('post' == get_post_type()) {
        $HTML->div(X\THEWPTOOLS::posted_on(), '.entry-meta');
    }

    if (is_search()) {
        $HTML->s_div('.entry-summary');
            the_excerpt();
        $HTML->end();
    } else {
        $HTML->s_div('.entry-content');
            the_content(__('Continue reading <span class="meta-nav">&rarr;</span>', '__textdomain__'));
            wp_link_pages(
                array(
                    'before' => '<div class="page-link"><span>'.__('Pages:', '__textdomain__').'</span>',
                    'after' => '</div>'
                )
            );
        $HTML->end();
    }

    $HTML->clear();
    $HTML->s_footer('.entry-meta');
        $show_sep = false;
        if ('post' == get_post_type()) {
            $categories_list = get_the_category_list(__(', ', '__textdomain__'));
            if ($categories_list) {
                $HTML->s_span('.cat-links')
                    ->span(__('Posted in', '__textdomain__'), '.entry-utility-prep entry-utility-prep-cat-links')
                    ->blank($categories_list)
                ->end();    
                $show_sep = true;
            }
            $tags_list = get_the_tag_list('', __(', ','__textdomain__'));

            if ($tags_list) {
                if ($show_sep) {
                    $HTML->span(' | ', 'sep');
                }
                $HTML->s_span('.tag-links')
                    ->span(__('Tagged', '__textdomain__'), '.entry-utility-prep entry-utility-prep-tag-links')
                    ->blank($tags_list)
                ->end();                    
                $show_sep = true;
            }
        }

        edit_post_link(__('Edit', '__textdomain__'), '<span class="edit-link">', '</span>');

$HTML->end('#post-'.get_the_ID());
?>