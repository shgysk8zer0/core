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

final class CSP extends \ArrayObject
{
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
		$keys = array_keys($params);
		array_walk($keys, [__CLASS__, '_convertKey']);
		parent::__construct(array_combine($keys, array_values($params)));
	}

	/**
	 * Set a paramater
	 * @param string $param Paramater to set
	 * @param mixed  $value Value to set it to
	 */
	public function __set($param, $value)
	{
		static::_convertKey($param);
		$this[$param] = $value;
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
		static::_convertKey($param);
		if (array_key_exists($param, $this)) {
			if (!is_array($this[$param])) {
				$this[$param] = array_merge([$this[$param]], $values);
			} else {
				$this[$param] = array_merge($this[$param], $values);
			}
		} else {
			$this[$param] = $values;
		}
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

		if (is_string($src) and array_key_exists($src, self::KEYWORDS)) {
			$src = self::KEYWORDS[$src];
		}

		$carry .= (is_array($src))
			? $item . ' ' . join(' ', $src) . ';'
			: "{$item} {$src};";
		return $carry;
	}
}
