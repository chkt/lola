<?php

namespace lola\type;

use lola\type\NoPropertyException;



class StructuredData
{
	
	const VERSION = '0.2.4';
	
	
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
}
