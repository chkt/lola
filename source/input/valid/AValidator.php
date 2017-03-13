<?php

namespace lola\input\valid;

use lola\input\valid\IValidator;

use lola\input\valid\IValidationStep;
use lola\input\valid\IValidationTransform;
use lola\input\valid\IValidationCatchingTransform;
use lola\input\valid\IValidationInterceptor;



abstract class AValidator
implements IValidator
{

	const VERSION = '0.6.0';



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


	private function _resolveChain(string $name) : IValidationStep {
		if (!$this->hasChain($name)) throw new \ErrorException();

		for (
			$step = $this->_steps[$name];
			$step instanceof IValidationTransform;
			$step = $step->getNextStep()
		) {
			if (
				!$step->isValid() ||
				$step instanceof IValidationCatchingTransform &&
				$step->wasRecovered()
			) return $step;
		}

		return $step;
	}


	public function isChainValid(string $name) : bool {
		return $this
			->_resolveChain($name)
			->isValid();
	}

	public function getChainResult(string $name) {
		return $this
			->_resolveChain($name)
			->getResult();
	}

	public function getChainFailure(string $name) : IValidationException {
		return $this
			->_resolveChain($name)
			->getError();
	}


	private function _recoverChain(
		IValidationException $ex,
		array& $chain,
		IValidationCatchingTransform& $recovery
	) {
		for ($i = count($chain) - 1; $i > -1; $i -= 1) {
			if ($chain[$i] !== $recovery) continue;

			array_splice($chain, $i, count($chain) - $i);

			$recovery->recover($ex);

			return $recovery->getRecoveredResult();
		}
	}


	private function _processChain(IValidationStep $step, & $origin) : AValidator {
		$stack = [];
		$recover = null;
		$value = $origin;

		while (true) {
			$step->validate($value);

			if (!is_null($this->_interceptor)) $this->_interceptor->intercept($step);

			if (!$step->isValid()) {
				if (!is_null($recover)) {
					$value = $this->_recoverChain($step->getError(), $stack, $recover);

					break;
				}

				$this->_failures[] = $step->getError();

				return $this;
			}

			$value = $step->getResult();

			if (!($step instanceof IValidationTransform)) break;

			if ($step instanceof IValidationCatchingTransform) $recover = $step;

			$stack[] = $step;
			$step = $step->getNextStep();
		}

		while (!empty($stack)) {
			$step = array_pop($stack);

			$value = $step
				->transform($value)
				->getTransformedResult();
		}

		$origin = $value;

		return $this;
	}


	public function validate($value) : IValidator {
		$this->_source = $value;
		$this->_result = $value;
		$this->_failures = [];

		foreach ($this->_steps as $step) $this->_processChain($step, $value);

		$this->_result = $value;

		return $this;
	}


	public function reset() : IValidator {
		foreach ($this->_steps as $step) $step->reset();

		$this->_source = null;
		$this->_result = null;
		$this->_failures = [];

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
