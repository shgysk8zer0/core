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
use \shgysk8zer0\Core as Core;

/**
 * Class for quickly and easily creating HTML <table>s
 *
 * The arguments in the constructor become the valid cells & headers, in order.
 *
 * After that, magic __get() method appends to a $data array
 * if the $key is present in $headers.
 *
 * If you want to continue onto the next row (leaving any unset fileds
 * blank), simply call nextRow(). Can also be chained using the magic __call()
 * method, which only sets $data, similarly to __set().
 *
 * @example
 * $table = new Table('first_name', 'last_name');
 * // or $table = new Table(['first_name', 'last_name']);
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
 *
 * $table([...], ...);
 *
 * echo $table
 * @todo Extend DOMDocument and use that for building HTML
 * @todo Set array_keys of $_data and preserve them
 */
final class Table
implements Core\Interfaces\Table
{
	use API\Traits\Magic_Methods;
	use API\Traits\Magic\Call_Setter;

	const MAGIC_PROPERTY   = '_data';

	//const RESTRICT_SETTING = true;

	/**
	 * Array to contain data for current row
	 * @var array
	 */
	protected $_data = [];

	/**
	 * Array for valid keys for arrays. Becomes table's header & footer <th>'s
	 * @var array
	 */
	private $headers =[];

	/**
	 * Array containing keys from $headers with all null values
	 * @var array
	 */
	private $empty_row = [];

	/**
	 * Array of $data arrays, filtered to only include those in $headers
	 * @var array
	 */
	protected $_table_data = [];

	/**
	 * Optional table caption (if set & string)
	 * @var string
	 */
	public $caption;

	/**
	 * Whether or not to set the border attribute on <table>
	 * @var mixed
	 */
	public $border = false;

	/**
	 * <table> element
	 * @var HTML_El
	 */
	private $_table;

	/**
	 * <thead> element
	 * @var HTML_El
	 */
	private $_thead;

	/**
	 * <tfoot> element
	 * @var HTML_El
	 */
	private $_tfoot;

	/**
	 * <tbody> element
	 * @var HTML_El
	 */
	private $_tbody;

	/**
	 * Sets up default values for class
	 *
	 * $empty_row as an associative array with its keys defined by $headers,
	 * but all of its values null
	 *
	 * @param mixed ...
	 * @example $table = new table($cells[] | 'field1'[, ...])
	 */
	public function __construct()
	{
		$this->headers = array_filter(flatten(func_get_args()), 'is_string');
		$this->empty_row = array_combine(
			$this->headers,
			array_pad([], count($this->headers), null)
		);
		$this->_table = new Core\HTML_El('table', null, null, true);
	}

	/**
	 * Called whenever $table is used as a string and returns <table>
	 *
	 * @param void
	 * @return string Table's HTML
	 * @example echo $table
	 * @example $var = "$table"
	 * @todo convert to using \DOMElement
	 */
	public function __toString()
	{
		if (! empty($this->_data)) {
			$this->nextRow();
		}

		$table = is_int($this->border)
			? "<table border=\"{$this->border}\">"
			: '<table>';

		if (is_string($this->caption)) {
			$table .= "<caption>{$this->caption}</caption>";
		}

		$headers = $this::buildRow($this->headers, 'th');
		$table .= "<thead>{$headers}</thead>";
		$table .= "<tfoot>{$headers}</tfoot>";

		unset($headers);

		$table .= '<tbody>';
		foreach ($this->_table_data as $row) {
			$table .= $this::buildRow($row);
		}
		$table .= '</tbody>';
		return $table . '</table>';
	}

	/**
	 * Sets any number of rows of data at once using func_get_args
	 *
	 * @param array ...
	 * @return self
	 * @example $table([...], ...);
	 */
	public function __invoke()
	{
		array_map(
			function(array $cols = array())
			{
				array_map([$this, '__set'],
					array_keys($cols),
					array_values($cols)
				);
				$this->nextRow();
			},
			func_get_args()
		);
		return $this;
	}

	/**
	 * Filters $data for row and pushes to the $table array
	 *
	 * @param void
	 * @return self
	 * @example $table->nextRow();
	 * @todo Throw an InvalidArgumentException for all values set incorrectly
	 */
	public function nextRow()
	{
		$this->data = array_merge(
			$this->empty_row,
			array_intersect_key($this->_data, $this->empty_row)
		);

		if (! empty($this->data)) {
			$this->_table_data[] = $this->data;
		}

		$this->_data = [];

		return $this;
	}

	/**
	 * Builds and returns a table row from an array
	 *
	 * @param array  $content   Array of content/innerHTML for child elements
	 * @param string $tag       Tag name for child elements
	 * @param string $parent_el Tag name for parent element
	 * @todo convert to using \DOMElement
	 * @deprecated
	 */
	private static function buildRow(
		array $content = array(),
		$tag = 'td',
		$parent_el = 'tr'
	)
	{
		return array_reduce(
			$content,
			function($html, $str) use ($tag)
			{
				return $html .= "<{$tag}>{$str}</{$tag}>";
			},
			"<{$parent_el}>"
		) . "</{$parent_el}>";
	}

	/**
	 * Alias of nextRow
	 *
	 * @deprecated
	 */
	public function next_row()
	{
		return $this->nextRow();
	}

	/**
	 * Returns all $data as a Table formatted string
	 *
	 * @param bool $echo
	 * @return mixed HTML formatted <table> string from $data if $echo is false
	 * @deprecated
	 */
	public function out($echo = false, $border = false)
	{
		if (is_bool($border) or is_int($border)) {
			$this->border = $border;
		}

		if ($echo) {
			echo $this;
		} else {
			return "$this";
		}
	}
}
