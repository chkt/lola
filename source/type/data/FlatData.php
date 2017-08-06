<?php

namespace lola\type\data;



class FlatData
implements IItemMutator
{

	private $_data;


	public function __construct(array& $data = []) {
		$this->_data =& $data;
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

		if (!array_key_exists($key, $this->_data)) throw new FlatAccessException($key);

		return $this->_data[$key];
	}

	public function setItem(string $key, $item) : IItemMutator {
		if (empty($key)) throw new \ErrorException();

		$this->_data[$key] = $item;

		return $this;
	}
}
