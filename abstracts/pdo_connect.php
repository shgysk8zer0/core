<?php
namespace shgysk8zer0\Core\Abstracts;


abstract class PDO_Connect extends \PDO
{
const STATEMENT_CLASS = '\\shgysk8zer0\\Core\\Resources\\PDOStatement';
	use \shgysk8zer0\Core\Traits\PDO;

	final public function __construct($con)
	{
		try {

			$this->getCreds($con);
			parent::__construct(
				$this->dsn,
				$this->user,
				$this->password,
				[
					\PDO::ATTR_STATEMENT_CLASS => [$this::STATEMENT_CLASS, [$this]],
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
				]
			);

			$this->connected = true;

		} catch(\Exception $e) {
			exit($e);
		}
	}
}
