<?php
namespace Redaxscript\Model;

use Redaxscript\Db;

/**
 * parent class to provide the comment model
 *
 * @since 4.0.0
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
	 * @since 4.0.0
	 *
	 * @param int $commentId
	 *
	 * @return string
	 */

	public function getRouteById(int $commentId = null)
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
	 * get the comment by article
	 *
	 * @since 4.0.0
	 *
	 * @param int $articleId
	 * @param int $offset
	 * @param string $language
	 *
	 * @return object
	 */

	public function getByArticleAndSub(int $articleId = null, int $offset = null, string $language = null)
	{
		$settingModel = new Setting();
		$comments = Db::forTablePrefix('comments')
			->where('article', $articleId)
			->where('status', 1)
			->whereLanguageIs($language)
			->orderGlobal('rank');

		// this handles pagination for comments ... http://dev.redaxscript.com/home/welcome/2 ... 2 equals $offset
		// you need a unit test first before you simplify this code
		$num_rows = $comments->findMany()->count();
		$sub_maximum = ceil($num_rows / $settingModel->get('limit'));
		$sub_active = $offset;
		if ($offset > $sub_maximum || !$offset)
		{
			$sub_active = 1;
		}
		else
		{
			$offset_string = ($sub_active - 1) * $settingModel->get('limit') . ', ';
		}
		return $comments
			->limit($offset_string . $settingModel->get('limit'))
			->findArray();
	}

	/**
	 * publish each comment by date
	 *
	 * @since 4.0.0
	 *
	 * @param string $date
	 *
	 * @return int
	 */

	public function publishByDate(string $date = null) : int
	{
		return Db::forTablePrefix('comments')
			->where('status', 2)
			->whereLt('date', $date)
			->findMany()
			->set('status', 1)
			->save()
			->count();
	}

	/**
	 * create the comment by array
	 *
	 * @since 4.0.0
	 *
	 * @param array $createArray
	 *
	 * @return bool
	 */

	public function createByArray(array $createArray = []) : bool
	{
		return Db::forTablePrefix('comments')
			->create()
			->set(
			[
				'author' => $createArray['author'],
				'email' => $createArray['email'],
				'url' => $createArray['url'],
				'text' => $createArray['text'],
				'language' => $createArray['language'],
				'article' => $createArray['article']
			])
			->save();
	}
}
