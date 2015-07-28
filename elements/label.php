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
 * Creates a simple <label> that is easy to work with as both a DOMElement as
 * well as an HTML string.
 */
class Label extends \DOMElement
{
	use API\Traits\Magic\DOMAttributes;
	use API\Traits\Magic\Call;
	use API\Traits\Magic\HTML_String;
	use API\Traits\DOMImportHTML;

	/**
	 * Creates a new <label> for an input
	 *
	 * @param string $for        The ID of the <input> this is a label for
	 * @param mixed  $content    Either a string or DOMNode
	 * @param array  $attributes An optional array of additional attributes
	 */
	public function __construct($for, $content = null, array $attributes = array())
	{
		$attributes['for'] = $for;
		$this->_createSelf('label', $attributes);
		if (is_string($content)) {
			$this->importHTML($content);
		} elseif ($content instanceof \DOMNode) {
			$this->appendChild($content);
		}
	}
}
