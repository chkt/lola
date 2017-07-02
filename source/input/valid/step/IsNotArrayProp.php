<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;

use lola\input\valid\ValidationException;



final class IsNotArrayProp
extends AValidationTransform
{

	private $_prop;


	public function __construct(string $prop) {
		if (empty($prop)) throw new \ErrorException();

		parent::__construct();

		$this->_prop = $prop;
	}


	public function getId() : string {
		return 'isNotArrayProp.' . $this->_prop;
	}


	protected function _validate($source) {
		if (!is_array($source)) throw new ValidationException($this->getId() . '.noArray', 1);

		if (array_key_exists($this->_prop, $source)) throw new ValidationException($this->getId() . '.exists', 2);

		return $source;
	}
}
