<?php

namespace lola\model;

use lola\type\AIterateable;
use lola\model\collection\ICollection;

use lola\model\IModel;
use lola\model\IResourceQuery;
use lola\model\IResourceCollection;



abstract class ACollection
extends AIterateable
implements ICollection
{

	private $_resource = null;
	private $_factory = null;


	public function __construct(IResourceCollection $resource, Callable $itemFactory) {
		parent::__construct();

		$this->_resource = $resource;
		$this->_factory = $itemFactory;

		$this->_length = $resource->getLength();
	}


	protected function& _useItem($index) {
		$items =& $this->_items;

		if (!array_key_exists($index, $items)) {
			$resource =& $this->_resource->useItem($index);
			$items[$index] = call_user_func_array($this->_factory, [ & $resource ]);
		}

		return $items[$index];
	}


	public function isLive() : bool {
		return $this->_resource->isLive();
	}


	public function hasItem(IResourceQuery $query) : bool {
		return $this->_resource->getIndexOf($query) !== -1;
	}

	public function& useItem(IResourceQuery $query) : IModel {
		$index = $this->_resource->getIndexOf($query);
		$null = null;

		return $index !== -1 ? $this->_useItem($index) : $null;
	}


	public function update() : ICollection {
		$this->_resource->update();

		return $this;
	}
}
