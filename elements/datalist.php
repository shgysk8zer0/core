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
 * Creates a simple <datalist> that is easy to work with as both a DOMElement as
 * well as an HTML string.
 */
class Datalist extends \DOMElement
{
	use API\Traits\Magic\DOMAttributes;
	use API\Traits\Magic\Call;
	use API\Traits\Magic\HTML_String;

	/**
	 * Creates a new <datalist> along with its <option>s
	 *
	 * @param string $id         The name of the <datalist>
	 * @param array  $options    An array of <option>s
	 */
	public function __construct($id, array $options)
	{
		$this->_createSelf('datalist', array('id' => $id));
		array_map([$this, '_createOption'], $options);
	}

	/**
	 * Appends a new <option>
	 *
	 * @param  string $option The option to add to datalist
	 * @return void
	 */
	protected function _createOption($option)
	{
		$this->appendChild(new \DOMElement('option'))->setAttribute('value', $option);
	}
}
