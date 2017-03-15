<?php

namespace lola\input\valid;

use lola\input\valid\IValidationException;



interface IValidationTransform
{

	public function wasValidated() : bool;

	public function isValid() : bool;


	public function getId() : string;


	public function hasNextStep() : bool;

	public function& useNextStep() : IValidationTransform;


	public function getSource();

	public function getResult();

	public function getError() : IValidationException;


	public function validate($source) : IValidationTransform;

	public function reset() : IValidationTransform;
}
