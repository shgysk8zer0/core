<?php
	namespace shgysk8zer0\Core
	{
		define('BASE', dirname(dirname(dirname(__DIR__))));
		set_include_path(BASE);
		spl_autoload_extensions('.php');
		spl_autoload_register('spl_autoload');
	}
