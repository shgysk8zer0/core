<?php
	/**
	 * Class to load image, resize/scale/rotate it, convert to other types,
	 * append text, etc.
	 *
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @package core
	 * @link http://php.net/manual/en/ref.image.php
	 * @version 1.0.0
	 * @copyright 2014, Chris Zuber
	 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
	 * This program is free software; you can redistribute it and/or
	 * modify it under the terms of the GNU General Public License
	 * as published by the Free Software Foundation, either version 3
	 * of the License, or (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 *
	 * @var resource $image      [Image data]
	 * @var string   $fname      [/path/to/image]
	 * @var int      $type       [Type from image constants]
	 * @var string   $extension  [Extension with leading "."]
	 * @var string   $mime_type  [image/jpeg|gif|png]
	 */

	namespace shgysk8zer0\core;

	class Image {
		const JPG = IMAGETYPE_JPEG, PNG = IMAGETYPE_PNG, GIF = IMAGETYPE_GIF;
		protected $image, $fname, $type, $extension, $width, $height;
		public $mime_type, $alt='';
		public static $DEFAULT_TYPE = IMAGETYPE_JPEG;

		/**
		 * Create a new instace of class from file
		 *
		 * @param string $fname [path/to/image]
		 */
		public function __construct($fname) {
			$this->fname = $fname;
			if(@file_exists($this->fname)) {
				$this->img_data();
				$this->read_img();
			}
		}

		/**
		 * Get value of protected variables (read-only)
		 *
		 * @param  string $prop  [Name of variable]
		 * @return [mixed]       [Value or null]
		 */
		public function __get($prop) {
			return (isset($this->$prop)) ? $this->$prop : null;
		}

		/**
		 * Check if a given protected variable is set
		 *
		 * @param  string  $prop [Name of variable]
		 * @return boolean       [Whether or not it is set]
		 */
		public function __isset($prop) {
			return isset($this->$prop);
		}

		/**
		 * Called whenever class is used as a string. Returns a full HTML <img/>
		 *
		 * @param void
		 * @return string [HTML <img/> complete with alt, width, & height]
		 */
		public function __toString() {
			return "<img src=\"{$this->as_data_uri($this::$DEFAULT_TYPE)}\" alt=\"{$this->alt}\" width=\"{$this->width}\" height=\"{$this->height}\" />";
		}

		/**
		 * Called whenever class is called as a function. Returns full HTML <figure>
		 * along with <img /> & optional <figcaption>.
		 *
		 * @param  string $caption [Optional <figcaption> content]
		 * @return string          [HTML <figure> with <img /> & <figcaption>]
		 */
		public function __invoke($caption = null) {
			$html = '<figure>' . "{$this}";
			if(is_string($caption)) $html .="<figcaption>{$caption}</figcaption>";
			$html .= '</figure>';
			return $html;
		}

		public static function create($src, $alt = '', $scale = null) {
			$img = new self($src);
			$img->alt = $alt;
			if(is_float($scale)) $img->scale($scale);
			return "{$img}";
		}

		/**
		 * Get image type(IMAGETYPE_*), mime type (image/*) and extension (.*)
		 *
		 * @param void
		 * @return void
		 */
		final protected function img_data() {
			$this->type = getimagesize($this->fname)[2];
			$this->mime_type = image_type_to_mime_type($this->type);
			$this->extension = image_type_to_extension($this->type);
		}

		/**
		 * Reads an image and save as class variable/resource
		 *
		 * @param void
		 * @return void
		 */
		final protected function read_img() {
			switch($this->type) {
				case self::JPG: {
					$this->image = imagecreatefromjpeg($this->fname);
				} break;

				case self::GIF: {
					$this->image = imagecreatefromgif($this->fname);
				} break;

				case self::PNG: {
					$this->image = imagecreatefrompng($this->fname);
				} break;
			}
			$this->width = $this->get_width();
			$this->height = $this->get_height();
		}


		/**
		 * Get current width of $this->image
		 *
		 * @param void
		 * @return int [Image width in pixels]
		 */
		final public function get_width() {
			return imagesx($this->image);
		}

		/**
		 * Get current height of $this->image
		 *
		 * @param void
		 * @return int [Image height in pixels]
		 */
		final public function get_height() {
			return imagesy($this->image);
		}

		/**
		 * Scale $this->image by a factor of $scalar
		 *
		 * @param  float $scalar [Factor to scale by, E.G. 0.5 for half]
		 * @return self
		 */
		final public function scale($scalar) {
			$this->resize($this->get_width() * $scalar, $this->get_height() * $scalar);
			return $this;
		}

		/**
		 * Rotate an image. Must update background.
		 *
		 * @param  float  $degrees             [Angle in degrees]
		 * @param  integer $bgd_color          [From any imagecolor*() function]
		 * @param  integer $ignore_transparent [> 0 means true]
		 * @return self
		 */
		final public function rotate($degrees, $bgd_color = 0, $ignore_transparent = 0) {
			$this->image = imagerotate($this->image, $degrees, $bgd_color, $ignore_transparent);
			return $this;
		}

		/**
		 * Resize an image to an exact width and height
		 *
		 * @param  int $width  [Width in pixels]
		 * @param  int $height [Height in pixels]
		 * @return self        [With $this->image as resized iamge]
		 */
		final public function resize($width, $height) {
			$new_image = imagecreatetruecolor($width, $height);
			imagecopyresampled(
				$new_image,
				$this->image,
				0,
				0,
				0,
				0,
				$width,
				$height,
				$this->get_width(),
				$this->get_height()
			);
			$this->image = $new_image;
			$this->width = $width;
			$this->height = $height;
			return $this;
		}

		/**
		 * Crop an image from $x, $y to $width & $height
		 *
		 * @param  int $x       [Starting x coordinate]
		 * @param  int $y       [Starting y coordinate]
		 * @param  int  $width  [Ending width in pixels]
		 * @param  int  $height [Ending height in pixels]
		 * @return self
		 */
		final public function crop($x = 0, $y = 0, $width = null, $height = null) {
			if(is_null($width)) $width = $this->get_width();
			if(is_null($height)) $height = $this->get_height();
			imagecrop($this->image, [
				'x' => $x,
				'y' => $y,
				'width' => $width,
				'height' => $height
			]);
			$this->width = $width;
			$this->height = $height;
			return $this;
		}

		/**
		 * Get image data as binary string
		 *
		 * @param  int     $type    [From image constants]
		 * @param  int     $quality [Quality or compression level]
		 * @param  string  $output  [Optional output file instead of return]
		 * @return string           [If no $output, returns binary data as string]
		 */
		final public function as_binary($type = IMAGETYPE_JPEG,  $quality = 100, $output = null) {
			$this->type = $type;
			$this->extension = image_type_to_extension($this->type);
			ob_start();
			switch($type) {
				case IMAGETYPE_JPEG: {
					imagejpeg($this->image, $output, $quality);
				} break;

				case IMAGETYPE_GIF: {
					imagegif($this->image, $output, $quality);
				} break;

				case IMAGETYPE_PNG: {
					imagepng($this->image, $output, $quality);
				} break;
			}
			return ob_get_clean();
		}

		/**
		 * Get base64 encoded data-uri from $this->image
		 *
		 * @param void
		 * @return string [Base64 encoded data-URI of image data]
		 */
		final public function as_data_uri($type = null) {
			if(!is_int($type)) {
				$type = $this->type;
			}
			$mime = image_type_to_mime_type($type);
			return "data:{$mime};base64," . base64_encode($this->as_binary($type));
		}

		/**
		 * Save an image to file, automatically setting correct extension
		 *
		 * @param  string  $fname   [Filename to save to. Extension not needed]
		 * @param  int     $type    [From image constants]
		 * @param  int     $quality [Quality or cmpression level]
		 * @return self
		 */
		final public function save($fname, $type = IMAGETYPE_JPEG, $quality = 90) {
			$fname = pathinfo($fname, PATHINFO_FILENAME);
			$extension = image_type_to_mime_type($type);
			$this->as_binary($type, $fname . $extension, $quality);
			return $this;
		}

		/**
		 * Download copy of $this->image, with optional filename & type conversion
		 *
		 * @param  string $fname [Optional name for file]
		 * @param  int    $type  [From image constants]
		 * @return self
		 */
		final public function download($fname = null, $type = null) {
			if(is_null($fname)) $fname = $this->fname;
			if(is_null($type)) $type = $this->type;
			$extension = image_type_to_extension($type);
			$fname = pathinfo($fname, PATHINFO_FILENAME);
			$mime_type = image_type_to_mime_type($type);
			$img = $this->as_binary($type);
			header("Content-Disposition: attachment; filename=\"{$fname}{$extension}\"");
			header("Content-Type: {$mime_type}");
			header('Content-Length:' . strlen($img));
			echo $img;
			return $this;
		}

		/**
		 * Set headers for image output (currently only Content-Type)
		 *
		 * @param void
		 * @return self
		 */
		final public function set_headers() {
			header('Content-Type: ' . $this->mime_type);
			return $this;
		}

		/**
		 * Create a color for working with image (lines, text, etc)
		 *
		 * @param  int     $r [Red]
		 * @param  int     $g [Green]
		 * @param  int     $b [Blue]
		 * @param  int     $a [Alpha/transparency]
		 * @return int
		 */
		final public function color($r, $g, $b, $a = 255) {
			return imageColorAllocateAlpha($this->image, $r, $g, $b, $a);
		}

		/**
		 * Loads a user-defined bitmap and returns its identifier.
		 * @param  string $font [path/to/font.gdf]
		 * @return int
		 */
		final public function font($font) {
			return imageloadfont($font);
		}

		/**
		 * Write text to an image
		 *
		 * @param  string  $string [Text content to write]
		 * @param  int     $x      [X coordinate to start at]
		 * @param  int     $y      [Y coordinate to start at]
		 * @param  int     $color  [From an imageColor*() function]
		 * @param  int     $font   [From imageloadfont() or built-in font]
		 * @return self
		 */
		final public function text($string, $x = 0, $y = 0, $color = 0, $font = 5) {
			imagestring ($this->image , $font, $x, $y, $string, $color);
			return $this;
		}

		/**
		 * Enable/disable alpha blending mode
		 *
		 * @param  bool   $bool [True to enable, false to disable]
		 * @return self
		 */
		final public function alpha_blending($bool = false) {
			imagealphablending($this->image, $bool);
			return $this;
		}

		/**
		 * Enable/disable alhpa transparency (must disable blending to enable)
		 *
		 * @param  bool   $bool [True to enable, false to disable]
		 * @return self
		 */
		final public function save_alpha($bool = true) {
			imagesavealpha($this->image, $bool);
		}

		/**
		 * Single method to enable/disable transparency. Sets save_alpha & alpha_blending
		 * @param  bool   $bool [True to enable, false to disable]
		 * @return self
		 */
		final public function enable_transparency($bool = true) {
			imagealphablending($this->image, !$bool);
			imagesavealpha($this->image, $bool);
			return $this;
		}
	}
?>
