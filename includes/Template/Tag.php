<?php
namespace Redaxscript\Template;

use Redaxscript\Db;
use Redaxscript\Config;
use Redaxscript\Console;
use Redaxscript\Breadcrumb;
use Redaxscript\Filesystem;
use Redaxscript\Head;
use Redaxscript\Language;
use Redaxscript\Model;
use Redaxscript\Registry;
use Redaxscript\Request;
use Redaxscript\View;

/**
 * parent class to provide template tags
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Template
 * @author Henry Ruhs
 */

class Tag
{
	/**
	 * base
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */

	public static function base()
	{
		$base = new Head\Base(Registry::getInstance());
		return $base->render();
	}

	/**
	 * title
	 *
	 * @since 3.0.0
	 *
	 * @param string $text
	 *
	 * @return string
	 */

	public static function title($text = null)
	{
		$title = new Head\Title(Registry::getInstance());
		return $title->render($text);
	}

	/**
	 * link
	 *
	 * @since 3.0.0
	 *
	 * @return object
	 */

	public static function link()
	{
		return Head\Link::getInstance();
	}

	/**
	 * meta
	 *
	 * @since 3.0.0
	 *
	 * @return object
	 */

	public static function meta()
	{
		return Head\Meta::getInstance();
	}

	/**
	 * script
	 *
	 * @since 3.0.0
	 *
	 * @return object
	 */

	public static function script()
	{
		return Head\Script::getInstance();
	}

	/**
	 * style
	 *
	 * @since 3.0.0
	 *
	 * @return object
	 */

	public static function style()
	{
		return Head\Style::getInstance();
	}

	/**
	 * breadcrumb
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */

	public static function breadcrumb()
	{
		$breadcrumb = new Breadcrumb(Registry::getInstance(), Language::getInstance());
		$breadcrumb->init();
		return $breadcrumb->render();
	}

	/**
	 * console line
	 *
	 * @since 3.0.0
	 *
	 * @return string|bool
	 */

	public static function consoleLine()
	{
		$console = new Console\Console(Registry::getInstance(), Request::getInstance(), Language::getInstance(), Config::getInstance());
		$output = $console->init('template');
		if (strlen($output))
		{
			return htmlentities($output);
		}
		return false;
	}

	/**
	 * console form
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */

	public static function consoleForm()
	{
		$consoleForm = new View\ConsoleForm(Registry::getInstance(), Language::getInstance());
		return $consoleForm->render();
	}

	/**
	 * search form
	 *
	 * @since 3.0.0
	 *
	 * @param string $table
	 *
	 * @return string
	 */

	public static function searchForm($table = null)
	{
		$searchForm = new View\SearchForm(Registry::getInstance(), Language::getInstance());
		return $searchForm->render($table);
	}

	/**
	 * partial
	 *
	 * @since 3.2.0
	 *
	 * @param string|array $partial
	 *
	 * @return string
	 */

	public static function partial($partial = null)
	{
		$output = null;

		/* template filesystem */

		$templateFilesystem = new Filesystem\File();
		$templateFilesystem->init('.');

		/* process partial */

		foreach ((array)$partial as $file)
		{
			$output .= $templateFilesystem->renderFile($file);
		}
		return $output;
	}

	/**
	 * get the registry
	 *
	 * @since 2.6.0
	 *
	 * @param string $key
	 *
	 * @return string
	 */

	public static function getRegistry($key = null)
	{
		$registry = Registry::getInstance();
		return $registry->get($key);
	}

	/**
	 * get the language
	 *
	 * @since 2.6.0
	 *
	 * @param string $key
	 * @param string $index
	 *
	 * @return string
	 */

	public static function getLanguage($key = null, $index = null)
	{
		$language = Language::getInstance();
		return $language->get($key, $index);
	}

	/**
	 * get the setting
	 *
	 * @since 2.6.0
	 *
	 * @param string $key
	 *
	 * @return string
	 */

	public static function getSetting($key = null)
	{
		$settingModel = new Model\Setting();
		return $settingModel->get($key);
	}

	/**
	 * content
	 *
	 * @since 2.3.0
	 *
	 * @return string
	 */

	public static function content()
	{
		// @codeCoverageIgnoreStart
		return self::_migrate('router');
		// @codeCoverageIgnoreEnd
	}

	/**
	 * extra
	 *
	 * @since 2.3.0
	 *
	 * @param string $filter
	 *
	 * @return string
	 */

	public static function extra($filter = null)
	{
		// @codeCoverageIgnoreStart
		return self::_migrate('extras',
		[
			$filter
		]);
		// @codeCoverageIgnoreEnd
	}

	/**
	 * category raw
	 *
	 * @since 3.0.0
	 *
	 * @return Db
	 */

	public static function categoryRaw()
	{
		return Db::forTablePrefix('categories');
	}

	/**
	 * article raw
	 *
	 * @since 3.0.0
	 *
	 * @return Db
	 */

	public static function articleRaw()
	{
		return Db::forTablePrefix('articles');
	}

	/**
	 * extra raw
	 *
	 * @since 3.0.0
	 *
	 * @return Db
	 */

	public static function extraRaw()
	{
		return Db::forTablePrefix('extras');
	}

	/**
	 * navigation
	 *
	 * @since 3.0.0
	 *
	 * @param string $type
	 * @param array $optionArray
	 *
	 * @return string
	 */

	public static function navigation($type = null, array $optionArray = [])
	{
		// @codeCoverageIgnoreStart
		if ($type === 'languages' || $type === 'templates')
		{
			return self::_migrate($type . '_list',
			[
				$optionArray
			]);
		}
		return self::_migrate('navigation_list',
		[
			$type,
			$optionArray
		]);
		// @codeCoverageIgnoreEnd
	}

	/**
	 * migrate
	 *
	 * @since 2.3.0
	 *
	 * @param string $function
	 * @param array $parameterArray
	 *
	 * @return string
	 */

	protected static function _migrate($function = null, $parameterArray = [])
	{
		// @codeCoverageIgnoreStart
		ob_start();

		/* call with parameter */

		if (is_array($parameterArray))
		{
			call_user_func_array($function, $parameterArray);
		}

		/* else simple call */

		else
		{
			call_user_func($function);
		}
		return ob_get_clean();
		// @codeCoverageIgnoreEnd
	}
}
