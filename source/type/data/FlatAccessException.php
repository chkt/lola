<?php

namespace lola\type\data;



class FlatAccessException
extends \Exception
implements IAccessException
{

	private $_key;


	public function __construct(string $key) {
		parent::__construct('ACC_NO_PROP:' . $key);

		$this->_key = $key;
	}


	public function getMissingKey() : string {
		return $this->_key;
	}
}
