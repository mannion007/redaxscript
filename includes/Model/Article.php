<?php
namespace Redaxscript\Model;

use Redaxscript\Db;

/**
 * parent class to provide the article model
 *
 * @since 3.3.0
 *
 * @package Redaxscript
 * @category Model
 * @author Henry Ruhs
 */

class Article
{
	/**
	 * get the article id by alias
	 *
	 * @since 3.3.0
	 *
	 * @param string $articleAlias
	 *
	 * @return integer
	 */

	public function getIdByAlias($articleAlias = null)
	{
		return Db::forTablePrefix('articles')->where('alias', $articleAlias)->findOne()->id;
	}

	/**
	 * get the article route by id
	 *
	 * @since 3.3.0
	 *
	 * @param integer $articleId
	 *
	 * @return string
	 */

	public function getRouteById($articleId = null)
	{
		$route = null;
		$articleArray = Db::forTablePrefix('articles')
			->tableAlias('a')
			->leftJoinPrefix('categories', 'a.category = c.id', 'c')
			->leftJoinPrefix('categories', 'c.parent = p.id', 'p')
			->select('p.alias', 'parent_alias')
			->select('c.alias', 'category_alias')
			->select('a.alias', 'article_alias')
			->where('a.id', $articleId)
			->findArray();

		/* build route */

		if (is_array($articleArray[0]))
		{
			$route = implode('/', array_filter($articleArray[0]));
		}
		return $route;
	}

	/**
	 * publish each article by date
	 *
	 * @since 3.3.0
	 *
	 * @param string $date
	 *
	 * @return integer
	 */

	public function publishByDate($date = null)
	{
		$articles = Db::forTablePrefix('articles')
			->where('status', 2)
			->whereLt('date', $date)
			->findMany()
			->set('status', 1)
			->save();
		return count($articles);
	}
}
