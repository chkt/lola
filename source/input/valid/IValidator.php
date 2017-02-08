<?php

namespace lola\input\valid;

use lola\type\IProjectable;



interface IValidator
extends IProjectable
{

	public function wasValidated() : bool;

	public function isValid() : bool;


	public function getSource();

	public function getResult();

	public function getFailures() : array;


	public function validate($value) : IValidator;

	public function reset() : IValidator;

	public function assert() : IValidator;
}
