<?php
	error_reporting(E_ALL);
	if (!defined('BASE')) {
		define('BASE', dirname(dirname(dirname(__DIR__))));
	}
	set_include_path(BASE);
	spl_autoload_extensions('.php');
	spl_autoload_register('spl_autoload');

	set_error_handler(['\\shgysk8zer0\\Core\\Errors', 'printError'], E_ALL);

	$linter = new \shgysk8zer0\Core\Tests\Linter;

	$classes_with_errors = array_filter(
		$linter->getClasses(dirname(__DIR__)),
		[$linter, 'lintScript']
	);

	if (!empty($classes_with_errors)) {
		exit(1);
	}
