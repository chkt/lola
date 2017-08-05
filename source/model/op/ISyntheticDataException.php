<?php

namespace lola\model\op;



interface ISyntheticDataException
extends \Throwable
{

	public function getAttributes() : array;

	public function getQuery();
}
