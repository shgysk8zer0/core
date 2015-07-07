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
 * Easily create and build <table>s and get either a DOMElement or HTML string
 */
class Table extends \DOMElement implements API\Interfaces\Magic_Methods, API\Interfaces\String
{
	use API\Traits\Magic\Call_Setter;

	/**
	 * Array containing column names
	 *
	 * @var array
	 */
	protected $_headers = array();

	/**
	 * Array containing rows of values to be converted into tbody > tr
	 *
	 * @var array
	 */
	protected $_rows    = array();

	/**
	 * The <thead> of the table
	 *
	 * @var \DOMElement
	 */
	protected $_thead;

	/**
	 * The <tfoot> of the table
	 *
	 * @var \DOMElement
	 */
	protected $_tfoot;

	/**
	 * The <tbody> of the table
	 *
	 * @var \DOMElement
	 */
	protected $_tbody;

	/**
	 * The current row number, incremented when moving to next row
	 *
	 * @var int
	 */
	private   $_row     = 0;

	/**
	 * The optional <caption> for the table
	 *
	 * @var string
	 */
	public $caption  = null;

	/**
	 * Value for the `border` attribute of the <table>
	 *
	 * @var int
	 */
	public $border   = 1;

	/**
	 * Creates the table element, along with its head, foot, & body
	 *
	 * @param string $col ...    Any number of strings to create columns from
	 */
	public function __construct()
	{
		parent::__construct('table');
		(new \DOMDocument('1.0', 'UTF-8'))->appendChild($this);
		$this->_headers = array_filter(func_get_args(), 'is_string');
		$this->_headers = array_map('strtolower', $this->_headers);
		$this->_getEmptyRow();
		$this->_thead = $this->appendChild(new THead);
		$this->_tfoot = $this->appendChild(new TFoot);
		$this->_tbody = $this->appendChild(new TBody);
		$this->_thead->build($this->_headers);
		$this->_tfoot->build($this->_headers);
	}

	/**
	 * Magic setter method, setting cell value for $col on current row
	 *
	 * @param string $col   The name of the column
	 * @param string $value Text value for the cell
	 * @return void
	 */
	public function __set($col, $value)
	{
		$col = strtolower($col);
		if (in_array($col, $this->_headers)) {
			$this->_rows[$this->_row][$col] = $value;
		} else {
			throw new \InvalidArgumentException(sprintf('No column named %s', $col));
		}
	}

	/**
	 * Magic getter for the class retrieves cell value for current row
	 *
	 * @param string $col   The name of the column
	 * @return string      The value of the cell in the current row
	 */
	public function __get($col)
	{
		$col = strtolower($col);
		if (isset($this->{$col})) {
			return $this->_rows[$this->_row][$col];
		}
	}

	/**
	 * Checks if column is set for current row
	 *
	 * @param string $col   The name of the column
	 * @return bool        Whether or not the column has been set for current row
	 */
	public function __isset($col)
	{
		$col = strtolower($col);
		return isset($this->_rows[$this->_row][$col]);
	}

	/**
	 * Removes the cell value for the current row
	 *
	 * @param string $col   The name of the column
	 * @return void
	 */
	public function __unset($col)
	{
		$col = strtolower($col);
		$this->{$col} = null;
	}

	/**
	 * Create the HTML string for the <table>
	 *
	 * @param void
	 * @return string HTML version of this table
	 */
	final public function __toString()
	{
		try {
			return $this->ownerDocument->saveHTML($this->getNode());
		} catch (\Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
			return '';
		}
	}

	/**
	 * Build the <table> element from supplied data and then clears it
	 *
	 * @param void
	 * @return self
	 */
	public function getNode()
	{
		$this->setAttribute('border', is_int($this->border) ? $this->border : 1);
		if (is_string($this->caption)) {
			$caption = new \DOMElement('caption', $this->caption);
			$this->insertBefore($caption, $this->_thead);
		} elseif ($this->caption instanceof \DOMNode) {
			$caption = new \DOMElement('caption');
			$this->insertBefore($caption, $this->_thead);
			if (isset($this->caption->ownerDocument)) {
				$this->caption = $this->ownerDocument->importNode($this->caption, true);
			}
			$caption->appendChild($this->caption);
		}
		array_map([$this->_tbody, 'buildRow'], $this->_rows);
		$this->_rows = array();
		$this->_row = 0;
		$this->_getEmptyRow();
		return $this;
	}

	/**
	 * Moves to the next row of the table
	 *
	 * @param void
	 * @return self
	 */
	public function nextRow()
	{
		$this->_row++;
		$this->_getEmptyRow();
		return $this;
	}

	/**
	 * Private method for setting initial row values
	 * Called whenever incrementing row through `nextRow` as well as in constructor
	 *
	 * @param void
	 * @return void
	 */
	private function _getEmptyRow()
	{
		$this->_rows[$this->_row] = array_combine(
			$this->_headers,
			array_pad(array(), count($this->_headers), null)
		);
	}
}
