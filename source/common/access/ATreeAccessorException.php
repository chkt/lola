<?php

namespace lola\common\access;



abstract class ATreeAccessorException
extends \Exception
implements ITreeAccessorException
{

	private $_item;

	private $_resolved;
	private $_missing;


	public function __construct(
		& $item,
		array $resolved = [],
		array $missing = []
	) {
		$this->_item =& $item;

		$this->_resolved = $resolved;
		$this->_missing = $missing;

		parent::__construct(sprintf(
			$this->_produceMessage(),
			$this->getResolvedKeySegment(),
			$this->getMissingKeySegment()
		));
	}


	abstract protected function _produceMessage() : string;


	public function& useResolvedItem() {
		return $this->_item;
	}


	public function getKey() : string {
		return implode('.', array_merge($this->_resolved, $this->_missing));
	}


	public function getResolvedKeySegment() : string {
		return implode('.', $this->_resolved);
	}

	public function getMissingKeySegment() : string {
		return implode('.', $this->_missing);
	}


	public function getResolvedPath() : array {
		return $this->_resolved;
	}

	public function getMissingPath() : array {
		return $this->_missing;
	}
}
