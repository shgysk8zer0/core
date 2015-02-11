<?php
namespace shgysk8zer0\Core\Traits;

trait Legacy_PDOStatement
{
	final public function get_results($col = null)
	{
		return $this->getResults($col);
	}
}
