<?php

namespace lola\input\form;

use lola\input\valid\IValidationInterceptor;

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

		if (!array_key_exists($id, $this->_map)) throw new \ErrorException();

		$this->_map[$id]->setValidation($step);

		return $this;
	}
}
