<?php
namespace Redaxscript\Filesystem;

/**
 * children class to handle a file in the filesystem
 *
 * @since 3.2.0
 *
 * @package Redaxscript
 * @category Filesystem
 * @author Henry Ruhs
 */

class File extends Filesystem
{
	/**
	 * create the file
	 *
	 * @since 3.2.0
	 *
	 * @param string $file name of the file
	 * @param int $mode file access mode
	 *
	 * @return bool
	 */

	public function createFile(string $file = null, int $mode = 0777) : bool
	{
		$path = $this->_root . DIRECTORY_SEPARATOR . $file;
		return !is_file($path) && touch($path) && chmod($path, $mode);
	}

	/**
	 * read content of file
	 *
	 * @since 3.0.0
	 *
	 * @param string $file name of the file
	 *
	 * @return string|bool
	 */

	public function readFile(string $file = null)
	{
		$path = $this->_root . DIRECTORY_SEPARATOR . $file;
		if (is_file($path))
		{
			return file_get_contents($path);
		}
		return false;
	}

	/**
	 * render content of file
	 *
	 * @since 3.0.0
	 *
	 * @param string $file name of the file
	 *
	 * @return string
	 */

	public function renderFile(string $file = null) : string
	{
		$path = $this->_root . DIRECTORY_SEPARATOR . $file;
		ob_start();
		if (is_file($path))
		{
			include($path);
		}
		return ob_get_clean();
	}

	/**
	 * write content to file
	 *
	 * @since 3.0.0
	 *
	 * @param string $file name of the file
	 * @param string $content content of the file
	 *
	 * @return bool
	 */

	public function writeFile(string $file = null, string $content = null) : bool
	{
		$path = $this->_root . DIRECTORY_SEPARATOR . $file;
		return strlen($content) && file_put_contents($path, $content) > 0;
	}

	/**
	 * remove the file
	 *
	 * @since 3.2.0
	 *
	 * @param string $file name of the file
	 *
	 * @return bool
	 */

	public function removeFile(string $file = null) : bool
	{
		$path = $this->_root . DIRECTORY_SEPARATOR . $file;
		return is_file($path) && unlink($path);
	}
}
