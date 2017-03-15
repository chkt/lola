<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;

use lola\input\valid\ValidationException;



final class IsFloatNonNaN
extends AValidationTransform
{

	public function getId() : string {
		return 'floatNonNaN';
	}


	protected function _validate($value) {
		if (!is_float($value)) throw new ValidationException($this->getId() . '.nofloat', 1);

		if (is_nan($value)) throw new ValidationException($this->getId() . '.nan', 2);

		return $value;
	}
}
