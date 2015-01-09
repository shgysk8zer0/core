<?php
	namespace shgysk8zer0\Core
	{
		error_reporting(E_ALL);
		define('BASE', dirname(dirname(dirname(__DIR__))));
		set_include_path(BASE);
		spl_autoload_extensions('.php');
		spl_autoload_register('spl_autoload');

		function getClasses($dir, array &$classes)
		{
			$paths = glob($dir . DIRECTORY_SEPARATOR . '*');
			$classes = array_merge($classes, array_filter($paths, __NAMESPACE__ . '\\isClass'));

			$dirs = array_filter($paths, '\\is_dir');

			if (!empty($dirs)) {
				foreach($dirs as $sub_dir) {
					getClasses($sub_dir, $classes);
				}
			}
		}

		function isClass($path)
		{
			return (
				is_file($path)
				and in_array(
					'.' . pathinfo($path, PATHINFO_EXTENSION),
					explode(',', spl_autoload_extensions())
				)
			);
		}

		function lintClass($class)
		{
			$class = escapeshellarg($class);
			$output = [];
			$return_var = null;
			exec("php -l {$class}", $output, $return_var);
			return $return_var;
		}


		$classes = [];
		$errors = [];
		getClasses(dirname(__DIR__), $classes);

		$classes_with_errors = array_filter($classes, __NAMESPACE__ . '\\lintClass');

		if (!empty($classes_with_errors)) {
			print_r($classes_with_errors);
			exit(1);
		}
		exit(0);
	}
