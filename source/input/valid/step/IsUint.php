<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;

use lola\input\valid\ValidationException;



final class IsUint
extends AValidationTransform
{

	public function getId() : string {
		return 'uint';
	}


	protected function _validate($source) {
		if (!is_int($source)) throw new ValidationException($this->getId() . '.noint', 1);

		if ($source < 0) throw new ValidationException($this->getId() . '.negative', 2);

		return $source;
	}
}
