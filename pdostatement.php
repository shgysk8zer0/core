<?php
namespace shgysk8zer0\Core;

use \shgysk8zer0\Core_API as API;
final class PDOStatement extends \PDOStatement implements API\Interfaces\PDOStatement
{
	use API\Traits\PDOStatement;
	use API\Traits\PDOStatement_Magic;
	use Traits\Legacy_PDOStatement;
}
