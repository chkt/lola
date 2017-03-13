<?php

namespace lola\input\valid;

use lola\type\IProjectable;

use lola\input\valid\IValidationException;



interface IValidator
extends IProjectable
{

	public function wasValidated() : bool;

	public function isValid() : bool;


	public function getSource();

	public function getResult();

	public function getFailures() : array;


	public function hasChain(string $name) : bool;

	public function isChainValid(string $name) : bool;

	public function getChainResult(string $name);

	public function getChainFailure(string $name) : IValidationException;


	public function validate($value) : IValidator;

	public function reset() : IValidator;

	public function assert() : IValidator;
}
