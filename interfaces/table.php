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
namespace shgysk8zer0\Core\Interfaces;

use \shgysk8zer0\Core_API as API;

/**
 * Class for quickly and easily creating HTML <table>s
 *
 * The arguments in the constructor become the valid cells & headers, in order.
 *
 * After that, magic __get() method appends to a $data array
 * if the $key is present in $cells.
 *
 * If you want to continue onto the next row (leaving any unset fileds
 * blank), simply call next_row(). Can also be chained using the magic __call()
 * method, which only sets $data, similarly to __set().
 *
 * @example
 * $table = new table('first_name', 'last_name');
 * $table->first_name = 'John';
 * $table->last_name = 'Smith';
 * $table->foo = 'bar';	//Does nothing
 * $table->nextRow();
 *
 * $table->first_name(
 * 		$fist
 * )->last_name(
 * 		$last
 * )->nextRow();
 * @todo Extend DOMDocument and use taht for building HTML
 * @todo use Core_API traits
 */
interface Table extends API\Interfaces\Magic_Methods, API\Interfaces\toString
{
	/**
	 * Chaninable magic method, in this case only to set values
	 *
	 * Also calls the private set() method too add a value to a field
	 *
	 * @param string $cell
	 * @param array $arguments
	 * @return self
	 * @example $table->$cell[1]($value1)->$cell[2]($value2)...
	 */
	public function __call($cell, array $arguments);

	/**
	 * Method to move to the next row of $data array.
	 * Increments $row, which is used in set() method
	 * when settings data ($data[$row]).
	 *
	 * Also sets the data for that row to an empty
	 * array pre-set with the keys defined by $cells
	 *
	 * @param void
	 * @return self
	 * @example $table->nextRow();
	 */
	public function nextRow();

	/**
	 * Returns all $data as a CSV formatted string
	 *
	 * Uses private buildTable() method to convert $data
	 * array into a <table>
	 *
	 * @param bool $echo
	 * @return mixed (HTML formatted <table> string from $data if $echo is false)
	 */
	public function out($echo = false, $border = false);
}
