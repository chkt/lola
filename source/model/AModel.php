<?php

namespace lola\model;

use lola\model\IModel;
use lola\model\IResource;
use lola\common\projection\IProjector;



abstract class AModel
implements IModel
{

	const VERSION = '0.2.4';



	private $_resource = null;
	private $_projector;

	private $_data = null;
	private $_update = true;


	public function __construct(IResource $resource, IProjector $projector) {
		$this->_resource = $resource;
		$this->_projector = $projector;

		$this->_data = null;
		$this->_update = true;
	}


	protected function& _useResource() {
		if (is_null($this->_data)) $this->_data = $this->_resource->getData();

		return $this->_data;
	}

	protected function& _useProjector() {
		$this->_projector->setSource($this->_useResource());

		return $this->_projector;
	}


	protected function _hasResourceProperty($key) {
		return $this->_useResource()->hasItem($key);
	}

	protected function& _useResourceProperty($key) {
		return $this->_useResource()->useItem($key);
	}

	protected function _setResourceProperty($key, $value) {
		$this->_useResource()->setItem($key, $value);

		if ($this->_update) $this->_updateResource();

		return $this;
	}

	protected function _addResourceProperty($key, $value) {
		$this->_useResource()->addItem($key, $value);

		if ($this->_update) $this->_updateResource();

		return $this;
	}

	protected function _removeResourceProperty($key) {
		$this->_useResource()->removeItem($key);

		if ($this->_update) $this->_updateResource();

		return $this;
	}


	protected function _updateResource() {
		if (!is_null($this->_data)) $this->_resource
			->setData($this->_data)
			->update();

		return $this;
	}

	protected function _deleteResource() {
		$this->_resource->delete();

		return $this;
	}


	public function isLive() {
		return $this->_resource->isLive();
	}


	public function wasCreated() {
		return $this->_resource->wasCreated();
	}

	public function wasRead() {
		return $this->_resource->wasRead();
	}


	public function deferUpdates() {
		$this->_update = false;

		return $this;
	}

	public function update() {
		$this->_updateResource();

		$this->_update = true;

		return $this;
	}


	public function getProjection(array $selection = null) : array {
		return $this
			->_useProjector()
			->getProjection($selection);
	}
}
