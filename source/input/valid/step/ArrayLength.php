<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;
use lola\input\valid\IValidationTransform;

use lola\input\valid\ValidationException;



final class ArrayLength
extends AValidationTransform
{

	private $_length;


	public function __construct(int $length, IValidationTransform $next = null) {
		parent::__construct($next);

		$this->_length = $length;
	}


	public function getId() : string {
		return 'arrayLength.' . $this->_length;
	}


	protected function _validate($source) {
		if (!is_array($source)) throw new ValidationException($this->getId() . '.noarray', 1);

		if (count($source) !== $this->_length) throw new ValidationException($this->getId() . '.noteq', 2);

		return $source;
	}
}
