<?php

namespace lola\type\data;



interface ICompoundAccessor
extends IKeyAccessor
{

	public function isArray(string $key) : bool;

	public function isInstance(string $key, string $qname) : bool;


	public function& useArray(string $key) : array;

	public function& useInstance(string $key, string $qname);
}
