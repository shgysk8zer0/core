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
namespace shgysk8zer0\Core;

use \shgysk8zer0\Core_API as API;

/**
 * Extends DOMElement with magic methods
 */
class HTML_El extends \DOMElement implements API\Interfaces\String, API\Interfaces\MagicDOM
{
	use API\Traits\Magic\DOMElement;
	use API\Traits\Magic\Call_Setter;
	use API\Traits\MAgic\HTML_String;

	/**
	 * Created a new HTML Element
	 *
	 * @param string $name         The tag name of the element
	 * @param string $value        The value of the element.
	 * @param string $namespaceURI A namespace URI to create the element within a specific namespace.
	 * @param bool   $createDoc    Whether or not to create parent document and append to
	 */
	public function __construct(
		$name         = 'div',
		$value        = null,
		$namespaceURI = null,
		$createDoc    = false
	)
	{
		parent::__construct($name, $value, $namespaceURI);
		if ($createDoc) {
			(new \DOMDocument)->appendChild($this);
		}
	}
}
