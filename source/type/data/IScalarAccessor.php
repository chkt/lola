<?php

namespace lola\type\data;

use lola\type\data\IKeyMutator;



interface IScalarAccessor
extends IKeyMutator
{

	public function isBool(string $key) : bool;

	public function isInt(string $key) : bool;

	public function isFloat(string $key) : bool;

	public function isString(string $key) : bool;


	public function getBool(string $key) : bool;

	public function getInt(string $key) : int;

	public function getFloat(string $key) : float;

	public function getString(string $key) : string;


	public function setBool(string $key, bool $value) : IScalarAccessor;

	public function setInt(string $key, int $value) : IScalarAccessor;

	public function setFloat(string $key, float $value) : IScalarAccessor;

	public function setString(string $key, string $value) : IScalarAccessor;
}
