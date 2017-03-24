<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;

use lola\input\valid\ValidationException;



final class IsIntEqual
extends AValidationTransform
{

	private $_value;


	public function __construct(int $value) {
		parent::__construct();

		$this->_value = $value;
	}


	public function getId() : string {
		return 'isIntEqual.' . $this->_value;
	}


	protected function _validate($source) {
		if (!is_int($source)) throw new ValidationException($this->getId() . '.noInt', 1);

		if ($source !== $this->_value) throw new ValidationException($this->getId() . '.notEq', 2);

		return $source;
	}
}
