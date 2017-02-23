<?php

namespace lola\service;

use lola\service\IGetModelService;
use lola\service\ICreateModelService;

use lola\model\IModel;



interface IGetCreateModelService
extends IGetModelService, ICreateModelService
{

	public function getOrCreateModel(array $map) : IModel;
}
