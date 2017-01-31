<?php

namespace lola\input;



interface IValidator
{

	public function wasValidated() : bool;

	public function isValid() : bool;


	public function reset() : IValidator;

	public function setValid() : IValidator;

	public function getInvalid() : ValidationException;

	public function setInvalid(ValidationException $exception) : IValidator;


	public function validate($value);

	public function assert() : IValidator;
}
