<?php

namespace lola\input\valid\step;

use lola\input\valid\AValidationTransform;
use lola\input\valid\IValidationTransform;

use lola\input\valid\IValidationException;
use lola\input\valid\ValidationException;



final class Call
extends AValidationTransform
{

	private $_fn;


	public function __construct(callable $fn, IValidationTransform $next = null) {
		parent::__construct($next);

		$this->_fn = $fn;
	}


	public function getId() : string {
		return 'call';
	}


	protected function _validate($source) {
		try {
			$result = call_user_func($this->_fn, $source);
		}
		catch (IValidationException $ex) {
			throw $ex;
		}
		catch (\Exception $ex) {
			throw new ValidationException($this->getId() . '.error', 1);
		}

		return $result;
	}
}
