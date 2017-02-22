<?php

namespace lola\model;

use lola\model\AModel;
use lola\model\IResource;
use lola\type\IProjector;

use lola\model\ModelActionQueue;



abstract class AActionModel
extends AModel
{

	const VERSION = '0.2.1';



	private $_log = null;

	private $_updateQueue = null;
	private $_deleteQueue = null;

	private $_isUpdating = false;
	private $_isDeleting = false;


	public function __construct(IResource& $resource, IProjector& $projector) {
		parent::__construct($resource, $projector);

		$this->_log = new ModelActionLog();

		$this->_createQueue = null;
		$this->_readQueue = null;
		$this->_updateQueue = null;
		$this->_deleteQueue = null;

		$this->_isUpdating = false;
		$this->_isDeleting = false;
	}


	protected function& _useUpdateQueue() {
		if (is_null($this->_updateQueue)) $this->_updateQueue = new ModelActionQueue();

		return $this->_updateQueue;
	}

	protected function& _useDeleteQueue() {
		if (is_null($this->_deleteQueue)) $this->_deleteQueue = new ModelActionQueue();

		return $this->_deleteQueue;
	}

	private function _processQueue(ModelActionQueue $queue) {
		while ($this->_log->getLength() !== 0) {
			$log = $this->_log;

			$this->_log = new ModelActionLog();
			$queue->process($this, $log);
		}

		return $this;
	}


	protected function _setResourceProperty($key, $value) {
		$old = $this->_useResourceProperty($key);

		if ($old !== $value) {
			$this->_log->push($key, $old, $value);

			try {
				parent::_setResourceProperty($key, $value);
			}
			catch (\Exception $ex) {
				$this->_log->pop();

				throw $ex;
			}
		}

		return $this;
	}

	protected function _addResourceProperty($key, $value) {
		$this->_log->push($key, null, $value);

		try {
			parent::_addResourceProperty($key, $value);
		}
		catch (\Exception $ex) {
			$this->_log->pop();

			throw $ex;
		}

		return $this;
	}

	protected function _removeResourceProperty($key) {
		$this->_log->push($key, $this->_useResourceProperty($key), null);

		try {
			parent::_removeResourceProperty($key);
		} catch (\Exception $ex) {
			$this->_log->pop();

			throw $ex;
		}

		return $this;
	}


	protected function _updateResource() {
		if (!$this->_isUpdating) {
			$this->_isUpdating = true;

			if (!is_null($this->_updateQueue)) $this->_processQueue($this->_updateQueue);
			else $this->_log->clear();

			parent::_updateResource();

			$this->_isUpdating = false;
		}

		return $this;
	}

	protected function _deleteResource() {
		if (!$this->_isDeleting) {
			$this->_isDeleting = true;

			if (!is_null($this->_deleteQueue)) $this->_processQueue($this->_deleteQueue);
			else $this->_log->clear();

			parent::_deleteResource();

			$this->_isDeleting = false;
		}

		return $this;
	}
}
