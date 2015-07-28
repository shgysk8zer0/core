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

use \shgysk8zer0\Core_API as API;

/**
 * Class for creating <img>s in HTML, automatially attempting to get dimensions
 */
class Image extends \DOMElement implements API\Interfaces\Magic_Methods, API\Interfaces\String
{
	use API\Traits\Magic\DOMAttributes;
	use API\Traits\Magic\Call;

	public function __construct($src, $alt = '', array $attributes = array())
	{
		if ($dimensions = @getimagesize($src)) {
			$attributes['width'] = $dimensions[0];
			$attributes['height'] = $dimensions[1];
		}
		$attributes['src'] = $src;
		$attributes['alt'] = $alt;
		$this->_createSelf('img', $attributes);
	}
}
