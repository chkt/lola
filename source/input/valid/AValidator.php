<?php

namespace lola\input\valid;

use lola\input\valid\IValidator;

use lola\input\valid\IValidationTransform;
use lola\input\valid\IValidationInterceptor;



abstract class AValidator
implements IValidator
{

	private $_steps;
	private $_interceptor;

	private $_source;
	private $_result;
	private $_failures;


	public function __construct(array $steps, IValidationInterceptor $interceptor = null) {
		$this->_steps = $steps;
		$this->_interceptor = $interceptor;

		$this->_source = null;
		$this->_result = null;
		$this->_failures = [];
	}


	public function wasValidated() : bool {
		return !is_null($this->_source);
	}

	public function isValid() : bool {
		return !is_null($this->_source) && empty($this->_failures);
	}


	public function getSource() {
		return $this->_source;
	}

	public function getResult() {
		return $this->_result;
	}

	public function getFailures() : array {
		return $this->_failures;
	}


	public function hasChain(string $name) : bool {
		return array_key_exists($name, $this->_steps);
	}

	public function& useChain(string $name) : IValidationTransform {
		if (!$this->hasChain($name)) throw new \ErrorException();

		return $this->_steps[$name];
	}


	public function validate($value) : IValidator {
		$this->_source = $value;
		$this->_result = $value;
		$this->_failures = [];

		foreach ($this->_steps as $name => & $step) {
			$step->validate($value);

			if (!is_null($this->_interceptor)) $this->_interceptor->intercept($name, $step);

			if ($step->isValid()) $value = $step->getResult();
			else $this->_failures[] = $step->getError();
		}

		$this->_result = $value;

		return $this;
	}


	public function reset() : IValidator {
		$this->_source = null;
		$this->_result = null;
		$this->_failures = [];

		foreach ($this->_steps as $step) $step->reset();

		return $this;
	}


	public function assert() : IValidator {
		if (!empty($this->_failures)) throw $this->_failures[0];

		return $this;
	}


	public function getProjection(array $selection = []) : array {
		$failures = [];

		foreach ($this->_failures as $failure) $failures[] = $failure->getProjection();

		return [
			'state' => is_null($this->_source) ? 'new' : (empty($this->_failures) ? 'valid' : 'invalid'),
			'source' => $this->_source,
			'result' => $this->_result,
			'failures' => $failures
		];
	}
}
