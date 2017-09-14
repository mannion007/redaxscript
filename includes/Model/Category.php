<?php
namespace Redaxscript\Model;

use Redaxscript\Db;

/**
 * parent class to provide the category model
 *
 * @since 3.3.0
 *
 * @package Redaxscript
 * @category Model
 * @author Henry Ruhs
 */

class Category
{
	/**
	 * get the category id by alias
	 *
	 * @since 3.3.0
	 *
	 * @param string $categoryAlias
	 *
	 * @return integer
	 */

	public function getIdByAlias($categoryAlias = null)
	{
		return Db::forTablePrefix('categories')->where('alias', $categoryAlias)->findOne()->id;
	}

	/**
	 * get the category route by id
	 *
	 * @since 3.3.0
	 *
	 * @param integer $categoryId
	 *
	 * @return string
	 */

	public function getRouteById($categoryId = null)
	{
		$route = null;
		$categoryArray = Db::forTablePrefix('categories')
			->tableAlias('c')
			->leftJoinPrefix('categories', 'c.parent = p.id', 'p')
			->select('p.alias', 'parent_alias')
			->select('c.alias', 'category_alias')
			->where('c.id', $categoryId)
			->findArray();

		/* build route */

		if (is_array($categoryArray[0]))
		{
			$route = implode('/', array_filter($categoryArray[0]));
		}
		return $route;
	}

	/**
	 * publish each category by date
	 *
	 * @since 3.3.0
	 *
	 * @param string $date
	 *
	 * @return integer
	 */

	public function publishByDate($date = null)
	{
		$categories = Db::forTablePrefix('categories')
			->where('status', 2)
			->whereLt('date', $date)
			->findMany()
			->set('status', 1)
			->save();
		return count($categories);
	}
}
