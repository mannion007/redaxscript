<?php
namespace Redaxscript\Model;

use Redaxscript\Db;

/**
 * parent class to provide the comment model
 *
 * @since 3.3.0
 *
 * @package Redaxscript
 * @category Model
 * @author Henry Ruhs
 */

class Comment
{
	/**
	 * get the comment route by id
	 *
	 * @since 3.3.0
	 *
	 * @param integer $commentId
	 *
	 * @return string
	 */

	public function getRouteById($commentId = null)
	{
		$route = null;
		$commentArray = Db::forTablePrefix('comments')
			->tableAlias('m')
			->leftJoinPrefix('articles', 'm.article = a.id', 'a')
			->leftJoinPrefix('categories', 'a.category = c.id', 'c')
			->leftJoinPrefix('categories', 'c.parent = p.id', 'p')
			->select('p.alias', 'parent_alias')
			->select('c.alias', 'category_alias')
			->select('a.alias', 'article_alias')
			->where('m.id', $commentId)
			->findArray();

		/* build route */

		if (is_array($commentArray[0]))
		{
			$route = implode('/', array_filter($commentArray[0])) . '#comment-' . $commentId;
		}
		return $route;
	}

	/**
	 * publish each comment by date
	 *
	 * @since 3.3.0
	 *
	 * @param string $date
	 *
	 * @return integer
	 */

	public function publishByDate($date = null)
	{
		$comments = Db::forTablePrefix('comments')
			->where('status', 2)
			->whereLt('date', $date)
			->findMany()
			->set('status', 1)
			->save();
		return count($comments);
	}

	/**
	 * get article comments
	 *
	 * @since 3.3.0
	 *
	 * @param integer $articleId
	 * @param string $language
	 * @param integer $limit
	 *
	 * @return object
	 */

	public function getCommentsById($articleId = null, $language = 'en', $limit = null)
	{
		$comments = Db::forTablePrefix('comments')
			->where(
				[
					'status' => 1,
					'article' => $articleId
				])
			->whereLanguageIs($language)
			->orderGlobal('rank');
		if ($limit)
		{
			$comments->limit($limit);
		}

		return $comments->findArray();
	}
}
