<?php
namespace shgysk8zer0\Core
{
	error_reporting(E_ALL);
	if (!defined('BASE')) {
		define('BASE', dirname(dirname(dirname(__DIR__))));
	}
	set_include_path(BASE . PATH_SEPARATOR . BASE . DIRECTORY_SEPARATOR . 'chriszuber/config/');
	spl_autoload_extensions('.php');
	spl_autoload_register('spl_autoload');

	new Errors;

	$linter = new \shgysk8zer0\Core\Tests\Linter;

	$classes_with_errors = array_filter(
		$linter->getClasses(dirname(__DIR__)),
		[$linter, 'lintScript']
	);

	if (!empty($classes_with_errors)) {
		exit(1);
	}
}
