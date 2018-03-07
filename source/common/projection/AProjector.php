<?php

namespace lola\common\projection;

use eve\common\access\ITraversableAccessor;



abstract class AProjector
implements IProjector
{

	private $_source;

	private $_keys;
	private $_transforms;


	public function __construct(array $transforms = []) {
		$this->_source = null;

		$this->_keys = array_keys($transforms);
		$this->_transforms = array_values($transforms);
	}


	public function setSource(ITraversableAccessor $source) : IProjector {
		$this->_source = $source;

		return $this;
	}


	public function getProjection(array $selection = null) : array {
		if (is_null($this->_source)) throw new \ErrorException();

		$keys = $this->_keys;
		$trns = $this->_transforms;
		$res = [];

		if (is_null($selection)) $selection = $keys;

		for ($i = 0, $l = count($keys); $i < $l; $i += 1) {
			if (!in_array($keys[$i], $selection)) continue;

			$ret = call_user_func_array($trns[$i], [ $this->_source ]);
			$res = array_merge($res, $ret);
		}

		return $res;
	}
}
