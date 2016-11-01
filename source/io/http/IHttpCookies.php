<?php

namespace lola\io\http;



interface IHttpCookies
{

	public function hasChanges() : bool;

	public function getChangedNames() : array;


	public function isUpdated(string $name) : bool;

	public function isRemoved(string $name) : bool;

	public function isSecure(string $name) : bool;

	public function isHttpOnly(string $name) : bool;


	public function getValue(string $name) : string;

	public function getExpiry(string $name) : int;

	public function getPath(string $name) : string;

	public function getDomain(string $name) : string;


	public function set(string $name, string $value, int $expires, array $options);

	public function reset(string $name);
}
