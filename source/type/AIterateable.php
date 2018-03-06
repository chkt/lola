<?php

namespace lola\type;



/**
 * Abstract iterateable
 */
abstract class AIterateable
implements IIterateable
{

	/**
	 * The iteration cursor position
	 * @var int
	 */
	private $_cursor;


	/**
	 * Creates a new instance
	 */
	public function __construct() {
		$this->_cursor = 0;
	}


	/**
	 * Returns true if the item at $index exists, false otherwise
	 * @param int $index - The iteration index
	 * @return bool;
	 */
	abstract protected function _hasItem(int $index) : bool;


	/**
	 * Returns the current cursor position
	 * @return int
	 */
	public function getIndex() : int {
		return $this->_cursor;
	}

	/**
	 * Sets the iteration index to $index
	 * @param int $index - The new iteration index
	 * @return AIterateable
	 */
	protected function _setIndex(int $index) : AIterateable {
		$this->_cursor = $index;

		return $this;
	}


	/**
	 * Returns a reference to the item at $index
	 * @param int $index - The iteration index
	 */
	abstract protected function& _useItem(int $index);


	/**
	 * Returns a reference to the item at $index if item exists, null otherwise
	 * @param int $index - The iteration index
	 * @return mixed
	 */
	public function& useIndex(int $index) {
		$this->_setIndex($index);

		if ($this->_hasItem($index)) return $this->_useItem($index);

		$null = null;

		return $null;
	}

	/**
	 * Returns a reference to the item relative to the current iteration position by $offset
	 * @param int $offset - The offset to the iteration index
	 * @return mixed
	 */
	public function& useOffset(int $offset) {
		return $this->useIndex($this->getIndex() + $offset);
	}


	/**
	 * Returns a reference to the first item
	 * @return mixed
	 */
	public function& useFirst() {
		return $this->useIndex(0);
	}

	/**
	 * Returns a reference to the previous item relative to the iteration index
	 * @return mixed
	 */
	public function& usePrev() {
		return $this->useOffset(-1);
	}

	/**
	 * Returns a reference to the next item relative to the iteration index
	 * @return mixed
	 */
	public function& useNext() {
		return $this->useOffset(1);
	}


	/**
	 * Yields all items in order
	 */
	public function& iterate() : \Generator {
		$this->_setIndex(0);

		for (
			$item =& $this->useFirst();
			!is_null($item);
			$item =& $this->useNext()
		) yield $this->getIndex() => $item;
	}
}
