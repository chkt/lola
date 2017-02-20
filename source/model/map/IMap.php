<?php

namespace lola\model\map;



interface IMap
{

	public function hasKey(string $key) : bool;


	public function getBool(string $key) : bool;

	public function setBool(string $key, bool $value) : IMap;


	public function getInt(string $key) : int;

	public function setInt(string $key, int $value) : IMap;


	public function getFloat(string $key) : float;

	public function setFloat(string $key, float $value) : IMap;


	public function getString(string $key): string;

	public function setString(string $key, string $value) : IMap;


	public function removeKey(string $key) : IMap;

	public function renameKey(string $key, string $to) : IMap;
}
