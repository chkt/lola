<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;
use lola\input\valid\IValidationTransform;

use lola\input\valid\ValidationException;



final class Constraint
extends AValidationTransform
{

	private $_constraint;


	public function __construct(array $constraint, IValidationTransform $next = null) {
		parent::__construct($next);

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
