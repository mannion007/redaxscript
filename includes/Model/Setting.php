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
	 * get the setting
	 *
	 * @since 4.0.0
	 *
	 * @param string $key key of the item
	 *
	 * @return string|array|boolean
	 */

	public static function getSetting($key = null)
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
		else
		{
			return $settings;
		}
		return false;
	}

	/**
	 * set the setting
	 *
	 * @since 4.0.0
	 *
	 * @param string $key key of the item
	 * @param string $value value of the item
	 *
	 * @return bool
	 */

	public static function setSetting(string $key = null, string $value = null) : bool
	{
		return Db::forTablePrefix('settings')
			->where('name', $key)
			->findOne()
			->set('value', $value)
			->save();
	}
}
