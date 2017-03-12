<?php

namespace lola\input\valid;

use lola\input\valid\IValidationTransform;



interface IValidationCatchingTransform
extends IValidationTransform
{

	public function wasRecovered() : bool;


	public function getRecoveredResult();


	public function recover(IValidationException $exception) : IValidationCatchingTransform;
}
