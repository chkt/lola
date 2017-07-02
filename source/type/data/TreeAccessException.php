<?php

namespace lola\type\data;

use lola\type\data\ITreeAccessException;



class TreeAccessException
extends \Exception
implements ITreeAccessException
{

	const TYPE_NO_PROP = 1;
	const TYPE_NO_BRANCH = 2;


	static private function _getMessage(int $type) : string {
		$map = [
			self::TYPE_NO_PROP => 'ACC_NO_PROP:',
			self::TYPE_NO_BRANCH => 'ACC_NO_BRANCH:',
		];

		if (!array_key_exists($type, $map)) return 'ACC_UNID';

		return $map[$type];
	}



	private $_type;
	private $_resolved;
	private $_missing;

	private $_item;


	public function __construct(
		int $type = self::TYPE_NO_PROP,
		& $item = null,
		array $missing = [],
		array $resolved = []
	) {
		parent::__construct(
			self::_getMessage($type) .  implode('.', $resolved) . '!' . implode('.', $missing),
			$type
		);

		$this->_type = $type;
		$this->_resolved = $resolved;
		$this->_missing = $missing;

		$this->_item =& $item;
	}


	public function& useResolvedItem() {
		return $this->_item;
	}


	public function getResolvedKey() : string {
		return implode('.', $this->_resolved);
	}

	public function getResolvedPath() : array {
		return $this->_resolved;
	}


	public function getMissingKey() : string {
		return implode('.', $this->_missing);
	}

	public function getMissingPath() : array {
		return $this->_missing;
	}
}
