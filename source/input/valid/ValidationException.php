<?php

namespace lola\input\valid;

use Exception;
use lola\input\valid\IValidationException;



class ValidationException
extends Exception
implements IValidationException
{

	const VERSION = '0.6.0';



	public function __construct(string $message, int $code = 0) {
		parent::__construct($message, $code);
	}


	public function getProjection(array $selection = []) : array {
		return [
			'message' => $this->getMessage(),
			'code' => $this->getCode()
		];
	}
}
