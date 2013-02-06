<?php
namespace Xiphe\THEMASTER\classes;

use Xiphe\THEMASTER\core as core;
use Xiphe as X;

/**
 * Provides themes and plugins with a form interface to select a file from the Media Library.
 * [Original Plugin](http://sltaylor.co.uk/wordpress/plugins/slt-file-select/)
 *
 * 
 * This program is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by 
 * the Free Software Foundation; version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
 * GNU General Public License for more details. 
 * 
 * You should have received a copy of the GNU General Public License 
 * along with this program; if not, write to the Free Software 
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
 *
 * 
 * @author   Idea & Original Plugin: [Steve Taylor](http://sltaylor.co.uk)
 *           Heavy Adjustments and Conversion into TM-Class: [Hannes Diercks](info@xiphe.net)
 * @version  0.2.1
 * @license  GPLv2
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

        $validation = $this->sanitizeValidation($validation);

    	$HTML->s_div('.tm-fileselect_buttonwrap')
    		->button(__('Upload/Choose', 'themaster'), '.button-secondary tm-fileselect_button')
            ->hidden('.tm-fileselect_value|name='.esc_attr($name).'|value='.esc_attr($value));

            if (is_object($GLOBALS['post'])) {
                $HTML->hidden('.tm-fileselect_parent_id|name=tm-fileselect_parent_id|value='.$HTML->esc($GLOBALS['post']->ID));
            }

            $HTML->hidden(array(
                'class' => 'tm-fileselect_previewsize',
                'name' => 'tm-fileselect_previewsize',
                'value' => $previewSize
            ))
            ->hidden(array(
                'class' => 'tm-fileselect_validation',
                'name' => 'tm-fileselect_validation',
                'value' => $validation
            ))
            ->hidden(array(
                'class' => 'tm-fileselect_validation_nonce',
                'name' => 'tm-fileselect_validation_nonce',
                'value' => wp_create_nonce('tm-fileselect-allow:'.$validation)
            ))
            ->hidden(array(
                'class' => 'tm-fileselect_multiple',
                'name' => 'tm-fileselect_multiple',
                'value' => $multiple
            ))
            ->hidden(array(
                'class' => 'tm-fileselect_nonce',
                'name' => 'tm-fileselect_nonce',
                'value' => wp_create_nonce('tm-fileselect_getfile')
            ))
        ->end()
        ->sg_ul('#'.esc_attr($name).'_preview|.tm-fileselect_preview');
    		if (!empty($value)) {
                if($multiple) {
                    $values = explode(',', esc_attr($value));
                } else {
                    $values = array(esc_attr($value));
                }
                foreach ($values as $value) {
                    if (empty($value)) {
                        continue;
                    }
                    extract($this->parseAttachementData($value));
                    echo $this->_get_preview($namespace, $id, esc_attr($previewSize));
                }
            }
        $HTML->end('#'.esc_attr($name).'_preview')->clear()
        ->end('.tm-fileselect_fullwrap');
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


    public function validateTypeFor($attachments, $validTypes) {
        if (!is_array($attachments)) {
            $attachments = array($attachments);
        }
        $validTypes = explode('|', $validTypes);
        $allOk = true;

        foreach ($attachments as $k => $attachment) {
            if (is_object($attachment)) {
                $attachmentID = $attachment->id;
            } else {
                $attachmentID = $attachment;
            }

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

    public function sanitizeValidation($val)
    {
        if (is_string($val)) {
            $val = preg_replace('/[\\\\\/]+/', '/', $val);
        }
        return $val;
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

    public function get_typeErrorMessageFor($attachments)
    {
        if (!is_array($attachments)) {
            $attachments = array($attachments);
        }

        $valids = array();
        $files = array();
        foreach ($attachments as $k => $attachment) {
            if (is_object($attachment)) {
                $id = $attachment->id;
            } else {
                $id = $attachment;
            }

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

    public function bind_attachment($attachments, $postID) {
        if (!is_array($attachments)) {
            $attachments = explode(',', $attachments);
        }

        global $wpdb;
        foreach ($attachments as $k => $attachment) {
            if (is_object($attachment)) {
                $id = $attachment->id;
            } else {
                $id = $attachment;
            }

            $post = get_post($id);
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
                'save' => __('Save', 'themaster'),
                'selectAll' => __('Select all', 'themaster'),
                'unselectAll' => __('Remove selection', 'themaster')
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
        /* validate the request */
        if (!is_admin()
         || !wp_verify_nonce(esc_attr($_REQUEST['nonce']), 'tm-fileselect_getfile')
         || empty($_REQUEST['id'])
        ) {
            $this->_exit('error', 'unauthenticated', 1);
        }

        /* Ensure a the post is present */
        if ((!isset($GLOBALS['post']) || !is_object($GLOBALS['post']))
            && isset($_REQUEST['parent_id'])
        ) {
            $GLOBALS['post'] = get_post(intval($_REQUEST['parent_id']));
        }

        /* Parse an attachment list from input */
        $input = explode(',', esc_attr($_REQUEST['id']));

        $size = esc_attr($_REQUEST['size']);

        /* Loop though the namespaces */
        foreach($input as $data) {
            if (empty($data)) {
                continue;
            }
            extract($this->parseAttachementData($data));
            $this->_get_preview($namespace, $id, $size);
        }

        exit;
    }

    private function _get_preview($namespace, $id, $size = 'medium')
    {
        if (empty($size)) {
            $size = 'medium';
        }

        if ($namespace === 'fileselect') {
            $attachment_url = wp_get_attachment_url($id);
            if ($attachment_url == false) {
                return false;
            }
        } elseif (do_action("xiphe_themaster_fileselect_validate_$namespace", $id) === false) {
            return false;
        }

        $r = core\THEBASE::get_view('fileselect/open_preview', compact('namespace', 'id'));

        if ($namespace === 'fileselect') {
            $attachment_markup = core\THEBASE::get_view(
                'fileselect/preview',
                compact('namespace', 'id', 'size')
            );
        } else {
            ob_start();
            do_action(
                "xiphe_themaster_fileselect_preview_$namespace",
                $id,
                esc_attr($_REQUEST['size'])
            );
            $attachment_markup = ob_get_clean();
        }

        $attachment_markup = apply_filters('xiphe_themaster_fileselect_attachment', $attachment_markup);
        if (empty($attachment_markup)) {
            return false;
        }
        $r .= $attachment_markup;


        
        $r .= self::sget_HTML()->r_end('.tm-fileselect_wrap');
        $r = apply_filters('xiphe_themaster_fileselect_preview', $r, $id);

        if (is_string($r)) {
            echo $r;
        }
    }

    public function parseInputData($data) {
        /* The return object */
        $r = array();

        if (!is_array($data)) {
            $data = explode(',', $data);
        }

        /* Loop though all inputs (separated by commas) */
        foreach ($data as $i => $attachment) {
            /* Ignore empty */
            if (empty($attachment)) {
                continue;
            }

            extract($this->parseAttachementData($attachment));

            /* build a new entry into the return array */
            $r[$namespace][] = (object) array(
                'id' => $id,
                'position' => $i
            );
        }

        return $r;
    }

    public function parseAttachementData($input) {
        /* split into namespace and id */
        $r = array();

        if (empty($input)) {
            return array();
        }

        preg_match('/[a-z_]+/', $input, $namespace);
        $r['namespace'] = trim($namespace[0], '_');
        $r['id'] = intval(str_replace($r['namespace'].'_', '', $input));

        return $r;
    }

    public function attachNamespaces($data)
    {
        $r = array();

        foreach ($data as $namespace => $attachments) {
            foreach ($attachments as $data) {
                $r[$data->position] = "{$namespace}_{$data->id}";
            }
        }
        ksort($r);

        return $r;
    }

    public function toString($data)
    {
        return implode(',', $this->attachNamespaces($data));
    }

    public function upload_mimes($mimes)
    {
        $mimes['svg|svgz'] = 'image/svg+xml';

        if (empty($_REQUEST['tm-fileselect_validation'])) {
            return $mimes;
        }

        $allowed = $this->sanitizeValidation($_REQUEST['tm-fileselect_validation']);

        if (!wp_verify_nonce($_REQUEST['tm-fileselect_validation_nonce'], 'tm-fileselect-allow:'.$allowed)) {
            return array();
        }

        $newMimes = array();
        $allowed = explode('|', $allowed);
        foreach ($allowed as $allow) {
            if (!strstr($allow, '/')) {
                $allow .= '/';
            } elseif (strstr($allow, '/*')) {
                $allow = str_replace('/*', '/', $allow);
            }

            foreach ($mimes as $k => $v) {
                if(strpos($v, $allow) === 0) {
                    $newMimes[$k] = $v;
                }
            }
        }
        $mimes = $newMimes;

        return $mimes;
    }

    public function gettext($translated_text, $text, $domain)
    {
        if ($text == 'Sorry, this file type is not permitted for security reasons.'
         && !empty($_REQUEST['tm-fileselect_validation'])
         && ($vals = $_REQUEST['tm-fileselect_validation']) != false
        ) {
            $vals = $this->sanitizeValidation($vals);
            $name = isset($_REQUEST['name']) ? $_REQUEST['name'] :
                $_FILES['async-upload']['name'];
            return sprintf(
                __('Sorry your file %1$s does not match with the allowed Filetypes (%2$s).'),
                esc_attr($name),
                X\THETOOLS::readableList($vals)
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

    public function get_link($id)
    {
        $post = get_post($id);
		$attachment_url = wp_get_attachment_url($id);
        if ($attachment_url == false) {
            return false;
        }
		$ft = wp_check_filetype($attachment_url);
		$fts = explode('/', $ft['type']);

        $HTML = core\THEBASE::sget_HTML();
        $r = $HTML->sr_a(array(
            'href' => wp_get_attachment_url($id),
            'class' => 'tm-fileselect_attachment tm-fileselect_attachment-'.$fts[0]
                .' tm-fileselect_attachment-'.$fts[1]
        ));

        $r .= $HTML->r_span($post->post_title, '.tm-fileselect_attachment-title');
        $r .= $HTML->r_end();

        $r .= $HTML->r_br();
        $r .= $HTML->r_span(basename($attachment_url), '.hidden tm-fileselect_attachment-file');

		return $r;
    }
}