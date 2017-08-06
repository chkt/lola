<?php

namespace lola\type\data;



interface IItemAccessor
extends IKeyAccessor
{

	public function& useItem(string $key);
}
