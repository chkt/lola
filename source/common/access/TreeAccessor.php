<?php

namespace lola\common\access;

use eve\common\access\IItemAccessor;



class TreeAccessor
implements IItemAccessor
{

	private $_data;


	public function __construct(array& $data) {
		$this->_data =& $data;
	}


	protected function _handleBranchException(TreeBranchException $ex) {
		return false;
	}

	protected function _handlePropertyException(TreePropertyException $ex) {
		return false;
	}


	final protected function& _useItem($key) {
		$data =& $this->_data;
		$segs = explode('.', $key);

		for ($i = 0, $l = count($segs); $i < $l; $i += 1) {
			$seg = $segs[$i];

			if (strlen($seg) === 0) throw new \ErrorException(sprintf('ACC invalid key "%s"', $key));

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
				$ex = new TreePropertyException(
					$data,
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


	public function hasKey(string $key) : bool {
		try {
			$this->_useItem($key);
		}
		catch (ITreeAccessorException $ex) {
			return false;
		}

		return true;
	}

	public function getItem(string $key) {
		return $this->_useItem($key);
	}
}
