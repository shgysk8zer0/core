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

final class TBody extends \DOMElement
{
	use \shgysk8zer0\Core\Traits\TableRow;

	/**
	 * Create a new <tbody> using DOMElement
	 *
	 * @param void
	 */
	public function __construct()
	{
		parent::__construct('tbody');
	}

	/**
	 * Creates a new `<tr>` from an array of cells
	 *
	 * @param  array  $cells The `<td>`s to create
	 *
	 * @return void
	 */
	public function buildRow(array $cells)
	{
		$this->_buildRow($cells, 'td');
	}
}
