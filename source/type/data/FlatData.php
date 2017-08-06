<?php

namespace lola\type\data;



class FlatData
implements IItemMutator
{

	private $_data;


	public function __construct(array& $data = []) {
		$this->_data =& $data;
	}


	protected function _handleAccessException(FlatAccessException $ex) : bool {
		return false;
	}


	public function hasKey(string $key) : bool {
		if (empty($key)) throw new \ErrorException('ACC_INV_KEY: ' . $key);

		return array_key_exists($key, $this->_data);
	}

	public function removeKey(string $key) : IKeyMutator {
		if (empty($key)) throw new \ErrorException('ACC_INV_KEY: ' . $key);

		if (array_key_exists($key, $this->_data)) unset($this->_data[$key]);

		return $this;
	}


	public function& useItem(string $key) {
		if (empty($key)) throw new \ErrorException('ACC_INV_KEY: ' . $key);

		if (!array_key_exists($key, $this->_data)) {
			$ex = new FlatAccessException($key);

			if (
				!$this->_handleAccessException($ex) ||
				!array_key_exists($key, $this->_data)
			) throw $ex;
		}

		return $this->_data[$key];
	}

	public function setItem(string $key, $item) : IItemMutator {
		if (empty($key)) throw new \ErrorException();

		$this->_data[$key] = $item;

		return $this;
	}
}
