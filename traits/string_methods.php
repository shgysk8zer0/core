<?php
namespace shgysk8zer0\Core\Traits;

trait String_Methods
{
	public function str_replace(array $replacements, $in)
	{
		return str_replace(
			array_keys($replacements),
			array_values($replacements),
			$in
		);
	}
}
