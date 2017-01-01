<?php
/**
 * @author Chris Zuber <shgysk8zer0@gmail.com>
 * @package shgysk8zer0\Core
 * @version 1.0.0
 * @copyright 2016, Chris Zuber
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

final class CSP extends \ArrayObject implements API\Interfaces\ToString
{
	use \shgysk8zer0\Core_API\Traits\GetInstance;

	/**
	 * Header to set to enforce policy
	 * @var string
	 */
	const ENFORCE = 'Content-Security-Policy:';

	/**
	 * Header to set to only report CSP violations
	 * @var string
	 */
	const REPORT  = 'Content-Security-Policy-Report-Only:';

	/**
	 * Array for converting to keywords
	 * @var array
	 */
	const KEYWORDS = array(
		'self'          => "'self'",
		'none'          => "'none'",
		'unsafe-inline' => "'unsafe-inline'",
		'unsafe-eval'   => "'unsafe-eval'",
	);

	/**
	 * Creates a new instance of class from an array of paramaters
	 * @param array $params Initial policy to set
	 */
	public function __construct(Array $params = array('default-src' => "'self'"))
	{
		parent::__construct();
		array_map([$this,'__set'], array_keys($params), array_values($params));
	}

	/**
	 * Set a paramater
	 * @param string $param Paramater to set
	 * @param mixed  $value Value to set it to
	 */
	public function __set($param, $value)
	{
		static::_convertKey($param);
		if (array_key_exists($param, $this) and ! in_array($value, $this[$param])) {
			is_array($value) ? array_map(
				[$this, __FUNCTION__],
				array_pad([], count($value), $param),
				$value
			) : array_push($value, $this[$param]);
		} else {
			$this[$param] = is_array($value) ? $value : [$value];
		}
	}

	/**
	 * Retrieve a paramater
	 * @param  string $param Paramater to get
	 * @return mixed         Its value
	 */
	public function __get($param)
	{
		static::_convertKey($param);
		if (array_key_exists($param, $this)) {
			return $this[$param];
		}
	}

	/**
	 * Checks if a paramater is set
	 * @param  string  $param Name of parameter
	 * @return boolean        Whether or not it is set
	 */
	public function __isset($param)
	{
		static::_convertKey($param);
		return array_key_exists($param, $this);
	}

	/**
	 * Removes a paramater
	 * @param string $param Paramater to remove
	 */
	public function __unset($param)
	{
		static::_convertKey($param);
		unset($this[$param]);
	}

	/**
	 * Chainable setter method
	 * @param  string $param  Paramater to set/append
	 * @param  Array  $values Values to use
	 * @return void
	 */
	public function __call($param, Array $values)
	{
		$this->__set($param, $values);
		return $this;
	}

	/**
	 * Gets the value to set for a CSP header or meta tag
	 * @return string Paramaters formatted as a policy string
	 */
	public function __toString()
	{
		return array_reduce(
			array_keys($this->getArrayCopy()),
			[$this, '_reducePolicy']
		);
	}

	/**
	 * Sets a CSP header
	 * @param  boolean $report_only Whether ot not to only report policy violations
	 * @return void
	 */
	public function __invoke($report_only = false)
	{
		header($report_only ? self::REPORT . $this : self::ENFORCE . $this);
	}

	/**
	 * Exchanges characters to set the correct property in the policy
	 * @param  string $key "default_src | default-src ..."
	 * @return void
	 */
	private static function _convertKey(&$key)
	{
		$key = str_replace('_', '-', strtolower($key));
	}

	/**
	 * Builds up a string from an an array of paramaters. Requires `array_keys`
	 * @param  string $carry The string thus far
	 * @param  mixed  $item  The current item
	 * @return string        String updated with current item
	 */
	private function _reducePolicy($carry = '', $item)
	{
		$src = $this[$item];

		array_walk($src, [$this, '_keywordWalk']);

		$carry .= $item . ' ' . join(' ', $src) . ';';
		return $carry;
	}

	/**
	 * Replaces any keywords
	 * @param  string $key "self", "*", "none", etc.
	 * @return void
	 */
	private function _keywordWalk(&$key)
	{
		if (array_key_exists($key, self::KEYWORDS)) {
			$key = self::KEYWORDS[$key];
		}
	}
}
