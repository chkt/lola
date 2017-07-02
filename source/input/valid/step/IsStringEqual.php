<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;

use lola\input\valid\ValidationException;



final class IsStringEqual
extends AValidationTransform
{

	private $_value;


	public function __construct(string $value) {
		parent::__construct();

		$this->_value = $value;
	}


	public function getId() : string {
		return 'stringEquals.' . $this->_value;
	}


	protected function _validate($source) {
		if (!is_string($source)) throw new ValidationException($this->getId() . '.nostring', 1);

		if ($source !== $this->_value) throw new ValidationException($this->getId() . '.notequal', 2);

		return $source;
	}
}
