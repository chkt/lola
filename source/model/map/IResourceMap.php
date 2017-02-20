<?php

namespace lola\model\map;



interface IResourceMap
{

	public function hasKey(string $key) : bool;


	public function getBool(string $key) : bool;

	public function setBool(string $key, bool $value) : IResourceMap;


	public function getInt(string $key) : int;

	public function setInt(string $key, int $value) : IResourceMap;


	public function getFloat(string $key) : float;

	public function setFloat(string $key, float $value) : IResourceMap;


	public function getString(string $key) : string;

	public function setString(string $key, string $value) : IResourceMap;
}
