<?php

namespace lola\model;

use eve\common\access\ITraversableAccessor;
use eve\inject\IInjectable;



interface IModelFactory
extends IInjectable
{

	public function produceModelData() : ITraversableAccessor;
}
