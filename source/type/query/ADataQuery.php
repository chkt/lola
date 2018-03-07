<?php

namespace lola\type\query;

use eve\common\access\IItemAccessor;
use eve\common\access\exception\IAccessorException;



abstract class ADataQuery
implements IDataQuery
{

	private $_propertyMap;
	private $_operatorMap;

	private $_require;


	public function __construct(
		array $propertyMap,
		array $operatorMap
	) {
		$this->_propertyMap = $propertyMap;
		$this->_operatorMap = $operatorMap;

		$this->_require = [];
	}


	public function getRequirements() : array {
		return $this->_require;
	}

	public function setRequirements(array $require) : IDataQuery {
		$this->_require = $require;

		return $this;
	}


	private function _getPropertyNameOf(int $condition) : string {
		$map = $this->_propertyMap;

		if (!array_key_exists($condition, $map)) throw new \ErrorException();

		return $map[$condition];
	}

	private function _getOperatorNameOf(int $condition) : int {
		$map = $this->_operatorMap;

		return array_key_exists($condition, $map) ? $map[$condition] : self::OP_EQ;
	}


	private function _resolveCompare(int $op, $value, $test) : bool {
		switch ($op) {
			case self::OP_EQ : return $value === $test;
			case self::OP_NEQ : return $value !== $test;
			case self::OP_LT : return $value < $test;
			case self::OP_GT : return $value > $test;
			case self::OP_LTE : return $value <= $test;
			case self::OP_GTE : return $value >= $test;
			case self::OP_EXISTS : return $test;
			default : throw new \ErrorException();
		}
	}


	public function match(IItemAccessor $data) : bool {
		foreach ($this->_require as $cond => $test) {
			$prop = $this->_getPropertyNameOf($cond);
			$op = $this->_getOperatorNameOf($cond);

			try {
				$value = $data->getItem($prop);
			}
			catch (IAccessorException $ex) {
				if ($op === self::OP_EXISTS && $test !== true) continue;
				else return false;
			}

			if (!$this->_resolveCompare($op, $value, $test)) return false;
		}

		return true;
	}
}
