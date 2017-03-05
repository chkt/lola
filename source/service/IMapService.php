<?php

namespace lola\service;

use lola\model\map\IMap;



interface IMapService
{

	public function& useMap() : IMap;
}
