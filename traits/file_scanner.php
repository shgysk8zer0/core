<?php
namespace shgysk8zer0\Core\Traits;

trait File_Scanner
{
	final public function getFiles($path, $pattern = '*')
	{
		return array_filter(glob($path . DIRECTORY_SEPARATOR . $pattern), 'is_file');
	}

	final public function isClass($file)
	{
		return (
			@is_file($file)
			and in_array(
				".{$this->getExtension($file)}",
				explode(',', spl_autoload_extensions())
			)
		);
	}

	final public function getExtension($file)
	{
		return pathinfo($file, PATHINFO_EXTENSION);
	}

	final public function getClasses($dir = __DIR__)
	{
		$paths = glob($dir . DIRECTORY_SEPARATOR . '*');
		$classes = [];
		foreach ($paths as $path) {
			if (is_dir($path)) {
				$classes = array_merge($classes, $this->getClasses($path));
			} elseif($this->isClass($path)) {
				array_push($classes, $path);
			}
		}
		return $classes;
	}
}
