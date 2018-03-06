<?php

namespace lola\type\data;



interface ISafeItemAccessor
extends IItemAccessor
{

	public function& useItem(string $key, $default = null);
}
