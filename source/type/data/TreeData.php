<?php

namespace lola\type\data;



function array_merge_deep(array $a, array $b) : array {
	foreach ($b as $key => $item) {
		if (
			!is_array($item) ||
			!array_key_exists($key, $a) ||
			!is_array($a[$key])
		) $a[$key] = $item;
		else $a[$key] = array_merge_deep($a[$key], $b[$key]);
	}

	return $a;
}



class TreeData
implements IItemMutator, ITreeMutator
{

	private $_data;


	public function __construct(array& $data = []) {
		$this->_data =& $data;
	}


	protected function _handleBranchException(TreeBranchException $ex) : bool {
		return false;
	}

	protected function _handlePropertyException(TreePropertyException $ex) : bool {
		return false;
	}


	private function& _useKey(array& $data, string $key) {
		$segs = explode('.', $key);

		for ($i = 0, $l = count($segs); $i < $l; $i += 1) {
			$seg = $segs[$i];

			if (strlen($seg) === 0) throw new \ErrorException('ACC_INV_KEY: ' . $key);

			if (ctype_digit($seg)) $seg = (int) $seg;

			if (!is_array($data)) {
				$ex = new TreeBranchException(
					$data,
					array_slice($segs, 0, $i),
					array_slice($segs, $i)
				);

				if (!$this->_handleBranchException($ex)) throw $ex;

				$i -= 1;
			}
			else if (!array_key_exists($seg, $data)) {
				$ex = new TreePropertyException($data,
					array_slice($segs, 0, $i),
					array_slice($segs, $i)
				);

				if (!$this->_handlePropertyException($ex)) throw $ex;

				$i -= 1;
			}
			else $data =& $data[$seg];
		}

		return $data;
	}


	public function isBranch(string $key) : bool {
		try {
			return is_array(self::_useKey($this->_data, $key));
		}
		catch (ITreeAccessException $ex) {
			return false;
		}
	}

	public function isLeaf(string $key) : bool {
		try {
			return !is_array(self::_useKey($this->_data, $key));
		}
		catch (ITreeAccessException $ex) {
			return false;
		}
	}


	public function hasKey(string $key) : bool {
		try {
			self::_useKey($this->_data, $key);
		}
		catch (ITreeAccessException $ex) {
			return false;
		}

		return true;
	}


	public function removeKey(string $key) : IKeyMutator {
		if (empty($key)) throw new \ErrorException();

		$index = strrpos($key, '.');

		if ($index === false) {
			$data =& $this->_data;
			$prop = $key;
		}
		else {
			try {
				$data =& self::_useKey($this->_data, substr($key, 0, $index));
			}
			catch (ITreeAccessException $ex) {
				$data = [];
			}
			$prop = substr($key, $index + 1);
		}

		unset($data[$prop]);

		return $this;
	}


	public function& useItem(string $key) {
		return self::_useKey($this->_data, $key);
	}

	public function setItem(string $key, $item) : IItemMutator {
		try {
			$ref =& self::_useKey($this->_data, $key);
		}
		catch (TreePropertyException $ex) {
			$ref =& $ex->useResolvedItem();
			$segs = $ex->getMissingPath();

			foreach ($segs as $seg) {
				$ref[$seg] = [];
				$ref =& $ref[$seg];
			}
		}

		$ref = $item;

		return $this;
	}


	public function merge(ITreeAccessor $a, ITreeAccessor $b) : ITreeMutator {
		$this->_data = array_merge_deep($a->getProjection(), $b->getProjection());

		return $this;
	}

	public function mergeEq(ITreeAccessor $b) : ITreeMutator {
		$this->_data = array_merge_deep($this->_data, $b->getProjection());

		return $this;
	}


	public function filter(ITreeAccessor $tree, array $filter) : ITreeMutator {
		$data = $tree->getProjection();
		$this->_data = [];

		foreach ($filter as $key) $this->setItem($key, self::_useKey($data, $key));

		return $this;
	}

	public function filterSelf(array $filter) : ITreeMutator {
		$data = $this->_data;
		$this->_data = [];

		foreach ($filter as $key) $this->setItem($key, self::_useKey($data, $key));

		return $this;
	}


	public function select(ITreeAccessor $tree, string $key) : ITreeMutator {
		if (!$tree->isBranch($key)) throw new \ErrorException('ACC_NO_BRANCH: ' . $key);

		$data = $tree->getProjection();
		$this->_data = self::_useKey($data, $key);

		return $this;
	}

	public function selectSelf(string $key) : ITreeMutator {
		if (!$this->isBranch($key)) throw new \ErrorException('ACC_NO_BRANCH: ' . $key);

		$this->_data = self::_useKey($this->_data, $key);

		return $this;
	}


	public function insert(ITreeAccessor $tree, string $key) : ITreeMutator {
		$this->setItem($key, $tree->getProjection());

		return $this;
	}


	public function getProjection(array $selection = []) : array {
		return $this->_data;
	}
}
