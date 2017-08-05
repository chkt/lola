<?php

namespace lola\service\model;

use lola\type\data\IItemAccessor;



interface ISyntheticDataService
{

	public function getSyntheticData(int $type, array $attributes) : IItemAccessor;
}
