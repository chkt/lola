<?php

namespace lola\model;

use eve\inject\IInjectable;



interface IModelFactory
extends IInjectable
{

	public function produceModelData() : array;
}
