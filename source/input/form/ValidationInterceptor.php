<?php

namespace lola\input\form;

use lola\input\valid\IValidationInterceptor;

use lola\input\valid\IValidateable;
use lola\input\valid\IValidationStep;



class ValidationInterceptor
implements IValidationInterceptor
{

	private $_map;


	public function __construct(array $map) {
		$this->_map = $map;
	}


	public function intercept(IValidationStep& $step) : IValidationInterceptor {
		$id = $step->getId();

		if (array_key_exists($id, $this->_map)) {
			$ins = $this->_map[$id];

			if (!($ins instanceof IValidateable)) throw new \ErrorException();

			$ins->setValidation($step);
		}

		return $this;
	}
}
