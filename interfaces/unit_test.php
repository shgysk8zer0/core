<?php
namespace shgysk8zer0\Core\Interfaces;

interface Unit_Test
{
	public function __get($prop);

	public function __call($method, array $args = null);

	public function lint($file);

	public static function exceptionHandler(\Exception $exception);

	public function errorHandler(
		$error_level = null,
		$error_message = null,
		$file = null,
		$line = null,
		$scope = null
	);
}
