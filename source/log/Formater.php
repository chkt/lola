<?php

namespace lola\log;

use lola\log\ILogger;



class Formater
{
	
	private $_scope = 0;
	
	private $_pair = null;
	
	
	public function __construct() {
		$this->_scope = 0;
		
		$this->_pair = [
			ILogger::TAG_ERROR_FILE => [
				ILogger::TAG_ERROR_LINE => ':',
				ILogger::TAG_STACK => PHP_EOL
			],
			ILogger::TAG_ERROR_LINE => [
				ILogger::TAG_STACK => PHP_EOL
			],
			ILogger::TAG_ERROR_SOURCE => [
				ILogger::TAG_ERROR_ARG_NAME => '(',
				ILogger::TAG_ERROR_ARG_VAL => '('
			],
			ILogger::TAG_ERROR_ARG_NAME => [
				ILogger::TAG_ERROR_ARG_NAME => ', ',
				ILogger::TAG_STACK_FILE => ') ',
				ILogger::TAG_STACK => ')' . PHP_EOL,
				ILogger::TAG_NONE => ')'
			],
			ILogger::TAG_ERROR_ARG_VAL => [
				ILogger::TAG_ERROR_ARG_NAME => ', ',
				ILogger::TAG_ERROR_ARG_VAL => ', ',
				ILogger::TAG_STACK_FILE => ') ',
				ILogger::TAG_STACK => ')' . PHP_EOL,
				ILogger::TAG_NONE => ')'
			],
			ILogger::TAG_STACK_FILE => [
				ILogger::TAG_STACK_LINE => ':',
				ILogger::TAG_STACK => PHP_EOL
			],
			ILogger::TAG_STACK_LINE => [
				ILogger::TAG_STACK => PHP_EOL
			],
			ILogger::TAG_SCOPE_OPEN => [
				ILogger::TAG_PROPERTY_KEY => PHP_EOL,
				ILogger::TAG_ARRAY_KEY => PHP_EOL,
				ILogger::TAG_SCOPE_CLOSE => ''
			],
			ILogger::TAG_SCOPE_CLOSE => [
				ILogger::TAG_PROPERTY_KEY => ',' . PHP_EOL,
				ILogger::TAG_ARRAY_KEY => ', ' . PHP_EOL,
				ILogger::TAG_SCOPE_CLOSE => PHP_EOL
			],
			ILogger::TAG_PROPERTY_KEY => [
				ILogger::TAG_PROPERTY_TYPE => ' : ',
				ILogger::TAG_PROPERTY_VALUE => ' : ',
				ILogger::TAG_SCOPE_OPEN => ' : '
			],
			ILogger::TAG_ARRAY_KEY => [
				ILogger::TAG_PROPERTY_VALUE => ' => ',
				ILogger::TAG_SCOPE_OPEN => ' => '
			],
			ILogger::TAG_PROPERTY_VALUE => [
				ILogger::TAG_PROPERTY_KEY => ',' . PHP_EOL,
				ILogger::TAG_ARRAY_KEY => ',' . PHP_EOL,
				ILogger::TAG_SCOPE_CLOSE => PHP_EOL
			]
		];
	}
	
	
	public function apply($prevType, $nextType = ILogger::TAG_NONE) {
		if ($prevType === ILogger::TAG_SCOPE_OPEN) $this->_scope += 1;
		if ($nextType === ILogger::TAG_SCOPE_CLOSE) $this->_scope -= 1;
				
		if (!array_key_exists($prevType, $this->_pair)) return ' ';
		
		$next = $this->_pair[$prevType];
		
		if (!array_key_exists($nextType, $next)) return ' ';
		
		return str_replace(PHP_EOL, PHP_EOL . str_pad('', $this->_scope * 2), $next[$nextType]);
	}
}
