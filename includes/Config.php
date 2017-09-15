<?php
namespace Redaxscript;

/**
 * children class to store database config
 *
 * @since 2.4.0
 *
 * @package Redaxscript
 * @category Config
 * @author Henry Ruhs
 */

class Config extends Singleton
{
	/**
	 * path to config file
	 *
	 * @var string
	 */

	protected static $_configFile = 'config.php';

	/**
	 * array of the config
	 *
	 * @var array
	 */

	protected static $_configArray = [];

	/**
	 * init the class
	 *
	 * @since 2.4.0
	 *
	 * @param string $configFile file with config
	 */

	public function init($configFile = null)
	{
		if (is_file($configFile))
		{
			self::$_configFile = $configFile;
		}

		/* load config */

		$configArray = include(self::$_configFile);
		if (is_array($configArray))
		{
			self::$_configArray = $configArray;
		}
	}

	/**
	 * get the value from config
	 *
	 * @since 2.2.0
	 *
	 * @param string $key key of the item
	 *
	 * @return string|array|boolean
	 */

	public function get($key = null)
	{
		if (is_array(self::$_configArray) && array_key_exists($key, self::$_configArray))
		{
			return self::$_configArray[$key];
		}
		else if (!$key)
		{
			return self::$_configArray;
		}
		return false;
	}

	/**
	 * set the value to config
	 *
	 * @since 2.2.0
	 *
	 * @param string $key key of the item
	 * @param string $value value of the item
	 */

	public function set($key = null, $value = null)
	{
		self::$_configArray[$key] = $value;
	}

	/**
	 * parse from database url
	 *
	 * @since 3.0.0
	 *
	 * @param string $dbUrl database url to be parsed
	 */

	public function parse($dbUrl = null)
	{
		$dbUrl = parse_url($dbUrl);
		$this->clear();
		$this->set('dbType', str_replace('postgres', 'pgsql', $dbUrl['scheme']));
		$this->set('dbHost', $dbUrl['port'] ? $dbUrl['host'] . ':' . $dbUrl['port'] : $dbUrl['host']);
		$this->set('dbName', trim($dbUrl['path'], '/'));
		$this->set('dbUser', $dbUrl['user']);
		$this->set('dbPassword', $dbUrl['pass']);
	}

	/**
	 * write config to file
	 *
	 * @since 2.4.0
	 *
	 * @return boolean
	 */

	public function write()
	{
		$configKeys = array_keys(self::$_configArray);
		$lastKey = end($configKeys);

		/* process config */

		$content = '<?php' . PHP_EOL . 'return' . PHP_EOL . '[' . PHP_EOL;
		foreach (self::$_configArray as $key => $value)
		{
			if ($value)
			{
				$content .= '	\'' . $key . '\' => \'' . $value . '\'';
			}
			else
			{
				$content .= '	\'' . $key . '\' => null';
			}
			if ($key !== $lastKey)
			{
				$content .= ',';
			}
			$content .= PHP_EOL;
		}
		$content .= '];';

		/* write to file */

		return file_put_contents(self::$_configFile, $content) > 0;
	}

	/**
	 * clear the config
	 *
	 * @since 3.0.0
	 */

	public function clear()
	{
		self::$_configArray = [];
	}
}
