<?php

namespace lola\input\valid;

use lola\input\valid\IValidationStep;

use lola\input\valid\IValidationException;



abstract class AValidationStep
implements IValidationStep
{

	const VERSION = '0.6.0';



	private $_source;
	private $_result;
	private $_error;


	public function __construct() {
		$this->_source = null;
		$this->_result = null;
		$this->_error = null;
	}


	public function wasValidated() : bool {
		return !is_null($this->_source);
	}


	public function isValid() : bool {
		return !is_null($this->_source) && is_null($this->_error);
	}


	public function getSource() {
		return $this->_source;
	}

	public function getResult() {
		return $this->_result;
	}

	public function getError() : IValidationException {
		if (is_null($this->_error)) throw new \ErrorException();

		return $this->_error;
	}


	abstract protected function _validate($source);


	public function validate($source) : IValidationStep {
		$this->_source = $source;
		$this->_result = $source;
		$this->_error = null;

		try {
			$this->_result = $this->_validate($source);
		} catch (IValidationException $ex) {
			$this->_error = $ex;
		}

		return $this;
	}

	public function reset() : IValidationStep {
		$this->_source = null;
		$this->_result = null;
		$this->_error = null;

		return $this;
	}
}
