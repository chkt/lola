<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;

use lola\input\valid\ValidationException;



final class ToFloat
extends AValidationTransform
{

	public function getId() : string {
		return 'float';
	}


	protected function _validate($source) {
		if (is_array($source) || is_object($source)) throw new ValidationException($this->getId() . 'nonScalar', 1);

		return (float) $source;
	}
}
