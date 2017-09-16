<?php
namespace Redaxscript\Tests\Filesystem;

use Redaxscript\Filesystem;
use org\bovigo\vfs\vfsStream as Stream;
use Redaxscript\Tests\TestCaseAbstract;

/**
 * DirectoryTest
 *
 * @since 3.2.0
 *
 * @package Redaxscript
 * @category Tests
 * @author Henry Ruhs
 */

class DirectoryTest extends TestCaseAbstract
{
	/**
	 * setUp
	 *
	 * @since 3.2.0
	 */

	public function setUp()
	{
		Stream::setup('root', 0777, $this->getProvider('tests/provider/Filesystem/filesystem_setup.json'));
	}

	/**
	 * providerCreate
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */

	public function providerCreate() : array
	{
		return $this->getProvider('tests/provider/Filesystem/directory_create.json');
	}

	/**
	 * providerRemove
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */

	public function providerRemove() : array
	{
		return $this->getProvider('tests/provider/Filesystem/directory_remove.json');
	}

	/**
	 * providerRemove
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */

	public function providerClear() : array
	{
		return $this->getProvider('tests/provider/Filesystem/directory_clear.json');
	}

	/**
	 * testCreate
	 *
	 * @since 3.2.0
	 *
	 * @param string $root
	 * @param bool $recursive
	 * @param string $directory
	 * @param array $expectArray
	 *
	 * @dataProvider providerCreate
	 */

	public function testCreate($root = null, bool $recursive = null, $directory = null, array $expectArray = [])
	{
		/* setup */

		$filesystem = new Filesystem\Directory();
		$filesystem->init(Stream::url($root), $recursive);
		$filesystem->createDirectory($directory);

		/* actual */

		$actualArray = $filesystem->getArray();

		/* compare */

		$this->assertEquals($expectArray, $actualArray);
	}

	/**
	 * testRemove
	 *
	 * @since 3.2.0
	 *
	 * @param string $root
	 * @param bool $recursive
	 * @param string $directory
	 * @param array $expectArray
	 *
	 * @dataProvider providerRemove
	 */

	public function testRemove($root = null, bool $recursive = null, $directory = null, array $expectArray = [])
	{
		/* setup */

		$filesystem = new Filesystem\Directory();
		$filesystem->init(Stream::url($root), $recursive);
		$filesystem->removeDirectory($directory);

		/* actual */

		$actualArray = $filesystem->getArray();

		/* compare */

		$this->assertEquals($expectArray, $actualArray);
	}


	/**
	 * testClear
	 *
	 * @since 3.2.0
	 *
	 * @param string $root
	 * @param bool $recursive
	 * @param array $expectArray
	 *
	 * @dataProvider providerClear
	 */

	public function testClear($root = null, bool $recursive = null, array $expectArray = [])
	{
		/* setup */

		$filesystem = new Filesystem\Directory();
		$filesystem->init(Stream::url($root), $recursive);
		$filesystem->clearDirectory();

		/* actual */

		$actualArray = $filesystem->getArray();

		/* compare */

		$this->assertEquals($expectArray, $actualArray);
	}
}
