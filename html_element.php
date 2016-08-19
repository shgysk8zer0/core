<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @link https://developer.github.com/webhooks/
 * @copyright 2015, Chris Zuber
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

use \shgysk8zer0\Core_API as API;

/**
 * Clean and easy way to create an HTML element. Uses Magic_Methods to deal with attributes
 *
 * @uses \DOMDocument
 * @uses \DOMElement
 */
class HTML_Element implements API\Interfaces\Magic_Methods, API\Interfaces\toString
{
	use API\Traits\Magic_Methods;
	use API\Traits\Magic\Call;

	const MAGIC_PROPERTY = 'attributes';
	const TAGNAME = 'div';
	const VERSION = '1.0';
	const CHARSET = 'UTF-8';

	/**
	 * Array of attributes to set when Element is created
	 * @var array
	 */
	public $attributes = [];

	/**
	 * Tag name of element to create
	 * @var string
	 */
	private $tagname = self::TAGNAME;

	/**
	 * Text content of element created
	 * @var string
	 */
	public $HTML = '';

	/**
	 * Creeates a new HTML element
	 * Sets class properties (attributes are merged to any existing)
	 *
	 * @param string $tagname    Tag name of element to create
	 * @param string $html       Text content of element created
	 * @param array  $attributes Array of attributes to set when Element is created
	 * @example new $el = HTML_Element('a', 'Click Me', ['href' => '#link_here']);
	 */
	public function __construct(
		$tagname = self::TAGNAME,
		$html = '',
		array $attributes = array()
	)
	{
		$this->tagname = "$tagname";
		$this->HTML = "$html";
		$this->attributes = array_merge($attributes, $this->attributes);
	}

	/**
	 * Returns the HTML element as string.
	 * Attributes and text content are set in here.
	 *
	 * @param void
	 * @return string HTML as a string
	 * @example echo $el;
	 */
	public function __toString()
	{
		$DOM = new \DOMDocument(self::VERSION, self::CHARSET);
		$node = new \DOMElement($this->tagname, $this->HTML);
		$DOM->appendChild($node);
		array_map(
			[$node, 'setAttribute'],
			array_keys($this->attributes),
			array_values($this->attributes)
		);
		return $DOM->saveHTML($node);
	}
}
