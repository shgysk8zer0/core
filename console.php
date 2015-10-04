<?php

namespace shgysk8zer0\Core;

use \shgysk8zer0\Core_API as API;

final class Console implements API\Interfaces\String
{
	use API\Traits\Magic_Methods;

	use API\Traits\GetInstance;

	const MAGIC_PROPERTY = '_settings';
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
		$this->_timestamp = version_compare(PHP_VERSION, '5.1.0', '>=') ? $_SERVER['REQUEST_TIME'] : time();
		$this->_json['request_uri'] = $_SERVER['REQUEST_URI'];
	}

	public function __destruct()
	{
		if (!empty($this->_rows)) {
			header(self::HEADER_NAME . ':' . base64_encode($this));
		}
	}

	public function __toString()
	{
		return utf8_encode(json_encode($this->_json));
	}

	public function log(){}

	private function _log($type, array $args)
	{

	}
}
