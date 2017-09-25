<?php
namespace Redaxscript\Router;

use Redaxscript\Config;
use Redaxscript\Registry;
use Redaxscript\Request;
use Redaxscript\Language;
use Redaxscript\Messenger;

/**
 * abstract class to create a router class
 *
 * @since 4.0.0
 *
 * @package Redaxscript
 * @category Router
 * @author Henry Ruhs
 */

class RouterAbstract extends Parameter
{
	/**
	 * instance of the registry class
	 *
	 * @var Registry
	 */

	protected $_registry;

	/**
	 * instance of the language class
	 *
	 * @var Language
	 */

	protected $_language;

	/**
	 * instance of the config class
	 *
	 * @var Config
	 */

	protected $_config;

	/**
	 * constructor of the class
	 *
	 * @since 4.0.0
	 *
	 * @param Registry $registry instance of the registry class
	 * @param Request $request instance of the request class
	 * @param Language $language instance of the language class
	 * @param Config $config instance of the config class
	 */

	public function __construct(Registry $registry, Request $request, Language $language, Config $config)
	{
		parent::__construct($request);
		$this->_registry = $registry;
		$this->_language = $language;
		$this->_config = $config;
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
		if ($this->_request->getPost() && $this->_request->getPost('token') !== $this->_registry->get('token'))
		{
			return $this->_preventCSRF();
		}
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
}