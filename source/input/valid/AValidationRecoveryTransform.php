<?php

namespace lola\input\valid;

use lola\input\valid\AValidationTransform;
use lola\input\valid\IValidationRecoveryTransform;



abstract class AValidationRecoveryTransform
extends AValidationTransform
implements IValidationRecoveryTransform
{

	private $_recovered;


	public function __construct(IValidationTransform $next) {
		parent::__construct($next);

		$this->_recovered = false;
	}


	public function wasRecovered() : bool {
		return $this->_recovered;
	}


	public function& useTerminalStep() : IValidationTransform {
		if ($this->wasRecovered()) return $this;

		return parent::useTerminalStep();
	}


	abstract protected function _recover(IValidationException $exception);


	protected function _validateNextStep(IValidationTransform &$next, $source) {
		$result = $this->getSource();

		$next->validate($source);

		if ($next->isValid()) $result = $this->_transform($this->getSource(), $next->getResult());
		else {
			$result = $this->_recover($next->getError());
			$this->_recovered = true;
		}

		return $result;
	}

	public function reset() : IValidationTransform {
		$this->_recovered = false;

		return parent::reset();
	}
}
