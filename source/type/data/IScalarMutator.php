<?php

namespace lola\type\data;



interface IScalarMutator
extends IKeyMutator, IScalarAccessor
{

	public function setBool(string $key, bool $value) : IScalarAccessor;

	public function setInt(string $key, int $value) : IScalarAccessor;

	public function setFloat(string $key, float $value) : IScalarAccessor;

	public function setString(string $key, string $value) : IScalarAccessor;
}
