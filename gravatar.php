<?php

namespace shgysk8zer0\Core;
final class Gravatar extends URLSearchParams
{
	const GRAVATAR     = 'https://www.gravatar.com/avatar/';
	const DEFAULT_SIZE = 80;
	const MAX_SIZE     = 2048;

	private $_email = '';

	public function __construct($email, $size = self::DEFAULT_SIZE, Array $params = array())
	{
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$this->_email = $email;
		} else {
			throw new \InvalidArgumentException("{$email} is not a valid email address");
		}

		if (filter_var($size, FILTER_VALIDATE_INT, ['options' => [
			'default'   => false,
			'min_range' => 1,
			'max_range' => self::MAX_SIZE
		]])) {
			$params['s'] = $size;
			parent::__construct($params);
		} else {
			throw new \InvalidArgumentException('$size is required to be an integer less than ' . self::MAX_SIZE);
		}
	}

	public function __toString()
	{
		$query = parent::__toString();
		$url = self::GRAVATAR . md5(strtolower($this->_email));
		return strlen($query) === 0 ? $url : "$url?$query";
	}

	public function __call($param, $value)
	{
		$this->$param = count($value) === 1 ?  $value[0] : $value;
	}
}
