<?php

namespace shgysk8zer0\Core;

use \shgysk8zer0\Core_API as API;
final class ExceptionLog implements API\Interfaces\Logger, API\Interfaces\File_Resources
{
	use API\Traits\File_Resources;
	use API\Traits\Logger;
	use API\Traits\Logger_Interpolation;

	const TEMPLATE       = "{level}: exception '{class}' with message '{message}' in {file}:{line}\n@time: {time}\nStack trace:\n{trace}";
	const HTML_TEMPLATE = "<div><strong>{level}:</strong> exception <code>{class}</code> with message <q>{message}</q> in <samp>{file}:{line}</samp><br />@time: {time}<br /><strong>Stack trace:</strong><br /><pre>{trace}</pre></div>";
	const LOG_FILE       = 'logs/exceptions.log';

	/**
	 * Echo the exception if log file cannot be opened?
	 * @var bool
	 */
	protected $echo_on_fopen_error = false;

	public function __construct($log_file = self::LOG_FILE, $echo_on_fopen_error = false)
	{
		$this->echo_on_fopen_error = $echo_on_fopen_error;
		@$this->fopen($log_file, false, 'a+');
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
		$context['level'] = ucwords($level);
		if (@ is_resource($this->fhandle)) {
			$this->filePutContents($this->interpolate($message, $context) . PHP_EOL, FILE_APPEND);
		} elseif ($this->echo_on_fopen_error) {
			echo $this->interpolate($message, $context) . PHP_EOL;
		}
	}

	public function __invoke(\Exception $e)
	{
		$this->warning(
			is_resource($this->fhandle) ? self::TEMPLATE : self::HTML_TEMPLATE,
			array(
				'class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'time' => new DateTime,
				'trace' => $e->getTraceAsString()
			)
		);
	}
}
