<?php

namespace lola\input;

use lola\type\IProjectable;



interface IField
extends IProjectable
{

	public function isChanged() : bool;

	public function isEmpty() : bool;

	public function isMultiValue() : bool;

	public function isImmutable() : bool;

	public function isSubmit() : bool;

	
	public function isValid() : bool;


	public function getName() : string;


	public function getValue() : string;

	public function setValue(string $value) : IField;


	public function getValues() : array;

	public function setValues(array $values) : IField;


	public function invalidate(int $state) : IField;
}
