<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;



final class Noop
extends AValidationTransform
{

	public function getId() : string {
		return 'noop';
	}


	protected function _validate($source) {
		return $source;
	}
}
