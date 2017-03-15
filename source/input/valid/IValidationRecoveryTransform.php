<?php

namespace lola\input\valid;

use lola\input\valid\IValidationTransform;



interface IValidationRecoveryTransform
extends IValidationTransform
{

	public function wasRecovered() : bool;
}
