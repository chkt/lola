<?php

namespace lola\input\valid;

use Exception;
use lola\input\valid\IValidationException;



class ValidationException
extends Exception
implements IValidationException
{

	const VERSION = '0.6.0';



	private $_final;


	public function __construct(string $message, int $code = 0, bool $final = false) {
		parent::__construct($message, $code);

		$this->_final = $final;
	}


	public function isFinal() : bool {
		return $this->_final;
	}


	public function getProjection(array $selection = []) : array {
		return [
			'message' => $this->getMessage(),
			'code' => $this->getCode(),
			'final' => $this->isFinal()
		];
	}
}
