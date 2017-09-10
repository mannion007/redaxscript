<?php
namespace Redaxscript\Model;

use Redaxscript\Db;

/**
 * parent class to provide the search model
 *
 * @since 3.3.0
 *
 * @package Redaxscript
 * @category Model
 * @author Henry Ruhs
 */

class Search extends ModelAbstract
{
	/**
	 * get by the table
	 *
	 * @since 3.3.0
	 *
	 * @param string $table
	 * @param string $search
	 *
	 * @return array
	 */

	public function getByTable($table = null, $search = null)
	{
		$columnArray = $this->_buildColumnArray($table);
		$likeArray = $this->_buildLikeArray($table, $search);
		return Db::forTablePrefix($table)
			->whereLikeMany($columnArray, $likeArray)
			->where('status', 1)
			->whereLanguageIs($this->_registry->get('language'))
			->orderByDesc('date')
			->findMany();
	}

	/**
	 * build the column array
	 *
	 * @since 3.3.0
	 *
	 * @param string $table name of the table
	 *
	 * @return array
	 */

	protected function _buildColumnArray($table = null)
	{
		return array_filter(
		[
			$table === 'categories' || $table === 'articles' ? 'title' : null,
			$table === 'categories' || $table === 'articles' ? 'description' : null,
			$table === 'categories' || $table === 'articles' ? 'keywords' : null,
			$table === 'articles' || $table === 'comments' ? 'text' : null
		]);
	}

	/**
	 * build the like array
	 *
	 * @since 3.3.0
	 *
	 * @param string $table name of the table
	 * @param array $search value of the search
	 *
	 * @return array
	 */

	protected function _buildLikeArray($table = null, $search = null)
	{
		return array_filter(
		[
			$table === 'categories' || $table === 'articles' ? '%' . $search . '%' : null,
			$table === 'categories' || $table === 'articles' ? '%' . $search . '%' : null,
			$table === 'categories' || $table === 'articles' ? '%' . $search . '%' : null,
			$table === 'articles' || $table === 'comments' ? '%' . $search . '%' : null
		]);
	}
}
