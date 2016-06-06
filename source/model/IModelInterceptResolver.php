<?php

namespace lola\model;

use lola\model\AModel;
use lola\model\ModelActionLog;



interface IModelInterceptResolver
{
	
	public function link(AModel $source);
	
	public function update(AModel $source, ModelActionLog $log);
	
	public function delete(AModel $source, ModelActionLog $log);
}
