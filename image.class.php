<?php
	/**
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
	 */

	namespace core;

	class Image {
		protected $image, $fname, $type, $extension;
		public $mime_type;

		public function __construct($fname) {
			$this->fname = $fname;
			if(@file_exists($this->fname)) {
				$this->img_data();
				$this->read_img();
			}
		}

		final protected function img_data() {
			$img_data = getimagesize($this->fname);
			$this->type = $img_data[2];
			$this->mime_type = image_type_to_mime_type($this->type);
			$this->extension = image_type_to_extension($this->type);
		}

		final protected function read_img() {
			switch($this->type) {
				case IMAGETYPE_JPEG: {
					$this->image = imagecreatefromjpeg($this->fname);
				} break;

				case IMAGETYPE_GIF: {
					$this->image = imagecreatefromgif($this->fname);
				} break;

				case IMAGETYPE_PNG: {
					$this->image = imagecreatefrompng($this->fname);
				} break;
			}
		}


		final public function get_width() {
			return imagesx($this->image);
		}

		final public function get_height() {
			return imagesy($this->image);
		}

		final public function rotate($degrees, $bgd_color = 0, $ignore_transparent = 0) {
			$this->image = imagerotate($this->image, $degrees, $bgd_color, $ignore_transparent);
			return $this;
		}

		final public function as_binary($type = IMAGETYPE_JPEG,  $quality = 100, $output = null) {
			$this->type = $type;
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

		final public function as_data_uri() {
			return "data:{$this->mime_type};base64," . base64_encode($this->as_binary($this->type));
		}

		final public function scale($scalar) {

			//$this->image = imagescale($this->image, $scalar * $this->get_width(), $scalar * $this->get_height());
			$this->resize($this->get_width() * $scalar, $this->get_height() * $scalar);
			return $this;
		}

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
			return $this;
		}

		final public function crop($x = 0, $y = 0, $width = null, $height = null) {
			if(is_null($width)) $width = $this->get_width();
			if(is_null($height)) $height = $this->get_height();
			imagecrop($this->image, [
				'x' => $x,
				'y' => $y,
				'width' => $width,
				'height' => $height
			]);
		}

		final public function color($r, $g, $b, $a = 255) {
			return imageColorAllocateAlpha($this->image, $r, $g, $b, $a);
		}

		final public function font($font) {
			return imageloadfont($font);
		}

		final public function text($string, $x = 0, $y = 0, $color = 0, $font = 5) {
			imagestring ($this->image , $font, $x, $y, $string, $color);
			return $this;
		}

		final public function alpha_blending($bool = false) {
			imagealphablending($this->image, $bool);
			return $this;
		}

		final public function save_alpha($bool = true) {
			imagesavealpha($this->image, $bool);
		}

		final public function save($fname, $type = IMAGETYPE_JPEG, $quality = 90) {
			//file_put_contents($this->as_binary($type), $fname);
			$fname = pathinfo($fname, PATHINFO_FILENAME);
			$extension = image_type_to_mime_type($type);
			$this->as_binary($type, $fname . $extension, $quality);
			return $this;
		}

		final public function set_headers() {
			header('Content-Type: ' . $this->mime_type);
			return $this;
		}

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
	}
?>
