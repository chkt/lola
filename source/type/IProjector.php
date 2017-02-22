<?php

namespace lola\type;



interface IProjector
{

	public function setSource(StructuredData& $source) : IProjector;

	public function getProjection(array $selection = null)  : array;
}
