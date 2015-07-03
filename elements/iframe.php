<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 1.0.0
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
namespace shgysk8zer0\Core\Elements;

/**
 * Class to create <iframe>s and work with attributes using magic methods
 */
class Iframe extends \DOMElement
{
	/**
	 * Creates the DOMElement, appends it to a DOMDocument, and sets attributes
	 *
	 * @param string $src        The source of the <iframe>
	 * @param array  $attributes An array of attributes to set
	 */
	public function __construct($src, array $attributes = array())
	{
		parent::__construct('iframe');
		$dom = new \DOMDocument('1.0', 'UTF-8');
		$dom->appendChild($this);
		$this->setAttribute('src', $src);
		foreach ($attributes as $name => $value) {
			$this->setAttribute($name, $value);
		}
	}

	/**
	 * Adds new attribute
	 *
	 * @param string  $name  The name of the attribute.
	 * @param mixed   $value The value of the attribute.
	 * @return void
	 * @see https://secure.php.net/manual/en/domelement.setattribute.php
	 * @example $this->class = 'classname'
	 */
	public function __set($attr, $value)
	{
		$this->setAttribute($attr, $value);
	}

	/**
	 * Returns value of attribute
	 *
	 * @param  string $name The name of the attribute.
	 * @return string       The value of the attribute, or an empty string
	 * @see https://secure.php.net/manual/en/domelement.getattribute.php
	 * @example echo $this->glass // Echoes 'classname'
	 */
	public function __get($attr)
	{
		return $this->getAttribute($attr);
	}

	/**
	 * Checks to see if attribute exists
	 *
	 * @param  string $name The attribute name.
	 * @return bool         TRUE on success or FALSE on failure.
	 * @see https://secure.php.net/manual/en/domelement.hasattribute.php
	 * @example isset($element->class)
	 */
	public function __isset($attr)
	{
		return $this->hasAttribute($attr);
	}

	/**
	 * Removes attribute
	 *
	 * @param string $name The name of the attribute.
	 * @return void
	 * @see https://secure.php.net/manual/en/domelement.removeattribute.php
	 * @example unset($element->class)
	 */
	public function __unset($attr)
	{
		$this->removeAttribute($attr);
	}

	/**
	 * Returns the <iframe>'s HTML as a string
	 *
	 * @param void
	 * @return string HTML for the <iframe>
	 * @see https://secure.php.net/manual/en/domdocument.savehtml.php
	 * @example echo $element // Echoes '<iframe src="" ...></iframe>'
	 */
	public function __toString()
	{
		return $this->ownerDocument->saveHTML($this);
	}
}
