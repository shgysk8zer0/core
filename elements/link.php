<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @subpackage Elements
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

use \shgysk8zer0\Core_API as API;

/**
 * Allows <link> elements to be easily created and manipulated
 */
class Link extends \DOMElement
{
	use API\Traits\Magic\DOMAttributes;
	use API\Traits\Magic\Call;
	use API\Traits\Magic\HTML_String;

	/**
	 * Creates a new <link> element with an optional array of attributes.
	 * Appends it to a temporary DOMDocument as required to work with DOMElements,
	 * so be sure to use `importNode` as needed
	 *
	 * @param string $rel        The `rel` attribute for the link
	 * @param string $href       The `href` attribute for the link
	 * @param array  $attributes An optional array of additional attributes
	 */
	public function __construct($rel, $href, array $attributes = array())
	{
		$attributes['rel'] = $rel;
		$attributes['href'] = $href;
		$this->_createSelf('link', $attributes);
	}
}
