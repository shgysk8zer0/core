<?php
	/**
	 *
	 */
	namespace shgysk8zer0\Core\resources;

	abstract class Unit_Test
	{
		protected $methods = [], $child_class;
		protected static $defined_levels = [];
		public static $exceptions = [];
		protected static $CLIs = ['cli'];

		const ERROR_HANDLER_LEVEL = E_ALL;
		const DEFAULT_ERROR_LEVEL = E_ALL;
		const ERROR_HANDLER_METHOD = 'errorHandler';
		const EXCEPTION_HANDLER_METHOD = 'exceptionHandler';
		public static $ECHO_EXCEPTIONS = true;

		public function __construct()
		{
			$this->forceCLI();
			$this->init();
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
				[__CLASS__, $this::ERROR_HANDLER_METHOD],
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
			$error_message = array_search($error_level, static::$defined_levels) . ": {$error_message}";
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
	}
