<?php

namespace lola\type;



interface IProjector
{

	public function get(array $selection = null)  : array;
}
