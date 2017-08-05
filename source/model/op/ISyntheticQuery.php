<?php

namespace lola\model\op;

use lola\type\data\IScalarAccessor;



interface ISyntheticQuery
{

	public function getAttributes() : IScalarAccessor;

	public function setAttributes(IScalarAccessor $attributes) : ISyntheticQuery;


	public function getQuery();
}
