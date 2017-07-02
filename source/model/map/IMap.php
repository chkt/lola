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


	public function getList(string $key) : array;

	public function setList(string $key, array $list) : IMap;


	public function getSet(string $key) : array;

	public function setSet(string $key, array $set) : IMap;


	public function getMap(string $key) : array;

	public function setMap(string $key, array $hash) : IMap;


	public function removeKey(string $key) : IMap;

	public function renameKey(string $key, string $to) : IMap;
}
