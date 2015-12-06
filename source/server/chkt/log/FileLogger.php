<?php

namespace chkt\log;

use chkt\log\ILogger;

use chkt\log\Colorizer;

use chkt\http\HttpRequest;
use chkt\http\HttpReply;



class FileLogger implements ILogger {	
	
	static protected function _valueToString($value) {
		if (is_null($value)) return 'null';
		else if (is_callable($value)) return 'callable';
		else if (is_string($value)) return "'$value'";
		else if (is_object($value)) return get_class($value);
		else if (is_array($value)) return 'Array';
		else return (string) $value;
	}
	
	
	static protected function _buildStackOrigin($offset) {
		$stack = debug_backtrace(0, $offset);
		$step = $stack[$offset - 1];
		
		$file = Colorizer::encode($step['file'], 'f-blue', 'bright');
		$line = $step['line'];
		
		return "$file:$line";
	}
	
	protected function _buildTraceOrigin(Array $trace) {
		$file = array_key_exists('file', $trace) ? Colorizer::encode($trace['file'], 'f-blue') : Colorizer::encode('?', 'f-red');
		$line = array_key_exists('line', $trace) ? $trace['line'] : Colorizer::encode('?', 'f-red');
		
		return "$file:$line";
	}
	
	
	
	static protected function _buildExceptionMessage(\Exception $ex) {
		$data = [];
		$data[] = Colorizer::encode('! ' . get_class($ex), 'f-red', 'bright');
		
		$code = $ex->getCode();
		$msg = $ex->getMessage();
		
		if (!empty($code)) $data[] = "[$code]";
		if (!empty($msg)) $data[] = "'$msg'";
		
		$data[] = 'IN';
		$data[] = Colorizer::encode($ex->getFile(), 'f-red') . ':' . $ex->getLine();
		
		return implode(' ', $data);
	}
	
	static protected function _buildTraceArguments(Array $args, Array $params = null) {
		$data = [];
		
		if (is_null($params)) $data[] = '?';
		else {
			foreach ($params as $key => $param) {
				$item = [];
				$item[] = "\$$param->name";
				
				if (array_key_exists($key, $args)) $item[] = self::_valueToString($args[$key]);
				else if ($param->isOptional()) $item[] = self::_valueToString($param->getDefaultValue());
				else $item[] = 'unset';
				
				$data[] = implode(' ', $item);
			}
		}		
		
		return '(' . implode(', ', $data) . ')';
	}
	
	
	static protected function _buildExceptionStack(\Exception $ex) {
		$trace = $ex->getTrace();
		$data = [];
		
		for ($i = 0, $l = count($trace); $i < $l; $i += 1) {
			$step = $trace[$i];
			$item = [];
			
			$item[] = Colorizer::encode((string) $i, 'f-red', 'bright');
			
			if (array_key_exists('class', $step)) {
				$item[] = Colorizer::encode($step['class'] . $step['type'] . $step['function'], 'f-red');
				
				$method = new \ReflectionMethod($step['class'], $step['function']);
				$params = $method->getParameters();
			}
			else {
				$item[] = Colorizer::encode($step['function'], 'f-red');
				
				try {
					$fn = new \ReflectionFunction($step['function']);
					$params = $fn->getParameters();
				} catch (\Exception $ex) {
					$params = null;
				}
			}
			
			$item[] = self::_buildTraceArguments($step['args'], $params);
			$item[] = self::_buildTraceOrigin($step);
			
			$data[] = implode(' ', $item);
		}
		
		return implode(PHP_EOL, $data);
	}
	
	
	
	public function log($str) {
		if (!is_string($str) || empty($str)) throw new \ErrorException();
		
		error_log($str);
		
		return $this;
	}
	
	public function logRequest(HttpRequest $request, $stackOffset = self::STACK_IGNORE) {
		$access = [];
		
		$access[] = Colorizer::encode('<', 'f-magenta', 'bright');
		$access[] = Colorizer::encode($request->getMethod(), 'bright');
		$access[] = Colorizer::encode($request->getPath(), 'f-green', 'bright');
		
		if ($stackOffset !== self::STACK_IGNORE) $access[] = self::_buildStackOrigin($stackOffset + 1);
		
		return $this->log(implode(' ', $access));
	}
	
	public function logClient($ip = self::IP_OR_UA) {
		$data = [];
		$data[] = Colorizer::encode('~', 'f-magenta', 'bright');
		
		$ua = HttpRequest::originClientUA();
		
		if (!empty($ua)) $data[] = $ua;
		
		if (
			$ip == self::IP_ALWAYS ||
			$ip === self::IP_OR_UA && empty($ua)
		) $data[] = Colorizer::encode('[' . HttpRequest::originClientIP() . ']', 'f-red', 'bright');
		
		return $this->log(implode(' ', $data));
	}
	
	public function logReply(HttpReply $reply, $stackOffset = self::STACK_IGNORE) {
		$access = [];
		$access[] = Colorizer::encode('>', 'f-magenta', 'bright');
		$access[] = Colorizer::encode($reply->getCodeMessage(), 'bright');
		
		if ($reply->isRedirect()) {
			$access[] = 'REDIRECT';
			$access[] = Colorizer::encode($reply->getRedirectTarget(), 'f-green', 'bright');
		}
		
		if ($stackOffset !== self::STACK_IGNORE) $access[] = self::_buildStackOrigin($stackOffset + 1);
		
		return $this->log(implode(' ', $access));
	}
	
	
	public function logException(\Exception $ex, $stack = true, $deep = true) {
		$data = [];
		$data[] = self::_buildExceptionMessage($ex);
		
		if ($deep) {
			while(!is_null($prev = $ex->getPrevious())) {
				$data[] = self::_buildExceptionMessage($prev);
				
				$ex = $prev;
			}
		}
		
		if ($stack) $data[] = self::_buildExceptionStack($ex);
		
		return $this->log(implode(PHP_EOL, $data));
	}
}
