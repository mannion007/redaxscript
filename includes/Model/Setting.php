<?php
namespace Redaxscript\Model;

use Redaxscript\Db;

/**
 * parent class to provide the setting model
 *
 * @since 4.0.0
 *
 * @package Redaxscript
 * @category Model
 * @author Henry Ruhs
 */

class Setting
{
	/**
	 * get the value from setting
	 *
	 * @since 4.0.0
	 *
	 * @param string $key key of the item
	 *
	 * @return string|bool
	 */

	public function get($key = null)
	{
		$settings = Db::forTablePrefix('settings')->findMany();

		/* process settings */

		if ($key)
		{
			foreach ($settings as $setting)
			{
				if ($setting->name === $key)
				{
					return $setting->value;
				}
			}
		}
		return false;
	}

	/**
	 * set the value to setting
	 *
	 * @since 4.0.0
	 *
	 * @param string $key key of the item
	 * @param string $value value of the item
	 *
	 * @return bool
	 */

	public function set(string $key = null, string $value = null) : bool
	{
		return Db::forTablePrefix('settings')
			->where('name', $key)
			->findOne()
			->set('value', $value)
			->save();
	}
}
