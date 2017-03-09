<?php

namespace lola\model;

use lola\type\ASizedIterateable;



final class ModelActionLog
extends ASizedIterateable
{

	private $_items;
	private $_length;


	public function __construct() {
		parent::__construct();

		$this->_items = [];
		$this->_length = 0;
	}


	public function getLength() : int {
		return $this->_length;
	}


	protected function& _useItem(int $index) {
		return $this->_items[$index];
	}


	public function push($prop, $old, $new) {
		if (!is_string($prop) || empty($prop)) throw new \ErrorException();

		$this->_items[] = [
			'property' => $prop,
			'oldData' => $old,
			'newData' => $new
		];

		$this->_length += 1;

		return $this;
	}

	public function pop() {
		$res = array_pop($this->_items);

		$this->_length -= 1;

		return $res;
	}

	public function clear() {
		$this->_items = [];

		$this->_cursor = 0;
		$this->_length = 0;

		return $this;
	}
}
