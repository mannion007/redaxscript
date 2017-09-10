<?php
namespace Redaxscript\Model;

/**
 * parent class to provide the content model
 *
 * @since 3.3.0
 *
 * @package Redaxscript
 * @category Model
 * @author Henry Ruhs
 */

class Content
{
	/**
	 * get the content table by alias
	 *
	 * @since 3.3.0
	 *
	 * @param string $alias
	 *
	 * @return string
	 */

	public function getTableByAlias($alias = null)
	{
		$table = null;
		if (is_string($alias))
		{
			$categoryModel = new Category();
			if ($categoryModel->getIdByAlias($alias) > 0)
			{
				$table = 'categories';
			}
			else
			{
				$articleModel = new Article();
				if ($articleModel->getIdByAlias($alias) > 0)
				{
					$table = 'articles';
				}
			}
		}
		return $table;
	}

	/**
	 * get the route by table and content id
	 *
	 * @since 3.3.0
	 *
	 * @param string $table
	 * @param string $contentId
	 *
	 * @return string
	 */

	public function getRouteByTableAndId($table = null, $contentId = null)
	{
		$route = null;
		if (is_string($table) && is_numeric($contentId))
		{
			/* switch table */

			switch ($table)
			{
				case 'categories':
					$categoryModel = new Category();
					$route = $categoryModel->getRouteById($contentId);
					break;
				case 'articles':
					$articleModel = new Article();
					$route = $articleModel->getRouteById($contentId);
					break;
				case 'comments':
					$commentModel = new Comment();
					$route = $commentModel->getRouteById($contentId);
					break;
			}
		}
		return $route;
	}
}
