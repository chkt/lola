<?php

namespace lola\input\valid;

use lola\input\valid\IValidationTransform;

use lola\input\valid\IValidationException;



abstract class AValidationTransform
implements IValidationTransform
{

	private $_validated;
	private $_source;
	private $_result;
	private $_error;

	private $_nextStep;


	public function __construct(IValidationTransform $next = null) {
		$this->_validated = false;
		$this->_source = null;
		$this->_result = null;
		$this->_error = null;

		$this->_nextStep = $next;
	}


	public function wasValidated() : bool {
		return $this->_validated;
	}

	public function isValid() : bool {
		return $this->_validated && is_null($this->_error);
	}


	public function getSource() {
		return $this->_source;
	}

	public function getResult() {
		return $this->_result;
	}

	public function getError() : IValidationException {
		if (!$this->_validated || is_null($this->_error)) throw new \ErrorException();

		return $this->_error;
	}


	public function hasNextStep() : bool {
		return !is_null($this->_nextStep);
	}

	public function& useNextStep() : IValidationTransform {
		if (!$this->hasNextStep()) throw new \ErrorException();

		return $this->_nextStep;
	}

	public function& useTerminalStep() : IValidationTransform {
		if (!$this->hasNextStep()) return $this;

		$next =& $this->useNextStep();

		if (!$next->wasValidated()) return $this;
		else if (!$next->isValid()) return $next;
		else return $next->useTerminalStep();
	}


	abstract protected function _validate($source);

	protected function _transform($source, $result) {
		return $result;
	}


	protected function _validateNextStep(IValidationTransform& $next, $source) {
		if (!$next->validate($source)->isValid()) throw $next->getError();

		return $this->_transform($this->getSource(), $next->getResult());
	}


	public function validate($source) : IValidationTransform {
		$this->_validated = true;
		$this->_source = $source;
		$this->_result = $source;
		$this->_error = null;

		try {
			$result = $this->_validate($source);

			if ($this->hasNextStep()) $result = $this->_validateNextStep($this->useNextStep(), $result);
		}
		catch (IValidationException $ex) {
			$this->_error = $ex;

			return $this;
		}

		$this->_result = $result;

		return $this;
	}

	public function reset() : IValidationTransform {
		$this->_validated = false;
		$this->_source = null;
		$this->_result = null;
		$this->_error = null;

		if ($this->hasNextStep()) $this
			->useNextStep()
			->reset();

		return $this;
	}
}
