<?php

namespace lola\type;

use eve\common\projection\IProjectable;
use eve\common\access\ITraversableAccessor;



class StructuredData
implements ITraversableAccessor
{

	static public function& useKey(array& $data, $key) {
		if (!is_string($key)) throw new \ErrorException();
		
		$segs = explode('.', $key);
		
		for ($i = 0, $l = count($segs); $i < $l; $i += 1) {			
			$seg = $segs[$i];
			
			if (strlen($seg) === 0) throw new \ErrorException('INVSEG:' . $key);
			
			if (ctype_digit($seg)) $seg = (int) $seg;
			
			if (!is_array($data)) throw new \ErrorException('INVPROP:' . $key);
			else if (!array_key_exists($seg, $data)) throw new NoPropertyException($data, array_slice($segs, $i));
			
			$data =& $data[$seg];
		}
		
		return $data;
	}
	
	
	
	private $_data = null;
	
	
	public function __construct(array& $data) {
		$this->_data =& $data;
	}
	
	
	public function hasItem($key) {
		try {
			self::useKey($this->_data, $key);
		} catch (NoPropertyException $ex) {
			return false;
		}
		
		return true;
	}
	
	public function& useItem($key) {
		return self::useKey($this->_data, $key);
	}
	
	public function setItem($key, $value) {
		$item =& self::useKey($this->_data, $key);
		$item = $value;
		
		return $this;
	}
	
	public function addItem($key, $value) {
		try {
			self::useKey($this->_data, $key);
			
			throw new \ErrorException('HASPROP:' . $key);
		} catch (NoPropertyException $ex) {
			$prop =& $ex->useResolvedProperty();
			$path = $ex->getMissingPath();
		}
		
		foreach ($path as $seg) {
			$prop[$seg] = [];
			$prop =& $prop[$seg];
		}
		
		$prop = $value;
		
		return $this;
	}
	
	public function removeItem($key) {
		if (!is_string($key) || empty($key)) throw new \ErrorException();
		
		$index = strrpos($key, '.');
		
		if ($index === false) unset($this->_data[$key]);
		else {
			$path = substr($key, 0, $index);
			$prop = substr($key, $index + 1);
			
			if (empty($prop)) throw new \ErrorException('INVSEG:' . $key);
			
			unset(self::useKey($this->_data, $path)[$prop]);
		}
		
		return $this;
	}
	
	
	public function toArray() {
		return $this->_data;
	}


	public function isEqual(IProjectable $b) : bool {
		return $this->_data === $b->getProjection();
	}


	public function hasKey(string $key) : bool {
		return $this->hasItem($key);
	}


	public function getItem(string $key) {
		return $this->useItem($key);
	}


	public function& iterate() : \Generator {
		for(
			$keys = [], $k = [ array_keys($this->_data) ], $v = [ array_values($this->_data) ],
			$index = [ 0 ], $len = [ count($k) ], $last = 0;
			$last >= 0;
		) {
			$i = $index[$last]++;

			if ($i === $len[$last]) {
				array_pop($keys);
				array_pop($k);
				array_pop($v);
				array_pop($index);
				array_pop($len);
				$last -= 1;

				continue;
			}

			$key = $k[$last][$i];
			$item = $v[$last][$i];

			if (is_array($item)) {
				array_push($keys, $key);
				array_push($k, array_keys($item));
				array_push($v, array_values($item));
				array_push($index, 0);
				array_push($len, count($item));
				$last += 1;

				continue;
			}

			yield (implode('.', array_merge($keys, [ $key ]))) => $item;
		}
	}


	public function getProjection() : array {
		return $this->toArray();
	}
}
