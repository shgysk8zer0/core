<?php
namespace shgysk8zer0\Core\Traits;

trait Legacy_Login
{
	final public function create_from(array $source = array())
	{
		trigger_error(__METHOD__ . 'is deprecated', E_USER_DEPRECATED);
		return $this->createFrom($source);
	}

	final public function login_with(array $source = array())
	{
		trigger_error(__METHOD__ . 'is deprecated', E_USER_DEPRECATED);
		return $this->loginWith($source);
	}
}
