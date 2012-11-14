<?php 

namespace Xiphe\THEMASTER\classes;
use Xiphe as X;
use Xiphe\THEMASTER\core as core;

/**
 * ResponsiveImages is a PHP Class served by !THE MASTER
 *
 * This class serves verry small images (50px width) on pageload and then loads
 * bigger versions later by using javascript.
 * It can use realpath or Wordpress attachement_IDs as source.
 *
 * @copyright Copyright (c) 2012, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   1.0.2
 * @link      https://github.com/Xiphe/-THE-MASTER/
 * @package   !THE MASTER
 */
class ResponsiveImages extends core\THEWPMASTER {
    /* -------------------- *
     *  INSTANCE VARIABLES  *
     * -------------------- */

    /**
     * This is a singleton.
     * 
     * @var boolean
     */
	public $singleton = true;

	/**
	 * Action Hooks into Wordpress.
	 * @var array
	 */
	protected $actions_ = array(
		'wp_ajax_tm_responsiveimageget' => array(
			'wp_ajax_nopriv_tm_responsiveimageget',
			'wp_ajax_tm_responsiveimageget'
		),
		'wp_ajax_tm_responsiveslideshowget' => array(
			'wp_ajax_nopriv_tm_responsiveslideshowget',
			'wp_ajax_tm_responsiveslideshowget'
		)
	);


    /* -------------------- *
     *  INITIATION METHODS  *
     * -------------------- */

	/**
	 * First initiation.
	 * 
	 * @return void
	 */
	public function init() {
		$this->reg_js('resizeend');
		$this->reg_js('tm-responsiveimages');
		$this->reg_jsVar('ajaxurl', admin_url('admin-ajax.php'));
	}


    /* -------------- *
     *  AJAX METHODS  *
     * -------------- */

	/**
	 * Ajax getter for the full image url.
	 *
	 * @access public
	 * @return void
	 */
	public function wp_ajax_tm_responsiveimageget() {
		/*
		 * Cleanup the path.
		 */
		$img = X\THETOOLS::get_directPath(esc_attr($_REQUEST['image']));

		/*
		 * Verify Nonce.
		 */
		if (!isset($_REQUEST['nonce'])
		 || !X\THEWPTOOLS::verify_noprivnonce(
				esc_attr($_REQUEST['nonce']),
				'tm-responsive',
				$img
			)
		) {
			$this->_exit('error', 'Authentication failed.', 1);
		}

		/*
		 * If image is not an ID - Add the ABSPATH const.
		 */
		if (!is_numeric($img) && defined('ABSPATH')) {
			$img = ABSPATH.$img;
		}

		/*
		 * Get the requested image url and send it to the output array.
		 */
		$this->_r['uri'] = $this->get_url(
			$img,
			esc_attr($_REQUEST['width'])
		);

		/*
		 * Check if the image was available.
		 */
		if ($this->_r['uri'] == false) {
			$this->_exit('error', 'Image not available.', 2);
		}

		/*
		 * Exit script and print the json encoded output array.
		 */
		$this->_exit('ok', 'URI is attached.', 0);
	}

	/**
	 * Ajax getter for the next slideshow-image.
	 *
	 * @access public
	 * @return void
	 */
	public function wp_ajax_tm_responsiveslideshowget() {
		/*
		 * Verify nonce.
		 */
		if (!isset($_REQUEST['nonce'])
		 || !X\THEWPTOOLS::verify_noprivnonce(
				esc_attr($_REQUEST['nonce']),
				'tm-responsive',
				esc_attr($_REQUEST['image'])
			)
		) {
			$this->_exit('error', 'Authentication failed.', 2);
		}

		/*
		 * Get the next image and put it into the output array.
		 */
		$this->_r['img'] = trim($this->get_image(
			esc_attr($_REQUEST['image']),
			esc_attr($_REQUEST['width']),
			(isset($_REQUEST['class']) ? esc_attr($_REQUEST['class']) : null),
			(isset($_REQUEST['id']) ? esc_attr($_REQUEST['id']) : null),
			(isset($_REQUEST['alt']) ? esc_attr($_REQUEST['alt']) : null),
			(isset($_REQUEST['title']) ? esc_attr($_REQUEST['title']) : null)
		));

		/*
		 * Check if the image was available.
		 */
		if (empty($this->_r['img'])) {
			$this->_exit('error', 'Image not available.', 2);
		}

		/*
		 * Exit script and print the json encoded output array.
		 */
		$this->_exit('ok', 'Image is attached', 0);
	}


    /* ---------------- *
     *  GETTER METHODS  *
     * ---------------- */

    /**
     * Getter for an image url in the specified with.
     *
     * @access public
     * @param  string $image attachment ID or image path
     * @param  mixed  $width the targeted image width.
     * @return mixed         the image url or false if image not available.
     */
	public function get_url($image, $width = 'auto')
	{
		if (!($image = $this->_get_baseImageFile($image))) {
			return false;
		}
		$height = $this->_get_dims($image, $width);
		return $this->_get_imageUrl($image, $width, $height);
	}

	/**
	 * Getter for the absolute image file.
	 *
	 * @access public
	 * @param  string  $image attachment ID or image path
	 * @param  mixed   $width the targeted image width.
	 * @param  boolean $round whether or not the end site should be rounded.
	 * @return bookean        the image file path or false if image is not available.
	 */
	public function get_imagefile($image, $width = 'auto', $round = true)
	{
		if (!($image = $this->_get_baseImageFile($image))) {
			return false;
		}
		$height = $this->_get_dims($image, $width, $round);
		return $this->_get_imageFile($image, $width, $height);
	}

	/**
	 * Getter for an array of tag attributes that should be attached to 
	 * an html-tag when its using $image as background image.
	 *
	 * @access public
	 * @param  string $image attachment ID or image path
	 * @param  mixed  $width the targeted image width
	 * @return mixed         the attr array or false if image is not available.
	 */
	public function get_bg_imageAttrs($image, $width = 'auto')
	{
		$slideshow = $this->_is_slideshow($image);

		$origin = $image;
		if (!($image = $this->_get_baseImageFile($image))) {
			return false;
		}

		$ratio;
		$height;
		$loadWidth = $this->_get_loadWidh($image, $width, $height, $ratio);

		$url = $this->get_url(
			$image, 
			$loadWidth
		);

		return array(
			'style' => "background-image: url('$url');",
			'class' => 'tm-responsiveimage tm-responsivebgimage',
			'data-ratio' => $ratio,
			'data-origin' => $origin,
			'data-loaded' => $loadWidth,
			'data-maxwidth' => $width,
			'data-nonce' => X\THEWPTOOLS::create_noprivnonce('tm-responsive', $origin),
		);
	}

	/**
	 * Getter for an responsive image tag.
	 *
	 * @access public
	 * @param  string  $image    attachment ID or image path.
	 * @param  mixed   $width    the targeted image width
	 * @param  mixed   $addClass optional additional classes for the img tag
	 * @param  string  $addId    optional id for the img tag
	 * @param  string  $alt      optional alt attr for the tag. Set to false to disable the alt.
	 * @param  string  $title    optional title attr for the tag. Set to false to disable the title.
	 * @return mixed             the image tag or false on error.
	 */
	public function get_image($image, $width = 'auto', $addClass = false, $addId = null, $alt = null, $title = null)
	{
		/*
		 * Check if $image is array or object initiate slideshow and use first entry as startimage.
		 */
		$slideshow = $this->_is_slideshow($image);

		/*
		 * Check if alt-text is provided or image meta-alt is provided.
		 */
		if ($alt === null && $image == intval($image)) {
			$alt = get_post_meta($image, '_wp_attachment_image_alt', true);
		}

		if ($title === null && $image == intval($image)) {
			$title = get_the_title($image);
		}

		/*
		 * Convert attachment ids into image paths and check if image file is existent.
		 */
		$origin = $image;
		if (!($image = $this->_get_baseImageFile($image))) {
			return false;
		}

		if (!is_numeric($origin) && defined('ABSPATH')) {
			$origin = str_replace(ABSPATH, '', $origin);
		}

		$height;
		$ratio;
		$loadWidth = $this->_get_loadWidh($image, $width, $height, $ratio);

		$url = $this->get_url(
			$image, 
			$loadWidth
		);

		$args = array(
			'src' => $url,
			'class' => trim('tm-responsiveimage'.(!empty($addClass) ? ' '.str_replace('tm-responsiveimage', '', $addClass) : '')),
			'data-ratio' => $ratio,
			'data-origin' => $origin,
			'data-loaded' => $loadWidth,
			'data-maxwidth' => $width,
			'data-nonce' => X\THEWPTOOLS::create_noprivnonce('tm-responsive', $origin),
			'width' => '100%',
			'id' => $addId
		);
		if (isset($alt)) {
			$args['alt'] = $alt;
			$args['data-fixalt'] = true;
		}
		if (isset($title)) {
			$args['title'] = $title;
			$args['data-fixtitle'] = true;
		}
		if ($slideshow) {
			foreach ($slideshow as $k => $ss) {
				if (!is_numeric($ss) && defined('ABSPATH')) {
					$slideshow[$k] = str_replace(ABSPATH, '', $ss);
				}
			}
			$args['data-slideshow'] = implode(',', $slideshow);
			$args['data-slidenonce'] = X\THEWPTOOLS::create_noprivnonce('tm-responsive', $args['data-slideshow']);
		}
		return core\THEBASE::sget_HTML()->r_img($args);
	}

	/**
	 * Echo wrapper for $this->get_image();
	 * 
	 * @access public
	 * @param  string  $image    attachment ID or image path.
	 * @param  mixed   $width    the targeted image width
	 * @param  mixed   $addClass optional additional classes for the img tag
	 * @param  string  $addId    optional id for the img tag
	 * @param  string  $alt      optional alt attr for the tag. Set to false to disable the alt.
	 * @param  string  $title    optional title attr for the tag. Set to false to disable the title.
	 * @return void
	 */
	public function image($image, $width = 'auto', $addClass = false, $addId = null, $alt = null, $title = null) {
		echo $this->get_image($image, $width, $addClass, $addId, $alt, $title);
	}


    /* ------------------ *
     *  INTERNAL METHODS  *
     * ------------------ */

    /**
     * Gets the dimensions in wich the image should be loaded.
     *
     * @access private 
     * @param  string  $image   attachment ID or image path.
     * @param  mixed   $width   the targeted image width
     * @param  mixed   $height  the targeted image height
     * @return intager          the loading width.
     */
	private function _get_loadWidh($image, &$width, &$height, &$ratio)
	{
		/*
		 * Check if image should be delivered in full size directly.
		 * (drct) prefix before actual with.
		 */
		$direct = false;
		if (substr($width, 0, 4) == 'drct') {
			$direct = true;
			$width = intval(str_replace('drct', '', $width));
		}

		/*
		 * Get the potential dimensions.
		 */		
		$height = $this->_get_dims($image, $width, false, $ratio);

		/*
		 * Get the url of mini-thumb or direct full image if drct prefix was set.
		 */
		if ($direct) {
			$loadWidth = $width;
			$this->_get_dims($image, $loadWidth);
		} else {
			$loadWidth = 50;
		}
		return $loadWidth;		
	}

	/**
	 * Checks if the given image is single or slideshow.
	 * 
     * @access private 
	 * @param  string  $image  attachment ID or image path.
	 * @return boolean
	 */
	private function _is_slideshow(&$image)
	{
		$slideshow = false;
		if (is_string($image)) {
			$image = explode(',', $image);
		}
		if (count($image) > 1) {
			$slideshow = array();
			foreach ($image as $k => $img) {
				if (!isset($theimg)) {
					$theimg = $img;
				} else {
					$slideshow[] = $img;
				}
			}
			$image = $theimg;
			if (empty($slideshow)) {
				$slideshow = false;
			} else {
				$slideshow[] = $image;
			}
		} else {
			$image = $image[0];
		}
		return $slideshow;
	}

	/**
	 * Getter for the url of the image in the given dimensions
	 *
     * @access private 
	 * @param  string  $image   attachment ID or image path.
     * @param  mixed   $width   the targeted image width
     * @param  mixed   $height  the targeted image height
	 * @return string           the url
	 */
	private function _get_imageUrl($image, $width, $height)
	{
		return $this->_gen_imageUrlFrom(
			$this->_get_imageFile($image, $width, $height)
		);
	}

	/**
	 * Replaces the ABSPATH in given File with the wordpress installation url
	 * and cleans up the directory separators.
	 *
	 * @access private 
	 * @param  string $file the image file
	 * @return string       the image url
	 */
	private function _gen_imageUrlFrom($file)
	{
		return preg_replace(
			'/[\/\\\\]/',
			'/',
			get_bloginfo('wpurl').'\\'.str_replace(
				ABSPATH,
				'',
				$file
			)
		);
	}

	/**
	 * Getter for the real image filepath.
	 *
	 * If the image does not exist it will be generated.
	 *
	 * @access private 
	 * @param  string $image  the original image file
	 * @param  mixed  $width  the targeted width
	 * @param  mixed  $height the targeted height
	 * @return string         the image filepath
	 */
	private function _get_imageFile($image, $width, $height)
	{
		$file = $this->_build_imageFileName($image, $width, $height);
		if (!file_exists($file)) {
			$this->_gen_image($image, $file, $width, $height);
		}
		return $file;
	}

	/**
	 * Constructs the new image file name by adding -[width]x[height] to the end of the name.
	 * 
	 * @access private 
	 * @param  string $image  the original image file
	 * @param  mixed  $width  the targeted width
	 * @param  mixed  $height the targeted height
	 * @return string         the target image path
	 */
	private function _build_imageFileName($image, $width, $height) {
		return dirname($image).DS.'tm-responsive'.DS.pathinfo($image, PATHINFO_FILENAME)
			.'-'.$width.'x'.$height.'.'.pathinfo($image, PATHINFO_EXTENSION);
	}

	/**
	 * If the targeed image does not exist - this method generates the new, resized image.
	 * 
	 * @access private 
	 * @param  string  $original path to the original image file
	 * @param  string  $target   path where the resized image should be stored
	 * @param  intager $width    the resize width
	 * @param  intager $height   the resize height
	 * @return boolean           true if the image creation was successfull
	 */
	private function _gen_image($original, $target, $width, $height)
	{
		$type = wp_check_filetype($original);
		$type = $type['type'];

		switch ($type) {
			case 'image/gif':
				$original = imagecreatefromgif($original);
				break;
			case 'image/png':
				$original = imagecreatefrompng($original);
				break;
			default:
				$original = imagecreatefromjpeg($original);
				break;
		}
		$new_image = imagecreatetruecolor($width, $height);

		if ($type == 'image/png' || $type == 'image/gif') {
			imagealphablending($new_image, false);
			imagesavealpha($new_image,true);
			$transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
			imagefilledrectangle($new_image, 0, 0, $width, $height, $transparent);
		}

      	imagecopyresampled(
      		$new_image,
      		$original, 
      		0, 0, 0, 0,
      		$width,
      		$height,
      		imagesx($original),
      		imagesy($original)
      	);

      	if (!is_dir(dirname($target))) {
      		mkdir(dirname($target), 0777);
      	}
      	switch ($type) {
			case 'image/gif':
				$r = imagegif($new_image, $target);
				break;
			case 'image/png':
				$r = imagepng($new_image, $target, 0, PNG_NO_FILTER);
				break;
			default:
				$r = imagejpeg($new_image, $target, 100);
				break;
		}
		imagedestroy($original);
		imagedestroy($new_image);
		return $r;
	}

	/**
	 * Geter for the appropriate dimenseions of the target image.
	 * 
	 * @param  string  $image the original image file
	 * @param  mixed   $width the targeted image width
	 * @param  boolean $round whether or not the size should be rounded.
	 * @return intager        the height for the target image.
	 */
	private function _get_dims($image, &$width, $round = true, &$ratio = null)
	{
		$dims = getimagesize($image);
		$height = $dims[1];
		$ratio = round($dims[0]/$dims[1], 4);

		$wWidth = $width;
		$width = $dims[0];

		if ($width == 'auto') {
			return $dims[1];
		}

		if (strstr($wWidth, '%')) {
			$wWidth = floatval('0.'.str_replace('%', '', $wWidth));
			$wWidth = round($dims[0]*$wWidth);
		}

		if ($round) {
			if($wWidth < 200) {
				$rnd = 50;
			} elseif($wWidth < 1000) {
				$rnd = 100;
			} else {
				$rnd = 200;
			}
			$wWidth = ceil(intval($wWidth)/$rnd)*$rnd;
		}
		if ($wWidth > $dims[0]) {
			$wWidth = $dims[0];
			return $dims[1];
		}

		return round($wWidth/$ratio);
	}

	/**
	 * Getter for the original image file.
	 * Converts attachment IDs into real image paths.
	 * 
	 * @param  string $image attachment ID or image path
	 * @return string        the original image path or false on error.
	 */
	private function _get_baseImageFile($image) {
		if (!is_int($image)) {
			if (file_exists($image)) {
				return $image;
			} 
			$image = intval($image);
		}
		$image = wp_get_attachment_metadata($image);
		if (!is_array($image)) {
			return false;
		}
		$ft = wp_check_filetype($image['file']);
		$fts = explode('/', $ft['type']);
		if ($fts[0] != 'image') {
			return false;
		} 
		return WP_CONTENT_DIR.DS.'uploads'.DS.$image['file'];
	}
}
?>