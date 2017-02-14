<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @copyright 2017, Chris Zuber
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
namespace shgysk8zer0\Core;

use shgysk8zer0\Core_API\Traits\FileUploads;

/**
 * Greatly simplifies creating, rotating, scaling, and converting images
 */
class Image extends \ArrayObject implements \JsonSerializable
{
	use \shgysk8zer0\Core_API\Traits\FileUploads;
	const EXTS = [
		'image/jpeg' => 'jpg',
		'image/png'  => 'png',
		'image/gif'  => 'gif',
		'image/webp' => 'webp',
	];

	const SUPPORTED_FONTS_TYPES = [
		'application/x-font-ttf',
	];

	const BLACK       = 0;
	const WHITE       = 16777215;
	const RED         = 16711680;
	const GREEN       = 65280;
	const BLUE        = 255;
	const CYAN        = 65535;
	const MAGENTA     = 16711935;
	const YELLOW      = 16776960;
	const TRANSPARENT = 2130706432;

	/**
	 * Image resource
	 * @var resource
	 */
	private $_handle;

	/**
	 * Array of loaded fonts [$name => $identifier]
	 * @var Array
	 */
	private $_fonts = [];

	/**
	 * Image loaded as brush
	 * @var resouce
	 */
	private $_brush;

	public function __construct($image, Image $from = null)
	{
		parent::__construct(
			(is_resource($image) and isset($from)) ? [
				'mime'      => $from->mime,
				'extension' => $from->extension,
				'basename'  => $from->basename,
				'imageType' => $from->imageType,
			] : [],
			self::ARRAY_AS_PROPS
		);
		if (is_string($image) and file_exists($image)) {
			$this->_loadImageFromFile($image);
		} elseif (is_resource($image)) {
			$this->_handle = $image;
			$this->width = imagesx($image);
			$this->height = imagesy($image);
		} else {
			throw new \InvalidArgumentException("{$image} does not exist");
		}
	}

	/**
	 * Destroy an image when class instance is destroyed
	 */
	public function __destruct()
	{
		imagedestroy($this->_handle);
	}

	/**
	 * Returns image info for debugging functions, such as `var_dump`
	 * @return Array Image info
	 */
	public function __debugInfo(): Array
	{
		return $this->getArrayCopy();
	}

	/**
	 * Returns Array of image info for use with `json_encode`
	 * @return Array Image info
	 */
	public function jsonSerialize(): Array
	{
		return $this->getArrayCopy();
	}

	/**
	 * Returns the Image as a base64 encoded data URI
	 * @return string <img src="data:image/jpeg;base64,..." />
	 */
	public function __toString()
	{
		return sprintf(
			'<img src="%s" width="%d" height="%d" />',
			$this->asBase64(),
			imagesx($this->_handle),
			imagesy($this->_handle)
		);
	}

	/**
	 * Set the thickness for line drawing
	 * @param  Int  $thickness Thickness, in pixels
	 * @return self            Return self to make chainable
	 * @see https://php.net/manual/en/function.imagesetthickness.php
	 * @example $img->setStroke(15)->drawLine(0. 0, 10, 10)->setStroke(1)->...
	 */
	final public function setStroke(Int $thickness): self
	{
		imagesetthickness($this->_handle, $thickness);
		return $this;
	}

	/**
	 * Set the brush image for line drawing
	 * @param Mixed $img Filename, resource, or Image instance
	 * @return self      Return self to make chainable
	 * @see https://secure.php.net/manual/en/function.imagesetbrush.php
	 */
	final public function setBrush($img): self
	{
		unset($this->_brush);
		if (is_string($img) and file_exists($img)) {
			$mime = static::getMimeFromFile($img);
			$brush = static::_loadFromFile($img, $mime);
			imagesetbrush($this->_handle, $brush);
		} elseif (is_resource($img)) {
			imagesetbrush($this->_handle, $img);
		} elseif ($img instanceof self) {
			imagesetbrush($this->_handle, $img->_handle);
		} else {
			throw new \InvalidArgumentException('Failed to load brush.');
		}
		return $this;
	}

	/**
	 * Load a new font
	 * @param  String $file Path to compatible binary font file ('*.gdf')
	 * @return Int          The font identifier which is always bigger than 5
	 * @see https://secure.php.net/manual/en/function.imageloadfont.php
	 */
	final public function loadFont(String $file): Int
	{
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		$name = basename($file, ".{$ext}");
		if ($this->hasFont($name)) {
			return $this->getFont($name);
		} else {
			$font = imageloadfont($file);
			if (is_int($font)) {
				$this->_fonts[$name] = $font;
				return $font;
			} else {
				throw new \InvalidArgumentException("Cannot load $file as a font.");
			}
		}
	}

	/**
	 * Load a set of fonts
	 * @param  String $fonts A list of fonts to load
	 * @return Array         An array of font identifiers (integers > 5)
	 * @example $img->loadFonts('font1.gdf', 'font2.gdf', ...);
	 */
	final public function loadFonts(String ...$fonts): Array
	{
		return array_map([$this ,'loadFont'], $fonts);
	}

	/**
	 * Check if a font has been loaded (using Image::loadFont)
	 * @param  String $font_name Name of font, without extension
	 * @return Bool              Whether or not the font has been loaded
	 */
	final public function hasFont(String $font_name): Bool
	{
		return array_key_exists($font_name, $this->_fonts);
	}

	/**
	 * Get a font identifier for a previously loaded font by name
	 * @param  String $font_name Name of font, without extension
	 * @return Int               The font identifier
	 */
	final public function getFont(String $font_name): Int
	{
		if ($this->hasFont($font_name)) {
			return $this->_fonts[$font_name];
		} else {
			throw new \InvalidArgumentException("Attempting to get an unloaded font, $font_name");
		}
	}

	/**
	 * Draw a character
	 * @param  String  $char     The character to draw
	 * @param  Int     $x        x-coordinate of the start
	 * @param  Int     $y        y-coordinate of the start
	 * @param  Int     $color    A color identifier
	 * @param  integer $font     Fonts identifier (1-5 are built-in fonts. Use loadFont to add more)
	 * @param  Bool    $vertical Write the character vertically instead of horizontally
	 * @return self              Returns self to make chainable
	 * @see https://php.net/manual/en/function.imagechar.php
	 * @see https://php.net/manual/en/function.imagecharup.php
	 */
	final public function setChar(
		String $char,
		Int $x         = 0,
		Int $y         = 0,
		Int $color     = self::BLACK,
		Int $font      = 1,
		Bool $vertical = false
	): self
	{
		if (strlen($char) !== 1) {
			throw new \InvalidArgumentException(sprintf(
				'%s can only set one character at a time, got %n',
				__METHOD__, strlen($char)
			));
		}
		if ($vertical) {
			imagecharup($this->_handle, $font, $x, $y, $char, $color);
		} else {
			imagechar($this->_handle, $font, $x, $y, $char, $color);
		}
		return $this;
	}

	/**
	 * Write a string to an image horizontally
	 * @param  String  $string      The string to write
	 * @param  Int     $x           x-coordinate of the start
	 * @param  Int     $y           y-coordinate of the start
	 * @param  Int     $color       A color identifier
	 * @param  Float   $line_height Scale factor for font height when adding a line of text
	 * @param  integer $font        Fonts identifier (1-5 are built-in fonts. Use loadFont to add more)
	 * @return Array                Coordinates of the current position [$x, $y]
	 * @see https://php.net/manual/en/function.imagestring.php
	 */
	final public function writeString(
		String $string,
		Int    $x          = 0,
		Int    $y          = 0,
		Int    $color      = self::BLACK,
		int    $font       = 1,
		Float  $line_height = 1.3
	): Array
	{
		$lines = explode(PHP_EOL, $string);
		$size = imagefontheight($font);
		foreach ($lines as $line) {
			imagestring($this->_handle, $font, $x, $y, $line, $color);
			$y += $size * $line_height;
		}
		return [$x, $y];
	}

	/**
	 * Write a string to an image vertically
	 * @param  String  $string      The string to write
	 * @param  Int     $x           x-coordinate of the start
	 * @param  Int     $y           y-coordinate of the start
	 * @param  Int     $color       A color identifier
	 * @param  Float   $line_height Scale factor for font height when adding a line of text
	 * @param  integer $font        Fonts identifier (1-5 are built-in fonts. Use loadFont to add more)
	 * @return Array                Coordinates of the current position [$x, $y]
	 * @see https://php.net/manual/en/function.imagestringup.php
	 */
	final public function writeVerticalString(
		String $string,
		Int    $x           = 0,
		Int    $y           = 0,
		Int    $color       = self::BLACK,
		int    $font        = 1,
		Float  $line_height = 1.3
	): Array
	{
		$lines = explode(PHP_EOL, $string);
		$size = imagefontwidth($font);
		foreach ($lines as $line) {
			imagestringup($this->_handle, $font, $x, $y, $line, $color);
			$x += $size * $line_height;
		}
		return [$x, $y];
	}

	/**
	 * Write text to the image using TrueType fonts
	 * @param  String  $text     Text to write
	 * @param  String  $fontfile /path/to/font.ttf
	 * @param  integer $size     The font size in pts
	 * @param  integer $x        Startinge x-coordinate
	 * @param  integer $y        Basele y-coordinate
	 * @param  Int     $color    Color identifier
	 * @param  Float   $angle    Rotation angle in degrees
	 * @return self              Return self to make chainable
	 * @see https://secure.php.net/manual/en/function.imagettftext.php
	 */
	final public function writeWithFont(
		String $text,
		String $fontfile,
		Float  $size     = 14,
		Int    $x        = 12,
		Int    $y        = 30,
		Int    $color    = self::BLACK,
		Float  $angle    = 0
	): self
	{
		$info = pathinfo($fontfile);
		if (!array_key_exists('extension', $info)) {
			$fontfile .= '.ttf';
			$info['extension'] = 'ttf';
		}

		if (!file_exists($fontfile)) {
			throw new \InvalidArgumentException("Could not locate font: {$info['basename']}");
		}
		// $fontfile = realpath($fontfile);
		$mime = static::getMimeFromFile($fontfile);
		if (!in_array($mime, self::SUPPORTED_FONTS_TYPES)) {
			throw new \InvalidArgumentException("Could not load font '{$info['basename']}'' of type $mime");
		}
		imagettftext($this->_handle, $size, $angle, $x, $y, $color, $fontfile, $text);

		return $this;
	}

	/**
	 * Creates an RGB[A] color
	 * @param  Int $rgba ([0,255], [0,255], [0,255], [0,127]?)
	 * @return Int       Color represented as an integer
	 * @see https://php.net/manual/en/function.imagecolordeallocate.php
	 * @see https://php.net/manual/en/function.imagecolorallocatealpha.php
	 */
	final public function rgba(Int ...$rgba): Int
	{
		if (count($rgba) === 3) {
			return imagecolorallocate($this->_handle, ...$rgba);
		} elseif (count($rgba) === 4) {
			return imagecolorallocatealpha($this->_handle, ...$rgba);
		} else {
			throw new \InvalidArgumentException(sprintf(
				'%s expects [3, 4] integer RGB[A] arguments but got %d: %s',
				__METHOD__, count($rgba), join(', ', $rgba)
			));
		}
	}

	/**
	 * Create a color from a hex string, optionally with transparency
	 * @param  String $hex "#?rgb","#?rrggbb", "#?rgba", or "#?rrggbbaa"
	 * @return Int         Color identifier
	 */
	final public function hexColor(String $hex): Int
	{
		$hex = str_replace('#', null, $hex);
		if (strlen($hex) === 3) {
			$rgba = [
				hexdec(substr($hex, 0, 1) . substr($hex, 0, 1)),
				hexdec(substr($hex, 1, 1) . substr($hex, 1, 1)),
				hexdec(substr($hex, 2, 1) . substr($hex, 2, 1))
			];
		} elseif (strlen($hex) === 4) {
			$rgba = [
				hexdec(substr($hex, 0, 1) . substr($hex, 0, 1)),
				hexdec(substr($hex, 1, 1) . substr($hex, 1, 1)),
				hexdec(substr($hex, 2, 1) . substr($hex, 2, 1)),
				hexdec(substr($hex, 3, 1) . substr($hex, 3, 1))
			];
		} elseif (strlen($hex) === 6) {
			$rgba = [
				hexdec(substr($hex, 0, 2)),
				hexdec(substr($hex,2,2)),
				hexdec(substr($hex,4,2))
			];
		} elseif (strlen($hex) === 8) {
			$rgba = [
				hexdec(substr($hex,0,2)),
				hexdec(substr($hex,2,2)),
				hexdec(substr($hex,4,2)),
				hexdec(substr($hex,6,2))
			];
		} else {
			throw new \InvalidArgumentException(sprintf(
				'%s expects a valid 3, 4, 6, or 8 character hex string. Got %s',
				__METHOD__, $hex
			));
		}
		return static::rgba(...$rgba);
	}

	/**
	 * Get the index of the color of a pixel
	 * @param  Int $x x-coordinate of the point
	 * @param  Int $y y-coordinate of the point
	 * @return Int    The index of the color
	 * @see https://php.net/manual/en/function.imagecolorat.php
	 */
	final public function colorAt(Int $x, Int $y): Int
	{
		return imagecolorat($this->_handle, $x, $y);
	}

	/**
	 * Enable or disable interlace
	 * @param  Bool $enabled Enable/disable interlacing
	 * @return self          Returns self to make chainable
	 * @see https://php.net/manual/en/function.imageinterlace.php
	 */
	final public function setInterlace(Bool $enabled = true): self
	{
		imageinterlace($this->_handle, $enabled);
		return $this;
	}

	/**
	* Set the blending mode for an image
	* @param  boolean $blendmode Whether to enable the blending mode or not
	* @return self                Returns self to make chainable
	* @see https://php.net/manual/en/function.imagealphablending.php
	*/
	final public function setAlphaBlending(Bool $blendmode = true): self
	{
		imagealphablending($this->_handle, $blendmode);
		imagesavealpha($this->_handle, !$blendmode);
		return $this;
	}

	/**
	 * Set the flag to save full alpha channel information
	 * @param  Bool $save Sets the flag to attempt to save full alpha channel informatio
	 * @return self       Returns self to make chainable
	 * @see https://secure.php.net/manual/en/function.imagesavealpha.php
	 */
	final public function saveAlpha(Bool $save = true): self
	{
		imagesavealpha($this->_handle, $save);
		imagealphablending($this->_handle, !$save);
		return $this;
	}

	/**
	 * Should antialias functions be used or not
	 * Disables transparency and may cause unexpected results
	 * @param  boolean $enabled Whether to enable antialiasing or not
	 * @return self             Returns self to make chainable
	 * @see https://secure.php.net/manual/en/function.imageantialias.php
	 */
	final public function setAntiAliasing(Bool $enabled = false): self
	{
		imageantialias($this->_handle, $enabled);
		return $this;
	}

	/**
	 * Flood fill
	 * Unlike the native function, this takes $color first, with default
	 * values for $x & $y of 0
	 * @param  Int     $color The fill color (use Image::rgba())
	 * @param  integer $x     x-coordinate of start point
	 * @param  integer $y     y-coordinate of start point
	 * @return self           Returns self to make chainable
	 * @see https://php.net/manual/en/function.imagefill.php
	 */
	final public function fill(Int $color, Int $x = 0, Int $y = 0): self
	{
		imagefill($this->_handle, $x, $y, $color);
		return $this;
	}

	/**
	 * Draw a line
	 * @param  Int  $x1    x-coordinate for first point
	 * @param  Int  $y1    y-coordinate for first point
	 * @param  Int  $x2    x-coordinate for second point
	 * @param  Int  $y2    y-coordinate for second point
	 * @param  Int  $color The line color
	 * @return self        Returns self to make chainable
	 * @see https://php.net/manual/en/function.imageline.php
	 */
	final public function drawLine(
		Int $x1,
		Int $y1,
		Int $x2,
		Int $y2,
		Int $color = self::BLACK
	): self
	{
		imageline($this->_handle, $x1, $y1, $x2, $y2, $color);
		return self;
	}

	/**
	* Draws an arc
	* @param  Int  $cx          x-coordinate of the center
	* @param  Int  $cy           y-coordinate of the center
	* @param  Int  $width        The arc width
	* @param  Int  $height       The arc height
	* @param  Int  $start        The arc start angle, in degrees
	* @param  Int  $end          The arc end angle, in degrees
	* @param  Int  $color        A color identifier
	* @param  Int  $fill_style   Optional IMG_ARC_* constant
	* @return self               Returns self to make chainable
	* @see https://secure.php.net/manual/en/function.imagearc.php
	* @see https://secure.php.net/manual/en/function.imagefilledarc.php
	*/
	final public function drawArc(
		Int $cx,
		Int $cy,
		Int $width,
		Int $height,
		Int $start,
		Int $end,
		Int $color       = self::BLACK,
		Int $fill_style  = null
		): self
		{
			if (isset($fill)) {
				imagefilledarc($this->_handle, $cx, $cy, $width, $height, $start, $end, $color, $fill_style);
			} else {
				imagearc($this->_handle, $cx, $cy, $width, $height, $start, $end, $color);
			}
			return $this;
		}

	/**
	 * Draw a rectangle
	 * @param  Int  $x1    x-coordinate for point 1
	 * @param  Int  $y1    y-coordinate for point 1
	 * @param  Int  $x2    x-coordinate for point 2
	 * @param  Int  $y2    y-coordinate for point 2
	 * @param  Int  $color The fill color (use Image::rgba())
	 * @param  Bool $fill  Whether or not to fill the rectagle with $color
	 * @return self        Returns self to make chainable
	 * @see https://secure.php.net/manual/en/function.imagefilledrectangle.php
	 */
	final public function drawRect(
		Int  $x1,
		Int  $y1,
		Int  $x2,
		Int  $y2,
		Int  $color = self::BLACK,
		Bool $fill  = false
	): self
	{
		if ($fill) {
			imagefilledrectangle($this->_handle, $x1, $y1, $x2, $y2, $color);
		} else {
			imagerectangle($this->_handle, $x1, $y1, $x2, $y2, $color);
		}
		return $this;
	}

	/**
	 * Draw an ellipse
	 * @param  Int  $cx     x-coordinate of the center
	 * @param  Int  $cy     y-coordinate of the center
	 * @param  Int  $width  The ellipse width
	 * @param  Int  $height The ellipse height
	 * @param  Int  $color  The color of the ellipse
	 * @param  Bool $fill   Whether or not to fill the ellipse with $color
	 * @return self         Returns self to make chainable
	 * @see https://secure.php.net/manual/en/function.imageellipse.php
	 */
	final public function drawEllipse(
		Int  $cx,
		Int  $cy,
		Int  $width,
		Int  $height,
		Int  $color = self::BLACK,
		Bool $fill  = false
	): self
	{
		if ($fill) {
			imagefilledellipse($this->_handle, $cx, $cy, $width, $height, $color);
		} else {
			imageellipse($this->_handle, $cx, $cy, $width, $height, $color);
		}
		return $this;
	}

	/**
	 * Draw a circle. Creates an ellipse with the same height and width
	 * @param  Int  $cx     x-coordinate of the center
	 * @param  Int  $cy     y-coordinate of the center
	 * @param  Int  $radius  The radius of the circle
	 * @param  Int  $color  The color of the circle
	 * @param  Bool $fill   Whether or not to fill the circle with $color
	 * @return self         Returns self to make chainable
	 * @see https://secure.php.net/manual/en/function.imageellipse.php
	 */
	final public function drawCircle(
		Int  $cx,
		Int  $cy,
		Int  $radius,
		Int  $color  = self::BLACK,
		Bool $fill   = false
	): self
	{
		return $this->drawEllipse($cx, $cy, $radius, $radius, $color, $fill);
	}

	/**
	 * Draw a filled polygon
	 * @param  Array  $points An array containing the x and y coordinates of the polygons vertices consecutively
	 * @param  Int    $color  The fill color (use Image::rgba())
	 * @param  Bool $fill     Whether or not to fill the polygon with $color
	 * @return self           Returns self to make chainable
	 * @see https://secure.php.net/manual/en/function.imagefilledpolygon.php
	 */
	final public function drawPoly(
		Array $points,
		Int   $color,
		Bool  $fill = false
	): self
	{
		if ($fill) {
			imagefilledpolygon($this->_handle, $points, count($points) / 2, $color);
		} else {
			imagepolygon($this->_handle, $points, count($points) / 2, $color);
		}
		return $this;
	}

	/**
	 * Scale an image using the given new width and height
	 * @param  Int     $width  The width to scale the image to
	 * @param  Int     $height The height to scale the image to. If omitted or negative, the aspect ratio will be preserved
	 * @param  Int  $mode      IMG_NEAREST_NEIGHBOUR, IMG_BILINEAR_FIXED, IMG_BICUBIC, or IMG_BICUBIC_FIXED
	 * @return self            A new image instance created from the scaled image
	 * @see https://secure.php.net/manual/en/function.imagescale.php
	 */
	public function scale(
		Int $width,
		Int $height = -1,
		Int $mode   = IMG_BILINEAR_FIXED
	): self
	{
		return new self(imagescale($this->_handle, $width, $height, $mode), $this);
	}

	/**
	 * Crop an image to the given rectangle
	 * @param  Int  $x      x-coordinate
	 * @param  Int  $y      y-coordinate
	 * @param  Int  $width  Image width
	 * @param  Int  $height Image height
	 * @return self         Cropped image as new Image instance
	 * @see https://php.net/manual/en/function.imagecrop.php
	 */
	final public function crop(Int $x, Int $y, Int $width, Int $height): self
	{
		$img = imagecrop($this->_handle, [
			'x'      => $x,
			'y'      => $y,
			'width'  => $width,
			'height' => $height
		]);
		return new self($img, $this);
	}

	/**
	 * Crop an image automatically using one of the available modes
	 * @param  Int     $mode      An IMG_CROP_* constant
	 * @param  float   $threshold The tolerance in percent to be used while comparing the image color and the color to crop
	 * @param  integer $color     Color identifier
	 * @return self               Cropped image as new Image instance
	 * @see https://secure.php.net/manual/en/function.imagecropauto.php
	 */
	public function autoCrop(
		Int   $mode      = IMG_CROP_DEFAULT,
		Float $threshold = .5,
		Int   $color     = self::TRANSPARENT
	): self
	{
		return new self(imagecropauto($this->_handle, $mode, $threshold, $color), $this);
	}

	/**
	 * Rotate an image with a given angle
	 * @param  Float   $angle              Rotation angle, in degrees (rotates anticlockwise)
	 * @param  Int     $bgd_color          Specifies the color of the uncovered zone after the rotation
	 * @param  boolean $ignore_transparent Ignore transparent colors
	 * @return self                        A new Image class made from the rotated image
	 * @see https://php.net/manual/en/function.imagerotate.php
	 */
	final public function rotate(
		Float $angle,
		Int   $bgd_color         = self::TRANSPARENT,
		Bool  $ignoretransparent = false
	): self
	{
		$rotated = imagerotate($this->_handle, $angle, $bgd_color, $ignoretransparent);
		return new self($rotated, $this);
	}

	/**
	 * Flips an image using a given mode
	 * @param  Int $mode Flip mode: IMG_FLIP_BOTH, IMG_FLIP_VERTICAL, IMG_FLIP_HORIZONTAL
	 * @return self      Returns self to make chainable
	 */
	final public function flip(Int $mode = IMG_FLIP_BOTH): self
	{
		imageflip($this->_handle, $mode);
		return $this;
	}

	/**
	 * Easily save an image to file as the correct type, if supported
	 * @param  Mixed $args  Arguments to pass to the appropriate function
	 * @return Bool         If it was successfully saved
	 */
	public function saveAs(...$args): Bool
	{
		$filename = $args[0];
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		switch(strtolower($ext)) {
			case 'jpg':
			case 'jpeg':
				return call_user_func_array([$this, 'asJPEG'], $args);

			case 'png':
				return call_user_func_array([$this, 'asPNG'], $args);

			case 'gif':
				return call_user_func_array([$this, 'asGIF'], $args);

			case 'webp':
				return call_user_func_array([$this, 'asWebP'], $args);

			default:
				throw new \InvalidArgumentException("{$filename} is not a supported image format.");
		}
	}

	/**
	 * Output or save image as JPEG
	 * @param  String  $to      If set, save to this file
	 * @param  Int     $quality Image quality [0, 100]
	 * @return Bool             Whether or not it was successful
	 * https://secure.php.net/manual/en/function.imagejpeg.php
	 */
	public function asJPEG(String $to = null, Int $quality = 80): Bool
	{
		if (is_null($to)) {
			header('Content-Type: image/jpeg');
		}
		return imagejpeg($this->_handle, $to, $quality);
	}

	/**
	 * Output or save image as PNG
	 * @param  String  $to      If set, save to this file
	 * @param  Int     $quality Compression level [0, 9]
	 * @param  Int     $filters PNG_FILTER_* constants
	 * @return Bool             Whether or not it was successful
	 * @see https://php.net/manual/en/function.imagepng.php
	 */
	public function asPNG(String $to = null, Int $quality = 8, Int $filters = PNG_NO_FILTER): Bool
	{
		if (is_null($to)) {
			header('Content-Type: image/png');
		}
		return imagepng($this->_handle, $to, $quality, $filters);
	}

	/**
	 * Output or save image as GIF
	 * @param  String $to If set, save to this file
	 * @return Bool       Whether or not it was successful
	 * @see https://secure.php.net/manual/en/function.imagegif.php
	 */
	public function asGIF(String $to = null): Bool
	{
		if (is_null($to)) {
			header('Content-Type: image/gif');
		}
		return imagegif($this->_handle, $to);
	}

	/**
	 * Output or save image as WebP
	 * @param  String  $to      If set, save to this file
	 * @param  integer $quality Image quality [0, 100]
	 * @return Bool             Whether or not it was successful
	 */
	public function asWebP(String $to = null, Int $quality = 80): Bool
	{
		if (is_null($to)) {
			header('Content-Type: image/webp');
		}
		return imagewebp($this->_handle, $to, $quality);
	}

	final public function asBase64(Int $format = IMAGETYPE_JPEG): String
	{
		ob_start();
		switch ($format) {
			case IMAGETYPE_JPEG:
				imagejpeg($this->_handle);
				break;

			case IMAGETYPE_PNG:
				imagepng($this->_handle);
				break;

			case IMAGETYPE_GIF:
				imagegif($this->_handle);
				break;

			default:
				throw new \InvalidArgumentException("Unsupported image format");
				break;
		}
		$mime = image_type_to_mime_type($format);
		$img_data = base64_encode(ob_get_clean());
		return "data:{$mime};base64,{$img_data}";
	}

	/**
	 * Check if an image format is supported by Mime-type
	 * @param  String $mime 'image/*'
	 * @return Bool         Whether or not it is supported
	 */
	public static function isSupportedMime(String $mime): Bool
	{
		return array_key_exists($mime, self::EXTS);
	}

	/**
	 * Determine the type of an image
	 * @param  String $filename The image being checked
	 * @return Int              IMAGETYPE_* constant
	 */
	private function _getImageType(String $filename): Int
	{
		return exif_imagetype($filename);
	}

	/**
	 * Loads an image from file and sets image data
	 * @param  String $filename Image to load
	 * @return resource         Image resource
	 */
	private function _loadImageFromFile(String $filename)
	{
		$this->mime      = static::getMimeFromFile($filename);
		$this->_handle   = static::_loadFromFile($filename, $this->mime);
		$this->extension = pathinfo($filename, \PATHINFO_EXTENSION);
		$this->basename  = basename($filename, ".{$this->extension}");
		$this->width     = imagesx($this->_handle);
		$this->height    = imagesy($this->_handle);
		$this->imageType = exif_imagetype($filename);
	}

	/**
	 * Gets a mime-type by inspecting file
	 * @param  String $filename /path/to/file.ext
	 * @return String           image/*
	 */
	public static function getMimeFromFile(String $filename): String
	{
		$finfo = new \FInfo(\FILEINFO_MIME_TYPE);
		return $finfo->file($filename);
	}

	/**
	 * Gets an image resource from a file using its mime-type
	 * @param  String $filename /path/to/img.ext
	 * @param  String $mime     image/*
	 * @return resource         An image resource created from the file
	 */
	private static function _loadFromFile(String $filename, String $mime)
	{
		switch($mime) {
			case 'image/jpeg':
				$img = imagecreatefromjpeg($filename);
				break;

			case 'image/png':
				$img = imagecreatefrompng($filename);
				break;

			case 'image/gif':
				$img = imagecreatefromgif($filename);
				break;

			case 'image/webp':
				$image = imagecreatefromwebp($filename);
				break;

			default:
				throw new \Exception("$filename is not a valid image. [{$this->mime}]");
		}
		return $img;
	}

	/**
	 * Create a new, blank image
	 * @param  Int  $width  Intial width of the image
	 * @param  Int  $height Intial height of the image
	 * @return self         Newly created image
	 */
	final public static function create(Int $width, Int $height): self
	{
		$img = imagecreatetruecolor($width, $height);
		return new self($img);
	}

	/**
	 * Create an image from an already validated file upload
	 * @param  UploadFile $file Instance of UploadFile
	 * @return self             Instance of Image created from $file
	 */
	final public static function fromUpload(UploadFile $file): self
	{
		$img = new self($file->tmp_name);
		$img->extension = pathinfo($file->name, PATHINFO_EXTENSION);
		$img->basename = basename($file->name, ".{$img->extension}");
		return $img;
	}

	/**
	 * Returns an array of Images from `$_FILES[$key]`
	 * @param  String $key Array key from `$_FILES`
	 * @return Array       [Image, Image, ...]
	 */
	public static function getAllUploads(String $key): Array
	{
		return array_map(function(Array $file): self
		{
			return static::fromUpload(new UploadFile($file));
		}, static::normalizeUploads()[$key] ?? []);

		return new self($file->tmp_name);
	}

	/**
	 * Create a new image from the image stream in the string
	 * @param  String $string A string containing the image data
	 * @return self           A new instance of image created from the string
	 * @see https://secure.php.net/manual/en/function.imagecreatefromstring.php
	 */
	final public static function loadFromString(String $string): self
	{
		return new self(imagecreatefromstring($string));
	}

	/**
	 * Convert and optimize uploaded images in various sizes & formats, saving to $dir
	 * @param  String $key     $_FILES[$key]
	 * @param  Array  $dir     Output directory ['path', 'to', 'outout']: 'path/to/output'
	 * @param  Array  $sizes   Array of sizes for scaling results
	 * @param  Array  $fomrats ['image/jpeg', ...]
	 * @return Array           Array of resulting image data
	 */
	final public static function responsiveImagesFromUpload(
		String $key,
		Array  $dir,
		Array  $sizes   = array(1200, 800, 400),
		Array  $formats = array('image/jpeg', 'image/webp')
	): Array
	{
		$dir = join(DIRECTORY_SEPARATOR, $dir);
		$formats = array_filter($formats, __CLASS__ . '::isSupportedMime');
		if (!is_dir($dir) and !mkdir($dir, 0755, true)) {
			throw new \RuntimeException("$dir does not exist and could not be created");
		} elseif (!is_writable($dir)) {
			throw new \RuntimeException("Could not write to directory, '$dir'");
		}
		$images = static::getAllUploads($key);
		return array_reduce($images, function(Array $carry, Image $image) use ($sizes, $formats, $dir): Array
		{
			$carry[$image->basename] = array_reduce($formats, function(Array $carry, String $format) use ($image, $sizes, $dir): Array
			{
				$carry[$format] = array_reduce($sizes, function(Array $carry, Int $size) use ($image, $format, $dir): Array
				{
					if ($image->width < $size) {
						return $carry;
					} else {
						$ext = self::EXTS[$format];
					}
					$name = "{$dir}/{$image->basename}-{$size}.{$ext}";
					$cp = $image->scale($size);

					if ($cp->saveAs($name)) {
						$carry[$size] = [
							'path'   => "/{$name}",
							'height' => $cp->height,
							'width'  => $cp->width,
							'type'   => $format,
							'size'   => filesize($name),
						];
					} else {
						trigger_error("Failed to save '$na,me'");
					}
					return $carry;
				}, []);
				return $carry;
			}, []);
			return $carry;
		}, []);
	}
}
