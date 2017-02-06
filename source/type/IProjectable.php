<?php

namespace lola\type;



interface IProjectable
{

	public function getProjection(array $selection = []) : array;
}
