<?php

namespace lola\input\form;

use lola\type\IProjectable;
use lola\input\valid\IValidateable;

use lola\input\valid\IValidationStep;



interface IField
extends IProjectable, IValidateable
{

	public function isChanged() : bool;

	public function isEmpty() : bool;

	public function isMultiValue() : bool;

	public function isImmutable() : bool;

	public function isSubmit() : bool;


	public function getName() : string;


	public function getValue() : string;

	public function setValue(string $value) : IField;


	public function getValues() : array;

	public function setValues(array $values) : IField;
}
