<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationCatchingTransform;

use lola\input\valid\IValidationException;



final class NullableTransform
extends AValidationCatchingTransform
{

	public function getId() : string {
		return 'nullable';
	}


	protected function _validate($source) {
		return $source;
	}

	protected function _transform($source, $result) {
		return $result;
	}

	protected function _recover(IValidationException $ex) {
		return null;
	}
}
