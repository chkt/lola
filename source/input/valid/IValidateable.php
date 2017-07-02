<?php

namespace lola\input\valid;

use lola\input\valid\IValidationTransform;



interface IValidateable
{

	public function& useValidation() : IValidationTransform;

	public function setValidation(IValidationTransform& $validation) : IValidateable;
}
