<?php
/*
Do not make final or abstract.
Constructor should require $src and have an optional array of attributes.

Required methods:

    __set($attr, $value): set attribute on iframe
    __get($attr) returns attribute value
    __isset($attr) returns whether or not attribute is set
    __unset($attr)removes an attribute
 */

namespace shgysk8zer0\Core\Elements;

class Iframe extends \DOMElement
{
	public function __construct($src, array $attributes = array())
	{
		$attributes['src'] = $src;
		parent::__construct('iframe');
		$dom = new \DOMDocument('1.0', 'UTF-8');
		$dom->appendChild($this);
		foreach ($attributes as $key => $value) {
			$this->setAttribute($key, $value);
		}
	}

	public function __set($name, $value)
	{
		$this->setAttribute($name, $value);
	}

	public function __get($name)
	{
		return $this->getAttribute($name);
	}

	public function __isset($name)
	{
		return $this->hasAttribute($name);
	}

	public function __unset($name)
	{
		$this->removeAttribute($name);
	}

	public function __toString()
	{
		return $this->ownerDocument->saveHTML($this);
	}
}
