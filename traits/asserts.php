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
		assert($test1 === $test2, $desc);
	}

	public function assertInt($test, $desc = null)
	{
		assert(is_int($test), $desc);
	}

	public function assertFloat($test, $desc = null)
	{
		assert(is_float($test), $desc);
	}

	public function assertNumeric($test, $desc = null)
	{
		assert(is_numeric($test), $desc);
	}

	public function assertString($test, $desc = null)
	{
		assert(is_string($test), $desc);
	}

	public function assertObject($test, $desc = null)
	{
		assert(is_object($test), $desc);
	}

	public function assertArray($test, $desc = null)
	{
		assert(is_array($test), $desc);
	}

	public function assertEmpty($test, $desc = null)
	{
		assert(empty($test), $desc);
	}

	public function assertNotEmpty($test, $desc)
	{
		assert(!empty($test), $desc);
	}

	public function assertBool($test, $desc = null)
	{
		assert(is_bool($test), $desc);
	}

	public function assertTrue($test, $desc = null)
	{
		assert($test, $desc);
	}

	public function assertFalse($test, $desc = null)
	{
		// No tests increment since the will be handled in assertTrue()
		$this->assertTrue(!$test, $desc);
	}

	public function assertType($test, $type, $desc = null)
	{
		assert(gettype($test) === $type, $desc);
	}

	public function assertClass($test, $className, $desc = null)
	{
		assert(get_class($test) === $className, $desc);
	}

	public function assertFunctionExists($test, $desc = null)
	{

		assert(function_exists($test), $desc);
	}

	public function assertMethodExists($class, $method, $desc = null)
	{
		assert(method_exists($class, $method), $desc);
	}

	public function assertThrows(Callable $callback, array $args = null, $desc = null)
	{
		try {
			$callback($args);
		} catch(\Exception $e) {
			$this->passed_asserts++;
			return;
		}
		assert(false, $desc);
		$this->failed_asserts++;
	}

	public function getFailedAsserts()
	{
		return $this->failed_asserts;
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
		$this->failed_asserts++;
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
