<?php

namespace lola\type;



final class NoPropertyException
extends \Exception
{
	
	const VERSION = '0.2.4';
	
	
	
	private $_resolved = null;
	private $_missing = null;
	
	
	
	public function __construct(array& $resolvedProp, array $missingPath) {
		parent::__construct("NOPROP:" . implode('.', $missingPath));
		
		$this->_resolved =& $resolvedProp;
		$this->_missing = $missingPath;
	}
	
	
	public function& useResolvedProperty() {
		return $this->_resolved;
	}
	
	public function getMissingProperty() {
		return implode('.' . $this->_missingPath);
	}
	
	public function getMissingPath() {
		return $this->_missing;
	}
}
