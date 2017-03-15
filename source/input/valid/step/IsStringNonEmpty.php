<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;

use lola\input\valid\ValidationException;



final class IsStringNonEmpty
extends AValidationTransform
{

	public function getId() : string {
		return 'stringNonEmpty';
	}


	protected function _validate($value) {
		if (!is_string($value)) throw new ValidationException($this->getId() . '.nostring', 1);

		if (empty($value)) throw new ValidationException($this->getId() . '.empty', 2);

		return $value;
	}
}
