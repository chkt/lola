<?php

namespace lola\input\form;

use eve\common\projection\IProjectable;



interface IProcessor
extends IProjectable
{

	const FLAG_VALIDATE = 0x1;
	const FLAG_COMMIT = 0x2;
	const FLAG_VALID = 0x4;
	const FLAG_MODIFIED = 0x8;

	const STATE_UNVALIDATED = 0x0;
	const STATE_UNCOMMITED = 0x1;
	const STATE_UNMODIFIED = 0x7;
	const STATE_INVALID = 0xb;
	const STATE_VALID = 0xf;



	public function getState() : int;

	public function setState(int $state) : IProcessor;


	public function& useField(string $name) : IField;


	public function getValue(string $name) : string;

	public function getValues(string $name) : array;

	public function getValidatedData(string $name);


	public function validate(array $input) : IProcessor;
}
