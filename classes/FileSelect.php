<?php
namespace Xiphe\THEMASTER\classes;

use Xiphe\THEMASTER\core as core;
use Xiphe as X;

/**
 * Provides themes and plugins with a form interface to select a file from the Media Library.
 * [Original Plugin](http://sltaylor.co.uk/wordpress/plugins/slt-file-select/)
 * 
 * @author   Logic: [Steve Taylor](http://sltaylor.co.uk)
 *           Adjustments and Conversion into TM-Class: [Hannes Diercks](xiphe@gmx.de)
 * @version  0.2.1
 */
class FileSelect extends core\THEWPMASTER {
	public $singleton = true;

    private $_typeErrors = array();

    private $_translateSizes = array();

    private $_sizes = array(
        'minwidth' => 0,
        'minheight' => 0,
        'maxwidth' => 0,
        'maxheight' => 0,
        'height' => 0,
        'width' => 0
    );

    /* 
     * Add Filters and Actions into this array. A simple Value will look for a similar called method
     * in this class. The Methodname can be specifyed by the key. Priority and accepted args can be
     * adjusted by adding them to the hookname. ( 'init|1|3' ).
     */
    protected $actions_ = array(
        'admin_print_scripts',
        'admin_print_styles',
        'admin_init',
        'wp_ajax_tm_fileselect_getfile',
        'wp_ajax_tm_fileselect_getfullsize',
        'save_post|0',
        'admin_head'
    );

    protected $filters_ = array(
        'upload_mimes',
        'gettext|10|3'
    );

    public function save_post()
    {
        if (isset($_REQUEST['tm-fileselect_validation'])) {
            unset($_REQUEST['tm-fileselect_validation']);
        }
    }

    public function button(
        $name,
        $value,
        $label = null,
        $previewSize = 'thumbnail',
        $validation = false,
        $multiple = false
    ) {
    	$HTML = core\THEBASE::sget_HTML()->s_div('.tm-fileselect_fullwrap');
        if (empty($label) && $label !== false) {
            $label = __('Select file', 'themaster');
        }
        if ($label !== false) {
            $HTML->label(esc_attr($label), preg_replace('/[\[\]]/', '_', esc_attr($name)));
        }
        if (is_array($value)) {
            $value = implode(',', $value);
        }
    	$HTML->s_div('.tm-fileselect_buttonwrap')
    		->button(__('Upload/Choose', 'themaster'), '.button-secondary tm-fileselect_button')
            ->hidden('.tm-fileselect_value|name='.esc_attr($name).'|value='.esc_attr($value))
            ->hidden('.tm-fileselect_previewsize|name=tm-fileselect_previewsize|value='.$HTML->esc($previewSize))
            ->hidden('.tm-fileselect_validation|name=tm-fileselect_validation|value='.$HTML->esc($validation))
            ->hidden('.tm-fileselect_multiple|name=tm-fileselect_multiple|value='.$HTML->esc($multiple))
            ->hidden('.tm-fileselect_nonce|name=tm-fileselect_nonce|value='.wp_create_nonce('tm-fileselect_getfile'))
        ->end()
        ->sg_ul('#'.esc_attr($name).'_preview|.tm-fileselect_preview');
    		if (!empty($value)) {
                if($multiple) {
                    $values = explode(',', esc_attr($value));
                } else {
                    $values = array(esc_attr($value));
                }
                foreach ($values as $value) {
                    echo $this->_get_preview($value, esc_attr($previewSize));
                }
            }
        $HTML->end('.tm-fileselect_fullwrap');
    }

    public function get_sizeStr($attachmentIDs) {
        if (!is_array($attachmentIDs)) {
            $attachmentIDs = explode(',', $attachmentIDs);
        }
        $r = array();
        foreach ((array) $attachmentIDs as $k => $attachmentID) {
            $meta = wp_get_attachment_metadata($attachmentID);
            $r[] = $meta['width'].'x'.$meta['height'];
        }
        return implode('|', $r);
    }


    public function validateTypeFor($attachmentIDs, $validTypes) {
        if (!is_array($attachmentIDs)) {
            $attachmentIDs = array($attachmentIDs);
        }
        $validTypes = explode('|', $validTypes);
        $allOk = true;


        if($attachmentIDs === '') {
            $attachmentIDs = array();
        }
        foreach ((array) $attachmentIDs as $k => $attachmentID) {
            $attachment_url = wp_get_attachment_url($attachmentID);
            $type = wp_check_filetype($attachment_url);
            $type = $type['type'];
            $ok = false;
            foreach ($validTypes as $i => $val) {
                if (!$ok) {
                    if (!strstr($val, '/')) {
                        $ttype = explode('/', $type);
                        $ttype = $ttype[0];
                    } elseif (strstr($val, '/*')) {
                        $ttype = explode('/', $type);
                        $ttype = $ttype[0];
                        $val = str_replace('/*', '', $val);
                    } else {
                        $ttype = $type;
                    }
                    if ($ttype == trim($val)) {
                        $ok = true;
                        break;
                    }
                }
            }

            if (!$ok) {
                $allOk = false;
                $this->_typeErrors[$attachmentID] = array(
                    'file' => basename($attachment_url),
                    'valid' => $validTypes
                );
            }
        }

        return $allOk;
    }

    public function validateSizeFor($attachmentIDs, $chksizes)
    {
        $checkSize = false;
        $readableSizes = array();
        $sizes = array();

        foreach ($this->_sizes as $k => $size) {
            if (isset($chksizes[$k])) {
                $sizes[$k] = $chksizes[$k];
                $checkSize = true;
                $readableSizes[] = $this->_translateSizes[$k].': '.$chksizes[$k].'px';
            }
        }

        if (!$checkSize) {
            return true;
        }
        $hasError = false;
        if($attachmentIDs === '') {
            $attachmentIDs = array();
        }
        foreach ((array) $attachmentIDs as $id) {
            $meta = wp_get_attachment_metadata($id);
            $ft = wp_check_filetype($meta['file']);
            $fts = explode('/', $ft['type']);
            if ($fts[0] == 'image') {
                $error = false;
                foreach ($sizes as $k => $size) {

                    if (($k == 'minwidth' && $meta['width'] < $size)
                     || ($k == 'minheight' && $meta['height'] < $size)
                     || ($k == 'maxwidth' && $meta['width'] > $size)
                     || ($k == 'maxheight' && $meta['height'] > $size)
                     || ($k == 'height' && $meta['height'] != $size)
                     || ($k == 'width' && $meta['width'] != $size)
                    ) {
                        $error = true;
                    }
                }
                if ($error) {
                    $hasError = true;
                    $this->_sizeErrors[$id] = array(
                        'file' => basename($meta['file']),
                        'valid' => $readableSizes
                    );
                }
            }
        }
       return !$hasError;
    }

    public function get_sizeErrorMessageFor($attachmentIDs)
    {
        if($attachmentIDs === '') {
            $attachmentIDs = array();
        }
        $valids = array();
        $errorFiles = array();
        foreach ((array) $attachmentIDs as $k => $id) {
            if (isset($this->_sizeErrors[$id])) {
                $errorFiles[] = $this->_sizeErrors[$id]['file'];
                $valids = array_merge($valids, array_flip($this->_sizeErrors[$id]['valid']));
            }
        }

        if (count($errorFiles)) {
            $msg = sprintf(
                __('%1$s %2$s %3$s not match with the allowed sizes (%4$s).', 'themaster'),
                (count($errorFiles) > 1 ? __('The images', 'themaster') : __('The image', 'themaster')),
                X\THETOOLS::readableList($errorFiles, ' '.__('and', 'themaster').' '),
                (count($errorFiles) > 1 ? __('do', 'themaster') : __('does', 'themaster')),
                X\THETOOLS::readableList(array_flip($valids), ' '.__('and', 'themaster').' ')
            );
            return $msg;
        }
        return false;
    }

    public function get_typeErrorMessageFor($attachmentIDs)
    {
        if($attachmentIDs === '') {
            $attachmentIDs = array();
        }
        $valids = array();
        $files = array();
        foreach ((array) $attachmentIDs as $k => $id) {
            if (isset($this->_typeErrors[$id])) {
                $files[] = $this->_typeErrors[$id]['file'];
                $valids = array_merge($valids, array_flip($this->_typeErrors[$id]['valid']));
            }
        }

        if (!count($files)) {
            return false;
        }
        return sprintf(
            __('The %1$s %2$s %3$s not match with the allowed mimetypes. Please use only %4$s.', 'themaster'),
            count($files) > 1 ? __('files', 'themaster') : __('file', 'themaster'),
            X\THETOOLS::readableList($files, ' '.__('and', 'themaster').' '),
            count($files) > 1 ? __('do', 'themaster') : __('does', 'themaster'),
            X\THETOOLS::readableList(array_flip($valids))
        );
    }

    public function bind_attachment($attachmentIDs, $postID) {
        if (!is_array($attachmentIDs)) {
            $attachmentIDs = explode(',', $attachmentIDs);
        }

        global $wpdb;
        foreach ($attachmentIDs as $k => $attachmentID) {
            $post = get_post($attachmentID);
            if (isset($post->post_parent) && empty($post->post_parent)) {
                $wpdb->update(
                    $wpdb->posts,
                    array('post_parent' => $postID),
                    array('ID' => $post->ID),
                    array('%d'),
                    array('%d')
                );
            }
        }
    }

	/**
     * Basic initiation before Wordpress is available.
     *
     * Internal and !THEMASTER related logic should be used here.
     * Initiation arguments passed to THEWPMASTERINIT function and
     * parsed from style.css are available as class variables.
     *
     * @return void
     */
    public function init() {
        $this->_translateSizes = array(
            'minwidth' => __('Minimal width', 'themaster'),
            'minheight' => __('Minimal height', 'themaster'),
            'maxwidth' => __('Maximal width', 'themaster'),
            'maxheight' => __('Maximal height', 'themaster'),
            'height' => __('Height', 'themaster'),
            'width' => __('Width', 'themaster')
        );
        $this->reg_adminJs('tm-fileselect');
        $this->reg_adminJsVar(
            'fileselecttext',
            array(
                'select' => __('Select', 'themaster'),
                'save' => __('Save', 'themaster')
            )
        );
    }

    public function admin_head()
    {
        $this->reg_adminJsVar(
            'fileselectbaseurl',
            'media-upload.php?post_id='.(isset($GLOBALS['post']->ID) ? $GLOBALS['post']->ID : 0)
        );
    }

    public function admin_init() {
    	if (basename($_SERVER['SCRIPT_FILENAME']) == 'media-upload.php' && array_key_exists('tm-fileselect_field', $_GET)) {
			add_filter('flash_uploader', create_function('$a','return false;'), 5);
    	}
    }

    public function admin_print_scripts()
    {
    	wp_enqueue_script('jquery');
    	wp_enqueue_script('media-upload');
    	wp_enqueue_script('thickbox');
    }

    public function admin_print_styles()
    {
    	wp_enqueue_style('thickbox');
    }

    public function wp_ajax_tm_fileselect_getfile()
    {
        if (!is_admin()
         || !wp_verify_nonce(esc_attr($_REQUEST['nonce']), 'tm-fileselect_getfile')
        ) {
            exit;
        }
        if (!is_array($_REQUEST['id'])) {
            $_REQUEST['id'] = array($_REQUEST['id']);
        }
        foreach ($_REQUEST['id'] as $id) {
            echo $this->_get_preview(esc_attr($id), esc_attr($_REQUEST['size']));
        }
        exit;
    }

    public function upload_mimes($mimes)
    {
        $mimes['svg|svgz'] = 'image/svg+xml';
        if (!empty($_REQUEST['tm-fileselect_validation'])
         && ($vals = $_REQUEST['tm-fileselect_validation']) != false
        ) {
            $newMimes = array();
            $vals = explode('|', $vals);
            foreach ($vals as $val) {
                if (!strstr($val, '/')) {
                    $val .= '/';
                } elseif (strstr($val, '/*')) {
                    $val = str_replace('/*', '/', $val);
                }

                foreach ($mimes as $k => $v) {
                    if(strpos($v, $val) === 0) {
                        $newMimes[$k] = $v;
                    }
                }
            }
            $mimes = $newMimes;
        }
        return $mimes;
    }
    public function gettext($translated_text, $text, $domain)
    {
        if ($text == 'Sorry, this file type is not permitted for security reasons.'
         && !empty($_REQUEST['tm-fileselect_validation'])
         && ($vals = $_REQUEST['tm-fileselect_validation']) != false
        ) {
            $name = isset($_REQUEST['name']) ? $_REQUEST['name'] :
                $_FILES['async-upload']['name'];
            return sprintf(
                __('Sorry your file %1$s does not match with the allowed Filetypes (%2$s).'),
                esc_attr($name),
                X\THETOOLS::readableList(esc_attr($_REQUEST['tm-fileselect_validation']))
            );
        }
        return $translated_text;
    }

    public function wp_ajax_tm_fileselect_getfullsize()
    {
        if (!is_admin()
         || !wp_verify_nonce(esc_attr($_REQUEST['nonce']), 'tm-fileselect_fullsize')
        ) {
            exit;
        }
        echo wp_get_attachment_image(esc_attr($_REQUEST['id']), 'full');
        exit;
    }

    private function _get_preview($id, $size = 'thumbnail')
    {
        $attachment_url = wp_get_attachment_url($id);
        if ($attachment_url == false) {
            return false;
        }
        
        $HTML = core\THEBASE::sget_HTML();
        $r = $HTML->sr_li(array(
            'class' => 'tm-fileselect_wrap '.(wp_attachment_is_image($id) ? 'tm-fileselect_wrap_image' : 'tm-fileselect_wrap_file'),
            'id' => 'tm-fileselect_id_'.$id
        ));
        $r .= $HTML->sr_div('.tm-fileselect_buttons_wrap');
        $r .= $HTML->sr_div('.tm-fileselect_buttons');
        $b = $HTML->sr_div('.tm-fileselect_removewrap');
        $b .= $HTML->r_button(__('Remove', 'themaster'), '.button-secondary tm-fileselect_remove');
        $b .= $HTML->r_end();
        $b .= $HTML->sr_div('.tm-fileselect_detailswrap');
        $b .= $HTML->r_a(__('Details', 'themaster'), array(
            'class' => '.button-secondary tm-fileselect_details',
            'href' => sprintf('\./media.php?attachment_id=%s&action=edit', $id),
            'target' => '_blank'
        ));
        $b .= $HTML->r_end(1);
        $b = apply_filters('Xiphe\THEMASTER\FileSelect_buttons', $b, $id);
        if (is_string($b)) {
            $r .= $b;
        }
        $r .= $HTML->r_end(2);
        if (wp_attachment_is_image($id)) {
            $r .= $HTML->rs_a(array(
                'href' => admin_url('admin-ajax.php?').http_build_query(array(
                    'action' => 'tm_fileselect_getfullsize',
                    'id' => $id,
                    'nonce' => wp_create_nonce('tm-fileselect_fullsize')
                )),
                'class' => 'thickbox tm-fileselect_attachmentwrap',
                'data-id' => $id
            ));
            $r .= wp_get_attachment_image($id, $size);
            $r .= $HTML->r_end('.thickbox');
        } else {
            $r .= $HTML->rs_span('.tm-fileselect_attachmentwrap|data-id='.$id);
            $r .= $this->_get_link($id);
            $r .= $HTML->r_end();
        }
        $r .= $HTML->r_end('.tm-fileselect_wrap');
        $r = apply_filters('Xiphe\THEMASTER\FileSelect_preview', $r, $id);
        if (is_string($r)) {
            return $r;
        }
    }

    private function _get_link($id)
    {
		$attachment_url = wp_get_attachment_url($id);
        if ($attachment_url == false) {
            return false;
        }
		$ft = wp_check_filetype($attachment_url);
		$fts = explode('/', $ft['type']);

		return core\THEBASE::sget_HTML()->r_a(
			basename( $attachment_url ),
			array(
				'href' => wp_get_attachment_url($id),
				'class' => 'tm-fileselect_attachment tm-fileselect_attachment-'.$fts[0]
                    .' tm-fileselect_attachment-'.$fts[1]
			)
		);
	}
}
?>