<?php
namespace Redaxscript\Controller;

use Redaxscript\Db;
use Redaxscript\Html;
use Redaxscript\Mailer;
use Redaxscript\Messenger;
use Redaxscript\Model;
use Redaxscript\Filter;
use Redaxscript\Validator;
use Redaxscript\View;

/**
 * children class to process the comment request
 *
 * @since 3.0.0
 *
 * @package Redaxscript
 * @category Controller
 * @author Henry Ruhs
 * @author Balázs Szilágyi
 */

class Comment extends ControllerAbstract
{
	/**
	 * process the class
	 *
	 * @since 3.3.0
	 *
	 * @return string
	 */

	public function process()
	{
		$specialFilter = new Filter\Special();
		$emailFilter = new Filter\Email();
		$urlFilter = new Filter\Url();
		$htmlFilter = new Filter\Html();
		$articleModel = new Model\Article();
		$settingModel = new Model\Setting();

		/* process post */

		$postArray =
		[
			'author' => $specialFilter->sanitize($this->_request->getPost('author')),
			'email' => $emailFilter->sanitize($this->_request->getPost('email')),
			'url' => $urlFilter->sanitize($this->_request->getPost('url')),
			'text' => $htmlFilter->sanitize($this->_request->getPost('text')),
			'article' => $specialFilter->sanitize($this->_request->getPost('article')),
			'task' => $this->_request->getPost('task'),
			'solution' => $this->_request->getPost('solution')
		];
		$route = $postArray['article'] ? $articleModel->getRouteById($postArray['article']) : null;

		/* handle error */

		$messageArray = $this->_validate($postArray);
		if ($messageArray)
		{
			return $this->_error(
			[
				'route' => $route,
				'message' => $messageArray
			]);
		}

		/* handle success */

		$createArray =
		[
			'author' => $postArray['author'],
			'email' => $postArray['email'],
			'url' => $postArray['url'],
			'text' => $postArray['text'],
			'language' => Db::forTablePrefix('articles')->whereIdIs($postArray['article'])->findOne()->language,
			'article' => $postArray['article'],
			'status' => $settingModel->getSetting('verification') ? 0 : 1
		];
		$mailArray =
		[
			'email' => $postArray['email'],
			'url' => $postArray['url'],
			'route' => $route,
			'author' => $postArray['author'],
			'text' => $postArray['text'],
			'article' => Db::forTablePrefix('articles')->whereIdIs($postArray['article'])->findOne()->title
		];

		/* create */

		if (!$this->_create($createArray))
		{
			return $this->_error(
			[
				'route' => $route,
				'message' => $this->_language->get('something_wrong')
			]);
		}

		/* mail */

		if (!$this->_mail($mailArray))
		{
			return $this->_warning(
			[
				'route' => $route,
				'message' => $this->_language->get('email_failed')
			]);
		}
		return $this->_success(
		[
			'route' => $route,
			'timeout' => $settingModel->getSetting('notification') ? 2 : 0,
			'message' => $settingModel->getSetting('moderation') ? $this->_language->get('comment_moderation') : $this->_language->get('comment_sent')
		]);
	}

	/**
	 * show the success
	 *
	 * @since 3.0.0
	 *
	 * @param array $successArray array of the success
	 *
	 * @return string
	 */

	protected function _success($successArray = [])
	{
		$messenger = new Messenger($this->_registry);
		return $messenger
			->setRoute($this->_language->get('continue'), $successArray['route'])
			->doRedirect($successArray['timeout'])
			->success($successArray['message'], $this->_language->get('operation_completed'));
	}

	/**
	 * show the warning
	 *
	 * @since 3.0.0
	 *
	 * @param array $warningArray array of the warning
	 *
	 * @return string
	 */

	protected function _warning($warningArray = [])
	{
		$messenger = new Messenger($this->_registry);
		return $messenger
			->setRoute($this->_language->get('continue'), $warningArray['route'])
			->doRedirect($warningArray['timeout'])
			->warning($warningArray['message'], $this->_language->get('operation_completed'));
	}

	/**
	 * show the error
	 *
	 * @since 3.0.0
	 *
	 * @param array $errorArray array of the error
	 *
	 * @return string
	 */

	protected function _error($errorArray = [])
	{
		$messenger = new Messenger($this->_registry);
		return $messenger
			->setRoute($this->_language->get('back'), $errorArray['route'])
			->error($errorArray['message'], $this->_language->get('error_occurred'));
	}

	/**
	 * validate
	 *
	 * @since 3.3.0
	 *
	 * @param array $postArray array of the post
	 *
	 * @return array
	 */

	protected function _validate($postArray = [])
	{
		$emailValidator = new Validator\Email();
		$captchaValidator = new Validator\Captcha();
		$urlValidator = new Validator\Url();
		$settingModel = new Model\Setting();

		/* validate post */

		$messageArray = [];
		if (!$postArray['author'])
		{
			$messageArray[] = $this->_language->get('author_empty');
		}
		if (!$postArray['email'])
		{
			$messageArray[] = $this->_language->get('email_empty');
		}
		else if ($emailValidator->validate($postArray['email']) === Validator\ValidatorInterface::FAILED)
		{
			$messageArray[] = $this->_language->get('email_incorrect');
		}
		if ($postArray['url'] && $urlValidator->validate($postArray['url']) === Validator\ValidatorInterface::FAILED)
		{
			$messageArray[] = $this->_language->get('url_incorrect');
		}
		if (!$postArray['text'])
		{
			$messageArray[] = $this->_language->get('comment_empty');
		}
		if (!$postArray['article'])
		{
			$messageArray[] = $this->_language->get('input_incorrect');
		}
		if ($settingModel->getSetting('captcha') > 0 && $captchaValidator->validate($postArray['task'], $postArray['solution']) === Validator\ValidatorInterface::FAILED)
		{
			$messageArray[] = $this->_language->get('captcha_incorrect');
		}
		return $messageArray;
	}

	/**
	 * create the comment
	 *
	 * @since 3.0.0
	 *
	 * @param array $createArray array of the create
	 *
	 * @return boolean
	 */

	protected function _create($createArray = [])
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

	/**
	 * send the mail
	 *
	 * @since 3.3.0
	 *
	 * @param array $mailArray array of the mail
	 *
	 * @return boolean
	 */

	protected function _mail($mailArray = [])
	{
		$settingModel = new Model\Setting();
		$urlArticle = $this->_registry->get('root') . '/' . $this->_registry->get('parameterRoute') . $mailArray['route'];

		/* html elements */

		$linkElement = new Html\Element();
		$linkElement->init('a');
		$linkEmail = $linkElement->copy();
		$linkEmail
			->attr(
			[
				'href' => 'mailto:' . $mailArray['email']
			])
			->text($mailArray['email']);
		$linkUrl = $linkElement->copy();
		$linkUrl
			->attr(
			[
				'href' => $mailArray['url']
			])
			->text($mailArray['url'] ? $mailArray['url'] : $this->_language->get('none'));
		$linkArticle = $linkElement->copy();
		$linkArticle
			->attr(
			[
				'href' => $urlArticle
			])
			->text($urlArticle);

		/* prepare mail */

		$toArray =
		[
			$this->_language->get('author') => $settingModel->getSetting('email')
		];
		$fromArray =
		[
			$mailArray['author'] => $mailArray['email']
		];
		$subject = $this->_language->get('comment_new');
		$bodyArray =
		[
			$this->_language->get('author') . $this->_language->get('colon') . ' ' . $mailArray['author'],
			'<br />',
			$this->_language->get('email') . $this->_language->get('colon') . ' ' . $linkEmail,
			'<br />',
			$this->_language->get('url') . $this->_language->get('colon') . ' ' . $linkUrl,
			'<br />',
			$this->_language->get('article') . $this->_language->get('colon') . ' ' . $linkArticle,
			'<br />',
			$this->_language->get('comment') . $this->_language->get('colon') . ' ' . $mailArray['text']
		];

		/* send mail */

		$mailer = new Mailer();
		$mailer->init($toArray, $fromArray, $subject, $bodyArray);
		return $mailer->send();
	}

	/**
	 * show comments
	 *
	 * @since 3.3.0
	 *
	 * @param array $articleId id of the article
	 * @param array $route route
	 *
	 * @return boolean
	 */
	public function getComments($articleId, $route)
	{
		/* process search */

		$resultArray = $this->_search($articleId);
		if ($resultArray)
		{
			$num_rows = count($resultArray);
			$sub_maximum = ceil($num_rows / Db::getSetting('limit'));
			$sub_active = $this->_registry->get('lastSubParameter');

			/* sub parameter */

			if ($this->_registry->get('lastSubParameter') > $sub_maximum || !$this->_registry->get('lastSubParameter'))
			{
				$sub_active = 1;
			}
			else
			{
				$offset_string = ($sub_active - 1) * Db::getSetting('limit') . ', ';
			}
		}

		/* query result */

		$resultArray = $this->_search($articleId, $offset_string . Db::getSetting('limit'));

		/* handle error */

		if (!$resultArray || !$num_rows)
		{
			$error = $this->_language->get('comment_no');
		}

		/* handle error */

		if ($error)
		{
			echo '<div class="rs-box-comment">' . $error . $this->_language->get('point') . '</div>';
		}

		/* handle result */

		$output = $this->_renderResult($resultArray);
		if ($output)
		{
			echo $output;
		}

		/* call pagination as needed */

		if ($sub_maximum > 1 && Db::getSetting('pagination') == 1)
		{
			pagination($sub_active, $sub_maximum, $route);
		}
	}

	/**
	 * search for comments
	 *
	 * @since 3.3.0
	 *
	 * @param array $articleId array of the search
	 * @param integer $limit
	 *
	 * @return object
	 */

	protected function _search($articleId = null, $limit = null)
	{
		$commentModel = new Model\Comment();
		return $commentModel->getCommentsById($articleId, $this->_language->get('language'), $limit);
	}

	/**
	 * render comments
	 *
	 * @since 3.3.0
	 *
	 * @param array $resultArray
	 *
	 * @return boolean
	 */
	public function _renderResult($resultArray = [])
	{
		$searchList = new View\Comment($this->_registry, $this->_language);
		return $searchList->render($resultArray);
	}

}