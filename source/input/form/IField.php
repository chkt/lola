<?php

namespace lola\input\form;

use eve\common\projection\IProjectable;
use lola\input\valid\IValidateable;



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
