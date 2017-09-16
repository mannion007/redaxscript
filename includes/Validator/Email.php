<?php
namespace Redaxscript\Validator;

/**
 * children class to validate email
 *
 * @since 2.2.0
 *
 * @package Redaxscript
 * @category Validator
 * @author Henry Ruhs
 * @author Sven Weingartner
 */

class Email implements ValidatorInterface
{
	/**
	 * validate the email
	 *
	 * @since 2.2.0
	 *
	 * @param string $email email address
	 * @param bool $dns optional validate dns
	 *
	 * @return int
	 */

	public function validate($email = null, $dns = true)
	{
		$output = ValidatorInterface::FAILED;

		/* validate email */

		if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
		{
			$output = ValidatorInterface::PASSED;
			$emailArray = array_filter(explode('@', $email));

			/* validate dns */

			if ($dns)
			{
				$dnsValidator = new Dns();
				$output = $dnsValidator->validate($emailArray[1], 'MX');
			}
		}
		return $output;
	}
}