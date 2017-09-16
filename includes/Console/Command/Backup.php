<?php
namespace Redaxscript\Console\Command;

use Redaxscript\Console\Parser;
use Redaxscript\Filesystem;

/**
 * children class to execute the backup command
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Console
 * @author Henry Ruhs
 */

class Backup extends CommandAbstract
{
	/**
	 * array of the command
	 *
	 * @var array
	 */

	protected $_commandArray =
	[
		'backup' =>
		[
			'description' => 'Backup command',
			'argumentArray' =>
			[
				'database' =>
				[
					'description' => 'Backup the database',
					'optionArray' =>
					[
						'directory' =>
						[
							'description' => 'Required directory'
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
			return $this->_database($parser->getOption());
		}
		return $this->getHelp();
	}

	/**
	 * backup the database
	 *
	 * @since 3.0.0
	 *
	 * @param array $optionArray
	 *
	 * @return string
	 */

	protected function _database(array $optionArray = [])
	{
		$dbType = $this->_config->get('dbType');
		$dbHost = $this->_config->get('dbHost');
		$dbName = $this->_config->get('dbName');
		$dbUser = $this->_config->get('dbUser');
		$dbPassword = $this->_config->get('dbPassword');
		$directory = $this->prompt('directory', $optionArray);
		$date = date('Y_m_d_H:i:s');
		$file = $dbName ? $dbName . '_' . $date . '.' . $dbType : $date . '.' . $dbType;

		/* backup filesystem */

		$backupFilesystem = new Filesystem\Directory();
		$backupFilesystem->init($directory);

		/* backup */

		if (is_dir($directory) || is_string($directory) && mkdir($directory))
		{
			$command = 'echo not supported';
			if ($dbType === 'mysql' && $dbHost && $dbName && $dbUser)
			{
				$command = 'mysqldump -u ' . $dbUser . ' -p' . $dbPassword . ' -h ' . $dbHost . ' ' . $dbName;
			}
			if ($dbType === 'pgsql' && $dbHost && $dbName)
			{
				$command = 'PGPASSWORD=' . $dbPassword . ' pg_dump -U postgres -h ' . $dbHost . ' ' . $dbName;
			}
			if ($dbType === 'sqlite' && $dbHost)
			{
				$command = 'sqlite3 ' . $dbHost . ' .dump';
			}
			exec($command, $outputArray, $error);
			$content = implode($outputArray, PHP_EOL);
			return $error === 0 && $backupFilesystem->writeFile($file, $content);
		}
		return false;
	}
}