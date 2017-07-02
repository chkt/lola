<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;

use lola\input\valid\ValidationException;



final class ToArrayLength
extends AValidationTransform
{

	public function getId() : string {
		return 'toArrayLength';
	}


	protected function _validate($source) {
		if (!is_array($source)) throw new ValidationException($this->getId() . '.noArray', 1);

		return count($source);
	}
}
