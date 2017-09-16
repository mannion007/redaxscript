<?php
namespace Redaxscript\Validator;

/**
 * interface to define a validator class
 *
 * @since 2.2.0
 *
 * @package Redaxscript
 * @category Validator
 * @author Henry Ruhs
 * @author Sven Weingartner
 */

interface ValidatorInterface
{
	/**
	 * status passed
	 *
	 * @const boolean
	 */

	const PASSED = true;

	/**
	 * status failed
	 *
	 * @const boolean
	 */

	const FAILED = false;

	/**
	 * validate the value
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */

	public function validate();
}
