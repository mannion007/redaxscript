<?php
namespace Redaxscript\Tests\View;

use Redaxscript\Tests\TestCaseAbstract;
use Redaxscript\View;

/**
 * ResetFormTest
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Tests
 * @author Henry Ruhs
 */

class ResetFormTest extends TestCaseAbstract
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
		$setting = $this->settingFactory();
		$setting->set('captcha', 1);
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
	 * providerRender
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */

	public function providerRender() : array
	{
		return $this->getProvider('tests/provider/View/reset_form_render.json');
	}

	/**
	 * testRender
	 *
	 * @since 3.0.0
	 *
	 * @param array $expectArray
	 *
	 * @dataProvider providerRender
	 */

	public function testRender(array $expectArray = [])
	{
		/* setup */

		$resetForm = new View\ResetForm($this->_registry, $this->_language);

		/* actual */

		$actual = $resetForm->render();

		/* compare */

		$this->assertStringStartsWith($expectArray['start'], $actual);
		$this->assertStringEndsWith($expectArray['end'], $actual);
	}
}
