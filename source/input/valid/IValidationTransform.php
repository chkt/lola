<?php

namespace lola\input\valid;

use lola\input\valid\IValidationStep;



interface IValidationTransform
{

	public function wasTransformed() : bool;

	public function getNextStep() : IValidationStep;

	public function getTransformedResult();


	public function transform($result) : IValidationTransform;
}
