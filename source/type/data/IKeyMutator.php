<?php

namespace lola\type\data;



interface IKeyMutator
extends IKeyAccessor
{

	public function removeKey(string $key) : IKeyMutator;
}
