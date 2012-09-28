<?php 
namespace Xiphe\THEMASTER;

class ResponsiveImages extends THEWPMASTER {
	public $singleton = true;

	protected $actions_ = array(
		'wp_ajax_tm_responsiveimageget',
		'wp_ajax_tm_responsiveimageget' => 'wp_ajax_nopriv_tm_responsiveimageget',
		'wp_ajax_tm_responsivslideshowget',
		'wp_ajax_tm_responsivslideshowget' => 'wp_ajax_nopriv_tm_responsivslideshowget',
	);

	public function init() {
		$this->reg_js('resizeend');
		$this->reg_js('tm-responsiveimages');
		$this->reg_jsVar('ajaxurl', admin_url('admin-ajax.php'));
	}

	public function wp_ajax_tm_responsiveimageget() {
		$img = THETOOLS::get_directPath(esc_attr($_REQUEST['image']));
		if (!isset($_REQUEST['nonce'])
		 || !THEWPTOOLS::verify_noprivnonce(
				esc_attr($_REQUEST['nonce']),
				'tm-responsive',
				$img
			)
		) {
			$this->_exit('error', 'Authentication failed.', 1);
		}

		if (!is_numeric($img) && defined('ABSPATH')) {
			$img = ABSPATH.$img;
		}

		$this->_r['uri'] = $this->get_url(
			$img,
			esc_attr($_REQUEST['width'])
		);
		if ($this->_r['uri'] == false) {
			$this->_exit('error', 'Image not available.', 2);
		}
		$this->_exit('ok', 'URI is attached.', 0);
	}

	public function wp_ajax_tm_responsivslideshowget() {
		if (!isset($_REQUEST['nonce'])
		 || !THEWPTOOLS::verify_noprivnonce(
				esc_attr($_REQUEST['nonce']),
				'tm-responsive',
				esc_attr($_REQUEST['image'])
			)
		) {
			$this->_exit('error', 'Authentication failed.', 2);
		}
		$this->_r['img'] = trim($this->get_image(
			esc_attr($_REQUEST['image']),
			esc_attr($_REQUEST['width']),
			(isset($_REQUEST['class']) ? esc_attr($_REQUEST['class']) : null),
			(isset($_REQUEST['id']) ? esc_attr($_REQUEST['id']) : null),
			(isset($_REQUEST['alt']) ? esc_attr($_REQUEST['alt']) : null),
			(isset($_REQUEST['title']) ? esc_attr($_REQUEST['title']) : null)
		));

		$this->_exit('ok', 'Image is attached', 0);
	}

	public function get_url($image, $width = 'auto')
	{
		if (!($image = $this->_get_baseImageFile($image))) {
			return false;
		}
		$height = $this->_get_dims($image, $width);
		return $this->_get_imageUrl($image, $width, $height);
	}

	public function get_imagefile($image, $width = 'auto', $round = true)
	{
		if (!($image = $this->_get_baseImageFile($image))) {
			return false;
		}
		$height = $this->_get_dims($image, $width, $round);
		return $this->_get_imageFile($image, $width, $height);
	}


	public function get_bg_imageAttrs($image, $width = 'auto')
	{
		$slideshow = $this->_is_slideshow($image);

		$origin = $image;
		if (!($image = $this->_get_baseImageFile($image))) {
			return false;
		}

		$height;
		$loadWidth = $this->_get_loadWidh($image, $width, $height);

		$url = $this->get_url(
			$image, 
			$loadWidth
		);

		return array(
			'style' => "background-image: url('$url');",
			'class' => 'tm-responsiveimage tm-responsivebgimage',
			'data-width' => $width,
			'data-height' => $height,
			'data-ratio' => ($height/$width),
			'data-origin' => $origin,
			'data-loaded' => $loadWidth,
			'data-template' => $this->_gen_imageUrlFrom(
				$this->_build_imageFileName($image, ':w', ':h')
			),
			'data-nonce' => THEWPTOOLS::create_noprivnonce('tm-responsive', $origin),
		);
	}


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
		$loadWidth = $this->_get_loadWidh($image, $width, $height);

		$url = $this->get_url(
			$image, 
			$loadWidth
		);

		$args = array(
			'src' => $url,
			'class' => trim('tm-responsiveimage'.(!empty($addClass) ? ' '.str_replace('tm-responsiveimage', '', $addClass) : '')),
			'data-width' => $width,
			'data-height' => $height,
			'data-ratio' => ($height/$width),
			'data-origin' => $origin,
			'data-loaded' => $loadWidth,
			'data-template' => $this->_gen_imageUrlFrom(
				$this->_build_imageFileName($image, ':w', ':h')
			),
			'data-nonce' => THEWPTOOLS::create_noprivnonce('tm-responsive', $origin),
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
			$args['data-slidenonce'] = THEWPTOOLS::create_noprivnonce('tm-responsive', $args['data-slideshow']);
		}
		return THEBASE::sget_HTML()->r_img($args);
	}

	public function image($image, $width = 'auto', $addClass = false, $addId = null, $alt = null, $title = null) {
		echo $this->get_image($image, $width, $addClass, $addId, $alt, $title);
	}

	private function _get_loadWidh($image, &$width, &$height)
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
		$height = $this->_get_dims($image, $width, false);

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

	private function _get_imageUrl($image, $width, $height)
	{
		return $this->_gen_imageUrlFrom(
			$this->_get_imageFile($image, $width, $height)
		);
	}

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

	private function _get_imageFile($image, $width, $height)
	{
		$file = $this->_build_imageFileName($image, $width, $height);
		if (!file_exists($file)) {
			$this->_gen_image($image, $file, $width, $height);
		}
		return $file;
	}

	private function _build_imageFileName($image, $width, $height) {
		return dirname($image).DS.'tm-responsive'.DS.pathinfo($image, PATHINFO_FILENAME)
			.'-'.$width.'x'.$height.'.'.pathinfo($image, PATHINFO_EXTENSION);
	}

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
      		mkdir(dirname($target));
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

	private function _get_dims($image, &$width, $round = true)
	{
		$dims = getimagesize($image);
		if ($width == 'auto') {
			$width = $dims[0];
			return $dims[1];
		}

		if (strstr($width, '%')) {
			$width = floatval('0.'.str_replace('%', '', $width));
			$width = round($dims[0]*$width);
		}

		if ($round) {
			if($width < 200) {
				$rnd = 50;
			} elseif($width < 1000) {
				$rnd = 100;
			} else {
				$rnd = 200;
			}
			$width = ceil(intval($width)/$rnd)*$rnd;
		}
		if ($width > $dims[0]) {
			$width = $dims[0];
			return $dims[1];
		}
		return round($width/($dims[0]/$dims[1]));
	}

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