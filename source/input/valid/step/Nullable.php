<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationRecoveryTransform;

use lola\input\valid\IValidationException;



final class Nullable
extends AValidationRecoveryTransform
{

	public function getId() : string {
		return 'nullable';
	}


	protected function _validate($source) {
		return $source;
	}
	

	protected function _recover(IValidationException $ex) {
		return null;
	}
}
