<?php

namespace lola\type\data;



interface IItemMutator
extends IKeyMutator, IItemAccessor
{

	public function setItem(string $key, $item) : IItemMutator;
}
