<?php
namespace shgysk8zer0\Core\Abstracts;

abstract class Events implements \shgysk8zer0\Core\Interfaces\Events
{
	protected static $registered = [];

	/**
	* [__isset description]
	*
	* @param  string  $eventName [description]
	* @return bool               [description]
	*/
	final public function __isset($eventName)
	{
		return array_key_exists($eventName, static::$registered);
	}

	final public function __set($eventName, \Closure $callback)
	{
		$this->createEvent($eventName, $callback);
	}

	/**
	* [__callStatic description]
	*
	* @param string $eventName [description]
	* @param array  $context   [description]
	* @return void
	*/
	final public static function __callStatic($eventName, array $context = array())
	{
		if (array_key_exists($eventName, static::$registered)) {
			static::$registered = array_filter(
				static::$registered[$eventName],
				function($handler) use ($context)
				{
					call_user_func($handler->callback, $context);
					return !$handler->unregister_on_call;
				}
			);
		}
	}

	/**
	* [__call description]
	*
	* @param  string $eventName [description]
	* @param  array  $context   [description]
	* @return void
	*/
	final public function __call($eventName, array $context = array())
	{
		static::__callStatic($eventName, $context);
	}

	/**
	* [createEvent description]
	*
	* @param string  $eventName [description]
	* @param Closure $callback  [description]
	* @return void
	*/
	final public function createEvent(
		$eventName,
		\Closure $callback,
		$unregister_on_call = false
	)
	{
		static::$registered["{$eventName}"][] = (object)[
		'callback' => $callback,
		'unregister_on_call' => $unregister_on_call
		];
	}
}
