<?php
namespace Redaxscript\Module;

use Redaxscript\Config;
use Redaxscript\Db;
use Redaxscript\Installer;
use Redaxscript\Language;
use Redaxscript\Model;
use Redaxscript\Registry;
use Redaxscript\Request;

/**
 * parent class to create a module
 *
 * @since 2.2.0
 *
 * @package Redaxscript
 * @category Module
 * @author Henry Ruhs
 */

class Module
{
	/**
	 * instance of the registry class
	 *
	 * @var Registry
	 */

	protected $_registry;

	/**
	 * instance of the request class
	 *
	 * @var Request
	 */

	protected $_request;

	/**
	 * instance of the language class
	 *
	 * @var Language
	 */

	protected $_language;

	/**
	 * instance of the config class
	 *
	 * @var Config
	 */

	protected $_config;

	/**
	 * array of the module
	 *
	 * @var array
	 */

	protected static $_moduleArray =
	[
		'status' => 1,
		'access' => null
	];

	/**
	 * array of the notification
	 *
	 * @var array
	 */

	protected static $_notificationArray = [];

	/**
	 * constructor of the class
	 *
	 * @since 3.0.0
	 *
	 * @param Registry $registry instance of the registry class
	 * @param Request $request instance of the request class
	 * @param Language $language instance of the language class
	 * @param Config $config instance of the config class
	 */

	public function __construct(Registry $registry, Request $request, Language $language, Config $config)
	{
		$this->_registry = $registry;
		$this->_request = $request;
		$this->_language = $language;
		$this->_config = $config;
	}

	/**
	 * init the class
	 *
	 * @since 2.4.0
	 *
	 * @param array $moduleArray custom module setup
	 */

	public function init($moduleArray = [])
	{
		/* merge module setup */

		if (is_array($moduleArray))
		{
			static::$_moduleArray = array_merge(static::$_moduleArray, $moduleArray);
		}

		/* load the language */

		if (is_array(static::$_moduleArray) && array_key_exists('alias', static::$_moduleArray))
		{
			$this->_language->load(
			[
				'modules' . DIRECTORY_SEPARATOR . static::$_moduleArray['alias'] . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . 'en.json',
				'modules' . DIRECTORY_SEPARATOR . static::$_moduleArray['alias'] . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . $this->_registry->get('language') . '.json'
			]);
		}
	}

	/**
	 * get the message from notification
	 *
	 * @since 3.0.0
	 *
	 * @param string $type type of the notification
	 *
	 * @return string|array|boolean
	 */

	public function getNotification($type = null)
	{
		if (is_array(self::$_notificationArray) && array_key_exists($type, self::$_notificationArray))
		{
			return self::$_notificationArray[$type];
		}
		else if (!$type)
		{
			return self::$_notificationArray;
		}
		return false;
	}

	/**
	 * set the message to notification
	 *
	 * @since 3.0.0
	 *
	 * @param string $type type of the notification
	 * @param string|array $message message of the notification
	 */

	public function setNotification($type = null, $message = null)
	{
		$moduleName = static::$_moduleArray['name'];
		static::$_notificationArray[$type][$moduleName][] = $message;
	}

	/**
	 * install the module
	 *
	 * @since 2.6.0
	 *
	 * @return boolean
	 */

	public function install()
	{
		if (is_array(static::$_moduleArray) && array_key_exists('alias', static::$_moduleArray))
		{
			$moduleModel = new Model\Module();
			$moduleModel->createByArray(static::$_moduleArray);

			/* create from sql */

			$directory = 'modules' . DIRECTORY_SEPARATOR . static::$_moduleArray['alias'] . DIRECTORY_SEPARATOR . 'database';
			if (is_dir($directory))
			{
				$installer = new Installer($this->_registry, $this->_request, $this->_language, $this->_config);
				$installer->init($directory);
				$installer->rawCreate();
			}
			Db::clearCache();
			return Db::forTablePrefix('modules')->where('alias', static::$_moduleArray['alias'])->count() === 1;
		}
		return false;
	}

	/**
	 * uninstall the module
	 *
	 * @since 2.6.0
	 *
	 * @return boolean
	 */

	public function uninstall()
	{
		if (is_array(static::$_moduleArray) && array_key_exists('alias', static::$_moduleArray))
		{
			$moduleModel = new Model\Module();
			$moduleModel->deleteByAlias(static::$_moduleArray['alias']);

			/* drop from sql */

			$directory = 'modules' . DIRECTORY_SEPARATOR . static::$_moduleArray['alias'] . DIRECTORY_SEPARATOR . 'database';
			if (is_dir($directory))
			{
				$installer = new Installer($this->_registry, $this->_request, $this->_language, $this->_config);
				$installer->init($directory);
				$installer->rawDrop();
			}
			Db::clearCache();
			return Db::forTablePrefix('modules')->where('alias', static::$_moduleArray['alias'])->count() === 0;
		}
		return false;
	}
}