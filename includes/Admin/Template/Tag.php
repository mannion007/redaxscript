<?php
namespace Redaxscript\Admin\Template;

use Redaxscript\Template\Tag as BaseTag;

/**
 * parent class to provide admin template tags
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Admin
 * @author Henry Ruhs
 */

class Tag extends BaseTag
{
	/**
	 * panel
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 *
	 * @codeCoverageIgnore
	 */

	public static function panel() : string
	{
		return self::_migrate('admin_panel_list');
	}
}
