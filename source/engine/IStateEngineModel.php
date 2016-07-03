<?php

namespace lola\engine;

use lola\model\IModel;



interface IStateEngineModel
extends IModel
{	
	
	public function getState();
	
	public function setState($state);
	
	
	public function useProvider();
}
