<?php
namespace Redaxscript\Tests\Detector;

use Redaxscript\Db;
use Redaxscript\Detector;
use Redaxscript\Tests\TestCaseAbstract;

/**
 * DetectorTest
 *
 * @since 2.1.0
 *
 * @package Redaxscript
 * @category Tests
 * @author Henry Ruhs
 */

class DetectorTest extends TestCaseAbstract
{
	/**
	 * setUp
	 *
	 * @since 3.1.0
	 */

	public function setUp()
	{
		parent::setUp();
		$installer = $this->installerFactory();
		$installer->init();
		$installer->rawCreate();
		$installer->insertSettings(
		[
			'adminName' => 'Test',
			'adminUser' => 'test',
			'adminPassword' => 'test',
			'adminEmail' => 'test@test.com'
		]);
		Db::forTablePrefix('categories')
			->create()
			->set(
			[
				'title' => 'Category One',
				'alias' => 'category-one'
			])
			->save();
		Db::forTablePrefix('articles')
			->create()
			->set(
			[
				'title' => 'Article One',
				'alias' => 'article-one',
				'language' => 'de',
				'template' => 'wide'
			])
			->save();
	}

	/**
	 * tearDown
	 *
	 * @since 3.1.0
	 */

	public function tearDown()
	{
		$installer = $this->installerFactory();
		$installer->init();
		$installer->rawDrop();
	}

	/**
	 * providerLanguage
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */

	public function providerLanguage() : array
	{
		return $this->getProvider('tests/provider/Detector/language.json');
	}

	/**
	 * providerTemplate
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */

	public function providerTemplate() : array
	{
		return $this->getProvider('tests/provider/Detector/template.json');
	}

	/**
	 * testLanguage
	 *
	 * @since 3.0.0
	 *
	 * @param array $registryArray
	 * @param array $queryArray
	 * @param array $sessionArray
	 * @param array $serverArray
	 * @param array $settingArray
	 * @param string $expect
	 *
	 * @dataProvider providerLanguage
	 */

	public function testLanguage(array $registryArray = [], $queryArray = [], $sessionArray = [], $serverArray = [], $settingArray = [], string $expect = null)
	{
		/* setup */

		$this->_registry->init($registryArray);
		$this->_request->set('get', $queryArray);
		$this->_request->set('session', $sessionArray);
		$this->_request->set('server', $serverArray);
		Db::setSetting('language', $settingArray['language']);
		$detector = new Detector\Language($this->_registry, $this->_request);

		/* actual */

		$actual = $detector->getOutput();

		/* compare */

		$this->assertEquals($expect, $actual);
	}

	/**
	 * testTemplate
	 *
	 * @since 3.0.0
	 *
	 * @param array $registryArray
	 * @param array $queryArray
	 * @param array $sessionArray
	 * @param array $settingArray
	 * @param string $expect
	 *
	 * @dataProvider providerTemplate
	 */

	public function testTemplate(array $registryArray = [], $queryArray = [], $sessionArray = [], $settingArray = [], string $expect = null)
	{
		/* setup */

		$this->_registry->init($registryArray);
		$this->_request->set('get', $queryArray);
		$this->_request->set('session', $sessionArray);
		Db::setSetting('template', $settingArray['template']);
		$detector = new Detector\Template($this->_registry, $this->_request);

		/* actual */

		$actual = $detector->getOutput();

		/* compare */

		$this->assertEquals($expect, $actual);
	}
}