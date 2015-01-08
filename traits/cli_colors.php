<?php
namespace shgysk8zer0\Core\Traits;

trait CLI_Colors
{
	protected static $foreground_CLI_colors = [
		'black' => '0;30',
		'dark_gray' => '1;30',
		'blue' => '0;34',
		'light_blue' => '1;34',
		'green' => '0;32',
		'light_green' => '1;32',
		'cyan' => '0;36',
		'light_cyan' => '1;36',
		'red' => '0;31',
		'light_red' => '1;31',
		'purple' => '0;35',
		'light_purple' => '1;35',
		'brown' => '0;33',
		'yellow' => '1;33',
		'light_gray' => '0;37',
		'white' => '1;37'
	],
	$background_CLI_colors = [
		'black' => '40',
		'red' => '41',
		'green' => '42',
		'yellow' => '43',
		'blue' => '44',
		'magenta' => '45',
		'cyan' => '46',
		'light_gray' => '47'
	];

	public static function setCLIColors(&$string, $foreground = null, $background = null)
	{
		if (isset($foreground)) {
			static::setCLIForegroundColor($string, $foreground);
		}
		if (isset($background)) {
			static::setCLIBackgroundColor($string, $background);
		}
		$string .="\033[0m";
	}

	private static function setCLIBackgroundColor(&$string, $color)
	{
		$color = strtolower(trim($color));
		if (array_key_exists($color, static::$background_CLI_colors)) {
			$string = "\33[" . static::$background_CLI_colors[$color] . "m{$string}";
		}
	}

	private static function setCLIForegroundColor(&$string, $color)
	{
		$color = strtolower(trim($color));
		if (array_key_exists($color, static::$foreground_CLI_colors)) {
			$string = "\33[" . static::$foreground_CLI_colors[$color] . "m{$string}";
		}
	}
}
