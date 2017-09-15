<?php
namespace Redaxscript\View;

use Redaxscript\Html;
use Redaxscript\Module;
use Redaxscript\Validator;

/**
 * children class to create the comments
 *
 * @since 3.3.0
 *
 * @package Redaxscript
 * @category View
 * @author Henry Ruhs
 * @author Balázs Szilágyi
 */
class Comment extends ViewAbstract
{
	/**
	 * render the view
	 *
	 * @since 3.3.0
	 *
	 * @param integer $resultArray identifier of the article
	 *
	 * @return string
	 */

	public function render($resultArray = null)
	{
		$output = Module\Hook::trigger('commentStart');
		$accessValidator = new Validator\Access();
		$counter = 0;

		/* html elements */

		$boxElement = new Html\Element();
		$boxElement->init('div',
		[
			'class' => 'rs-box-comment'
		]);
		$titleElement = new Html\Element();
		$titleElement->init('h3',
		[
			'class' => 'rs-title-comment'
		]);
		$linkElement = new Html\Element();
		$linkElement->init('a',
		[
			'rel' => 'nofollow'
		]);

		foreach ($resultArray as $result)
		{
			if ($accessValidator->validate($result['access'], $this->_registry->get('myGroups')) === Validator\ValidatorInterface::PASSED)
			{
				$output .= Module\Hook::trigger('commentFragmentStart', $result);
				$titleElement->attr('id', 'comment-' . $result['id']);
				$output .= $titleElement->html($result['url'] ?
					$linkElement
						->attr('href', $result['url'])
						->text($result['author'])
					: $result['author']);

				/* collect box output */

				$output .= $boxElement->text($result['text']);
				$output .= byline('comments', $result['id'], $result['author'], $result['date']);
				$output .= Module\Hook::trigger('commentFragmentEnd', $result);

				/* admin dock */

				if ($this->_registry->get('loggedIn') == $this->_registry->get('token') && $this->_registry->get('firstParameter') != 'logout')
				{
					$output .= admin_dock('comments', $result['id']);
				}
			}
			else
			{
				$counter++;
			}
		}

		if ($counter > 0 && count($resultArray) == $counter)
		{
			$output .=  $boxElement->text($this->_language->get('access_no'));
		}

		$output .= Module\Hook::trigger('commentEnd');
		return $output;
	}
}
