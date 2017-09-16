<?php
namespace Redaxscript\Validator;

use Redaxscript\Config;
use Redaxscript\Hash;

/**
 * children class to validate captcha
 *
 * @since 2.2.0
 *
 * @package Redaxscript
 * @category Validator
 * @author Henry Ruhs
 */

class Captcha implements ValidatorInterface
{
	/**
	 * validate the captcha
	 *
	 * @since 2.2.0
	 *
	 * @param string $task plain task
	 * @param string $hash hashed solution
	 *
	 * @return int
	 */

	public function validate($task = null, $hash = null)
	{
		$output = ValidatorInterface::FAILED;
		$captchaHash = new Hash(Config::getInstance());

		/* validate captcha */

		if ($task && $captchaHash->validate($task, $hash))
		{
			$output = ValidatorInterface::PASSED;
		}
		return $output;
	}
}
