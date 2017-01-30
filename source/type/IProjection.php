<?php

namespace lola\type;



interface IProjection
{

	public function get(array $selection = null)  : array;
}
