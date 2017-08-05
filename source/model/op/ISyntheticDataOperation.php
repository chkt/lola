<?php

namespace lola\model\op;

use lola\type\data\IItemAccessor;



interface ISyntheticDataOperation
{

	public function getSyntheticData(ISyntheticQuery $query) : IItemAccessor;
}
