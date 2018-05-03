<?php

namespace lola\common\access;

use eve\common\access\IKeyAccessor;



interface IScalarAccessor
extends IKeyAccessor
{

	public function isBool(string $key) : bool;

	public function isInt(string $key) : bool;

	public function isFloat(string $key) : bool;

	public function isString(string $key) : bool;


	public function getBool(string $key) : bool;

	public function getInt(string $key) : int;

	public function getFloat(string $key) : float;

	public function getString(string $key) : string;
}
