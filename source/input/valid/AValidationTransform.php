<?php

namespace lola\input\valid;

use lola\input\valid\IValidationStep;
use lola\input\valid\AValidationStep;
use lola\input\valid\IValidationTransform;



abstract class AValidationTransform
extends AValidationStep
implements IValidationTransform
{

	const VERSION = '0.6.0';



	private $_nextStep;
	private $_transformed;


	public function __construct(IValidationStep $next) {
		parent::__construct();

		$this->_nextStep = $next;
		$this->_transformed = null;
	}


	public function wasTransformed() : bool {
		return !is_null($this->_transformed);
	}


	public function getNextStep() : IValidationStep {
		return $this->_nextStep;
	}

	public function getTransformedResult() {
		if (!$this->wasTransformed()) throw new \ErrorException();

		return $this->_transformed;
	}


	public function reset() : IValidationStep {
		$this->_nextStep->reset();

		return parent::reset();
	}


	abstract protected function _transform($source, $result);


	public function transform($result) : IValidationTransform {
		if (!$this->wasValidated()) throw new \ErrorException();

		$this->_transformed = $this->_transform($this->getSource(), $result);

		return $this;
	}
}
