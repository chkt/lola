<?php

namespace lola\service;

use lola\model\IModel;



interface IGetModelService
{

	public function hasModel(array $query) : bool;

	public function getModel(array $query) : IModel;
}
