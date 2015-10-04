<?php

namespace shgysk8zer0\Core;

use \shgysk8zer0\Core_API as API;

class Console implements API\Interfaces\String, API\Interfaces\Console
{
	use API\Traits\Magic_Methods;

	use API\Traits\GetInstance;

	const MAGIC_PROPERTY = '_settings';

	/**
	 * @var string
	 */
	const LOG_FORMAT = '%s : %d';

	/**
	 * @var string
	 */
	const VERSION = '4.1.0';

	/**
	 * @var string
	 */
	const HEADER_NAME = 'X-ChromeLogger-Data';

	/**
	 * @var string
	 */
	const BACKTRACE_LEVEL = 'backtrace_level';

	/**
	 * @var string
	 */
	const LOG = 'log';

	/**
	 * @var string
	 */
	const WARN = 'warn';

	/**
	 * @var string
	 */
	const ERROR = 'error';

	/**
	 * @var string
	 */
	const GROUP = 'group';

	/**
	 * @var string
	 */
	const INFO = 'info';

	/**
	 * @var string
	 */
	const GROUP_END = 'groupEnd';

	/**
	 * @var string
	 */
	const GROUP_COLLAPSED = 'groupCollapsed';

	/**
	 * @var string
	 */
	const TABLE = 'table';

	/**
	 * @var string
	 */
	protected $_php_version;

	/**
	 * @var int
	 */
	protected $_timestamp;

	protected $_backtraces = array();

	protected $_processed;

	/**
	 * @var array
	 */
	protected $_json = array(
		'version' => self::VERSION,
		'columns' => array('log', 'backtrace', 'type'),
		'rows' => array()
	);

	protected $_settings = array(
		self::BACKTRACE_LEVEL => 1
	);

	public function __construct()
	{
		$this->_php_version = phpversion();
		$this->_timestamp = version_compare(PHP_VERSION, '5.1.0', '>=')
			? $_SERVER['REQUEST_TIME']
			: time();
		$this->_json['request_uri'] = $_SERVER['REQUEST_URI'];
	}

	public function __destruct()
	{
		header(self::HEADER_NAME . ':' . base64_encode($this));
	}

	public function __toString()
	{
		return utf8_encode(json_encode($this->_json));
	}

	public function log()
	{
		return $this->_log(self::LOG, func_get_args());
	}

	public function info()
	{
		return $this->_log(self::INFO, func_get_args());
	}

	public function table()
	{
		return $this->_log(self::TABLE, func_get_args());
	}

	public function warn()
	{
		return $this->_log(self::WARN, func_get_args());
	}

	public function error()
	{
		return $this->_log(self::ERROR, func_get_args());
	}

	public function group()
	{
		return $this->_log(self::GROUP, func_get_args());
	}

	public function groupCollapsed()
	{
		return $this->_log(self::GROUP_COLLAPSED, func_get_args());
	}

	public function groupEnd()
	{
		return $this->_log(self::GROUP_END, func_get_args());
	}

	protected function _formatLocation($file, $line, $format = self::LOG_FORMAT)
	{
		return sprintf($format, $file, $line);
	}

	protected function _log($type, array $args)
	{
		// nothing passed in, don't do anything
		if (count($args) === 0 && $type !== self::GROUP_END) {
			return;
		}
		$this->_processed = array();
		$logs = array();
		foreach ($args as $arg) {
			$logs[] = $this->_convert($arg);
		}
		$backtrace = debug_backtrace(false);
		$level = $this->{self::BACKTRACE_LEVEL};
		$backtrace_message = 'unknown';
		if (isset($backtrace[$level]['file'], $backtrace[$level]['line'])) {
			$backtrace_message = $this->_formatLocation(
				$backtrace[$level]['file'],
				$backtrace[$level]['line']
			);
		}
		$this->_addRow($logs, $backtrace_message, $type);
	}

	/**
	 * converts an object to a better format for logging
	 *
	 * @param Object
	 * @return array
	 */
	protected function _convert($object)
	{
		// if this isn't an object then just return it
		if (!is_object($object)) {
			return $object;
		}
		//Mark this object as processed so we don't convert it twice and it
		//Also avoid recursion when objects refer to each other
		$this->_processed[] = $object;
		$object_as_array = array();
		// first add the class name
		$object_as_array['___class_name'] = get_class($object);
		// loop through object vars
		$object_vars = get_object_vars($object);
		foreach ($object_vars as $key => $value) {
			// same instance as parent object
			if ($value === $object || in_array($value, $this->_processed, true)) {
				$value = 'recursion - parent object [' . get_class($value) . ']';
			}
			$object_as_array[$key] = $this->_convert($value);
		}
		$reflection = new \ReflectionClass($object);
		// loop through the properties and add those
		foreach ($reflection->getProperties() as $property) {
			// if one of these properties was already added above then ignore it
			if (array_key_exists($property->getName(), $object_vars)) {
				continue;
			}
			$type = $this->_getPropertyKey($property);
			if ($this->_php_version >= 5.3) {
				$property->setAccessible(true);
			}
			try {
				$value = $property->getValue($object);
			} catch (\ReflectionException $e) {
				$value = 'only PHP 5.3 can access private/protected properties';
			}
			// same instance as parent object
			if ($value === $object || in_array($value, $this->_processed, true)) {
				$value = 'recursion - parent object [' . get_class($value) . ']';
			}
			$object_as_array[$type] = $this->_convert($value);
		}
		return $object_as_array;
	}

	/**
	 * takes a reflection property and returns a nicely formatted key of the property name
	 *
	 * @param ReflectionProperty
	 * @return string
	 */
	protected function _getPropertyKey(\ReflectionProperty $property)
	{
		$static = $property->isStatic() ? ' static' : '';
		if ($property->isPublic()) {
			return 'public' . $static . ' ' . $property->getName();
		}
		if ($property->isProtected()) {
			return 'protected' . $static . ' ' . $property->getName();
		}
		if ($property->isPrivate()) {
			return 'private' . $static . ' ' . $property->getName();
		}
	}

	/**
	 * adds a value to the data array
	 *
	 * @var mixed
	 * @return void
	 */
	protected function _addRow(array $logs, $backtrace, $type)
	{
		// if this is logged on the same line for example in a loop, set it to null to save space
		if (in_array($backtrace, $this->_backtraces)) {
			$backtrace = null;
		}
		// for group, groupEnd, and groupCollapsed
		// take out the backtrace since it is not useful
		if ($type === self::GROUP || $type === self::GROUP_END || $type === self::GROUP_COLLAPSED) {
			$backtrace = null;
		}
		if (isset($backtrace)) {
			$this->_backtraces[] = $backtrace;
		}
		array_push($this->_json['rows'], array($logs, $backtrace, $type));
	}

	public function reportError(
		$errno,
		$errstr,
		$errfile,
		$errline,
		array $errcontext = array()
	)
	{
		$this->_addRow(
			array($errstr),
			$this->_formatLocation($errfile, $errline),
			self::ERROR
		);
	}

	public function reportException(\Exception $e)
	{
		$this->_addRow(
			array($e->getMessage()),
			$this->_formatLocation($e->getFile(), $e->getLine()),
			self::WARN
		);
	}
}
