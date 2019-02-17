<?php

namespace App\Validation;

use Respect\Validation\Exceptions\NestedValidationException;
use Slim\Http\Request;

/**
 * Class Validator
 * @package App\Validation
 */
class Validator
{
	/** @var array */
	public $errors = [];


	/**
	 * @param Request $request
	 * @param array $rules
	 * @return $this
	 */
	public function validate(Request $request, array $rules)
	{

		/** @var \Respect\Validation\Validator[] $validators */
		foreach ($rules as $field => $validators) {
			foreach ($validators as $validator) {

				try {

					$validator
						->setName($field)
						->assert($request->getParam($field));

				} catch (NestedValidationException $e) {
					$this->errors[$field][] = $e->getMessage();
				}
			}
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isFailed()
	{
		return !empty($this->errors);
	}

	/**
	 * @return string
	 */
	public function getErrorsAsString()
	{
		$result = [];
		foreach ($this->errors as $field => $messages) {
			$result[] = $field . ': ' . implode(", ", $messages);
		}

		return implode("; ", $result);
	}
}