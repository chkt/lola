<?php

namespace lola\input\valid;

use lola\input\valid\AValidationTransform;
use lola\input\valid\IValidationCatchingTransform;



abstract class AValidationCatchingTransform
extends AValidationTransform
implements IValidationCatchingTransform
{

	private $_recovered;
	private $_recovery;


	public function __construct(IValidationStep $next) {
		parent::__construct($next);

		$this->_recovered = false;
		$this->_recovery = null;
	}


	public function wasRecovered() : bool {
		return $this->_recovered;
	}


	public function getRecoveredResult() {
		if (!$this->wasRecovered()) throw new \ErrorException();

		return $this->_recovery;
	}


	abstract protected function _recover(IValidationException $exception);


	public function recover(IValidationException $exception) : IValidationCatchingTransform {
		if (!$this->wasValidated()) throw new \ErrorException();

		$this->_recovery = $this->_recover($exception);
		$this->_recovered = true;

		return $this;
	}
}
