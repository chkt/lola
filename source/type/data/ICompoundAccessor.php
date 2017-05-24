<?php

namespace lola\type\data;

use lola\type\data\IKeyMutator;



interface ICompoundAccessor
extends IKeyMutator
{

	public function isArray(string $key) : bool;

	public function isInstance(string $key, string $qname) : bool;


	public function& useArray(string $key) : array;

	public function& useInstance(string $key, string $qname);


	public function setArray(string $key, array $item) : ICompoundAccessor;

	public function setInstance(string $key, $item) : ICompoundAccessor;
}
