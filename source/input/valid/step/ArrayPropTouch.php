<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;
use lola\input\valid\IValidationTransform;

use lola\input\valid\ValidationException;



final class ArrayPropTouch
extends AValidationTransform
{

	private $_prop;


	public function __construct(string $prop, IValidationTransform $next = null) {
		if (empty($prop)) throw new \ErrorException();

		parent::__construct($next);

		$this->_prop = $prop;
	}


	public function getId() : string {
		return 'arrayPropTouch.' . $this->_prop;
	}


	protected function _validate($source) {
		if (!is_array($source)) throw new ValidationException($this->getId() . 'noarray', 1);

		if (!array_key_exists($this->_prop, $source)) $source[$this->_prop] = null;

		return $source;
	}
}
