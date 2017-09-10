<?php
namespace Redaxscript\Template\Helper;

use Redaxscript\Db;
use Redaxscript\Model;

/**
 * helper class to provide a canonical helper
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Template
 * @author Henry Ruhs
 */

class Canonical extends HelperAbstract
{
	/**
	 * process
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */

	public function process()
	{
		$contentModel = new Model\Content();
		$lastTable = $this->_registry->get('lastTable');
		$lastId = $this->_registry->get('lastId');
		$parameterRoute = $this->_registry->get('parameterRoute');
		$root = $this->_registry->get('root');

		/* find route */

		if ($lastTable === 'categories')
		{
			$articles = Db::forTablePrefix('articles')->where('category', $lastId);
			$articlesTotal = $articles->count();
			if ($articlesTotal === 1)
			{
				$lastTable = 'articles';
				$lastId = $articles->findOne()->id;
			}
		}
		$canonicalRoute = $contentModel->getRouteByTableAndId($lastTable, $lastId);

		/* handle route */

		if ($canonicalRoute)
		{
			return $root . '/' . $parameterRoute . $canonicalRoute;
		}
		return $root;
	}
}
