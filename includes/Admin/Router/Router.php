<?php
namespace Redaxscript\Admin\Router;

use Redaxscript\Admin;
use Redaxscript\Module;
use Redaxscript\Router\RouterAbstract;

/**
 * parent class to provide the admin router
 *
 * @since 4.0.0
 *
 * @package Redaxscript
 * @category Router
 * @author Henry Ruhs
 */

class Router extends RouterAbstract
{
	/**
	 * route the header
	 *
	 * @since 4.0.0
	 */

	public function routeHeader()
	{
		Module\Hook::trigger('adminRouteHeader');

		/* handle break */

		if ($this->_registry->get('adminRouterBreak'))
		{
			$this->_registry->set('contentError', false);
			exit();
		}
	}

	/**
	 * route the content
	 *
	 * @since 4.0.0
	 *
	 * @return string|null
	 */

	public function routeContent()
	{
		Module\Hook::trigger('adminRouteContent');
		$firstParameter = $this->getFirst();
		$adminParameter = $this->getAdmin();
		$tableParameter = $this->getTable();
		$tokenParameter = $this->getToken();
		$tokenArray =
		[
			'sort',
			'up',
			'down',
			'enable',
			'disabled',
			'publish',
			'unpublish',
			'install',
			'uninstall',
			'delete'
		];

		/* handle admin */

		if ($firstParameter === 'admin')
		{
			/* handle token and auth */

			if ($adminParameter)
			{
				if ($this->_request->getPost() && $this->_request->getPost('token') !== $this->_registry->get('token') || in_array($adminParameter, $tokenArray) && !$tokenParameter)
				{
					return $this->_errorToken();
				}
				$authGuard = $this->_authGuard();
				if ($authGuard)
				{
					return $authGuard;
				}
			}
			if (!$adminParameter || $adminParameter == 'view' && $tableParameter == 'users' || $this->_registry->get('cronUpdate'))
			{
				admin_last_update();
			}
			/* handle post */

			if ($this->_request->getPost('Redaxscript\Admin\View\SettingForm'))
			{
				return admin_update();
			}

			/* handle route */

			if ($adminParameter === 'view')
			{
				return $this->_renderView();
			}
			if ($adminParameter === 'new')
			{
				return $this->_renderNew();
			}
			if ($adminParameter === 'edit')
			{
				return $this->_renderEdit();
			}
			if ($adminParameter === 'process')
			{
				return admin_process();
			}
			if ($adminParameter === 'delete')
			{
				return admin_delete();
			}
			if ($adminParameter === 'up' || $adminParameter === 'down')
			{
				return admin_move();
			}
			if ($adminParameter === 'sort')
			{
				return admin_sort();
			}
			if ($adminParameter === 'publish' || $adminParameter === 'enable')
			{
				return admin_status(1);
			}
			if ($adminParameter === 'unpublish' || $adminParameter === 'disable')
			{
				return admin_status(0);
			}
			if ($adminParameter === 'install' || $adminParameter === 'uninstall')
			{
				return admin_install();
			}
		}
	}

	/**
	 * auth guard
	 *
	 * @since 4.0.0
	 *
	 * @return string|null
	 */

	protected function _authGuard()
	{
		$adminParameter = $this->getAdmin();
		$tableParameter = $this->getTable();
		$idParameter = $this->getId();
		$newArray =
		[
			'new',
			'process'
		];
		$editArray =
		[
			'edit',
			'view',
			'sort',
			'up',
			'down',
			'enable',
			'disabled',
			'publish',
			'unpublish',
			'process'
		];
		$permissionNew = in_array($adminParameter, $newArray) && $this->_registry->get('tableNew');
		$permissionEdit = in_array($adminParameter, $editArray) && $this->_registry->get('tableEdit');
		$permissionDelete = $adminParameter === 'delete' && $this->_registry->get('tableDelete');
		$permissionInstall = $adminParameter === 'install' && $this->_registry->get('tableInstall');
		$permissionUninstall = $adminParameter === 'uninstall' && $this->_registry->get('tableUninstall');
		$permissionProfile = $tableParameter === 'users' && $idParameter === $this->_registry->get('myId');

		/* handle permission */

		if (!$permissionNew && !$permissionEdit && !$permissionDelete && !$permissionInstall && !$permissionUninstall && !$permissionProfile)
		{
			return $this->_errorAccess();
		}
	}

	/**
	 * render the view
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _renderView() : string
	{
		$tableParameter = $this->getTable();

		/* handle table */

		ob_start();
		if ($tableParameter == 'categories')
		{
			admin_contents_list();
		}
		if ($tableParameter == 'articles')
		{
			admin_contents_list();
		}
		if ($tableParameter == 'extras')
		{
			admin_contents_list();
		}
		if ($tableParameter == 'comments')
		{
			admin_contents_list();
		}
		if ($tableParameter == 'users')
		{
			admin_users_list();
		}
		if ($tableParameter == 'groups')
		{
			admin_groups_list();
		}
		if ($tableParameter == 'modules')
		{
			admin_modules_list();
		}
		return ob_get_clean();
	}

	/**
	 * render the new
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _renderNew() : string
	{
		$tableParameter = $this->getTable();

		/* handle table */

		if ($tableParameter == 'categories')
		{
			$categoryForm = new Admin\View\CategoryForm($this->_registry, $this->_language);
			return $categoryForm->render();
		}
		if ($tableParameter == 'articles')
		{
			$articleForm = new Admin\View\ArticleForm($this->_registry, $this->_language);
			return $articleForm->render();
		}
		if ($tableParameter == 'extras')
		{
			$extraForm = new Admin\View\ExtraForm($this->_registry, $this->_language);
			return $extraForm->render();
		}
		if ($tableParameter == 'comments')
		{
			$commentForm = new Admin\View\CommentForm($this->_registry, $this->_language);
			return $commentForm->render();
		}
		if ($tableParameter == 'users')
		{
			$userForm = new Admin\View\UserForm($this->_registry, $this->_language);
			return $userForm->render();
		}
		if ($tableParameter == 'groups')
		{
			$groupForm = new Admin\View\GroupForm($this->_registry, $this->_language);
			return $groupForm->render();
		}
	}

	/**
	 * render the edit
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _renderEdit() : string
	{
		$tableParameter = $this->getTable();
		$idParameter = $this->getId();

		/* handle table */

		if ($tableParameter == 'categories')
		{
			$categoryForm = new Admin\View\CategoryForm($this->_registry, $this->_language);
			return $categoryForm->render($idParameter);
		}
		if ($tableParameter == 'articles')
		{
			$articleForm = new Admin\View\ArticleForm($this->_registry, $this->_language);
			return $articleForm->render($idParameter);
		}
		if ($tableParameter == 'extras')
		{
			$extraForm = new Admin\View\ExtraForm($this->_registry, $this->_language);
			return $extraForm->render($idParameter);
		}
		if ($tableParameter == 'comments')
		{
			$commentForm = new Admin\View\CommentForm($this->_registry, $this->_language);
			return $commentForm->render($idParameter);
		}
		if ($tableParameter == 'users')
		{
			$userForm = new Admin\View\UserForm($this->_registry, $this->_language);
			return $userForm->render($idParameter);
		}
		if ($tableParameter == 'groups')
		{
			$groupForm = new Admin\View\GroupForm($this->_registry, $this->_language);
			return $groupForm->render($idParameter);
		}
		if ($tableParameter == 'modules')
		{
			$moduleForm = new Admin\View\ModuleForm($this->_registry, $this->_language);
			return $moduleForm->render($idParameter);
		}
		if ($tableParameter == 'settings')
		{
			$settingForm = new Admin\View\SettingForm($this->_registry, $this->_language);
			return $settingForm->render();
		}
	}

	/**
	 * show the token error
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _errorToken() : string
	{
		$messenger = new Admin\Messenger($this->_registry);
		return $messenger
			->setRoute($this->_language->get('back'), 'admin')
			->error($this->_language->get('token_incorrect'), $this->_language->get('error_occurred'));
	}

	/**
	 * show the access error
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _errorAccess() : string
	{
		$messenger = new Admin\Messenger($this->_registry);
		return $messenger
			->setRoute($this->_language->get('back'), 'admin')
			->error($this->_language->get('access_no'), $this->_language->get('error_occurred'));
	}
}