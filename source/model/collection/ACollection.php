<?php

namespace lola\model\collection;

use eve\inject\IInjector;
use lola\type\ASizedIterateable;
use lola\type\IProjectable;
use lola\type\query\IDataQuery;
use lola\model\IModel;
use lola\model\AResourceDependencyFactory;



abstract class ACollection
extends ASizedIterateable
implements ICollection, IProjectable
{

	private $_injector;
	private $_resource;

	private $_itemModel;

	private $_items;


	public function __construct(
		IInjector& $injector,
		IResourceCollection& $resource,
		string $itemModel
	) {
		parent::__construct();

		$this->_injector =& $injector;
		$this->_resource =& $resource;

		$this->_itemModel = $itemModel;

		$this->_items = [];
	}


	protected function _produceModel($index) : IModel {
		$resource =& $this->_resource->useItem($index);

		$model = $this->_injector->produce($this->_itemModel, [
			'mode' => AResourceDependencyFactory::MODE_PASS,
			'resource' => & $resource,
		]);

		return $model;
	}


	protected function& _useItem(int $index) {
		$items =& $this->_items;

		if (!array_key_exists($index, $items)) $items[$index] = $this->_produceModel($index);

		return $items[$index];
	}


	public function isLive() : bool {
		return $this->_resource->isLive();
	}

	public function hasItems() : bool {
		return $this->isLive() && $this->getLength() !== 0;
	}


	public function getLength() : int {
		return $this->_resource->getLength();
	}


	public function hasItem(IDataQuery $query) : bool {
		return $this->_resource->getIndexOf($query) !== -1;
	}

	public function& useItem(IDataQuery $query) : IModel {
		$index = $this->_resource->getIndexOf($query);
		$null = null;

		return $index !== -1 ? $this->_useItem($index) : $null;
	}


	public function update() : ICollection {
		$this->_resource->update();

		return $this;
	}


	public function getProjection(array $selection = []) : array {
		$res = [];

		foreach ($this->iterate() as & $item) $res[] = $item->getProjection($selection);

		return $res;
	}
}
