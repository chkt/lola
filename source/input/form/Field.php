<?php

namespace lola\input\form;

use lola\input\form\IField;

use lola\input\valid\IValidationStep;
use lola\input\valid\step\NoopValidationStep;



class Field
implements IField
{

	const VERSION = '0.6.0';

	const FLAG_NONE = 0x0;
	const FLAG_SUBMIT = 0x1;
	const FLAG_IMMUTABLE = 0x2;



	private $_name;

	private $_valueFirst;
	private $_valueNow;

	private $_submit;
	private $_immutable;

	private $_validation;


	public function __construct(string $name, array $values = [], int $flags = self::FLAG_NONE) {
		if (empty($name)) throw new \ErrorException();

		$normalized = $this->_normalizeValues($values);

		$this->_name = $name;

		$this->_valueFirst = $normalized;
		$this->_valueNow = $normalized;

		$this->_submit = (bool) ($flags & self::FLAG_SUBMIT);
		$this->_immutable = (bool) ($flags & self::FLAG_IMMUTABLE);

		$this->_validation = null;
	}


	public function isChanged() : bool {
		return $this->_valueNow !== $this->_valueFirst;
	}

	public function isEmpty() : bool {
		return count($this->_valueNow === 1) && empty($this->_valueNow[0]);
	}

	public function isMultiValue() : bool {
		return count($this->_valueNow) > 1;
	}

	public function isImmutable() : bool {
		return $this->_immutable;
	}

	public function isSubmit() : bool {
		return $this->_submit;
	}


	public function getName() : string {
		return $this->_name;
	}


	private function _normalizeValues(array $values) : array {
		if (count($values) === 0) return $values;

		$unique = array_unique($values);

		for ($i = count($unique) - 1; $i > -1; $i -= 1) {
			$value = $unique[$i];

			if (!is_string($value)) throw new \ErrorException();

			if (empty($value)) array_splice($unique, $i, 1);
		}

		return $unique;
	}

	public function getValue() : string {
		return empty($this->_valueNow) ? '' : $this->_valueNow[0];
	}

	public function setValue(string $value) : IField {
		return $this->setValues([ $value ]);
	}


	public function getValues() : array {
		return $this->_valueNow;
	}

	public function setValues(array $values) : IField {
		if (!$this->_immutable) $this->_valueNow = $this->_normalizeValues($values);

		return $this;
	}


	public function& useValidation() : IValidationStep {
		if (is_null($this->_validation)) {
			$this->_validation = new NoopValidationStep();
			$this->_validation->validate($this->isMultiValue() ? $this->getValues() : $this->getValue());
		}

		return $this->_validation;
	}

	public function setValidation(IValidationStep& $step) : IField {
		$this->_validation =& $step;

		return $this;
	}


	public function getProjection(array $selection = []) : array {
		$validation = $this->useValidation();

		return [
			'changed' => $this->isChanged(),
			'empty' => $this->isEmpty(),
			'multiValue' => $this->isMultiValue(),
			'immutable' => $this->isImmutable(),
			'submit' => $this->isSubmit(),
			'name' => $this->getName(),
			'value' => $this->getValue(),
			'values' => $this->getValues(),
			'validated' => $this->useValidation()->wasValidated(),
			'valid' => $validation->isValid(),
			'error' => !$validation->isValid() ? $validation->getError()->getProjection() : null
		];
	}
}
