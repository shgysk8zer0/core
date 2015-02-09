<?php
namespace shgysk8zer0\Core\Traits;

trait Linter
{
	public static $output = [];

	/**
	 * Checks for parsing errors in script.
	 * NOTE: The return value is from a shell command, so 0 is true!
	 * @param script $script [description]
	 * @return int
	 * @see http://php.net/manual/en/function.exec.php
	 */
	public function lintScript($script)
	{
		$script = escapeshellarg($script);
		$return_var = null;
		exec("php -l {$script}", static::$output, $return_var);
		return $return_var;
	}
}
