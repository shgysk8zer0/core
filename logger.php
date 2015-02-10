<?php

namespace shgysk8zer0\Core;

use \shgysk8zer0\Core_API as API;

final class Logger implements API\Interfaces\Logger
{
	use API\Traits\Logger;
	use API\Traits\Default_Log_Method;
}
