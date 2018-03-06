<?php

namespace lola\input\valid;



class ValidationException
extends \Exception
implements IValidationException
{

	public function __construct(string $message, int $code = 0) {
		parent::__construct($message, $code);
	}


	public function getProjection() : array {
		return [
			'message' => $this->getMessage(),
			'code' => $this->getCode()
		];
	}
}
