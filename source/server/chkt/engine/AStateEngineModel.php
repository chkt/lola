<?php

namespace chkt\engine;

use chkt\model\AModel;



abstract class AStateEngineModel
extends AModel
{
	
	const VERSION = '0.1.5';
	
	
	
	abstract public function getState();
	
	abstract public function setState($state);
}
