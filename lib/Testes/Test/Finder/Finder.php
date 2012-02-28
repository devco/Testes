<?php

namespace Testes\Test\Finder;

class Finder implements FinderInterface
{
	private $dir;
	
	private $ns;

	public function __construct($dir, $ns = null)
	{
		$this->dir = realpath($dir);
		if ($this->dir === false) {
			throw new \UnexpectedValueException('The directory "' . $dir . '" does not exist.');
		}
		$this->ns = '\\' . trim($ns, '\\') . '\\';
	}

	public function getIterator()
	{
		$classes = new \ArrayIterator;
		foreach ($this->getTestFiles() as $file) {
			$classes->append($this->instantiate($file));
		}
		return $classes;
	}
	
	private function getTestFiles()
	{
		$files = array();
		foreach ($this->getRecursiveIterator() as $item) {
			if ($this->isValidFile($item)) {
				$files[] = $item->getRealpath();
			}
		}
		sort($files);
		return $files;
	}
	
	private function getRecursiveIterator()
	{
		return new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($this->dir),
			\RecursiveIteratorIterator::SELF_FIRST
		);
	}
	
	private function isValidFile(\SplFileInfo $file)
	{
		return $file->isFile() && preg_match('/^[^.].+\.php$/', $file->getBasename());
	}
	
	private function formatFile($file)
	{
		$file = str_replace('.php', '', $file);
		$file = substr($file, strlen($this->dir) + 1);
		$file = str_replace(DIRECTORY_SEPARATOR, '\\', $file);
		return $file;
	}
	
	private function instantiate($file)
	{
		require_once $file;
		$class = $this->formatFile($file);
		$class = $this->ns . $class;
		$class = new $class;
		return $class;
	}
}