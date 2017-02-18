<?php

namespace lola\model;

use lola\inject\IInjectable;



interface IModelFactory
extends IInjectable
{

	public function produceModelData() : array;
}
