<?php
namespace Redaxscript\Filesystem;

use CallbackFilterIterator;
use DirectoryIterator;
use EmptyIterator;
use phpDocumentor\Reflection\Types\Iterable_;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * parent class to handle the filesystem
 *
 * @since 3.2.0
 *
 * @package Redaxscript
 * @category Filesystem
 * @author Henry Ruhs
 */

class Filesystem
{
	/**
	 * value of the root
	 *
	 * @var string
	 */

	protected $_root;

	/**
	 * recursive flag
	 *
	 * @var bool
	 */

	protected $_recursive;

	/**
	 * array to be filtered
	 *
	 * @var array
	 */

	protected $_filterArray =
	[
		'.',
		'..'
	];

	/**
	 * iterator of the filesystem
	 *
	 * @var object
	 */

	protected $_iterator;

	/**
	 * init the class
	 *
	 * @since 3.2.0
	 *
	 * @param string $root value of the root
	 * @param bool $recursive recursive flag
	 * @param array $filterArray array to be filtered
	 */

	public function init(string $root = null, bool $recursive = false, $filterArray = [])
	{
		$this->_root = $root;
		$this->_recursive = $recursive;
		if (is_array($filterArray))
		{
			$this->_filterArray = array_merge($this->_filterArray, $filterArray);
		}
	}

	/**
	 * get the filesystem iterator
	 *
	 * @since 3.2.0
	 *
	 * @return iterable
	 */

	public function getIterator() : iterable
	{
		if (!$this->_iterator)
		{
			$this->updateIterator();
		}
		if ($this->_recursive)
		{
			return new RecursiveIteratorIterator($this->_iterator, RecursiveIteratorIterator::SELF_FIRST);
		}
		return $this->_iterator;
	}

	/**
	 * get the filesystem array
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */

	public function getArray() : array
	{
		$filesystemArray = [];
		$iterator = $this->getIterator();

		/* process iterator */

		foreach ($iterator as $value)
		{
			$filesystemArray[] = $value->getBasename();
		}
		return $filesystemArray;
	}

	/**
	 * get the sorted filesystem array
	 *
	 * @since 3.2.0
	 *
	 * @param int $flag
	 *
	 * @return array
	 */

	public function getSortArray(int $flag = SORT_FLAG_CASE) : array
	{
		$filesystemArray = $this->getArray();
		sort($filesystemArray, $flag);
		return $filesystemArray;
	}

	/**
	 * update the filesystem iterator
	 *
	 * @since 3.2.0
	 */

	public function updateIterator()
	{
		$this->_iterator = $this->_filterIterator($this->_scan($this->_root));
	}

	/**
	 * filter the filesystem iterator
	 *
	 * @since 3.2.0
	 *
	 * @param iterable $iterator iterator of the filesystem
	 *
	 * @return iterable
	 */

	protected function _filterIterator(iterable $iterator = null) : iterable
	{
		if ($this->_recursive)
		{
			return new RecursiveCallbackFilterIterator($iterator, $this->_validateItem());
		}
		return new CallbackFilterIterator($iterator, $this->_validateItem());
	}

	/**
	 * validate the filesystem item
	 *
	 * @since 3.2.0
	 *
	 * @return callable
	 */

	protected function _validateItem() : callable
	{
		return function ($item)
		{
			return !in_array($item->getFileName(), $this->_filterArray);
		};
	}

	/**
	 * scan the filesystem
	 *
	 * @since 3.2.0
	 *
	 * @param string $directory name of the directory
	 *
	 * @return iterable
	 */

	protected function _scan(string $directory = null) : iterable
	{
		if (is_dir($directory))
		{
			if ($this->_recursive)
			{
				return new RecursiveDirectoryIterator($directory);
			}
			return new DirectoryIterator($directory);
		}
		return new EmptyIterator();
	}
}
