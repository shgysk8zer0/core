<?php
	namespace shgysk8zer0\Core;

	/**
	 * Class for quickly and easily creating CSVs
	 *
	 * The arguments in the constructor become the valid fields, in order,
	 * for the file.
	 *
	 * After that, magic __get() method appends to a $data array
	 * if the $key is present in $fields.
	 *
	 * If you want to continue onto the next row (leaving any unset fileds
	 * blank), simply call next_row(). Can also be chained using the magic __call()
	 * method, which only sets $data, similarly to __set().
	 *

	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @package shgysk8zer0\Core
	 * @version 0.9.0
	 * @copyright 2014, Chris Zuber
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
	 *
	 * @var array $data
	 * @var array $fields
	 * @var int $row
	 * @var array $empty_row
	 * @var string $csv
	 * @var string $delimieter
	 * @var string $enclosure
	 * @var boolean $print_headers
	 * @example
	 * $csv = new CSV('first_name', 'last_name');
	 * $csv->first_name = 'John';
	 * $csv->last_name = 'Smith';
	 * $csv->foo = 'bar';	//Does nothing
	 * $csv->next_row();
	 *
	 * $csv->first_name(
	 * 		$fist
	 * )->last_name(
	 * 		$last
	 * )->next_row();
	 */
	class CSV implements magic_methods
	{
		private $data, $fields, $row, $empty_row, $csv;
		public $delimiter, $enclosure, $print_headers;

		/**
		 * Sets up default values for class
		 *
		 * $data needs to be a multi-dimenstional associative array
		 *
		 * $fields is a list of all columns to be included in the CSV,
		 * in the order that they should appear.
		 *
		 * $row is the current row (integer) to be working on. Incremented
		 * by next_row() method.
		 *
		 * $empty_row as an associative array with its keys defined by $fields,
		 * but all of its values null
		 *
		 * $delimeter and $enclosure are charcters specifially for CSV that
		 * define separation of fields (',' between fields and '"' where values contain ',' or spaces)
		 *
		 * @param mixed arguments (will take arguments as an array or comma separated list, either results in an array)
		 * @example $csv = new CSV($fields[] | 'field1'[, ...])
		 */
		public function __construct() {

			$this->data = [];
			$this->fields = flatten(func_get_args());
			$this->row = 0;
			$this->delimiter = ',';
			$this->enclosure = '"';
			$this->print_headers = false;
			$this->csv = null;
			$this->empty_row = array_combine($this->fields, array_pad([], count($this->fields), null));
			$this->data[0] = $this->empty_row;
		}

		/**
		 * Sets column value on current row
		 * @param string $field [Name of column to set]
		 * @param string $value [Value to set it to]
		 * @return void
		 * @example $csv->$field = $value
		 */
		public function __set($field, $value)
		{
			$this->set($field, (string)$value);
		}

		/**
		 * Magic getter method for the class
		 * Allows for fields to be appended to rather than having to
		 * be built ahead of time.
		 * @param  string $field [Column on current row to get value from]
		 * @return string        [Value of column on current row]
		 * @example $csv->field .= ' and on and on...'
		 */
		public function __get($field)
		{
			if (in_array($field, $this->fields)) {
				return $this->data[$this->row][$field];
			} else {
				return '';
			}
		}

		/**
		 * Checks that column is set for current row
		 * @param  string  $field [Name of column]
		 * @return boolean        [Whether or not it is set]
		 */
		public function __isset($field)
		{
			return array_key_exists($field, $this->data[$this->row]);
		}

		/**
		 * Removes column from current row
		 * @param string $field [Name of column]
		 * @return void
		 */
		public function __unset($field)
		{
			unset($this->data[$this->row][$field]);
		}

		/**
		 * Chaninable magic method, in this case only to set values
		 *
		 * Also calls the private set() method too add a value to a field
		 * @param  string $field     [description]
		 * @param  array  $arguments [description]
		 * @return self              [Makes it chainable]
		 * @example $csv->$field1($value1)->$field2($value2)...
		 */
		public function __call($field, array $arguments)
		{
			$this->set($field, $arguments[0]);

			return $this;
		}

		/**
		 * Method to move to the next row of $data array.
		 * Increments $row, which is used in set() method
		 * when settings data ($data[$row]).
		 *
		 * Also sets the data for that row to an empty
		 * array pre-set with the keys defined by $fields
		 * @return [type] [description]
		 * @example $csv->next_row();
		 */
		public function next_row()
		{
			$this->row++;
			$this->data[$this->row] = $this->empty_row;

			return $this;
		}

		/**
		 * Returns all $data as a CSV formatted string
		 *
		 * Uses private build_CSV() method to convert $data
		 * array into CSV
		 * @param  string $newline [Optionally set newline character]
		 * @return string          [CSV formatted string from $data]
		 */
		public function out($newline = null)
		{
			$this->build_CSV($newline);
			return $this->csv;
		}

		/**
		 * Saves all $data as a CSV file
		 *
		 * Uses private build_CSV() method to convert $data
		 * array into CSV
		 * @param  string $fname   [name without extension which is automatically added]
		 * @param  string $newline [Optionally set newline character]
		 * @return bool            [Whether or not save was successful]
		 */
		public function save($fname = 'out', $newline = null)
		{
			$fname = (string)$fname;
			$this->build_CSV($newline);
			return file_put_contents(BASE . "/{$fname}.csv", $this->csv);
		}

		/**
		 * Private method for setting fields for the current $row
		 * Checks if $field is in the array of available $fields
		 * and that both arguments are strings.
		 * If these conditions are true, it sets $data[$row][$field] to $value
		 * and returns true.
		 * Otherwise returns false without setting any data
		 * @param string $field [name of field to set for current row]
		 * @param string $value [value to set it to]
		 * @return bool         [whether or not $field is available]
		 */
		private function set($field, $value)
		{
			if (is_string($field) and in_array($field, $this->fields)) {
				$this->data[$this->row][$field] = (string)$value;
				return true;
			}

			return false;
		}

		/**
		 * Private method for converting a multi-dimensional associate array into CSV string
		 * Opens php://temp with read/write permissions, then
		 * loops through $data, appending each row to CSV formatted string
		 * using fputscsv(). If print_headers is true, the first row will be all $fields.
		 * If $newline is passed, it will convert PHP_EOl to $newline (Must use double quotes)
		 * Once all $data has been looped through, it sets $csv to the value of the CSV string
		 * @param string $newline [Optionally set newline character]
		 * @return void
		 * @example $this->build_CSV()
		 */
		private function build_CSV($newline = null)
		{
			if (is_null($this->csv)) {
				 // Open a memory "file" for read/write...
				$fp = fopen('php://temp', 'r+');
				// ... write the $input array to the "file" using fputcsv()...
				if ($this->print_headers) {
					fputcsv($fp, $this->fields, $this->delimiter, $this->enclosure);
					//fputs($fp, PHP_EOL);
				}
				foreach($this->data as $row) {
					fputcsv($fp, $row, $this->delimiter, $this->enclosure);
				}
				// ... rewind the "file" so we can read what we just wrote...
				rewind($fp);
				// ... read the entire line into a variable...
				$this->csv = rtrim(stream_get_contents($fp));
				fclose($fp);
				if (is_string($newline)) {
					$this->csv = str_replace(PHP_EOL, $newline, $this->csv);
				}
			}
		}
	}
