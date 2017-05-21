<?php

namespace lola\type\data;



interface IKeyMutator
{

	public function hasKey(string $key) : bool;

	public function removeKey(string $key) : IKeyMutator;
}
