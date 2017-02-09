<?php

namespace lola\input\form;

use lola\input\form\IProcessor;

use lola\input\valid\IValidator;
use lola\input\form\IField;



final class Processor
implements IProcessor
{

	const VERSION = '0.6.0';



	private $_state;

	private $_fields;
	private $_validator;



	public function __construct(array $fields, IValidator $validator) {
		$this->_state = self::STATE_UNVALIDATED;

		$this->_fields = $this->_normalizeFields($fields);
		$this->_validator = $validator;
	}


	private function _normalizeFields(array $fields) : array {
		$res = [];

		foreach($fields as & $field) {
			if (!($field instanceof IField)) throw new \ErrorException();

			$name = $field->getName();

			if (array_key_exists($name, $res)) throw new \ErrorException();

			$res[$name] =& $field;
		}

		return $res;
	}


	public function getState() : int {
		return $this->_state;
	}

	public function setState(int $state) : IProcessor {
		$this->_state = $state;

		return $this;
	}


	public function& useField(string $name) : IField {
		if (!array_key_exists($name, $this->_fields)) throw new \ErrorException();

		return $this->_fields[$name];
	}


	public function getValue(string $name) : string {
		return $this
			->useField($name)
			->getValue();
	}

	public function getValues(string $name) : array {
		return $this
			->useField($name)
			->getValues();
	}

	public function getValidatedData(string $name) {
		$field = $this->useField($name);

		if (($this->_state & self::STATE_UNMODIFIED) !== self::STATE_UNMODIFIED) return null;

		return $field
			->useValidation()
			->getResult();
	}


	private function _isSubmitted(array $data) : bool {
		foreach ($this->_fields as $field) {
			if (!$field->isSubmit()) continue;

			$name = $field->getName();

			if (array_key_exists($name, $data) && !empty($data[$name])) return true;
		}

		return false;
	}

	private function  _isChanged() : bool {
		foreach ($this->_fields as $field) {
			if ($field->isSubmit()) continue;

			if ($field->isChanged()) return true;
		}

		return false;
	}

	private function _assignFields(array $input) : Processor {
		foreach($this->_fields as $name => & $field) {
			if (!array_key_exists($name, $input)) continue;

			$item = $input[$name];

			if (is_array($item)) $field->setValues($item);
			else $field->setValue($item);
		}

		return $this;
	}


	public function validate(array $input) : IProcessor {
		if ($this->_state !== self::STATE_UNVALIDATED) throw new \ErrorException();

		$this->_state |= self::FLAG_VALIDATE;

		if (!$this->_isSubmitted($input)) return $this;

		$this->_state |= self::FLAG_COMMIT;

		$this->_assignFields($input);
		$this->_validator->validate($input);

		if ($this->_isChanged()) $this->_state |= self::FLAG_MODIFIED;

		if ($this->_validator->isValid()) $this->_state |= self::FLAG_VALID;

		return $this;
	}


	public function getProjection(array $selection = []) : array {
		$fields = [];

		foreach ($this->_fields as $name => $field) $fields[$name] = $field->getProjection();

		return [
			'state' => $this->_state,
			'fields' => $fields
		];
	}
}
