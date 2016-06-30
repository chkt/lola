<?php

namespace lola\log;

use lola\log\ILogger;



class Formater
{
	
	private $_scope = 0;
	
	
	public function __construct() {
		$this->_scope = 0;
	}
	
	
	public function apply($prevType, $nextType) {
		if ($prevType === ILogger::TAG_SCOPE_OPEN) $this->_scope += 1;
		if ($nextType === ILogger::TAG_SCOPE_CLOSE) $this->_scope -= 1;
		
		if ($prevType === ILogger::TAG_NEWLINE) return str_pad('', $this->_scope * 2);
		
		switch ($nextType) {
			case ILogger::TAG_PROPERTY_TERMINATOR : return '';
		}
		
		return ' ';
	}
}
