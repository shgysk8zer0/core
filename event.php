<?php
namespace shgysk8zer0\Core;
class Event implements Interfaces\Logger, Interfaces\Events
{
	use Traits\Events;
	use Traits\Logger;
	use Traits\Echo_Log_Method;
}
