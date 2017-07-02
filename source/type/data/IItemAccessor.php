<?php

namespace lola\type\data;

use lola\type\data\IKeyMutator;



interface IItemAccessor
extends IKeyMutator
{

	public function& useItem(string $key);

	public function setItem(string $key, $item) : IItemAccessor;
}
