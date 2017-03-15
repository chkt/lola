<?php

namespace lola\input\valid;

use lola\input\valid\IValidationTransform;



interface IValidationInterceptor
{

	public function intercept(string $chain, IValidationTransform& $step);
}
