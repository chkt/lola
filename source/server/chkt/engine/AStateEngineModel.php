<?php

namespace chkt\engine;

use chkt\model\AModel;



abstract class AStateEngineModel
extends AModel
{
	
	abstract public function getState();
	
	abstract public function setState($state);
}
