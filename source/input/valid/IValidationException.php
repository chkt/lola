<?php

namespace lola\input\valid;

use lola\type\IProjectable;



interface IValidationException
extends IProjectable
{

	public function isFinal() : bool;
}
