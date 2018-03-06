<?php

namespace lola\type\data;



interface ISafeScalarAccessor
extends IScalarAccessor
{

	public function getBool(string $key, bool $default = false) : bool;

	public function getInt(string $key, int $default = 0) : int;

	public function getFloat(string $key, float $default = 0.0) : float;

	public function getString(string $key, string $default = '') : string;
}
