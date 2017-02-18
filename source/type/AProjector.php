<?php

namespace lola\type;

use lola\type\IProjector;

use lola\type\StructuredData;



abstract class AProjector
implements IProjector
{

	private $_source;

	private $_keys;
	private $_transforms;


	public function __construct(StructuredData& $source, array $transforms = []) {
		$this->_source =& $source;

		$this->_keys = array_keys($transforms);
		$this->_transforms = array_values($transforms);
	}


	public function get(array $selection = null) : array {
		$keys = $this->_keys;
		$trns = $this->_transforms;
		$res = [];

		if (is_null($selection)) $selection = array_unique($keys);

		for ($i = 0, $l = count($keys); $i < $l; $i += 1) {
			if (!in_array($keys[$i], $selection)) continue;

			$ret = call_user_func_array($trns[$i], [ & $this->_source ]);
			$res = array_merge($res, $ret);
		}

		return $res;
	}
}
