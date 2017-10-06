<?php
namespace Redaxscript\Modules\Validator;

use Redaxscript\Module;

/**
 * children class to store module configuration
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Modules
 * @author Henry Ruhs
 */

class Config extends Module\Notification
{
	/**
	 * array of config
	 *
	 * @var array
	 */

	protected $_configArray =
	[
		'className' =>
		[
			'text' => 'rs-admin-text-panel',
			'code' => 'rs-admin-code-panel',
			'warning' => 'rs-admin-is-warning',
			'error' => 'rs-admin-is-error'
		],
		'apiUrl' => 'https://validator.w3.org/nu/?doc=',
		'typeArray' =>
		[
			'warning',
			'error'
		]
	];
}