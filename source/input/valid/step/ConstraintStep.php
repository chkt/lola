<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationStep;

use lola\input\valid\ValidationException;



final class ConstraintStep
extends AValidationStep
{

	private $_constraint;


	public function __construct(array $constraint) {
		parent::__construct();

		$this->_constraint = $constraint;
	}


	public function getId() : string {
		return 'constraint';
	}


	protected function _validate($value) {
		if (!in_array($value, $this->_constraint)) throw new ValidationException($this->getId() . '.invalid', 1);

		return $value;
	}
}
