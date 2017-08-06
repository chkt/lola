<?php

namespace lola\type\data;



interface ICompoundMutator
extends IKeyMutator, ICompoundAccessor
{

	public function setArray(string $key, array $item) : ICompoundAccessor;

	public function setInstance(string $key, $item) : ICompoundAccessor;
}
