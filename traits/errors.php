<?php
namespace shgysk8zer0\Core\Traits;

trait Errors
{
	public static $error_levels = [];

	public static function defineErrorLevels()
	{
		if (empty(static::$error_levels)) {
			$consts = get_defined_constants(true)['Core'];
			array_map(function($key, $val)
				{
					if (preg_match('/^E(_[A-Z]+){1,2}$/', $key)) {
						static::$error_levels[$key] = $val;
					}
				},
				array_keys($consts),
				array_values($consts)
			);
		}
	}

	public static function getErrorFromLevel($code)
	{
		static::defineErrorLevels();
		return array_search($code, static::$error_levels);
	}
}
