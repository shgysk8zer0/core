<?php

namespace shgysk8zer0\Core\Traits;

/**
 * @see http://php.net/manual/en/function.assert.php
 */
Trait Asserts {
	use \shgysk8zer0\Core\Traits\CLI_Colors;
	protected static $ASSERT_FAIL_TEMPLATE = 'assert(): "%DESC%" failed on line %LINE% in %FILE%';
	protected $passed_asserts = 0, $failed_asserts = 0, $total_asserts = 0;

	public function assertEquals($test1, $test2, $desc = null)
	{
		$this->total_asserts++;
		assert($test1 === $test2, $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertInt($test, $desc = null)
	{
		$this->total_asserts++;
		assert(is_int($test), $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertFloat($test, $desc = null)
	{
		$this->total_asserts++;
		assert(is_float($test), $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertNumeric($test, $desc = null)
	{
		$this->total_asserts++;
		assert(is_numeric($test), $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertString($test, $desc = null)
	{
		$this->total_asserts++;
		assert(is_string($test), $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertObject($test, $desc = null)
	{
		$this->total_asserts++;
		assert(is_object($test), $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertArray($test, $desc = null)
	{
		$this->total_asserts++;
		assert(is_array($test), $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertEmpty($test, $desc = null)
	{
		$this->total_asserts++;
		assert(empty($test), $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertNotEmpty($test, $desc)
	{
		$this->total_asserts++;
		assert(!empty($test), $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertBool($test, $desc = null)
	{
		$this->total_asserts++;
		assert(is_bool($test), $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertTrue($test, $desc = null)
	{
		$this->total_asserts++;
		assert($test, $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertFalse($test, $desc = null)
	{
		// No tests increment since the will be handled in assertTrue()
		$this->assertTrue(!$test, $desc);
	}

	public function assertType($test, $type, $desc = null)
	{
		$this->total_asserts++;
		assert(gettype($test) === $type, $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertClass($test, $className, $desc = null)
	{
		$this->total_asserts++;
		assert(get_class($test) === $className, $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertFunctionExists($test, $desc = null)
	{
		$this->total_asserts++;
		assert(function_exists($test), $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertMethodExists($class, $method, $desc = null)
	{
		$this->total_asserts++;
		assert(method_exists($class, $method), $desc) ? $this->passed_asserts++ : $this->failed_asserts++;
	}

	public function assertThrows(Callable $callback, array $args = null, $desc = null)
	{
		$this->total_asserts++;
		try {
			$callback($args);
		} catch(\Exception $e) {
			$this->passed_asserts++;
			return;
		}
		assert(false, $desc);
		$this->failed_asserts++;
	}

	/**
	* [assertFailed description]
	* @param [type] $file [description]
	* @param [type] $line [description]
	* @param [type] $code [description]
	* @param [type] $desc [description]
	*/
	final protected function assertFailed($file, $line, $code, $desc = null)
	{
		/*print_r([
		'file' => $file,
		'line' => $line,
		'code' => $code,
		'desc' => $desc
		]);*/
		static::setCLIColors($file, 'yellow');
		static::setCLIColors($line, 'blue');
		static::setCLIColors($desc, 'red');

		echo str_replace([
			'%DESC%',
			'%FILE%',
			'%LINE%',
			'%CODE%'
		], [
			$desc,
			$file,
			$line,
			$code
		], static::$ASSERT_FAIL_TEMPLATE) . PHP_EOL;
	}
}
