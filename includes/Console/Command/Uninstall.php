<?php
namespace Redaxscript\Console\Command;

use Redaxscript\Db;
use Redaxscript\Console\Parser;
use Redaxscript\Installer;

/**
 * children class to execute the uninstall command
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Console
 * @author Henry Ruhs
 */

class Uninstall extends CommandAbstract
{
	/**
	 * array of the command
	 *
	 * @var array
	 */

	protected $_commandArray =
	[
		'uninstall' =>
		[
			'description' => 'Uninstall command',
			'argumentArray' =>
			[
				'database' =>
				[
					'description' => 'Uninstall the database'
				],
				'module' =>
				[
					'description' => 'Uninstall the module',
					'optionArray' =>
					[
						'alias' =>
						[
							'description' => 'Required module alias'
						]
					]
				]
			]
		]
	];

	/**
	 * run the command
	 *
	 * @since 3.0.0
	 *
	 * @param string $mode name of the mode
	 *
	 * @return string
	 */

	public function run($mode = null)
	{
		$parser = new Parser($this->_request);
		$parser->init($mode);

		/* run command */

		$argumentKey = $parser->getArgument(1);
		if ($argumentKey === 'database')
		{
			return $this->_database();
		}
		if ($argumentKey === 'module')
		{
			return $this->_module($parser->getOption());
		}
		return $this->getHelp();
	}

	/**
	 * install the database
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */

	protected function _database()
	{
		$installer = new Installer($this->_registry, $this->_request, $this->_language, $this->_config);
		$installer->init();
		$installer->rawDrop();
		return Db::getStatus() === 1;
	}

	/**
	 * uninstall the module
	 *
	 * @since 3.0.0
	 *
	 * @param array $optionArray
	 *
	 * @return bool
	 */

	protected function _module(array $optionArray = [])
	{
		$alias = $this->prompt('alias', $optionArray);
		$moduleClass = 'Redaxscript\\Modules\\' . $alias . '\\' . $alias;

		/* uninstall */

		if (class_exists($moduleClass))
		{
			$module = new $moduleClass($this->_registry, $this->_request, $this->_language, $this->_config);
			return $module->uninstall();
		}
		return false;
	}
}
