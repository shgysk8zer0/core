<?php
namespace shgysk8zer0\Core\Traits;

trait Legacy_Login
{
	final public function create_from(array $source = array())
	{
		return $this->createFrom($source);
	}

	final public function login_with(array $source = array())
	{
		return $this->loginWith($source);
	}
}
