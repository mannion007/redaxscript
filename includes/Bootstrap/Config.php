<?php
namespace Redaxscript\Bootstrap;

use Redaxscript\Db;

/**
 * children class to boot the config
 *
 * @since 3.1.0
 *
 * @package Redaxscript
 * @category Bootstrap
 * @author Henry Ruhs
 */

class Config
{
	/**
	 * automate run
	 *
	 * @since 3.1.0
	 */

	protected function _autorun()
	{
		if (function_exists('ini_set'))
		{
			if (Db::getStatus() === 2)
			{
				ini_set('default_charset', Db::getSetting('charset'));
			}
			if (error_reporting() === 0)
			{
				ini_set('display_startup_errors', 0);
				ini_set('display_errors', 0);
			}
			ini_set('mbstring.substitute_character', 0);
			ini_set('session.use_trans_sid', 0);
			ini_set('url_rewriter.tags', 0);
		}
	}
}
