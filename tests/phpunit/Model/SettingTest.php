<?php
namespace Redaxscript\Tests\Module;

use Redaxscript\Model;
use Redaxscript\Tests\TestCaseAbstract;

/**
 * SettingTest
 *
 * @since 4.0.0
 *
 * @package Redaxscript
 * @category Tests
 * @author Henry Ruhs
 */

class SettingTest extends TestCaseAbstract
{
	/**
	 * setUp
	 *
	 * @since 4.0.0
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
	}

	/**
	 * tearDown
	 *
	 * @since 4.0.0
	 */

	public function tearDown()
	{
		$installer = $this->installerFactory();
		$installer->init();
		$installer->rawDrop();
	}

	/**
	 * testGetAndSetSetting
	 *
	 * @since 2.2.0
	 */

	public function testGetAndSetSetting()
	{
		/* setup */

		$settingModel = new Model\Setting();
		$settingModel->setSetting('charset', 'utf-16');

		/* actual */

		$actual = $settingModel->getSetting('charset');

		/* compare */

		$this->assertEquals('utf-16', $actual);
	}

	/**
	 * testGetSettingInvalid
	 *
	 * @since 2.2.0
	 */

	public function testGetSettingInvalid()
	{
		/* setup */

		$settingModel = new Model\Setting();

		/* actual */

		$actual = $settingModel->getSetting('invalidKey');

		/* compare */

		$this->assertFalse($actual);
	}
}
