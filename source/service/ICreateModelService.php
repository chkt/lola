<?php

namespace lola\service;

use lola\model\IModel;



interface ICreateModelService
{

	public function createModel(array $map) : IModel;
}
