<?php
namespace Redaxscript\Bootstrap;

use Redaxscript\Db;
use Redaxscript\Model;
use Redaxscript\Validator;

/**
 * children class to boot the content
 *
 * @since 3.1.0
 *
 * @package Redaxscript
 * @category Bootstrap
 * @author Henry Ruhs
 */

class Content extends BootstrapAbstract
{
	/**
	 * automate run
	 *
	 * @since 3.1.0
	 */

	protected function _autorun()
	{
		if ($this->_registry->get('dbStatus') === 2)
		{
			$this->_setContent();
		}
		$this->_setContentError();
	}

	/**
	 * set content
	 *
	 * @since 3.1.0
	 */

	protected function _setContent()
	{
		$firstParameter = $this->_registry->get('firstParameter');
		$secondParameter = $this->_registry->get('secondParameter');
		$fullRoute = $this->_registry->get('fullRoute');

		/* set by root */

		if (!$fullRoute || ($firstParameter === 'admin' && !$secondParameter))
		{
			$this->_setTableByRoot();
			$this->_setIdByRoot();
		}

		/* else set by parameter */

		else
		{
			$this->_setTableByParameter();
			$this->_setIdByParameter();
		}
	}

	/**
	 * set table by root
	 *
	 * @since 3.1.0
	 */

	protected function _setTableByRoot()
	{
		$settingModel = new Model\Setting();
		$homepage = $settingModel->getSetting('homepage');
		$table = $homepage > 0 ? 'articles' : 'categories';

		/* set registry */

		$this->_registry->set('firstTable', $table);
		$this->_registry->set('lastTable', $table);
	}

	/**
	 * set table by parameter
	 *
	 * @since 3.1.0
	 */

	protected function _setTableByParameter()
	{
		$firstParameter = $this->_registry->get('firstParameter');
		$secondParameter = $this->_registry->get('secondParameter');
		$thirdParameter = $this->_registry->get('thirdParameter');
		$lastParameter = $this->_registry->get('lastParameter');
		$contentModel = new Model\Content();

		/* set registry */

		if ($firstParameter)
		{
			$this->_registry->set('firstTable', $contentModel->getTableByAlias($firstParameter));
			if ($this->_registry->get('firstTable'))
			{
				$this->_registry->set('secondTable', $contentModel->getTableByAlias($secondParameter));
			}
			if ($this->_registry->get('secondTable'))
			{
				$this->_registry->set('thirdTable', $contentModel->getTableByAlias($thirdParameter));
			}
			if ($this->_registry->get('lastParameter'))
			{
				$this->_registry->set('lastTable', $contentModel->getTableByAlias($lastParameter));
			}
		}
	}

	/**
	 * set id
	 *
	 * @since 3.1.0
	 *
	 * @param array $whereArray
	 */

	protected function _setId($whereArray = [])
	{
		$aliasValidator = new Validator\Alias();
		$firstParameter = $this->_registry->get('firstParameter');
		$lastTable = $this->_registry->get('lastTable');
		if ($lastTable)
		{
			$id = Db::forTablePrefix($lastTable)
				->where($whereArray)
				->findOne()
				->id;
		}

		/* set registry */

		if ($firstParameter === 'admin' || $aliasValidator->validate($firstParameter, Validator\Alias::MODE_DEFAULT) === Validator\ValidatorInterface::FAILED)
		{
			if ($lastTable === 'categories')
			{
				$this->_registry->set('categoryId', $id);
				$this->_registry->set('lastId', $id);
			}
			if ($lastTable === 'articles')
			{
				$this->_registry->set('articleId', $id);
				$this->_registry->set('lastId', $id);
			}
		}
	}

	/**
	 * set id by root
	 *
	 * @since 4.0.0
	 */

	protected function _setIdByRoot()
	{
		$settingModel = new Model\Setting();
		$order = $settingModel->getSetting('order');
		$lastTable = $this->_registry->get('lastTable');
		$result = Db::forTablePrefix($lastTable);

		/* handle order */

		if ($order === 'asc')
		{
			$lastRank = $result->min('rank');
		}
		if ($order === 'desc')
		{
			$lastRank = $result->max('rank');
		}

		/* last rank */

		if ($lastRank)
		{
			$this->_setId(
			[
				'rank' => $lastRank,
				'status' => 1
			]);
		}
	}

	/**
	 * set id by parameter
	 *
	 * @since 3.1.0
	 */

	protected function _setIdByParameter()
	{
		$lastParameter = $this->_registry->get('lastParameter');
		if ($lastParameter)
		{
			$this->_setId(
			[
				'alias' => $lastParameter,
				'status' => 1
			]);
		}
	}

	/**
	 * set content error
	 *
	 * @since 3.1.0
	 */

	protected function _setContentError()
	{
		$aliasValidator = new Validator\Alias();
		$lastId = $this->_registry->get('lastId');
		$firstParameter = $this->_registry->get('firstParameter');
		$contentError = !$lastId && $aliasValidator->validate($firstParameter, Validator\Alias::MODE_DEFAULT) === Validator\ValidatorInterface::FAILED;
		$this->_registry->set('contentError', $contentError);
	}
}
