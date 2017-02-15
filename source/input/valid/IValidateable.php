<?php

namespace lola\input\valid;

use lola\input\valid\IValidationStep;



interface IValidateable
{

	public function& useValidation() : IValidationStep;

	public function setValidation(IValidationStep& $validation) : IValidateable;
}
