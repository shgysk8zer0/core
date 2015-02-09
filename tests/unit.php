<?php
namespace shgysk8zer0\Core
{
	error_reporting(E_ALL);
	if (!defined('BASE')) {
		define('BASE', dirname(dirname(dirname(__DIR__))));
	}
	set_include_path(BASE);
	spl_autoload_extensions('.php');
	spl_autoload_register('spl_autoload');

	set_error_handler(['\\shgysk8zer0\\Core\\Errors', 'printError'], E_ALL);

	$logger = new Logger;

	$events = new Event;

	$events->done = function(array $context) use ($logger)
	{
		$logger->debug(
			'Exiting script on line {line} with included files:' . PHP_EOL .'{files} and backtrace:' . PHP_EOL . '{trace}',
			[
				'line' => __LINE__,
				'files' => join(PHP_EOL, get_included_files()),
				'trace' => print_r(current(debug_backtrace()), true)
			]
		);
	};

	Event::done(extract(get_included_files()));

	exit();
	$linter = new \shgysk8zer0\Core\Tests\Linter;

	$classes_with_errors = array_filter(
		$linter->getClasses(dirname(__DIR__)),
		[$linter, 'lintScript']
	);

	if (!empty($classes_with_errors)) {
		exit(1);
	}
}
