<?php
namespace shgysk8zer0\Core;

use \shgysk8zer0\Core_API as API;

final class Event extends API\Abstracts\Events implements API\Interfaces\Magic_Events
{
	public function __construct($event, Callable $callback)
	{
		static::registerEvent($event, $callback);
	}

	final public static function __callStatic($event, array $context = array())
	{
		static::triggerEvent($event, $context);
	}
}
