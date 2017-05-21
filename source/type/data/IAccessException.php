<?php

namespace lola\type\data;



interface IAccessException
extends \Throwable
{

	public function getMissingKey() : string;
}
