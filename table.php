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

use \shgysk8zer0\Core as Core;
use \shgysk8zer0\Core_API as API;

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
 */
final class Table
implements Core\Interfaces\Table
{
	use API\Traits\Magic_Methods;
	use API\Traits\Magic\Call_Setter;

	const MAGIC_PROPERTY   = '_data';

	const RESTRICT_SETTING = true;

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
		$this->{self::MAGIC_PROPERTY} = $this->empty_row;
	}

	/**
	 * Called whenever $table is used as a string and returns <table>
	 *
	 * @param void
	 * @return string Table's HTML
	 * @example echo $table
	 * @example $var = "$table"
	 */
	public function __toString()
	{
		return "{$this->buildTable()}";
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
			array_filter(func_get_args(), 'is_array')
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
		$this->{self::MAGIC_PROPERTY} = array_merge(
			$this->empty_row,
			array_intersect_key($this->{self::MAGIC_PROPERTY}, $this->empty_row)
		);

		if (! empty($this->{self::MAGIC_PROPERTY})) {
			$this->_table_data[] = $this->{self::MAGIC_PROPERTY};
		}

		$this->{self::MAGIC_PROPERTY} = $this->empty_row;

		return $this;
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
	 * Build the <table> element from $_table_data, $caption & $headers
	 *
	 * @param void
	 * @return \DOMElement   $_table_data converted to an HTML <table>
	 * @uses Core\HTML_El
	 */
	public function buildTable()
	{
		if (! empty($this->{self::MAGIC_PROPERTY})) {
			$this->nextRow();
		}

		$table = new Core\HTML_El('table', null, null, true);

		if (is_int($this->border)) {
			$table->{'@border'} = $this->border;
		} elseif ($this->border === true) {
			$table->{'@border'} = 1;
		}

		if (is_string($this->caption)) {
			$table->caption = $this->caption;
		}

		$thead = $table->appendChild(new Core\HTML_El('thead'));
		$tfoot = $table->appendChild(new Core\HTML_El('tfoot'));

		$headers = array_reduce(
			$this->headers,
			[$this, '_buildHeaders'],
			$thead->appendChild(new Core\HTML_El('tr'))
		);

		$tfoot->appendChild($headers->cloneNode(true));

		unset($headers, $thead, $tfoot);

		array_reduce(
			$this->_table_data,
			[$this, '_buildBody'],
			$table->appendChild(new Core\HTML_El('tbody'))
		);

		return $table;
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

	/**
	 * Build the <tbody> from an array
	 *
	 * @param Core\HTML_El  $tbody The <table>'s <tbody> element
	 * @param array         $row   Array of cells to add
	 * @return Core\HTML_EL <tbody> with the row appended
	 */
	private function _buildBody(Core\HTML_El $tbody, array $row = array())
	{
		if (! empty($row)) {
			$tr = $tbody->appendChild(new Core\HTML_El('tr'));
			foreach ($row as $value) {
				$tr->td = $value;
			}
		}
		return $tbody;
	}

	/**
	 * Build <thead> or <tfoot> row
	 *
	 * @param Core\HTML_EL  $headers <thead> or <tfoot>
	 * @param string        $content Text content for <th> element
	 * @return Core\HTML_El <tr> with <th> appended
	 */
	private function _buildHeaders(Core\HTML_EL $headers, $content)
	{
		$headers->th = $content;
		return $headers;
	}
}
