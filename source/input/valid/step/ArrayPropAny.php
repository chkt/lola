<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;
use lola\input\valid\IValidationTransform;

use lola\input\valid\ValidationException;



final class ArrayPropAny
extends AValidationTransform
{

	private $_index;


	public function __construct(IValidationTransform& $next) {
		parent::__construct($next);

		$this->_index = -1;
	}


	public function getId() : string {
		return 'arrayPropAny';
	}


	protected function _validate($source) {
		if (!is_array($source)) throw new ValidationException($this->getId() . '.noArray', 1);

		if (array_values($source) !== $source) throw new ValidationException($this->getId() . '.notIndexed', 2);

		return $source;
	}

	protected function _transform($source, $result) {
		array_splice($source, $this->_index, 1, [ $result ]);

		return $source;
	}


	protected function _validateNextStep(IValidationTransform &$next, $source) {
		foreach ($source as $index => $item) {
			if (!$next->validate($item)->isValid()) continue;

			$this->_index = $index;

			return $this->_transform($source, $next->getResult());
		}

		throw new ValidationException($this->getId() . '.noProp', 3);
	}
}
