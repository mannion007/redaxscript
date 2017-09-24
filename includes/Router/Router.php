<?php
namespace Redaxscript\Router;

use Redaxscript\Controller;
use Redaxscript\Messenger;
use Redaxscript\Model;
use Redaxscript\Module;
use Redaxscript\View;

/**
 * parent class to provide the router
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
		Module\Hook::trigger('routeHeader');

		/* handle break */

		if ($this->_registry->get('routerBreak'))
		{
			$this->_registry->set('contentError', false);
			exit();
		}

		/* handle post */

		if ($this->_request->getPost('Redaxscript\View\SearchForm'))
		{
			$this->_redirectSearch();
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
		Module\Hook::trigger('routeContent');
		$firstParameter = $this->getFirst();
		$fileInstall = $this->_registry->get('file') === 'install.php' && $this->_config->get('env') !== 'production';

		/* handle attack */

		if ($this->_request->getPost() && $this->_request->getPost('token') !== $this->_registry->get('token'))
		{
			return $this->_preventCSRF();
		}

		/* handle post */

		if ($this->_request->getPost('Redaxscript\View\CommentForm'))
		{
			return $this->_processComment();
		}
		if ($this->_request->getPost('Redaxscript\View\LoginForm'))
		{
			return $this->_processLogin();
		}
		if ($this->_request->getPost('Redaxscript\View\ResetForm'))
		{
			return $this->_processReset();
		}
		if ($this->_request->getPost('Redaxscript\View\RecoverForm'))
		{
			return $this->_processRecover();
		}
		if ($this->_request->getPost('Redaxscript\View\RegisterForm'))
		{
			return $this->_processRegister();
		}
		if ($fileInstall && $this->_request->getPost('Redaxscript\View\InstallForm'))
		{
			return $this->_processInstall();
		}

		/* handle route */

		if ($firstParameter === 'search')
		{
			return $this->_processSearch();
		}
		if ($firstParameter === 'login')
		{
			return $this->_renderLogin();
		}
		if ($firstParameter === 'logout')
		{
			return $this->_processLogout();
		}
		if ($firstParameter === 'register')
		{
			return $this->_renderRegister();
		}
		if ($fileInstall)
		{
			return $this->_renderInstall();
		}
		ob_start();
		contents();
		return ob_get_clean();
	}

	/**
	 * prevent cross site request forgery
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _preventCSRF() : string
	{
		$messenger = new Messenger($this->_registry);
		return $messenger
			->setUrl($this->_language->get('home'), $this->_registry->get('root'))
			->error($this->_language->get('token_incorrect'), $this->_language->get('error_occurred'));
	}

	/**
	 * redirect the search
	 *
	 * @since 4.0.0
	 */

	protected function _redirectSearch()
	{
		$searchController = new Controller\Search($this->_registry, $this->_request, $this->_language);
		$searchController->doRedirect();
	}

	/**
	 * process the search
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _processSearch() : string
	{
		$searchController = new Controller\Search($this->_registry, $this->_request, $this->_language);
		return $searchController->process();
	}

	/**
	 * process the comment
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _processComment() : string
	{
		$commentController = new Controller\Comment($this->_registry, $this->_request, $this->_language);
		return $commentController->process();
	}

	/**
	 * process the login
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _processLogin() : string
	{
		$loginController = new Controller\Login($this->_registry, $this->_request, $this->_language);
		return $loginController->process();
	}

	/**
	 * process the reset
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _processReset() : string
	{
		$resetController = new Controller\Reset($this->_registry, $this->_request, $this->_language);
		return $resetController->process();
	}

	/**
	 * process the recover
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _processRecover() : string
	{
		$recoverController = new Controller\Recover($this->_registry, $this->_request, $this->_language);
		return $recoverController->process();
	}

	/**
	 * process the register
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _processRegister() : string
	{
		$registerController = new Controller\Register($this->_registry, $this->_request, $this->_language);
		return $registerController->process();
	}

	/**
	 * process the logout
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _processLogout() : string
	{
		$logoutController = new Controller\Logout($this->_registry, $this->_request, $this->_language);
		return $logoutController->process();
	}

	/**
	 * process the install
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _processInstall() : string
	{
		$this->_request->setSession('installArray',
		[
			'dbType' => $this->_request->getPost('db-type'),
			'dbHost' => $this->_request->getPost('db-host'),
			'dbName' => $this->_request->getPost('db-name'),
			'dbUser' => $this->_request->getPost('db-user'),
			'dbPassword' => $this->_request->getPost('db-password'),
			'dbPrefix' => $this->_request->getPost('db-prefix'),
			'adminName' => $this->_request->getPost('admin-name'),
			'adminUser' => $this->_request->getPost('admin-user'),
			'adminPassword' => $this->_request->getPost('admin-password'),
			'adminEmail' => $this->_request->getPost('admin-email')
		]);
		$installController = new Controller\Install($this->_registry, $this->_request, $this->_language, $this->_config);
		return $installController->process();
	}

	/**
	 * render the login
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _renderLogin() : string
	{
		$secondParameter = $this->getSecond();
		$thirdParameter = $this->getThird();
		$thirdSubParameter = $this->getThirdSub();
		$settingModel = new Model\Setting();

		/* handle login */

		if ((int)$settingModel->get('recovery') === 1)
		{
			if ($secondParameter === 'recover')
			{
				$recoverForm = new View\RecoverForm($this->_registry, $this->_language);
				return $recoverForm->render();
			}
			if ($secondParameter === 'reset' && $thirdParameter && $thirdSubParameter)
			{
				$resetForm = new View\ResetForm($this->_registry, $this->_language);
				return $resetForm->render();
			}
		}
		$loginForm = new View\LoginForm($this->_registry, $this->_language);
		return $loginForm->render();
	}

	/**
	 * render the register
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _renderRegister() : string
	{
		$settingModel = new Model\Setting();
		if ($settingModel->get('registration'))
		{
			$registerForm = new View\RegisterForm($this->_registry, $this->_language);
			return $registerForm->render();
		}
	}

	/**
	 * render the install
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */

	protected function _renderInstall() : string
	{
		$installArray = $this->_request->getSession('installArray');
		$systemStatus = new View\SystemStatus($this->_registry, $this->_language);
		$installForm = new View\InstallForm($this->_registry, $this->_language);
		return $systemStatus->render() . $installForm->render($installArray ? $installArray : []);
	}
}