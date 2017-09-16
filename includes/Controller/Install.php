<?php
namespace Redaxscript\Controller;

use Redaxscript\Config;
use Redaxscript\Db;
use Redaxscript\Filter;
use Redaxscript\Html;
use Redaxscript\Installer;
use Redaxscript\Language;
use Redaxscript\Mailer;
use Redaxscript\Messenger;
use Redaxscript\Registry;
use Redaxscript\Request;
use Redaxscript\Validator;

/**
 * children class to process install
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Controller
 * @author Henry Ruhs
 * @author Balázs Szilágyi
 */

class Install extends ControllerAbstract
{
	/**
	 * instance of the config class
	 *
	 * @var Config
	 */

	protected $_config;

	/**
	 * construct of the class
	 *
	 * @since 3.0.0
	 *
	 * @param Registry $registry
	 * @param Request $request
	 * @param Language $language
	 * @param Config $config
	 */

	public function __construct(Registry $registry, Request $request, Language $language, Config $config)
	{
		parent::__construct($registry, $request, $language);
		$this->_config = $config;
	}

	/**
	 * process the class
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */

	public function process() : string
	{
		$specialFilter = new Filter\Special();
		$emailFilter = new Filter\Email();

		/* process post */

		$postArray =
		[
			'dbType' => $this->_request->getPost('db-type'),
			'dbHost' => $this->_request->getPost('db-host'),
			'dbName' => $this->_request->getPost('db-name'),
			'dbUser' => $this->_request->getPost('db-user'),
			'dbPassword' => $this->_request->getPost('db-password'),
			'dbPrefix' => $this->_request->getPost('db-prefix'),
			'dbSalt' => $this->_request->getPost('db-salt'),
			'adminName' => $specialFilter->sanitize($this->_request->getPost('admin-name')),
			'adminUser' => $specialFilter->sanitize($this->_request->getPost('admin-user')),
			'adminPassword' => $specialFilter->sanitize($this->_request->getPost('admin-password')),
			'adminEmail' => $emailFilter->sanitize($this->_request->getPost('admin-email')),
			'refreshConnection' => $this->_request->getPost('refresh-connection')
		];

		/* handle error */

		$messageArray = $this->_validateDatabase($postArray);
		if ($messageArray)
		{
			return $this->_error(
			[
				'url' => 'install.php',
				'title' => $this->_language->get('database'),
				'message' => $messageArray
			]);
		}
		$messageArray = $this->_validateAccount($postArray);
		if ($messageArray)
		{
			return $this->_error(
			[
				'url' => 'install.php',
				'title' => $this->_language->get('account'),
				'message' => $messageArray
			]);
		}

		/* handle success */

		$configArray =
		[
			'dbType' => $postArray['dbType'],
			'dbHost' => $postArray['dbHost'],
			'dbName' => $postArray['dbName'],
			'dbUser' => $postArray['dbUser'],
			'dbPassword' => $postArray['dbPassword'],
			'dbPrefix' => $postArray['dbPrefix'],
			'dbSalt' => $postArray['dbSalt']
		];
		$adminArray =
		[
			'adminUser' => $postArray['adminUser'],
			'adminName' => $postArray['adminName'],
			'adminEmail' => $postArray['adminEmail'],
			'adminPassword' => $postArray['adminPassword']
		];

		/* touch file */

		if (!$this->_touch($configArray))
		{
			return $this->_error(
			[
				'url' => 'install.php',
				'message' => $this->_language->get('directory_permission_grant') . $this->_language->get('point')
			]);
		}

		/* write config */

		if (!$this->_write($configArray))
		{
			return $this->_error(
			[
				'url' => 'install.php',
				'message' => $this->_language->get('file_permission_grant') . $this->_language->get('colon') . ' config.php'
			]);
		}

		/* refresh connection */

		if ($postArray['refreshConnection'])
		{
			$this->_refreshConnection();
		}

		/* get the status */

		if (!$this->_getStatus())
		{
			return $this->_error(
			[
				'url' => 'install.php',
				'message' => $this->_language->get('database_failed')
			]);
		}

		/* install */

		if (!$this->_install($adminArray))
		{
			return $this->_error(
			[
				'url' => 'install.php',
				'message' => $this->_language->get('installation_failed')
			]);
		}

		/* mail */

		if (!$this->_mail($adminArray))
		{
			return $this->_warning(
			[
				'url' => $this->_registry->get('root'),
				'message' => $this->_language->get('email_failed')
			]);
		}
		return $this->_success(
		[
			'url' => $this->_registry->get('root'),
			'message' => $this->_language->get('installation_completed')
		]);
	}

	/**
	 * show the success
	 *
	 * @since 3.0.0
	 *
	 * @param array $successArray array of the success
	 *
	 * @return string
	 */

	protected function _success(array $successArray = []) : string
	{
		$messenger = new Messenger($this->_registry);
		return $messenger
			->setUrl($this->_language->get('home'), $successArray['url'])
			->doRedirect()
			->success($successArray['message'], $successArray['title']);
	}

	/**
	 * show the warning
	 *
	 * @since 3.0.0
	 *
	 * @param array $warningArray array of the warning
	 *
	 * @return string
	 */

	protected function _warning(array $warningArray = []) : string
	{
		$messenger = new Messenger($this->_registry);
		return $messenger
			->setUrl($this->_language->get('home'), $warningArray['url'])
			->doRedirect()
			->warning($warningArray['message'], $warningArray['title']);
	}

	/**
	 * show the error
	 *
	 * @since 3.0.0
	 *
	 * @param array $errorArray array of the error
	 *
	 * @return string
	 */

	protected function _error(array $errorArray = [])  : string
	{
		$messenger = new Messenger($this->_registry);
		return $messenger
			->setUrl($this->_language->get('back'), $errorArray['url'])
			->error($errorArray['message'], $errorArray['title']);
	}

	/**
	 * validate the database
	 *
	 * @since 3.0.0
	 *
	 * @param array $postArray array to be validated
	 *
	 * @return array
	 */

	protected function _validateDatabase(array $postArray = []) : array
	{
		$messageArray = [];
		if (!$postArray['dbType'])
		{
			$messageArray[] = $this->_language->get('type_empty');
		}
		if (!$postArray['dbHost'])
		{
			$messageArray[] = $this->_language->get('host_empty');
		}
		if ($postArray['dbType'] !== 'sqlite')
		{
			if (!$postArray['dbName'])
			{
				$messageArray[] = $this->_language->get('name_empty');
			}
			if (!$postArray['dbUser'])
			{
				$messageArray[] = $this->_language->get('user_empty');
			}
		}
		return $messageArray;
	}

	/**
	 * validate the account
	 *
	 * @since 3.0.0
	 *
	 * @param array $postArray array to be validated
	 *
	 * @return array
	 */

	protected function _validateAccount(array $postArray = []) : array
	{
		$emailValidator = new Validator\Email();
		$loginValidator = new Validator\Login();

		/* validate post */

		$messageArray = [];
		if (!$postArray['adminName'])
		{
			$messageArray[] = $this->_language->get('name_empty');
		}
		if (!$postArray['adminUser'])
		{
			$messageArray[] = $this->_language->get('user_empty');
		}
		else if ($loginValidator->validate($postArray['adminUser']) === Validator\ValidatorInterface::FAILED)
		{
			$messageArray[] = $this->_language->get('user_incorrect');
		}
		if (!$postArray['adminPassword'])
		{
			$messageArray[] = $this->_language->get('password_empty');
		}
		else if ($loginValidator->validate($postArray['adminPassword']) === Validator\ValidatorInterface::FAILED)
		{
			$messageArray[] = $this->_language->get('password_incorrect');
		}
		if (!$postArray['adminEmail'])
		{
			$messageArray[] = $this->_language->get('email_empty');
		}
		else if ($emailValidator->validate($postArray['adminEmail']) === Validator\ValidatorInterface::FAILED)
		{
			$messageArray[] = $this->_language->get('email_incorrect');
		}
		return $messageArray;
	}

	/**
	 * touch sqlite file
	 *
	 * @since 3.0.0
	 *
	 * @param array $configArray
	 *
	 * @return bool
	 */

	protected function _touch(array $configArray = []) : bool
	{
		if ($configArray['dbType'] === 'sqlite')
		{
			$file = $configArray['dbHost'] . '.tmp';
			return touch($file) && unlink($file);
		}
		return true;
	}

	/**
	 * write config file
	 *
	 * @since 3.0.0
	 *
	 * @param array $configArray
	 *
	 * @return bool
	 */

	protected function _write(array $configArray = []) : bool
	{
		$this->_config->set('dbType', $configArray['dbType']);
		$this->_config->set('dbHost', $configArray['dbHost']);
		$this->_config->set('dbName', $configArray['dbName']);
		$this->_config->set('dbUser', $configArray['dbUser']);
		$this->_config->set('dbPassword', $configArray['dbPassword']);
		$this->_config->set('dbPrefix', $configArray['dbPrefix']);
		$this->_config->set('dbSalt', $configArray['dbSalt']);
		return $this->_config->write();
	}

	/**
	 * get the status
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */

	protected function _getStatus() : int
	{
		return Db::getStatus();
	}

	/**
	 * refresh the connection
	 *
	 * @since 3.0.0
	 */

	protected function _refreshConnection()
	{
		Db::init();
		Db::resetDb();
	}

	/**
	 * install the database
	 *
	 * @since 3.0.0
	 *
	 * @param array $installArray
	 *
	 * @return bool
	 */

	protected function _install(array $installArray = []) : bool
	{
		$adminName = $installArray['adminName'];
		$adminUser = $installArray['adminUser'];
		$adminPassword = $installArray['adminPassword'];
		$adminEmail = $installArray['adminEmail'];
		if ($adminName && $adminUser && $adminPassword && $adminEmail)
		{
			$installer = new Installer($this->_registry, $this->_request, $this->_language, $this->_config);
			$installer->init();
			$installer->rawDrop();
			$installer->rawCreate();
			$installer->insertData(
			[
				'adminName' => $installArray['adminName'],
				'adminUser' => $installArray['adminUser'],
				'adminPassword' => $installArray['adminPassword'],
				'adminEmail' => $installArray['adminEmail']
			]);
			return $this->_getStatus() === 2;
		}
		return false;
	}

	/**
	 * send the mail
	 *
	 * @since 3.0.0
	 *
	 * @param array $mailArray
	 *
	 * @return bool
	 */

	protected function _mail(array $mailArray = []) : bool
	{
		/* html elements */

		$linkElement = new Html\Element();
		$linkElement
			->init('a',
			[
				'href' => $this->_registry->get('root')
			])
			->text($this->_registry->get('root'));

		/* prepare mail */

		$toArray =
		[
			$mailArray['adminName'] => $mailArray['adminEmail']
		];
		$fromArray =
		[
			Db::getSetting('author') => Db::getSetting('email')
		];
		$subject = $this->_language->get('installation');
		$bodyArray =
		[
			$this->_language->get('user') . $this->_language->get('colon') . ' ' . $mailArray['adminUser'],
			'<br />',
			$this->_language->get('password') . $this->_language->get('colon') . ' ' . $mailArray['adminPassword'],
			'<br />',
			$this->_language->get('url') . $this->_language->get('colon') . ' ' . $linkElement
		];

		/* send mail */

		$mailer = new Mailer();
		$mailer->init($toArray, $fromArray, $subject, $bodyArray);
		return $mailer->send();
	}
}