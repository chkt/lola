<?php

namespace lola\service;

use lola\model\collection\ICollection;



interface ICollectionService
{

	public function getCollection(array $query, int $limit = 10, int $offset = 0) : ICollection;
}
