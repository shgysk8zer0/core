<?php
namespace shgysk8zer0\Core\Interfaces;

interface Events
{
	/**
	* [__isset description]
	*
	* @param  string  $eventName [description]
	* @return bool               [description]
	*/
	public function __isset($eventName);

	public function __set($eventName, Callable $callback);

	/**
	* [__callStatic description]
	*
	* @param string $eventName [description]
	* @param array  $context   [description]
	* @return void
	*/
	public static function __callStatic($eventName, array $context = array());

	/**
	* [__call description]
	*
	* @param  string $eventName [description]
	* @param  array  $context   [description]
	* @return void
	*/
	public function __call($eventName, array $context = array());
}
