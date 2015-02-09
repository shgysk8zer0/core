<?php
namespace shgysk8zer0\Core\Tests;

class PDO extends \shgysk8zer0\Core\Abstracts\PDO_Connect
{
	public function query($query)
	{
		$stm = parent::query($query);
		$stm->execute();
		return $stm->fetchObject();
	}
}
