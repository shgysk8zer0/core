<?php

namespace shgysk8zer0\Core;

use \shgysk8zer0\Core_API as API;
final class ExceptionLog implements API\Interfaces\Logger
{
	use API\Traits\File_Resources;
	use API\Traits\Logger;
	use API\Traits\Logger_Interpolation;

	const TEMPLATE = "exception '{class}' with message '{message}' in {file}:{line}\n@time: {time}\nStack trace:\n{trace}";
	const LOG_FILE = 'logs/exceptions.log';

	public function __construct($log_file = self::LOG_FILE)
	{
		$this->fopen($log_file, false, 'a+');
		if (@is_resource($this->fhandle)) {
			$this->flock(LOCK_EX);
		}
	}

	public function __destruct()
	{
		if (is_resource($this->fhandle)) {
			if ($this->flocked) {
				$this->flock(LOCK_UN);
			}
			$this->fclose();
		}
	}

	public function log($level, $message, array $context = array()) {
		if (@ is_resource($this->fhandle)) {
			$this->filePutContents($this->interpolate($message, $context) . PHP_EOL, FILE_APPEND);
		}
	}

	public function __invoke(\Exception $e)
	{
		$this->warning(self::TEMPLATE, array(
			'class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
			'time' => new DateTime,
			'trace' => $e->getTraceAsString()
		));
	}
}
