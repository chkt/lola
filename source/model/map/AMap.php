<?php

namespace lola\model\map;

use lola\model\map\IMap;

use lola\model\map\IResourceMap;



abstract class AMap
implements IMap
{

	private $_resource;
	private $_base;


	public function __construct(IResourceMap& $resource, string $base) {
		if (empty($base)) throw new \ErrorException();

		$this->_resource =& $resource;
		$this->_base = $base;
	}


	private function _getId(string $key) : string {
		return implode('_', [
			$this->_base,
			$key
		]);
	}


	public function hasKey(string $key) : bool {
		return $this->_resource->hasKey($this->_getId($key));
	}


	public function getBool(string $key) : bool {
		return $this->_resource->getBool($this->_getId($key));
	}

	public function setBool(string $key, bool $value) : IMap {
		$this->_resource->setBool($this->_getId($key), $value);

		return $this;
	}


	public function getInt(string $key) : int {
		return $this->_resource->getInt($this->_getId($key));
	}

	public function setInt(string $key, int $value) : IMap {
		$this->_resource->setInt($this->_getId($key), $value);

		return $this;
	}


	public function getFloat(string $key) : float {
		return $this->_resource->getFloat($this->_getId($key));
	}

	public function setFloat(string $key, float $value) : IMap {
		$this->_resource->setFloat($this->_getId($key), $value);

		return $this;
	}


	public function getString(string $key) : string {
		return $this->_resource->getString($this->_getId($key));
	}

	public function setString(string $key, string $value) : IMap {
		$this->_resource->setString($this->_getId($key), $value);

		return $this;
	}


	public function getList(string $key) : array {
		return $this->_resource->getList($this->_getId($key));
	}

	public function setList(string $key, array $list) : IMap {
		$this->_resource->setList($this->_getId($key), $list);

		return $this;
	}


	public function getSet(string $key) : array {
		return $this->_resource->getSet($this->_getId($key));
	}

	public function setSet(string $key, array $set) : IMap {
		$this->_resource->setSet($this->_getId($key), $set);

		return $this;
	}


	public function getMap(string $key) : array {
		return $this->_resource->getMap($this->_getId($key));
	}

	public function setMap(string $key, array $map) : IMap {
		$this->_resource->setMap($this->_getId($key), $map);

		return $this;
	}


	public function removeKey(string $key) : IMap {
		$this->_resource->removeKey($this->_getId($key));

		return $this;
	}

	public function renameKey(string $key, string $to) : IMap {
		$this->_resource->renameKey($this->_getId($key), $this->_getId($to));

		return $this;
	}
}
