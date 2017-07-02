<?php

namespace lola\type;

use lola\type\IIterateable;



interface ISizedIterateable
extends IIterateable
{

	public function getLength() : int;


	public function& useLast();
}
