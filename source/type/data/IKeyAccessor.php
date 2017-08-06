<?php

namespace lola\type\data;



interface IKeyAccessor
{

	public function hasKey(string $key) : bool;
}
