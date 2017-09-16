<?php
namespace Redaxscript\View;

use Redaxscript\Model;
use Redaxscript\Db;
use Redaxscript\Html;
use Redaxscript\Module;
use Redaxscript\Validator;

/**
 * children class to create the result list
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category View
 * @author Henry Ruhs
 * @author Balázs Szilágyi
 */

class ResultList extends ViewAbstract
{
	/**
	 * render the view
	 *
	 * @since 3.0.0
	 *
	 * @param array $resultArray array for the result
	 *
	 * @return string
	 */

	public function render(array $resultArray = []) : string
	{
		$output = Module\Hook::trigger('resultListStart');
		$accessValidator = new Validator\Access();
		$contentModel = new Model\Content();

		/* html elements */

		$titleElement = new Html\Element();
		$titleElement->init('h2',
		[
			'class' => 'rs-title-result'
		]);
		$listElement = new Html\Element();
		$listElement->init('ol',
		[
			'class' => 'rs-list-result'
		]);
		$itemElement = new Html\Element();
		$itemElement->init('li');
		$linkElement = new Html\Element();
		$linkElement->init('a',
		[
			'class' => 'rs-link-result'
		]);
		$textElement = new Html\Element();
		$textElement->init('span',
		[
			'class' => 'rs-text-result-date'
		]);

		/* process result */

		foreach ($resultArray as $table => $result)
		{
			$outputItem = null;
			if ($result)
			{
				/* collect item output */

				foreach ($result as $value)
				{
					if ($accessValidator->validate($result->access, $this->_registry->get('myGroups')) === Validator\ValidatorInterface::PASSED)
					{
						$textDate = date(Db::getSetting('date'), strtotime($value->date));
						$linkElement
							->attr('href', $this->_registry->get('parameterRoute') . $contentModel->getRouteByTableAndId($table, $value->id))
							->text($value->title ? $value->title : $value->author);
						$textElement->text($textDate);
						$outputItem .= $itemElement->html($linkElement . $textElement);
					}
				}

				/* collect output */

				if ($outputItem)
				{
					$titleElement->text($this->_language->get($table));
					$listElement->html($outputItem);
					$output .= $titleElement . $listElement;
				}
			}
		}
		$output .= Module\Hook::trigger('resultListEnd');
		return $output;
	}
}
