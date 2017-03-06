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


	public function getList(string $key) : array;

	public function setList(string $key, array $list) : IResourceMap;


	public function getSet(string $key) : array;

	public function setSet(string $key, array $set) : IResourceMap;


	public function getMap(string $key) : array;

	public function setMap(string $key, array $map) : IResourceMap;


	public function removeKey(string $key) : IResourceMap;

	public function renameKey(string $key, string $to) : IResourceMap;
}
