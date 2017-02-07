<?php

namespace lola\input\valid;



interface IValidationStep
{

	public function wasValidated() : bool;

	public function isValid() : bool;


	public function getId() : string;


	public function getSource();

	public function getResult();

	public function getError() : IValidationException;


	public function validate($source) : IValidationStep;

	public function reset() : IValidationStep;
}
