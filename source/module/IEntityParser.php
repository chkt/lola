<?php

namespace lola\module;

use eve\entity\IEntityParser as IParentParser;



interface IEntityParser
extends IParentParser
{

	const COMPONENT_MODULE = 'module';
	const COMPONENT_NAME = 'name';
	const COMPONENT_CONFIG = 'config';
	const COMPONENT_DESCRIPTOR = 'descriptor';



	public function parse(string $entity, string $end = self::COMPONENT_TYPE) : array;
}
