<?php

namespace lola\model\op;

use eve\common\access\IItemAccessor;



interface ISyntheticDataOperation
{

	public function getSyntheticData(ISyntheticQuery $query) : IItemAccessor;
}
