<?php
namespace Redaxscript\Tests;

use Redaxscript\Mailer;
use org\bovigo\vfs\vfsStream as Stream;
use org\bovigo\vfs\vfsStreamFile as StreamFile;
use org\bovigo\vfs\vfsStreamWrapper as StreamWrapper;

/**
 * MailerTest
 *
 * @since 2.2.0
 *
 * @package Redaxscript
 * @category Tests
 * @author Henry Ruhs
 */

class MailerTest extends TestCaseAbstract
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
		Stream::setup('root');
		$file = new StreamFile('attachment.zip');
		StreamWrapper::getRoot()->addChild($file);
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
	 * providerMailer
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */

	public function providerMailer() : array
	{
		return $this->getProvider('tests/provider/mailer.json');
	}

	/**
	 * testSend
	 *
	 * @since 2.2.0
	 *
	 * @param array $toArray
	 * @param array $fromArray
	 * @param string $subject
	 * @param mixed $body
	 *
	 * @dataProvider providerMailer
	 */

	public function testSend($toArray = [], $fromArray = [], $subject = null, $body = null)
	{
		/* setup */

		$mailer = new Mailer();
		$mailer->init($toArray, $fromArray, $subject, $body);

		/* actual */

		$actual = $mailer->send();

		/* compare */

		$this->assertTrue($actual);
	}

	/**
	 * testSendAttachment
	 *
	 * @since 3.1.0
	 *
	 * @param array $toArray
	 * @param array $fromArray
	 * @param string $subject
	 * @param mixed $body
	 *
	 * @requires OS Linux
	 * @dataProvider providerMailer
	 */

	public function testSendAttachment($toArray = [], $fromArray = [], $subject = null, $body = null)
	{
		/* setup */

		$attachmentArray =
		[
			Stream::url('root/attachment.zip')
		];
		$mailer = new Mailer();
		$mailer->init($toArray, $fromArray, $subject, $body, $attachmentArray);

		/* actual */

		$actual = $mailer->send();

		/* compare */

		$this->assertTrue($actual);
	}
}
