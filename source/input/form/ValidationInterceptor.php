<?php

namespace lola\input\form;

use lola\input\valid\IValidationInterceptor;

use lola\input\valid\IValidateable;
use lola\input\valid\IValidationTransform;



class ValidationInterceptor
implements IValidationInterceptor
{

	private $_map;


	public function __construct(array $map) {
		$this->_map = $map;
	}


	public function intercept(string $chain, IValidationTransform& $step) : IValidationInterceptor {
		if (array_key_exists($chain, $this->_map)) {
			$ins = $this->_map[$chain];

			if (!($ins instanceof IValidateable)) throw new \ErrorException();

			$ins->setValidation($step);
		}

		return $this;
	}
}
