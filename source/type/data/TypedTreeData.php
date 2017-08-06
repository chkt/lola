<?php

declare(strict_types=1);
namespace lola\type\data;



class TypedTreeData
extends TreeData
implements IScalarMutator, ICompoundMutator
{

	public function isBool(string $key) : bool {
		try {
			return is_bool($this->useItem($key));
		}
		catch (IAccessException $ex) {
			return false;
		}
	}

	public function getBool(string $key) : bool {
		return $this->useItem($key);
	}

	public function setBool(string $key, bool $item) : IScalarAccessor {
		return $this->setItem($key, $item);
	}


	public function isInt(string $key) : bool {
		try {
			return is_int($this->useItem($key));
		}
		catch (IAccessException $ex) {
			return false;
		}
	}

	public function getInt(string $key) : int {
		return $this->useItem($key);
	}

	public function setInt(string $key, int $item) : IScalarAccessor {
		return $this->setItem($key, $item);
	}


	public function isFloat(string $key) : bool {
		try {
			return is_float($this->useItem($key));
		}
		catch (IAccessException $ex) {
			return false;
		}
	}

	public function getFloat(string $key) : float {
		return $this->useItem($key);
	}

	public function setFloat(string $key, float $item) : IScalarAccessor {
		return $this->setItem($key, $item);
	}


	public function isString(string $key) : bool {
		try {
			return is_string($this->useItem($key));
		}
		catch (IAccessException $ex) {
			return false;
		}
	}

	public function getString(string $key) : string {
		return $this->useItem($key);
	}

	public function setString(string $key, string $item) : IScalarAccessor {
		return $this->setItem($key, $item);
	}


	public function isArray(string $key) : bool {
		try {
			return is_array($this->useItem($key));
		}
		catch(IAccessException $ex) {
			return false;
		}
	}

	public function& useArray(string $key) : array {
		return $this->useItem($key);
	}

	public function setArray(string $key, array $item) : ICompoundAccessor {
		return $this->setItem($key, $item);
	}


	public function isInstance(string $key, string $qname) : bool {
		try {
			return $this->useItem($key) instanceof $qname;
		}
		catch (IAccessException $ex) {
			return false;
		}
	}

	public function& useInstance(string $key, string $qname) {
		$item =& $this->useItem($key);

		if (!($item instanceof $qname)) throw new \ErrorException('ACC_NO_INS:' . $key);

		return $item;
	}

	public function setInstance(string $key, $item) : ICompoundAccessor {
		if (!is_object($item)) throw new \ErrorException('ACC_NO_INS:' . $key);

		return $this->setItem($key, $item);
	}
}
