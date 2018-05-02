<?php

namespace lola\service\model;

use eve\common\access\IItemAccessor;



interface ISyntheticDataService
{

	public function getSyntheticData(int $type, array $attributes) : IItemAccessor;
}
