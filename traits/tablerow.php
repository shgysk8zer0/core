<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @subpackage Traits
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
namespace shgysk8zer0\Core\Traits;

/**
 * Trait intended to allow easy building of table rows by iterating over cells
 */
trait TableRow
{
	/**
	 * Creates a table row and populates with cells
	 *
	 * @param  array  $cells     An array of cells to add to the row
	 * @param  string $node_name The tag name for each cell node (default: td)
	 *
	 * @return \DOMElement       The created `<tr>`
	 */
	final protected function _buildRow(array $cells, $node_name = 'td')
	{
		$tr = $this->appendChild(new \DOMElement('tr'));
		foreach ($cells as $n => $cell) {
			if ($cell instanceof \DOMNode) {
				$td = $tr->appendChild(new \DOMElement($node_name));
				$td->appendChild(
					isset($cell->ownerDocumnt)
						? $this->ownerDocument->importNode($cell, true)
						: $cell
				);
			} elseif (
				is_string($cell)
				or is_null($cell)
				or is_numeric($cell)
				or (is_object($cell) and method_exists($cell, '__toString'))
			) {
				$tr->appendChild(new \DOMElement($node_name, $cell));
			} else {
				throw new \InvalidArgumentException(
					sprintf('%s requires either a string or DOMNode, %s given for &lt;%s&gt; #%d', __METHOD__, gettype($cell), $node_name, $n)
				);
			}
		}
		return $tr;
	}
}
