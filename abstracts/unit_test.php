<?php
	namespace shgysk8zer0\Core\Abstracts;
	/**
	 *
	 * @see http://php.net/manual/en/class.reflectionclass.php
	 */

	abstract class Unit_Test
	extends \ReflectionClass
	implements \shgysk8zer0\Core\Interfaces\Unit_Test
	{
		use \shgysk8zer0\Core\Traits\Asserts;

		public $methods, $child_class, $output;

		protected $reflected_class = null;

		private $constructor_args = null;

		protected static $defined_levels = [];

		public static $exceptions = [],
			$ASSERT_ACTIVE = true,
			$ASSERT_WARNING = false,
			$ASSERT_BAIL = false,
			$ASSERT_QUIET_EVAL = false,
			$ASSERT_CALLBACK = 'assertFailed',
			$CLIs = ['cli'],
			$ECHO_EXCEPTIONS = true;

		const ERROR_HANDLER_LEVEL = E_ALL;
		const DEFAULT_ERROR_LEVEL = E_ALL;
		const ERROR_HANDLER_METHOD = 'errorHandler';
		const EXCEPTION_HANDLER_METHOD = 'exceptionHandler';
		const ASSERT_FAILED_METHOD = 'assertFailed';

		/**
		 * [__construct description]
		 * @param [type] $testsClass [description]
		 */
		public function __construct($testsClass, array $constructor_args = null)
		{
			$this->forceCLI();
			parent::__construct($testsClass);
			$this->constructor_args = $constructor_args;
			assert_options(ASSERT_ACTIVE, static::$ASSERT_ACTIVE);
			assert_options(ASSERT_WARNING, static::$ASSERT_WARNING);
			assert_options(ASSERT_BAIL, static::$ASSERT_BAIL);
			assert_options(ASSERT_QUIET_EVAL, static::$ASSERT_QUIET_EVAL);
			assert_options(ASSERT_CALLBACK, [$this, $this::ASSERT_FAILED_METHOD]);
			$this->init();
		}

		/**
		 * [__get description]
		 * @param  [type] $prop [description]
		 * @return [type]       [description]
		 */
		final public function __get($prop)
		{
			if(method_exists($this, "get{$prop}")) {
				return \call_user_func([$this, "get{$prop}"]);
			}
		}

		/**
		 * [__call description]
		 * @param  [type] $method [description]
		 * @param  [type] $args   [description]
		 * @return [type]         [description]
		 */
		final public function __call($method, array $args = null)
		{
			if (is_null($this->reflected_class)) {
				if ($this->getConstructor()) {
					$this->reflected_class = (is_array($args))
						? $this->newInstanceArgs($this->constructor_args)
						: $this->newInstance();
				} else {
					$this->reflected_class = $this->newInstanceWithoutConstructor();
				}
			}

			if ($this->hasMethod($method)) {
				return $this->getMethod(
					$method
				)->invokeArgs(
					$this->reflected_class,
					$args
				);
			} else {
				echo 'No method: ' . $method . PHP_EOL;
			}
		}

		/**
		 * [init description]
		 * @return [type] [description]
		 */
		final protected function init()
		{
			$this->child_class = get_class();
			set_exception_handler([__CLASS__, $this::EXCEPTION_HANDLER_METHOD]);
			set_error_handler(
				[
					__CLASS__,
					$this::ERROR_HANDLER_METHOD
				],
				self::ERROR_HANDLER_LEVEL
			);

			if(empty(static::$defined_levels)) {
				$consts = get_defined_constants(true)['Core'];
				array_map(function($key, $val)
					{
						if(preg_match('/^E(_[A-Z]+){1,2}$/', $key)) {
							static::$defined_levels[$key] = $val;
						}
					},
					array_keys($consts),
					array_values($consts)
				);
				unset($consts);
			}

			if(empty($this->methods)) {
				$this->methods = array_diff(
					get_class_methods(get_class($this)),
					get_class_methods($this->child_class)
				);
			}

			if(is_array($this->methods) and !empty($this->methods)) {
				foreach($this->methods as $method) {
					call_user_func([$this, $method]);
				}
			}
			error_reporting(self::DEFAULT_ERROR_LEVEL);
		}

		/**
		 * [lint description]
		 * @param  [type] $file [description]
		 * @return [type]       [description]
		 */
		final public function lint($file)
		{
			$file = escapeshellarg($file);
			$output = [];
			$return_var;
			$tmp = exec("php5 -l {$file}", $output, $return_var);

			if($return_var !== 0) {
				trigger_error(join(PHP_EOL, $output));
			}
			return $this;
		}

		/**
		 * [forceCLI description]
		 */
		final protected function forceCLI()
		{
			if(!in_array(PHP_SAPI, static::$CLIs)) {
				http_response_code(404);
				exit();
			}
			return $this;
		}

		/**
		 * [exceptionHandler description]
		 * @param Exception $exception [description]
		 */
		final public static function exceptionHandler(\Exception $exception)
		{
			if(static::$ECHO_EXCEPTIONS) echo $exception . PHP_EOL;
			array_push(static::$exceptions, "{$exception}");
		}

		/**
		 * [errorHandler description]
		 * @param [type] $error_level   [description]
		 * @param [type] $error_message [description]
		 * @param [type] $file          [description]
		 * @param [type] $line          [description]
		 * @param [type] $scope         [description]
		 */
		final public function errorHandler(
			$error_level = null,
			$error_message = null,
			$file = null,
			$line = null,
			$scope = null
		)
		{
			$error_message = array_search(
				$error_level,
				static::$defined_levels
			) . ": {$error_message}";

			throw new \ErrorException($error_message, 0, $error_level, $file, $line);
			return true;
		}

		/**
		 * [results description]
		 * @return [type] [description]
		 */
		final public function results()
		{
			echo join(PHP_EOL, static::$exceptions);
		}

		final protected function getCaller($level = 0)
		{
			return debug_backtrace()[$level];
			$backtrace = debug_backtrace();
			return array_shift($backtrace);
		}
	}
