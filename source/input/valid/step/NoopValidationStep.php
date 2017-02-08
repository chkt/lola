<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationStep;



final class NoopValidationStep
extends AValidationStep
{

	public function getId() : string {
		return 'noop';
	}


	protected function _validate($source) {
		return $source;
	}
}
