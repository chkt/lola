<?php

namespace lola\input\valid;

use lola\input\valid\AValidationTransform;
use lola\input\valid\IValidationCatchingTransform;



abstract class AValidationCatchingTransform
extends AValidationTransform
implements IValidationCatchingTransform
{

	private $_recovered;


	public function __construct(IValidationStep $next) {
		parent::__construct($next);

		$this->_recovered = null;
	}


	public function wasRecovered() : bool {
		return !is_null($this->_recovered);
	}


	public function getRecoveredResult() {
		if (!$this->wasRecovered()) throw new \ErrorException();

		return $this->_recovered;
	}


	abstract protected function _recover(IValidationException $exception);


	public function recover(IValidationException $exception) : IValidationCatchingTransform {
		if (!$this->wasValidated()) throw new \ErrorException();

		$this->_recovered = $this->_recover($exception);

		return $this;
	}
}
