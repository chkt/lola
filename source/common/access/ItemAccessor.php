<?php

namespace lola\common\access;

use eve\common\access\IItemAccessor;
use lola\common\access\exception\AccessorException;



class ItemAccessor
implements IItemAccessor
{

	private $_selector;
	private $_data;


	public function __construct(IAccessorSelector $selector, array& $data) {
		$this->_selector = $selector;
		$this->_data =& $data;
	}


	final protected function& _useData() : array {
		return $this->_data;
	}

	final protected function _getSelector() : IAccessorSelector {
		return $this->_selector;
	}


	protected function _select(string $key) : IAccessorSelector {
		return $this->_selector->select($this->_data, $key);
	}


	protected function _handleSelectorFailure(IAccessorSelector $selector) {}


	public function hasKey(string $key) : bool {
		return $this->_selector
			->select($this->_data, $key)
			->isResolved();
	}

	public function getItem(string $key) {
		$selector = $this->_selector->select($this->_data, $key);

		if (!$selector->isResolved()) {
			$this->_handleSelectorFailure($this->_selector);

			if (!$selector->isResolved()) throw new AccessorException($selector);
		}

		return $selector->getResolvedItem();
	}
}
