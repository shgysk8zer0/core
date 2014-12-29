<?php
	/**
	 * Easily work with dates created from almost any
	 * imaginable form (Yesterday, 2014-01-01, 1/1/14, January 1st, ...)
	 *
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @package shgysk8zer0\core
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
	 * @var $obj
	 * @var array $data (data about the date as an array)
	 * @var sttring $src (What the date was created from)
	 * @var array $months (for converting January <=> 1)
	 * @var array $days (for converting Sunday <=> 1)
	 * @depreciated
	 */

	namespace shgysk8zer0\core;
	class simple_date {
		public $obj, $data = [], $src, $months = [
			'January',
			'February',
			'March',
			'April',
			'May',
			'June',
			'July',
			'August',
			'September',
			'October',
			'November',
			'December'
		],
		$days = [
			'Sunday',
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday'
		];

		/**
		 * Creates an associave array containing date info
		 * Keys[seconds, minutes, hours, mday, wday, mon, year, yday, weekday, month, timestamp]
		 * All values are stripped of leading '0's
		 * Seconds, minutes, are exactly what they are... no need to explain.
		 * Hours are Hours in [0-23]
		 * mday is numeric day of month [1-31]
		 * wday is numerica day of the week, starting on Sunday [0-6]
		 * mon = numeric month [1-12]
		 * year is numeric year [1-9999]
		 * yday is numeric day of the year [1-366]
		 * weekday is the textual day of the week [Sunday-Saturday]
		 * month is textual month name [January-December]
		 * timestamp is the unix timstamp and can be either positive or negative integer[+/- int]
		 *
		 * $t can be nearly any form of communicating time including a Unix timestamp, a variety
		 * of datetime formats, date only, time only, or nothing at all
		 *
		 * date formats include m/d/y, y-m-d, written [long form or abbreviated]
		 * time formats include 12/24 hour formats (depending on if AM/PM are included). Seconds are optional
		 *
		 * @link http://www.php.net/manual/en/function.getdate.php
		 * @link http://www.php.net/manual/en/datetime.construct.php
		 * @param mixed $t (null, int unix_timestamp, [date][time]_format)
		 */

		public function __construct($t = null) {
			(preg_match('/^\d+$/', $t)) ? $this->data = getdate($t) : $this->data = getdate(date_timestamp_get(date_create($t)));
			$this->data['timestamp'] = array_pop($this->data);
			$this->obj = $this->make();
			$this->src = $t;
		}

		/**
		 * Setter method for the class.
		 *
		 * @param string $key, mixed $value
		 * @return void
		 * @example "$storage->key = $value"
		 */

		public function __set($key, $value) {
			$key = str_replace('_', '-', $key);
			$this->data[$key] = $value;
			if($key === 'mon') {
				$this->data['month'] = $this->months[(int)$value - 1];
			}
			elseif($key === 'month') {
				$this->data['mon'] = array_search(caps($value), $this->months) +1;
			}
		}

		/**
		 * The getter method for the class.
		 *
		 * @param string $key
		 * @return mixed
		 * @example "$storage->key" Returns $value
		 */

		public function __get($key) {
			$key = str_replace('_', '-', $key);
			if(array_key_exists($key, $this->data)) {
				return $this->data[$key];
			}
			return false;
		}

		/**
		 * @param string $key
		 * @return boolean
		 * @example "isset({$storage->key})"
		 */

		public function __isset($key) {
			return array_key_exists(str_replace('_', '-', $key), $this->data);
		}

		/**
		 * Removes an index from the array.
		 *
		 * @param string $key
		 * @return void
		 * @example "unset($storage->key)"
		 */

		public function __unset($index) {
			unset($this->data[str_replace('_', '-', $index)]);
		}

		/**
		 * Chained magic getter and setter
		 * @param string $name, array $arguments
		 * @example "$storage->[getName|setName]($value)"
		 */

		public function __call($name, array $arguments) {
			$name = strtolower($name);
			$act = substr($name, 0, 3);
			$key = str_replace('_', '-', substr($name, 3));
			switch($act) {
				case 'get': {
					return $this->$key;
				}break;

				case 'set': {
					$this->$key = $arguments[0];
					return $this;
				}break;
			}
		}

		/**
		 * Returns an array of all array keys for $thsi->data
		 *
		 * @param void
		 * @return array
		 */

		public function keys() {
			return array_keys($this->data);
		}

		/**
		 * Converts the object's timestamp into the requested format
		 *
		 * @param string $format
		 * @return string
		 * @link http://php.net/manual/en/function.date.php
		 */

		public function out($format = 'Y-m-d\TH:i:s') {
			return date($format, $this->data['timestamp']);
		}

		/**
		 * @param void
		 * @retrun void
		 */

		public function update() {
			$str = "{$this->year}-{$this->mon}-{$this->mday}T{$this->hours}:{$this->minutes}:{$this->seconds}";
			$this->data['timestamp'] = date_timestamp_get(date_create($str));
//			$updated = new simple_date(
		}

		public function make() {
			return new \DateTime(date($this->out(), $this->data['timestamp']));
		}

		public function diff($t) {
			if(!preg_match('/\d{10}/', $t)) $t = date_timestamp_get(date_create($t));
			return $this->data['timestamp'] - $t;
		}
	}
?>
